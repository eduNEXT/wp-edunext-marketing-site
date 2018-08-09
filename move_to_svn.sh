#!/bin/bash

# mkdir svn
# cd svn
# svn checkout https://plugins.svn.wordpress.org/edunext-openedx-integrator


# First make sure you changed the version numbers in
# readme.txt :: Stable tag: m.x.p

# Moving files to the svn trunk
cp index.php svn/edunext-openedx-integrator/trunk/
cp LICENSE svn/edunext-openedx-integrator/trunk/
cp readme.txt svn/edunext-openedx-integrator/trunk/
cp uninstall.php svn/edunext-openedx-integrator/trunk/
cp wp-edunext-marketing-site.php svn/edunext-openedx-integrator/trunk/

cp -r assets svn/edunext-openedx-integrator/trunk/
cp -r includes svn/edunext-openedx-integrator/trunk/
cp -r lang svn/edunext-openedx-integrator/trunk/


# cd svn/edunext-openedx-integrator
# svn add trunk/*    # only new files
# svn ci -m 'Adding version 1.3.0 to the trunk' --username felipemontoya

# Create a tag
# svn cp trunk tags/1.3.0 --parents
# svn ci -m 'Adding version 1.3.0' --username felipemontoya
