=== Open edX LMS and WordPress integrator ===
Contributors: eduNEXT
Tags: wordpress, Open edX, LMS
Requires at least: 4.0
Tested up to: 5.2
Stable tag: 2.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


This plugin makes it easy to integrate a Wordpress site with an Open edX LMS provided by eduNEXT or with your own Open edX installation.


== Description ==

This plugin helps you set up your Wordpress site as the front-end site or marketing site for your online learning initiative powered by the Open edX platform.

The idea behind this integration is to use the greater flexibility of Wordpress for the content management pages and leave the open edX only to be used once the user logs in to visit his dashboard or courses.

In these integrations, Wordpress will typically be used for:

- The site's the homepage
- The course catalog page
- The pages that describe each of the courses
- Any additional static pages that the initiative requires

This plugin is made available by eduNEXT, a world class open edX services provider. The plugin is initially tuned to work against an open edX site provided by eduNEXT, but it can also be used to integrate Wordpress and any Open edX site.

The integration between open edX and Wordpress currently works at 3 different places:

1) In the wordpress navigation menu.
By adding a menu to your Wordpress site that allows users to log in / register to the Open edX site. Once the user has logged in, this menu becomes aware of the session and transforms to a user menu with all the options the Open edX user menu has.

2) At the page / post content level to link posts with courses.
By adding a Course button to a particular page or post, it can be used as a course description page, where all the information of the course can be stored and from which the user can access their courses based on the course settings and the learner state.
This button is added using a shortcode, and it takes care of rendering the correct action depending on the configuration that the course has for that particular user on the Open edX site.

3) At the static pages level.
Open edX includes some static pages for things like the Terms of Service, Privacy Policy, Honor coude and others, but it has very little capabilities for managing these web contents. The integration allows to host this content directly in wordpress and make sure that the traffic going to each of these static destinations reaches the right page.

Some additional integrations are currently under development. If you require a different kind of integration, or professional services to optimize your Open edX platform, contact eduNEXT at https://eduNEXT.co


== Installation ==

= Automatic Installation =

1. Go to the Plugins menu from the dashboard.
2. Click on the "Add New" button on this page.
3. Search for "Open edX LMS and WordPress integrator" in the search bar provided.
4. Click on "Install Now" once you have located the plugin.
5. On successful installation click the "Activate Plugin" link to activate the plugin.

= Manual Installation =

1. Download the "Open edX LMS and WordPress integrator" plugin from wordpress.org.
2. Now unzip and upload the folder using the FTP application of your choice.
3. The plugin can then be activated by navigating to the Plugins menu in the admin dashboard.


== Frequently Asked Questions ==

= What does this integrator do? =

This plugin allows to:
-Share the user session information between Open edX and Wordpress
-Add an Open edX user menu to the Wordpress navigation structure
-Add Course buttons to the Wordpress Posts or Pages that represent each course with the corresponding action and caption based on the on the Open edX course settings
-Integrate Woocommerce as the ecommerce solution to sale access to open edX courses
-Make use of the EOX-core API that extends the functionality of Open edX

= How is the integration configured? =

Changes have to be made in advance on the Open edX side and then the settings can be configured in the WP side using the plugin interface.

= How to do the Open edX User Menu Configuration? =

To create an Open edX menu:

1. Go to Appearance > Menus
2. On the accordion item called "Open edX WP Integrator", select from the list the menu-items you want to include in your menu. Press Add to Menu.
3. Organize the items in your menu.

The list of menu items you can use includes:

- Login/User Menu:
    If the user is logged in, the menu will display the name of the user with a link to the user dashboard in the LMS.
    Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged-out user>/<This will be replaced by the user name>

- Login/Dashboard:
    If the user is logged in, the menu will display the configured label with a link to the dashboard of the LMS.
    Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged-out user>/<Label displayed for the logged out user>

- Login Button:
    A menu item, with a link to the login page. If the user is already logged in, nothing will appear.

- Register Button:
    A menu item, with a link to the register page. If the user is already logged in, nothing will appear.

- User Menu:
    A menu item, with a link to the dashboard page using the username as the label. If the user is not logged in, this item will not appear.

- Resume your last course:
    A link to the last known location of a user in his or her courses. If the user is not logged in, this item will not appear.

- Dashboard:
    A link to the user dashboard. If the user is not logged in, this item will not appear.

- Profile:
    A link to the user profile page. If the user is not logged in, this item will not appear.

- Account:
    A link to the user account settings page. If the user is not logged in, this item will not appear.

- Sign Out:
    A link to a page that will log the user out. If the user is not logged in, this item will not appear.


= How to integrate access to Open edX Courses from the Wordpress pages or posts? =

Buttons to enroll or in general take any action call on the courses are produced using the `edunext_enroll_button` shortcode.

The simplest example is using the shortcode giving it only the course_id. E.g.:

    [edunext_enroll_button course_id="course-v1:organization+coursenumber+courserun"]

To configure any of the settings per-button, you can also change the setting of any setting defined in the settings page specifically for a particular shortcode.
In the Post / Page editor you\'ll be able to find a help box with all configuration possibilities for these shortcodes.

E.g: To change the label from "Enroll" which is the default, to "Enroll in the course now" you can use:
    [edunext_enroll_button course_id="course-v1:organization+coursenumber+courserun" label_enroll="Enroll in the course now"]

== Screenshots ==
1. General Settings
2. Configuration of the User Menu
3. Addition of the User Menu
4. Configuration of the course buttons
5. Addition of course buttons
6. Woocommerce integration
7. EOX - API backend

== Changelog ==

= 2.0 =
* 2019-06-01
* Improved internal documentation
* Improved arrangement of the different settings
* Added capabilities to store and manage Open edX enrollment requests


= 1.5 =
* 2018-12-16
* Adding some additional capabilities to the navigation menu integration


= 1.1 =
* 2018-04-16
* Adding the navigation menu integration

= 1.0 =
* 2018-01-20
* Initial release

== Upgrade Notice ==

= 1.0 =
* 2018-01-20
* Initial release

== Translations ==
* English - default, always included

Note: All the strings in this plugins are localized / translateable by default. In case you need assistance with a particular localization issue, or want to contribute with one traslation, please reach out to eduNEXT (https://edunext.co)