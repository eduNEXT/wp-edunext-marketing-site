# BUILD SCRIPTS
## DESCRIPTION

Build scripts to facilitate the development of the edunext-openedx-integrator plugin.

Targets:
- build: Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
- change-to: Change development environment to pro or lite version.
- dev-version: Print current version of dev environment.
- clean-env: Clean development environment, delete symlinks that change in every version.
- tidy: Remove build directories.

### VERSIONS

Files that provide specific functionality to each version of the plugin live under the path 'versions/<VERSION>'.

When the change-to target is invoked, it creates symlinks inside the 'includes' folder pointing to the correct version in the 'versions' folder.

e.g
```bash
make change-to-VERSION

dir
- includes/
--> (SYMLINK) class-wp-edunext-eox-core-api.php -> ../versions/VERSION/class-wp-edunext-eox-core-api.php

```

## USAGE

Read more in:
```bash
make help
```
Example:
```bash
make build
```

## INSTALL

Inside a virtualenv, run:

```bash
pip3 install -r scripts/requirements.txt
```

## CONFIG

It is possible to set custom settings for the plugin in the scripts/config.ini file.

## DEVELOPMENT WORKFLOW

The directories and files outside of "versions" and "scripts"  folders are the development environment, the changes that you make here will be reflected in your local WordPress env. You can use this to test a version and develop features for the plugin or specific versions of the plugin.

There are some  aspects to be considered that will make your life easier as a developer :

- When changing versions using the "make change-to" command always make sure to restart the WordPress container if you're using docker, because the files tend to be cached and the change will be reflected in the folder but not on the running WordPress.

- Read the config.ini file in the scripts folder to have a sense of where general files and specific version files should be.

- To develop a new version-specific feature you have to add the file in the proper directory and then run the "make change-to-lite" command, to have the file in the dev environment.

    e.g
    pro feature: paypal integration -> class-wp-edunext-eox-paypal-integration.php

    ```bash
    edunext-openedx-integrator
    ├── versions
    │   ├── lite
    ...
    │   └── pro
    │       ├── class-wp-edunext-eox-core-api.ph
            ├── class-wp-edunext-eox-paypal-integration.php <-----
    ```


- To develop a new version of the plugin, this meaning a new variant such as "MEGA" or "LITE_lightweight" not a new number version like 2.1.9, you should add the correct name version in the config.ini section [Versions] and the folder containing all the files in the folder with the same name.

    e.g
    ```bash
    scripts/config.ini
    ...
    [Versions]
    # Versions of the plugin
    lite
    pro
    MEGA <----
    ```


    ```bash
    edunext-openedx-integrator/
    ├── versions
    │   ├── lite
    ...
    │   └── pro
    ...
    ...
    │   └── MEGA <----
    ```

- To add a folder with features for all versions outside of the folders already included remember to add the file in the [Content] section of the config.ini file.

    e.g
    contact_forms/

    ```bash
    edunext-openedx-integrator
    ├── contact_forms/    <-----
    ```

    ```bash
    scripts/config.ini
    ...
    [Content]
    # Files and folders to be included in the build process
    # key: file or folder name
    LICENSE
    readme.txt
    *.php
    assets
    contact_forms <----
    ...
    ```



- To commit to the repository always be sure to be in the pro version in that way we avoid constant changes of symlinks to be taken as actual changes in code so we'll have a more consistent codebase.

- If something looks iffy you can use the "make clean-env" to clean the dev environment and check only the common files that are present in all the versions, this empty env most likely won't work in the WordPress env, but it can be used with "git diff" to make sure you're editing the appropriate files for the version