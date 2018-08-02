<?php
/*
 * Plugin Name: Open edX LMS and Wordpress integrator
 * Version: 1.2
 * Description: Set up your Wordpress site as the front end site or marketing site for your online learning initiative powered by the open edX platform.
 * Author: eduNEXT
 * Author URI: https://www.edunext.co/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wp-edunext-marketing-site
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Felipe Montoya
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-wp-edunext-marketing-site.php' );
require_once( 'includes/class-wp-edunext-marketing-site-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wp-edunext-marketing-site-admin-api.php' );
require_once( 'includes/lib/class-wp-edunext-marketing-site-post-type.php' );
require_once( 'includes/lib/class-wp-edunext-marketing-site-taxonomy.php' );
require_once( 'includes/lib/class-wp-edunext-marketing-site-menu.php' );
require_once( 'includes/eox-core.php' );


/**
 * Returns the main instance of WP_eduNEXT_Marketing_Site to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_eduNEXT_Marketing_Site
 */
function WP_eduNEXT_Marketing_Site () {
	$instance = WP_eduNEXT_Marketing_Site::instance( __FILE__, '1.2.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WP_eduNEXT_Marketing_Site_Settings::instance( $instance );
	}

	return $instance;
}

WP_eduNEXT_Marketing_Site();
