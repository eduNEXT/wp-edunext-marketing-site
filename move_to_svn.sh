#!/bin/bash

# mkdir svn
# cd svn
# svn checkout https://plugins.svn.wordpress.org/edunext-openedx-integrator

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
# svn add trunk/*
# svn ci -m 'Adding version 1.1.0 to the trunk' --username felipemontoya

# Create a tag
# mkdir tags/1.1.0
# cp -r trunk/* tags/1.1.0
# svn add tags/1.1.0/
# svn ci -m 'Adding version 1.1.0 to the tags' --username felipemontoya