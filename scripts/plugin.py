#!/usr/bin/env python
# -*- coding: utf-8 -*-
import argparse
from os import mkdir, path, symlink, mknod, remove, unlink, link
from shutil import rmtree, copy, copytree, make_archive
import glob
import sys
import configparser
""""
Build scripts to facilitate the development of the edunext-openedx-integrator plugin.

Targets:
- build: Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
- change-to: Change development environment to pro or lite version.
- release: Create the new version of the plugin (bump version).
- dev-version: Print current version of dev environment
"""

class Plugin():
    """
    Provides tools to build, release and develop the plugin
    """

    def __init__(self):
        """
        Read script settings from the config file
        """
        config = configparser.RawConfigParser(allow_no_value=True, inline_comment_prefixes="#")
        config.read(r'scripts/config.ini')
        self.VERSIONS = config['Versions'].keys()
        self.COMMON = config['Common']
        self.PATHS = config['Paths']
        self.INCLUDE = config['Include'].keys()

    def tidy(self):
        """
        Remove build directories
        """
        build_folder = self.COMMON['build_folder']
        rmtree(build_folder, ignore_errors=True)

    def build(self):
        """
        Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
        """
        import pudb; pudb.set_trace()
        build_folder = self.COMMON['build_folder']
        plugin_name = self.COMMON['basename']
        path_includes = self.INCLUDE
        versions_folder = self.COMMON['versions_folder']
        mkdir(build_folder)

        for version in self.VERSIONS:
            version_name = "{}-{}".format(plugin_name, version)
            path_zip = path.join(build_folder, version_name)
            version_path = path.abspath(path_zip)
            mkdir(version_path)

            # Include common files of the plugin
            for dir in path_includes:
                if path.isfile(dir):
                    copy(dir, version_path)
                elif path.isdir(dir):
                    copytree(dir, path.join(version_path, dir))
                else:
                    for file in glob.glob(dir):
                        copy(file, version_path)

            include_path = path.join(version_path, "includes")

            # Include specific version files
            dirpath = "{}/{}/*.php".format(versions_folder,version)
            for file in glob.glob(dirpath):
                copy(file, include_path)
            zip_name = path.join(build_folder, version_name)
            make_archive(zip_name, "zip", path_zip)

    def clean_links(self, folder_path):
        """Clean symlinks that change in every version"""
        for version in self.VERSIONS:
            dirpath = "{}/*.php".format(version)
            for file in glob.glob(dirpath):
                path_to_file = path.join(folder_path, path.basename(file))
                if path.islink(path_to_file):
                    unlink(path_to_file)

    def change_to(self, version):
        """
        Change development environment to pro or lite version.
        """
        clean_links("includes")
        dirpath = "{}/*.php".format(version)
        for file in glob.glob(dirpath):
            symlink('../'+file, path.join("includes", path.basename(file)))

    def get_version(self):
        """
        Return current development environment version
        """
        for version in self.VERSIONS:
            if path.isfile(".{}".format(version)):
                return version

if __name__== "__main__":
    plugin = Plugin()

parser = argparse.ArgumentParser(description="Build scripts to facilitate the development of the edunext-openedx-integrator plugin.")
parser.add_argument("-b", "--build", action="store_true", help="Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.")
parser.add_argument("-c", "--change-to", choices=plugin.VERSIONS, help="Change development environment to pro or lite version.")
parser.add_argument("-d", "--dev-version", action="store_true", help="Print current version of dev environment.")
parser.add_argument("-t", "--tidy", action="store_true", help="Remove build directories" )
args = parser.parse_args()

if args.build:
    plugin.build()

if args.dev_version:
    print plugin.get_version()

if args.change_to:
    plugin.change_to(args.change_to)

if args.tidy:
    plugin.tidy()

if len(sys.argv) == 1:
    parser.print_help()
