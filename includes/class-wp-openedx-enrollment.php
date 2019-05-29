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
			'supports' => array( 'title', 'revisions'),
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

		// Update the $post metadata

		update_post_meta( $post_id, 'course_id', sanitize_text_field( $_POST['oe_course_id'] ) );
		update_post_meta( $post_id, 'bundle_id', sanitize_text_field( $_POST['oe_bundle_id'] ) );
		update_post_meta( $post_id, 'email', sanitize_text_field( $_POST['oe_email'] ) );
		update_post_meta( $post_id, 'username', sanitize_text_field( $_POST['oe_username'] ) );
		update_post_meta( $post_id, 'mode', sanitize_text_field( $_POST['oe_mode'] ) );

		// Cant deal with bools right now

		// if ( isset( $_POST['oe_is_active'] ) ) {
		// 	update_post_meta( $post_id, 'is_active', TRUE );
		// } else {
		// 	update_post_meta( $post_id, 'is_active', FALSE );
		// }

		// if ( isset( $_POST['oe_force'] ) ) {
		// 	update_post_meta( $post_id, 'force', TRUE );
		// } else {
		// 	update_post_meta( $post_id, 'force', FALSE );
		// }

		// Here we can connect the API update logic

	}

	/**
	 * Prepare the site to work with the Enrollment object as a CPT
	 *
	 * @return void
	 */
	function set_up_admin() {

		// Extra info
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
	}

	/**
	 * Print openedx enrollment edit metabox
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function edit_form_after_title( $post ) {

		if ( $this->post_type != $post->post_type ) return;
		$post_id = $post->ID;

		?>
		<div id="namediv" class="postbox">
		<h2 class="">Open edX enrollment information</h2>
		<fieldset>
		<table class="form-table">
			<tbody>
				<tr>
					<td class="first"><label for="openedx_enrollment_course_id">course_id</label></td>
					<td>
						<input type="text" id="openedx_enrollment_course_id" name="oe_course_id"
						value="<?php echo(get_post_meta($post_id, 'course_id', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_bundle_id">bundle_id</label></td>
					<td>
						<input type="text" id="openedx_enrollment_bundle_id" name="oe_bundle_id"
						value="<?php echo(get_post_meta($post_id, 'bundle_id', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_email">email</label></td>
					<td>
						<input type="email" id="openedx_enrollment_email" name="oe_email"
						value="<?php echo(get_post_meta($post_id, 'email', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_username">username</label></td>
					<td>
						<input type="text" id="openedx_enrollment_username" name="oe_username"
						value="<?php echo(get_post_meta($post_id, 'username', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_mode">mode</label></td>
					<td>
						<input type="text" id="openedx_enrollment_mode" name="oe_mode"
						value="<?php echo(get_post_meta($post_id, 'mode', true)); ?>">
					</td>
				</tr>
				<!--
				<tr>
					<td class="first"><label for="openedx_enrollment_is_active">is_active</label></td>
					<td>
						<input type="checkbox" id="openedx_enrollment_is_active" name="oe_is_active" style="width: auto;">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_force">force</label></td>
					<td>
						<input type="checkbox" id="openedx_enrollment_force" name="oe_force" style="width: auto;">
					</td>
				</tr>
				 -->
			</tbody>
		</table>
		</fieldset>
		</div>
		<?php
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
