<h1><?php echo __( 'Configurations for the Woocommerce integration', 'wp-edunext-marketing-site' ); ?></h1>
<p><?php echo __( 'In a WordPress - Woocommerce - Open edX integrated scenario, everytime a user purchases a product that represents as course, the corresponding enrollment for that user in that course will be created in Open edX.', 'wp-edunext-marketing-site' ); ?></p>
<h2><?php echo __( 'On the Open edX side:', 'wp-edunext-marketing-site' ); ?></h2>
<p><?php echo __( 'This type of integration requires some aditions to the Open edX installation that are exclusive for eduNEXT\'s customers.', 'wp-edunext-marketing-site' ); ?></p>
<p><?php echo __( 'If you are using one of eduNEXT\'s <strong><a href="https://www.edunext.co/cloud/" target="_blank">Open edX cloud subscriptions</a></strong>, or <strong><a href="https://www.edunext.co/self-hosting/" target="_blank">Open edX on premise support plans</a></strong> you can simply request assistance to the eduNEXT support team to make sure the integration is properly configured on the Open edX side.', 'wp-edunext-marketing-site' ); ?></p>
<p><?php echo __( 'If you are running Open edX on your own, contact <strong><a href="https://www.edunext.co/consulting/" target="_blank">eduNEXT</a></strong> to ask for professional support.', 'wp-edunext-marketing-site' ); ?></p>
<h2><?php echo __( 'On the Woocommerce side:', 'wp-edunext-marketing-site' ); ?></h2>
<p><?php echo __( 'You can configure your site to sale access to Open edX courses.', 'wp-edunext-marketing-site' ); ?></p>
<p><?php echo __( 'For each course, you\'ll need to create one product and add 2 custom field to it:', 'wp-edunext-marketing-site' ); ?></p>
<ul>
    <li><?php echo __( '<strong>course_id</strong>: The exact value of the course id in open edX, for example: "course-v1:organization+mumber+run".', 'wp-edunext-marketing-site' ); ?></li>
    <li><?php echo __( '<strong>course_mode</strong>: The exact value of the course mode that your enrollment should be set to, for example: "honor".', 'wp-edunext-marketing-site' ); ?></li>
</ul>
