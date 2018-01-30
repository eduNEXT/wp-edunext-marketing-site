<?php
/*
 * Plugin Name: WP-eduNEXT-Marketig-Site
 * Version: 1.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: This is your starter template for your next WordPress plugin.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wp-edunext-marketig-site
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-wp-edunext-marketig-site.php' );
require_once( 'includes/class-wp-edunext-marketig-site-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-wp-edunext-marketig-site-admin-api.php' );
require_once( 'includes/lib/class-wp-edunext-marketig-site-post-type.php' );
require_once( 'includes/lib/class-wp-edunext-marketig-site-taxonomy.php' );

/**
 * Returns the main instance of WP-eduNEXT-Marketig-Site to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP-eduNEXT-Marketig-Site
 */
function WP-eduNEXT-Marketig-Site () {
	$instance = WP-eduNEXT-Marketig-Site::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WP-eduNEXT-Marketig-Site_Settings::instance( $instance );
	}

	return $instance;
}

WP-eduNEXT-Marketig-Site();