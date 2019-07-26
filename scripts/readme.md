# BUILD SCRIPTS
## DESCRIPTION

Build scripts to facilitate the development of the edunext-openedx-integrator plugin.

Targets:
- build: Generate a zip file for each version of the plugin (pro and lite) ready to be deployed to WordPress.
- change-to: Change development environment to pro or lite version.
- dev-version: Print current version of dev environment

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
