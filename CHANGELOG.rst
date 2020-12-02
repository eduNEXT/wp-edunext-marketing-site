Changelog
=========
..
   All enhancements and patches to the plugin will be documented
   in this file.  It adheres to the structure of http://keepachangelog.com/.

   This project adheres to Semantic Versioning (http://semver.org/).
.. There should always be an "Unreleased" section for changes pending release.

Unreleased
----------

2.6.1 - 2020-11-25
----------------

Fixed
~~~~~
- Fix automatic refresh of the eox api authentication token.
- Fix missing error messages.
- Checkout client-side prefilling: now properly reads mappings to the
  ``extended_profile``.
- Checkout server-side prefilling: now is possible to map any field in the Open
  edX user profile, instead of only those on the ``extended_profile``.
- Checkout server-side prefilling: there's no longer an *invisible* set of
  default mappings that's impossible to change.

Removed
~~~~~~~
- ``PHP Name Parser`` external library.

2.6.0 - 2019-12-16
----------------

Changed
~~~~~~~
- Pinned the PHP Code Sniffer rule set for Circle CI.

Fixed
~~~~~
- Fix handling of multiple products when a non related product is found.

2.5.0 - 2019-10-07
------------------

Changed
~~~~~~~
- Now display an error message for partially processed requests.

2.4.0 - 2019-09-10
------------------

Added
~~~~~
- Instructions on how to enable and use WordPress Debugging tools.
- Warning when the zip file exceeds 1MB in size
- Release script for publishing on the WordPress site

Changed
~~~~~~~
- The WordPress site only attempts to logout if the user is currently logged in.
