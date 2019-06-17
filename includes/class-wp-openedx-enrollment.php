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
			'show_in_menu' => $this->parent->_token . '_settings',
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
		$oer_request_type = sanitize_text_field( $_POST['oer_request_type'] );

		// We need to have all 3 required params to continue
		$oer_user_reference = $oer_email || $oer_username;
		if ( ! $oer_course_id || ! $oer_user_reference || ! $oer_mode ) return;

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

		if ($oer_request_type === 'enroll') {
			update_post_meta( $post_id, 'is_active', true );
		}
		if ($oer_request_type === 'unenroll') {
			update_post_meta( $post_id, 'is_active', false );
		}

		// Handle the eox-core API actions

		if ('oer_process' == $_POST['oer_action']) {
			$this->process_request($post_id, false);
		}
		if ('oer_force' == $_POST['oer_action']) {
			$this->process_request($post_id, true);
		}
		if ('oer_sync' == $_POST['oer_action']) {
			$this->sync_request( $post_id);
		}
	}

	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param bool $force Does this order need processing by force?
	 */
	function process_request( $post_id, $force, $do_pre_enroll = true ) {

		$user_args= $this->prepare_args($post_id, 'user');
		$user = WP_EoxCoreApi()->get_user_info($user_args);

		// If the user doesn't exist create pre-enrollment with the email provided
		if (is_wp_error($user) && $do_pre_enroll) {
			if (!empty($user_args['email'])) {
				$pre_enrollment_args= $this->prepare_args($post_id, 'pre-enrollment');
				$this->create_pre_enrollment($post_id, $pre_enrollment_args);
				return;
			} else {
				// TODO Polish error message display
				update_post_meta($post_id, 'errors', 'A valid username or email is needed.');
				$this->wp_update_post( $post_update );
				$this->update_post_status('eor-error', $post_id);
				return;
			}
		}
		$enrollment_args= $this->prepare_args($post_id, 'enrollment');
		$enrollment_args['force'] = $force;

		$enrollment = WP_EoxCoreApi()->get_enrollment($enrollment_args);

		// If the enrollment already exists update it
		if (is_wp_error($enrollment)) {
			$this->create_enrollment($post_id, $enrollment_args);
		} else {
			$this->update_enrollment($post_id, $enrollment_args);
		}
	}

	/**
	 * Prepare args to be passed to the api calls
	 * @param int post_id  The post ID
	 * @param string type The args type to be prepared (user, enrollment, ..)
	 * @param bool force in the case of post
	 *
	 * @return array args args ready to be pass
	 */
	function prepare_args( $post_id, $type) {

		$args = array();
		$user_args = array(
			'email' => get_post_meta($post_id, 'email', true),
			'username' => get_post_meta($post_id, 'username', true),
		);

		$enrollment_args = array(
			'course_id' => get_post_meta($post_id, 'course_id', true),
		);

		$enrollment_opts_args = array(
			'mode' => get_post_meta($post_id, 'mode', true),
			'is_active' => (get_post_meta($post_id, 'is_active', true)? 1: 0),
		);

		switch ($type) {
			case 'user':
				return $user_args;
			case 'enrollment':
				return array_merge($user_args, $enrollment_args, $enrollment_opts_args);
			case 'pre-enrollment' or 'basic enrollment':
				return array_merge($user_args, $enrollment_args);
		}

		return $args;
	}

	/**
	 * Update post metadata when a post is synced.
	 *
	 * @param int $post_id The post ID.
	 */
	function sync_request( $post_id) {

		$args = $this->prepare_args($post_id, 'basic enrollment');
		$response = WP_EoxCoreApi()->get_enrollment($args);

		if (is_wp_error($response)) {
			update_post_meta($post_id, 'errors', $response->get_error_message());

			# Update Status
			$this->update_post_status('eor-error', $post_id);

		} else {
			delete_post_meta($post_id, 'errors');

			// Only this fields can be updated
			update_post_meta($post_id, 'mode',  $response->mode);
			update_post_meta($post_id, 'is_active',  $response->is_active);
			# Update Status
			$this->update_post_status('eor-success', $post_id);
		}
	}


	/**
	 * Create enrollment.
	 *
	 * @param int $post_id The post ID
	 * @param array $args The request parameters to be sent to the api
	 */
	function create_enrollment( $post_id, $args) {

		$response = WP_EoxCoreApi()->create_enrollment( $args );

		if (is_wp_error($response)) {
			update_post_meta($post_id, 'errors', $response->get_error_message());
			$status = 'eor-error';
		} else {
			delete_post_meta($post_id, 'errors');
			$status = 'eor-success';
		}

		$this->update_post_status($status, $post_id);
	}

	/**
	 * Create pre-enrollment.
	 *
	 * @param int $post_id The post ID
	 * @param array $args The request parameters to be sent to the api
	 */
	function create_pre_enrollment( $post_id, $args) {
		$response = WP_EoxCoreApi()->create_pre_enrollment( $args );

		if (is_wp_error($response)) {
			update_post_meta($post_id, 'errors', $response->get_error_message());
			$status = 'eor-error';
		} else {
			update_post_meta($post_id, 'errors', 'The provided user does not exist. A pre-enrollment with the provided email was created instead. ');
			$status = 'eor-success';
		}
		$this->update_post_status($status, $post_id);
	}

	/**
	 * Update post status
	 *
	 * @param string $status The status of the request
	 * @param int $post_id The post ID
	 */
	function update_post_status( $status, $post_id) {
		$post_update = array(
			'ID' => $post_id,
			'post_status' => $status,
		);
		$this->wp_update_post($post_update);
	}


	/**
	 * Update enrollment.
	 *
	 * @param int $post_id The post ID.
	 * @param array $args The request parameters to be sent to the api
	 */
	function update_enrollment( $post_id, $args) {
		$response = WP_EoxCoreApi()->update_enrollment($args);
		if (is_wp_error($response)) {
			update_post_meta($post_id, 'errors', $response->get_error_message());
			$this->update_post_status('eor-error', $post_id);
		} else {
			delete_post_meta($post_id, 'errors');
			update_post_meta($post_id, 'mode',  $response->mode);
			update_post_meta($post_id, 'is_active',  $response->is_active);
			$this->update_post_status('eor-success', $post_id);
		}
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
	 * Adds the cpt columns to the list view
	 *
	 * @return array $column
	 */
	function add_columns_to_list_view( $column ) {
		$column['oer_status'] = 'Status';
		$column['oer_type'] = 'Type';
		return $column;
	}

	/**
	 * Fills the values of the custom columns in the list view
	 *
	 * @return void
	 */
	function fill_custom_columns_in_list_view( $column_name, $post_id ) {
		switch ($column_name) {
			case 'oer_status' :
				if ( get_post( $post_id )->post_status == 'eor-success' ) echo '<b style="color:green;">Success</b>';
				if ( get_post( $post_id )->post_status == 'eor-error' ) echo '<b style="color:red;">Error</b>';
				if ( get_post( $post_id )->post_status == 'eor-pending' ) echo '<b style="color:orange;">Pending</b>';
				break;
			case 'oer_type' :
				if ( get_post_meta($post_id, 'is_active', true) ) {
					echo 'Enroll';
				}
				else {
					echo 'Unenroll';
				}
				break;
			default:
		}
	}

	/**
	 * Prepare the site to work with the Enrollment object as a CPT
	 *
	 * @return void
	 */
	function set_up_admin() {

		// Edit view
		add_action( 'edit_form_after_title', array( $this, 'render_enrollment_info_form' ) );
		add_action( 'add_meta_boxes' , array( $this, 'replace_admin_meta_boxes' ) );

		// List view
		add_filter('post_row_actions', array( $this, 'remove_table_row_actions') );
		add_filter( 'manage_posts_custom_column', array( $this, 'fill_custom_columns_in_list_view' ), 10, 3  ) ;
		add_filter( 'manage_openedx_enrollment_posts_columns', array( $this, 'add_columns_to_list_view' ) ) ;
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

		$course_id = get_post_meta($post_id, 'course_id', true);
		$email = get_post_meta($post_id, 'email', true);
		$username = get_post_meta($post_id, 'username', true);
		$mode = get_post_meta($post_id, 'mode', true);
		$is_active = get_post_meta($post_id, 'is_active', true);

		$new_oer = false;
		if ( !$course_id && !$email && !$username) $new_oer = true;
		?>
		<div id="namediv" class="postbox">
		<h2 class="">Open edX enrollment request</h2>
		<fieldset>
		<input type="hidden" name="new_oer" value="<?php echo($new_oer) ?>">
		<table class="form-table">
			<tbody>
				<tr>
					<td class="first"><label for="openedx_enrollment_course_id">Course ID</label></td>
					<td>
						<input type="text" id="openedx_enrollment_course_id" name="oer_course_id"
						<?php if (!$new_oer) echo(" readonly"); ?>
						value="<?php echo($course_id); ?>">
					</td>
				</tr>
				<tr>
					<td class="first"><label>User</label></td>
					<td>
						<div style="width: 49%; display: inline-table;">
							<label for="openedx_enrollment_username">Username:</label>
							<input type="text" id="openedx_enrollment_username" name="oer_username"
							title="You only need to fill one. Either the email or username"
							<?php if (!$new_oer) echo(" readonly"); ?>
							value="<?php echo($username); ?>">
						</div>
						<div style="width: 49%; display: inline-table;">
							<label for="openedx_enrollment_email">Email:</label>
							<input type="email" id="openedx_enrollment_email" name="oer_email"
							<?php if (!$new_oer) echo(" readonly"); ?>
							title="You only need to fill one. Either the email or username"
							value="<?php echo($email); ?>">
						</div>
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_mode">Course Mode</label></td>
					<td>
						<select id="openedx_enrollment_mode" name="oer_mode">
							<option value="honor" <?php if ($mode == 'honor') echo('selected="selected"'); ?>><?php esc_html_e( 'Honor', 'wp-edunext-marketing-site' ); ?></option>
							<option value="audit" <?php if ($mode == 'audit') echo('selected="selected"'); ?>><?php esc_html_e( 'Audit', 'wp-edunext-marketing-site' ); ?></option>
							<option value="verified" <?php if ($mode == 'verified') echo('selected="selected"'); ?>><?php esc_html_e( 'Verified', 'wp-edunext-marketing-site' ); ?></option>
							<option value="credit" <?php if ($mode == 'credit') echo('selected="selected"'); ?>><?php esc_html_e( 'Credit', 'wp-edunext-marketing-site' ); ?></option>
							<option value="professional" <?php if ($mode == 'professional') echo('selected="selected"'); ?>><?php esc_html_e( 'Professional', 'wp-edunext-marketing-site' ); ?></option>
							<option value="no-id-professional" <?php if ($mode == 'no-id-professional') echo('selected="selected"'); ?>><?php esc_html_e( 'No ID Professional', 'wp-edunext-marketing-site' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="first"><label for="openedx_enrollment_is_active">Request type</label></td>
					<td>

						<select id="openedx_enrollment_is_active" name="oer_request_type">
							<option value="enroll" <?php if ($is_active or $new_oer) echo('selected="selected"'); ?>><?php esc_html_e( 'Enroll', 'wp-edunext-marketing-site' ); ?></option>
							<option value="unenroll" <?php if (!$is_active and !$new_oer) echo('selected="selected"'); ?>><?php esc_html_e( 'Un-enroll', 'wp-edunext-marketing-site' ); ?></option>
						</select>

					</td>
				</tr>

				<?php if (get_post_meta($post_id, 'errors', true)): ?>
				<!-- Temporal display of errors, TODO: move this to a polished div  -->
				<tr>
					<td class="first"><label for="openedx_enrollment_errors">Errors</label></td>
					<td>
						<p><?php echo(get_post_meta($post_id, 'errors', true)); ?></p>
					</td>
				</tr>
				<?php else: ?>
					<td class="first"><label for="openedx_enrollment_errors">Operation log</label></td>
					<td>
						<p>No errors ocurred processing this request</p>
					</td>
				<?php endif; ?>
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
