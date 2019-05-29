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
			'supports' => array(''),
			'menu_icon' => 'dashicons-admin-post'
		);
		$this->parent->register_post_type('openedx_enrollment', 'Open edX Enrollment Requests', 'Open edX Enrollment Request', '', $enrollment_cpt_options);

		// Register the CPT actions
		$this->register_save_hook();

		add_action( 'init', array( $this, 'register_status'), 10, 3 );
	}

	function register_save_hook() {
		add_action( 'save_post', array( $this, 'save'), 10, 3 );
	}

	function unregister_save_hook() {
		remove_action( 'save_post', array( $this, 'save'), 10, 3 );
	}

	/**
	 * Creates specific status for the post type
	 *
	 * @return  void
	 */
	function register_status() {
		register_post_status( 'eor-success', array(
			'label' => __( 'Success', 'wp-edunext-marketing-site' ),
			'public' => false,
			'internal' => true,
			'private' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Success <span class="count">(%s)</span>', 'Success <span class="count">(%s)</span>', 'wp-edunext-marketing-site' ),
		) );
		register_post_status( 'eor-pending', array(
			'label' => __( 'Pending', 'wp-edunext-marketing-site' ),
			'public' => false,
			'internal' => true,
			'private' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'wp-edunext-marketing-site' ),
		) );
		register_post_status( 'eor-error', array(
			'label' => __( 'Error', 'wp-edunext-marketing-site' ),
			'public' => false,
			'internal' => true,
			'private' => true,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Error <span class="count">(%s)</span>', 'Error <span class="count">(%s)</span>', 'wp-edunext-marketing-site' ),
		) );
	}

	/**
	 * Wrapper for the WP function that prevents an infinite cycle of hook recursion
	 *
	 * @param array $post The post info in an array.
	 */
	function wp_update_post( $post ) {
		$this->unregister_save_hook();

		wp_update_post( $post );

		$this->register_save_hook();
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

		$oer_course_id = sanitize_text_field( $_POST['oer_course_id'] );
		$oer_email = sanitize_text_field( $_POST['oer_email'] );
		$oer_username = sanitize_text_field( $_POST['oer_username'] );
		$oer_mode = sanitize_text_field( $_POST['oer_mode'] );

		// We need to have all 3 required params to continue
		if ( ! $oer_course_id && ! $oer_username || ! $oer_mode ) return;

		$post_update = array(
			'ID' => $post_id,
			'post_title' => $oer_course_id . ' | ' . $oer_username . ' | Mode: ' . $oer_mode ,
		);
		// Only update the post status if it has no custom status yet
		if ( $post->post_status != 'eor-success'  && $post->post_status != 'eor-pending' && $post->post_status != 'eor-error' ) {
			$post_update['post_status'] = 'eor-pending';
		}
		$this->wp_update_post($post_update);

		// Update the $post metadata
		update_post_meta( $post_id, 'course_id', $oer_course_id );
		update_post_meta( $post_id, 'email', $oer_email );
		update_post_meta( $post_id, 'username', $oer_username );
		update_post_meta( $post_id, 'mode', $oer_mode );

		// Handle the eox-core API actions

		if ('oer_process' == $_POST['oer_action']) {
			$this->process_request($post_id, $post, false);
		}
		if ('oer_force' == $_POST['oer_action']) {
			$this->process_request($post_id, $post, true);
		}
		if ('oer_sync' == $_POST['oer_action']) {
			// Do the Sync
		}
	}

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param post $post The post object.
	 * @param bool $force Does this order need procesing by force?
	 */
	function process_request( $post_id, $post, $force ) {
		// Do something
	}


	/**
	 * Filters the list of actions available on the list view below each object
	 *
	 * @return actions
	 */
    function remove_table_row_actions( $actions ){

		unset($actions['edit']);
		unset($actions['trash']);
		unset($actions['view']);
		unset($actions['inline hide-if-no-js']);

        return $actions;
    }

	/**
	 * Prepare the site to work with the Enrollment object as a CPT
	 *
	 * @return void
	 */
	function set_up_admin() {

		// Extra info
		add_action( 'edit_form_after_title', array( $this, 'render_enrollment_info_form' ) );

		add_action( 'add_meta_boxes' , array( $this, 'replace_admin_meta_boxes' ) );

   		add_filter('post_row_actions', array( $this, 'remove_table_row_actions') );

	}

	/**
	 * @return void
	 */
	function replace_admin_meta_boxes() {
		remove_meta_box( 'submitdiv', $this->post_type, 'side' );

		add_meta_box( 'openedx_enrollment_request_actions', sprintf( __( '%s actions', '' ), 'Open edX Enrollment Requests' ), array( $this, 'render_actions_box' ), $this->post_type, 'side', 'high' );
	}

	/**
	 * Renders the actions box for the edit post box
	 *
	 * @return void
	 */
	public function render_actions_box() {
		?>
		<ul class="enrollment_actions submitbox">

			<li class="wide" id="actions">
				<select name="oer_action">
					<option value=""><?php esc_html_e( 'Choose an action...', 'wp-edunext-marketing-site' ); ?></option>
					<option value="oer_process"><?php esc_html_e( 'Process', 'wp-edunext-marketing-site' ); ?></option>
					<option value="oer_force"><?php esc_html_e( 'Process --force', 'wp-edunext-marketing-site' ); ?></option>
					<option value="oer_sync"><?php esc_html_e( 'Synchronize (pull)', 'wp-edunext-marketing-site' ); ?></option>
				</select>
				<button class="button wc-reload"><span><?php esc_html_e( 'Apply', 'wp-edunext-marketing-site' ); ?></span></button>
			</li>

			<li class="wide">
				<button type="submit" class="button save_order button-primary" name="save" value="save_no_process"><?php esc_html_e( 'Save without processing', 'wp-edunext-marketing-site' ); ?>
				</button>
			</li>

		</ul>
		<?php
	}

	/**
	 * Print openedx enrollment edit metabox
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_enrollment_info_form( $post ) {

		if ( $this->post_type != $post->post_type ) return;
		$post_id = $post->ID;

		?>
		<div id="namediv" class="postbox">
		<h2 class="">Open edX enrollment request</h2>
		<fieldset>
		<table class="form-table">
			<tbody>
				<tr>
					<td class="first"><label for="openedx_enrollment_course_id">course_id</label></td>
					<td>
						<input type="text" id="openedx_enrollment_course_id" name="oer_course_id"
						value="<?php echo(get_post_meta($post_id, 'course_id', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_email">email</label></td>
					<td>
						<input type="email" id="openedx_enrollment_email" name="oer_email"
						value="<?php echo(get_post_meta($post_id, 'email', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_username">username</label></td>
					<td>
						<input type="text" id="openedx_enrollment_username" name="oer_username"
						value="<?php echo(get_post_meta($post_id, 'username', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_mode">mode</label></td>
					<td>
						<input type="text" id="openedx_enrollment_mode" name="oer_mode"
						value="<?php echo(get_post_meta($post_id, 'mode', true)); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_is_active">is_active</label></td>
					<td>
						<input type="checkbox" id="openedx_enrollment_is_active" name="oer_is_active" style="width: auto;">
					</td>
				</tr>
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
