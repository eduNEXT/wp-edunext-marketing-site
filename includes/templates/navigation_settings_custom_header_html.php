<h1><?php echo __( 'Configuration of your Open edX user menu in WordPress', 'wp-edunext-marketing-site' ); ?></h1>
<p><?php echo __( 'In a WordPress - Open edX integrated scenario, the user can go back and forth between the WordPress and the open edX applications and the experience should be as seamless as possible.', 'wp-edunext-marketing-site' ); ?></p>
<p><?php echo __( 'The idea here is to add to your WordPress Navigation the components that will allow the users to go to Open edX in case they are not logged in, or that will reproduce the Open edX user menu for users that are already logged in.', 'wp-edunext-marketing-site' ); ?></p>

<h2><?php echo __( '1. Visit "Appearance -> Menus"', 'wp-edunext-marketing-site' ); ?></h2>
<h2><?php echo __( '2. Look for the "Open edX WP integrator" box on the left column to select the menu-items you want to include in your user menu.', 'wp-edunext-marketing-site' ); ?></h2>
<h2><?php echo __( '3. Organize the items in your menu as needed and save the changes.', 'wp-edunext-marketing-site' ); ?></h2>

<p><?php echo __( 'The list of menu items you can use includes:', 'wp-edunext-marketing-site' ); ?></p>
<ul>
<li><?php echo __( '<strong>Login/User Menu</strong>: If the user is logged in, the menu will display the name of the user with a link to the user dashboard in the LMS.  Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged-out user>/<This will be replaced by the user name>', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>Login/Dashboard</strong>: If the user is logged in, the menu will display the configured label with a link to the dashboard of the LMS. Otherwise it will display a link to login, with the label provided. To change the label, you can edit the menu item in place. Be sure to follow the convention <Label displayed for logged-out user>/<Label displayed for the logged out user>', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>Login Button</strong>: A menu item, with a link to the login page. If the user is already logged in, nothing will appear.', 'wp-edunext-marketing-site' ); ?></li>   
<li><?php echo __( '<strong>Register Button</strong>: A menu item, with a link to the register page. If the user is already logged in, nothing will appear.', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>User Menu</strong>: A menu item, with a link to the dashboard page using the username as the label. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>Resume your last course</strong>: A link to the last known location of a user in his or her courses. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ); ?></li>   
<li><?php echo __( '<strong>Dashboard</strong>: A link to the user dashboard. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>Profile</strong>: A link to the user profile page. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>Account</strong>: A link to the user account settings page. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ); ?></li>
<li><?php echo __( '<strong>Sign Out</strong>: A link to a page that will log the user out. If the user is not logged in, this item will not appear.', 'wp-edunext-marketing-site' ); ?></li>
</ul>
<h4><?php echo __( 'Advanced Navigation Settings', 'wp-edunext-marketing-site' ); ?></h4>
<p><?php echo __( 'You normally don\'t need to make any changes here', 'wp-edunext-marketing-site' ); ?></p>
