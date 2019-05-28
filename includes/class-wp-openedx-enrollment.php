<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Openedx_Enrollment {

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.9.0
	 */
	public $parent = null;

	/**
	 * The name for the Open edX enrollment custom post type.
	 * @var 	string
	 * @access  public
	 * @since 	1.9.0
	 */
	public $post_type = "openedx_enrollment";

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.9.0
	 * @return  void
	 */
	public function __construct ( $parent ) {
		$this->parent = $parent;

		// Add the custom post type
		$enrollment_cpt_options = array(
			'public' => false,
			'hierarchical' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => true,
			'supports' => array( 'title', 'custom-fields', 'revisions'),
			'menu_icon' => 'dashicons-admin-post'
		);
		$this->parent->register_post_type('openedx_enrollment', 'Open edX Enrollments', 'Open edX Enrollment', '', $enrollment_cpt_options);

		// Register the CPT actions
		add_action( 'save_post', array( $this, 'save'), 10, 3 );
	}

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	function save( $post_id, $post, $update ) {

		if ( $this->post_type != $post->post_type ) return;

		// Here we can connect the API update logic
	}

	/**
	 * Main WP_Openedx_Enrollment Instance
	 *
	 * Ensures only one instance of WP_Openedx_Enrollment is loaded or can be loaded.
	 *
	 * @static
	 * @see WP_Openedx_Enrollment()
	 * @return Main WP_Openedx_Enrollment instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
