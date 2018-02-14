<?php
/*
 * Plugin Name: WP_eduNEXT_Marketig_Site
 * Version: 1.0
 * Plugin URI: https://www.edunext.co/
 * Description: This is your easy integration to open edX marketing sites
 * Author: Felipe Montoya
 * Author URI: https://www.edunext.co/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wp-edunext-marketig-site
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Felipe Montoya
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
 * Returns the main instance of WP_eduNEXT_Marketig_Site to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_eduNEXT_Marketig_Site
 */
function WP_eduNEXT_Marketig_Site () {
	$instance = WP_eduNEXT_Marketig_Site::instance( __FILE__, '1.0.1' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = WP_eduNEXT_Marketig_Site_Settings::instance( $instance );
	}

	return $instance;
}

WP_eduNEXT_Marketig_Site();