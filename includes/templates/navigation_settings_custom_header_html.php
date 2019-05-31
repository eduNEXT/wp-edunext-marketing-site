<h1><?= __( 'Configuration of your Open edX user menu in Wordpress' , 'wp-edunext-marketing-site' ) ?></h1>
<p><?= __( 'In a Wordpress - Open edX integrated scenario, the user can go back and forth between the Wordpress and the open edX applications and the experience should be as seamless as possible.', 'wp-edunext-marketing-site' ) ?></p>
<p><?= __( 'The idea here is to add to your Wordpress Navigation the components that will allow the users to go to Open edX in case they are not logged in, or that will reproduce the Open edX user menu for users that are already logged in.', 'wp-edunext-marketing-site' ) ?></p>

<h2><?= __( '1. Visit "Appearance -> Menus"', 'wp-edunext-marketing-site' ) ?></h2>
<h2><?= __( '2. Look for the "Open edX WP integrator" box on the left column to select the menu-items you want to include in your user menu.', 'wp-edunext-marketing-site' ) ?></h2>
<h2><?= __( '3. Organize the items in your menu as needed and save the changes.', 'wp-edunext-marketing-site' ) ?></h2>

<p><?= __( 'The list of menu items you can use includes:', 'wp-edunext-marketing-site' ) ?></p>
<ul>
<li><?= __( '<strong>Login/User Menu</strong>: If the user is logged in, the menu will display the name of the user with a link to the user dashboard in the LMS.  Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged-out user>/<This will be replaced by the user name>', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>Login/Dashboard</strong>: If the user is logged in, the menu will display the configured label with a link to the dashboard of the LMS. Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged-out user>/<Label displayed for the logged out user>', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>Login Button</strong>: A menu item, with a link to the login page. If the user is already logged in, nothing will appear.', 'wp-edunext-marketing-site' ) ?></li>	
<li><?= __( '<strong>Register Button</strong>: A menu item, with a link to the register page. If the user is already logged in, nothing will appear.', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>User Menu</strong>: A menu item, with a link to the dashboard page using the username as the label. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>Resume your last course</strong>: A link to the last known location of a user in his or her courses. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ) ?></li>	
<li><?= __( '<strong>Dashboard</strong>: A link to the user dashboard. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>Profile</strong>: A link to the user profile page. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>Account</strong>: A link to the user account settings page. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ) ?></li>
<li><?= __( '<strong>Sign Out</strong>: A link to a page that will log the user out. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ) ?></li>
</ul>
<h4><?= __( 'Advanced Navigation Settings', 'wp-edunext-marketing-site' ) ?></h4>
<p><?= __( 'You normally don\'t need to make any changes here', 'wp-edunext-marketing-site' ) ?></p>
