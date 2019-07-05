#!/usr/bin/env python
# -*- coding: utf-8 -*-
import argparse
from os import mkdir, path, symlink, mknod, remove, unlink, link, readlink
from shutil import rmtree, copy, copytree, make_archive
import glob
import sys
import configparser
""""
Build scripts to facilitate the development of the edunext-openedx-integrator plugin.

Targets:
- build: Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
- change-to: Change development environment to pro or lite version.
- dev-version: Print current version of dev environment
"""
CONFIG_FILE = r'scripts/config.ini'

class Plugin():
    """
    Provides tools to build, release and develop the plugin
    """

    def __init__(self):
        """
        Read script settings from the config file
        """
        config = configparser.RawConfigParser(allow_no_value=True, inline_comment_prefixes="#")
        config.read(CONFIG_FILE)
        self.VERSIONS = config['Versions'].keys()
        self.COMMON = config['Common']
        self.CONTENT = config['Content'].keys()
        self.build_folder = self.COMMON['build_folder']
        self.folder_basename = self.COMMON['folder_basename']
        self.plugin_name = self.COMMON['plugin_name']
        self.path_includes = self.CONTENT
        self.versions_folder = self.COMMON['versions_folder']
        self.include_folder = self.COMMON['includes_folder']
        self.versioning_files = config['Versioning'].keys()

    def tidy(self):
        """
        Remove build directories
        """
        rmtree(self.build_folder, ignore_errors=True)

    def build(self):
        """
        Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
        """
        mkdir(self.build_folder)

        for version in self.VERSIONS:
            version_name = "{}-{}".format(self.folder_basename, version)
            path_zip = path.join(self.build_folder, version_name)
            version_path = path.abspath(path_zip)
            mkdir(version_path)

            # Include common files of the plugin
            for dir in self.path_includes:
                if path.isfile(dir):
                    copy(dir, version_path)
                elif path.isdir(dir):
                    copytree(dir, path.join(version_path, dir))
                else:
                    for file in glob.glob(dir):
                        copy(file, version_path)

            include_path = path.join(version_path, self.include_folder)

            # Include specific version files
            dirpath = "{}/{}/*.php".format(self.versions_folder,version)
            for file in glob.glob(dirpath):
                copy(file, include_path)

            # Insert correct plugin name - version
            for file in self.versioning_files:
                file_path = path.join(version_path, file)
                with open(file_path, "r+") as f:
                    versioned_text = f.read()
                    versioned_text = versioned_text.replace(self.plugin_name, '{} ({})'.format(self.plugin_name, version.upper()))
                    f.seek(0)
                    f.write(versioned_text)
                    f.truncate()

            zip_name = path.join(self.build_folder, version_name)
            make_archive(zip_name, "zip", path_zip)

    def clean_env(self):
        """
        Clean development environment, delete symlinks that change in every version
        """
        for version in self.VERSIONS:
            dirpath = "{}/{}/*.php".format(self.versions_folder, version)
            for file in glob.glob(dirpath):
                path_to_file = path.join(self.include_folder, path.basename(file))
                if path.islink(path_to_file):
                    unlink(path_to_file)

    def change_to(self, version):
        """
        Change development environment to pro or lite version.
        """
        version_path = path.join(self.versions_folder, version)
        self.clean_env()

        dirpath = "{}/*.php".format(version_path)
        for file in glob.glob(dirpath):
            symlink( '../{}'.format(file), path.join(self.include_folder, path.basename(file)))

    def get_version(self):
        """
        Return current development environment version
        """
        include_path = self.COMMON['includes_folder']
        dirpath = "{}/*.php".format(include_path)

        for file in glob.glob(dirpath):
            if path.islink(file):
                link_path = readlink(file)
                for version in self.VERSIONS:
                    if version in link_path:
                        return version

        return 'Unstable or empty development environment'

if __name__== "__main__":
    plugin = Plugin()

parser = argparse.ArgumentParser(description="Build scripts to facilitate the development of the edunext-openedx-integrator plugin.")
parser.add_argument("-b", "--build", action="store_true", help="Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.")
parser.add_argument("-c", "--change-to", choices=plugin.VERSIONS, help="Change development environment to pro or lite version.")
parser.add_argument("-d", "--dev-version", action="store_true", help="Print current version of dev environment.")
parser.add_argument("-t", "--tidy", action="store_true", help="Remove build directories" )
parser.add_argument("-e", "--clean-env", action="store_true", help="Clean development environment, delete symlinks that change in every version" )
args = parser.parse_args()

if args.build:
    plugin.build()

if args.dev_version:
    print plugin.get_version()

if args.change_to:
    plugin.change_to(args.change_to)

if args.tidy:
    plugin.tidy()

if args.clean_env:
    plugin.clean_env()

if len(sys.argv) == 1:
    parser.print_help()
