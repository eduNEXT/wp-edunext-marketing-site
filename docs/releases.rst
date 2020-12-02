Release instructions
====================

.. _quickstart:

Quickstart
~~~~~~~~~~

This project project provides a helper script in order to facilitate the
building process of the target files:

.. code-block:: console

    $ make build

Running this command will generate zip files for the lite and pro versions of
the plugin.
You can find this file on the ``build/`` directory.
You can use this zip files to manually install de plugin.

If you want a quick overview of which commands are available you can run

.. code-block:: console

    $ make help
    build

.. _introduction:

Introduction
~~~~~~~~~~~~

The eduNEXT Open edX integrator plugin comes in two versions a *lite* version
available for free on the WordPress Plugin Directory and a fully featured *pro*
version, available only to eduNEXT clients. Most of the time you are going to
be working on the pro version as it includes all the features the plugin is
capable of.

To check which version files you are working on your development environment
run:

.. code-block:: console

    $ make dev-version
    pro

What the current working version actually means is that some files on the 
``includes/`` directory are symlinked to the ``versions/pro/`` or
``versions/lite/`` directories. In case some of this files are missing you are
probably going to get a message such as follows:

.. code-block:: console

    $ make dev-version
    Unstable or empty development environment

To fix this change your development environment to the pro version, or the lite
version if appropriate:

.. code-block:: console

    $ make change-to-pro

This will restore the symlinks that are missing.

.. _release:

Release
~~~~~~~

When you are ready to release a new version of the plugin the first step is to
bump the current version. For that you will need to manually update the version
number of the plugin on three files: ``scripts/config.ini``, ``readme.txt`` and
``wp-edunext-marketing-site.php``.

Remember that this projects follows Semantic Versioning, for more information
on how to increase the version number check `semver.org <https://semver.org>`_.

Check that you have changed all the necessary files by running:

.. code-block:: console

    $ make verify-sem-version
    python scripts/plugin.py -s
    Semantic version number is correct across versioned files.


To delete the build folder alongside its contents run:

.. code-block:: console

    $ make tidy

Both ``verify-sem-version`` and ``tidy`` are run whenever you invoke
``make build`` but can always be invoked independently if needed.

There's also a Makefile target in case you want to automatically
publish a new version of the lite plugin to the Wordpress.org 
Plugin Directory:

.. code-block:: console

    $ make release

This will run the ``build`` command and additionally will run
``scripts/release.sh`` which in turn will run several ``svn`` commands in order
to update the plugin. For more information on how to use the remote Subversion
repository provided by Wordpress check this chapter on the
`Plugin Handbook <https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/>`_.

.. note::

    The release command assumes that you have the correct credentials for the
    svn repository. In case the command fails, try to manually run the commands
    on the ``release.sh`` file and commit to the svn repo using the
    ``--username`` flag with the correct user.
