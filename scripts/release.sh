#!/bin/bash

# Read configuration from config.ini
source <(grep = <(grep -A6 '\[Common\]' scripts/config.ini))

if [ ! -d $release_folder ]; then
    mkdir $release_folder;
    svn checkout $repo_url  $release_folder;
fi

svn update $release_folder

# Check the new release tag is different from the last tag
last_tag=$(svn ls "${repo_url}/tags" | tail -1)

if [ $last_tag == "${sem_version}/" ]; then
    echo "The semantic version ${sem_version} is the same as the latest upstream tag ${last_tag}, please update your sem_version in config.ini";
    exit 2;
fi

# Update remote trunk and tags
cp -r "${build_folder}/${folder_basename}-lite/"* "${release_folder}/trunk/"
svn add "${release_folder}/trunk/"* --force # recurse into versioned directories
svn ci -m "Update trunk to version ${sem_version}" ${release_folder}
svn cp "${release_folder}/trunk" "${release_folder}/tags/${sem_version}"
svn ci -m "Add version ${sem_version}" ${release_folder}
