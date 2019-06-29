#!/usr/bin/env python
import argparse
from os import mkdir, path, symlink, mknod, remove, unlink, link
from shutil import rmtree, copy, copytree, make_archive
import glob
""""
Build scripts to facilitate the development of the edunext-openedx-integrator plugin.

Targets:
- build: Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
- change-to: Change development environment to pro or lite version.
- release: Create the new version of the plugin (bump version).
- dev-version: Print current version of dev environment
"""

versions = ["pro", "lite"]


def build():
    """
    Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
    """
    build_dir = "build"
    dirname = "edunext-openedx-integrator"
    folders = ["assets", "includes", "lang"]
    rmtree(build_dir, ignore_errors=True)
    path_zip = path.join(build_dir, dirname)
    pathname= path.abspath(path_zip)
    mkdir(build_dir)
    mkdir(pathname)

    copy("LICENSE", pathname)
    copy("readme.txt", pathname)
    for file in glob.glob(r'*.php'):
        copy(file, pathname)
    copy("readme.txt", pathname)
    # Copy folders
    for folder in folders:
        copytree(folder, "{}/{}".format(pathname, folder))

    include_path = path.join(pathname, "includes")

    for version in versions:
        clean_files(include_path)
        dirpath = "{}/*.php".format(version)
        for file in glob.glob(dirpath):
            copy(file, include_path)
        zip_name = "{}/{}-{}".format(build_dir, dirname, version)
        make_archive(zip_name, "zip", path_zip)

def clean_files(folder_path):
    """Clean files that change in every version"""
    for version in versions:
        dirpath = "{}/*.php".format(version)
        for file in glob.glob(dirpath):
            path_to_file = path.join(folder_path, path.basename(file))
            if path.isfile(path_to_file):
                remove(path_to_file)

def change_to(version):
    """
    Change development environment to pro or lite version.
    """
    for _version in versions:
            file = ".{}".format(_version)
            if path.isfile(file):
                remove(file)

    clean_files("includes")
    dirpath = "{}/*.php".format(version)
    for file in glob.glob(dirpath):
        link(file, path.join("includes", path.basename(file)))

    mknod(".{}".format(version))

def get_version():
    """
    Return current development environment version
    """
    for version in versions:
        if path.isfile(".{}".format(version)):
            return version


parser = argparse.ArgumentParser(description="Build scripts to facilitate the development of the edunext-openedx-integrator plugin.")
parser.add_argument("-b", "--build", action="store_true", help="Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.")
parser.add_argument("-c", "--change-to", choices=versions, help="Change development environment to pro or lite version.")
parser.add_argument("-d", "--dev-version", action="store_true", help="Print current version of dev environment.")
args = parser.parse_args()

if args.build:
    build()

if args.dev_version:
    print get_version()

if args.change_to:
    change_to(args.change_to)