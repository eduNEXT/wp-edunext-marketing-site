<?php
/**
 * EOX Core API
 */
class WP_EoxCoreApi

{

	/**
	 *
	 */
	private $notices = array();

	/**
	 *
	 */
	private $error_notices = array();

	/**
	 * Default values used to create a new edxapp user
	 */
	private $defaults = array(
		'email' => '',
		'username' => '',
		'password' => '',
		'fullname' => '',
		'is_active' => False,
		'is_staff' => False,
		'is_superuser' => False,
		'activate_user' => False,
	);

	private static $_instance;
	/**
	 * Main WP_EoxCoreApi Instance
	 *
	 * Ensures only one instance of WP_EoxCoreApi is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @static
	 * @see WP_EoxCoreApi()
	 * @return Main WP_EoxCoreApi instance
	 */
	public static function instance($file = '', $version = '1.2.0') {
		if (is_null(self::$_instance)) {
			self::$_instance = new self($file, $version);
		}

		return self::$_instance;
	} // End instance ()

	/**
	 *
	 */
	public function show_notices() {
		$notices = array_merge($this->notices, $this->error_notices);
		foreach ($notices as $message) {
			?>
			<div class="<?= $message['type'] ?> notice">
		        	<p><?= __($message['message'], 'eox-core-api') ?></p>
		    </div>
		    <?php
		}
	}

	/**
	 *
	 */
	function __construct() {
		if ( is_admin() ) {
			add_filter('wp-edunext-marketing-site_settings_fields', array($this, 'add_admin_settings'));
			add_action('admin_notices', array($this, 'show_notices'));
			add_action('eoxapi_after_settings_page_html', array($this, 'eoxapi_settings_custom_html'));
			add_action('wp_ajax_save_users_ajax', array($this, 'save_users_ajax'));
		}
	}

	/**
	 * Hook to add a complete tab in the plugin setting's page
	 */
	public function add_admin_settings($settings) {
		$settings['eoxapi'] = array(
			'title' => __('EOX API', 'wp-edunext-marketing-site') ,
			'description' => __('These settings modify the way to interact with the eox api.', 'wp-edunext-marketing-site') ,
			'fields' => array(
				array(
					'id' => 'eox_client_id',
					'label' => __('Client id', 'wp-edunext-marketing-site') ,
					'description' => __('Client id of the open edX instance API.', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => ''
				) ,
				array(
					'id' => 'eox_client_secret',
					'label' => __('Client secret', 'wp-edunext-marketing-site') ,
					'description' => __('Client secret of the open edX instance API.', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => ''
				)
			)
		);
		return $settings;
	}

	/**
	 * Renders the custom form in the admin page
	 */
	public function eoxapi_settings_custom_html() {
		include('templates/exoapi_settings_custom_html.php');
	}

	/**
	 *
	 */
	public function save_users_ajax() {
		check_ajax_referer('eoxapi');
		$new_users = json_decode($_POST['users']);

		if (is_null($new_users)) {
			$new_users = json_decode(stripslashes($_POST['users']));
		}

		if (is_array($new_users)) {
			foreach ($new_users as $user) {
				$this->eox_create_new_user($user);
			}
		} else if (is_null($new_users)) {
			$this->add_notice('error', 'Cannot parse as JSON, make sure to enter a valid JSON');
		} else {
			$this->add_notice('error', 'An array is needed, got ' . gettype($new_users) . ' instead');
		}
		$this->show_notices();
		wp_die();
	}

	/**
	 * Produce an authentication token for the eox api using oauth 2.0
	 */
	public function get_access_token() {
		$token = get_option('wpt_eox_token', '');
		$base_url = get_option('wpt_lms_base_url', '');
		if ($token !== '') {
			$url = $base_url . '/oauth2/access_token/' . $token . '/';
			$response = wp_remote_post($url);
			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$this->add_notice('error', $error_message);
				$error = new WP_Error('broke', $error_message, $response);
				return $error;
			}

			$json_reponse = json_decode($response['body']);
			if (!isset($json_reponse['error'])) {
				// Cached token its still valid, return it
				return $token;
			}
		}

		$client_id = get_option('wpt_eox_client_id', '');
		$client_secret = get_option('wpt_eox_client_secret', '');
		$args = array(
			'body' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'grant_type' => 'client_credentials'
			)
		);
		$url = $base_url . '/oauth2/access_token/';
		$response = wp_remote_post($url, $args);
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			$this->add_notice('error', $error_message);
			$error = new WP_Error('broke', __("Couldn't call the API to get a new", "eox-core-api") , $response);
		}

		$token_details = json_decode($response['body']);
		$token = $token_details->access_token;
		update_option('wpt_eox_token', $token);
		return $token;
	}

	/**
	 * Function to execute the API calls required to make a new edxapp user
	 */
	public function eox_create_new_user($args) {
		$token = $this->get_access_token();
		if (!is_wp_error($token)) {

			$data = wp_parse_args($args, $this->defaults);
			$base_url = get_option('wpt_lms_base_url', '');
			$url = $base_url . '/eox-core/api/v1/user/';
			$response = wp_remote_post($url, array(
				'headers' => 'Authorization: Bearer ' . $token,
				'body' => $data
			));
			$ref = $data['email'] ?: $data['username'] ?: $data['fullname'];
			if ($response['response']['code'] !== 200) {
				$response_json = json_decode($response['body']);
				$this->handle_api_errors($response_json, $ref);
			}
			else {
				$this->add_notice('notice-success', 'User creation success! <i>(' . $ref . ')</i>');
			}
		}
	}

	/**
	 *
	 */
	public function handle_api_errors($json, $ref) {
		if (isset($json->non_field_errors)) {
			foreach ($json->non_field_errors as $value) {
				$this->add_notice('error', $value . ' (' . $ref . ')');
			}
		}
		foreach (array_keys($this->defaults) as $key) {
			if (isset($json->$key)) {
				foreach ($json->$key as $value) {
					$this->add_notice('error', ucfirst($key) . ': ' . $value . ' <i>(' . $ref . ')</i>');
				}
			}
		}
	}

	/**
	 *
	 */
	public function add_notice($type, $message) {
		$notice = array(
			'type' => $type,
			'message' => $message
		);
		if ($type === 'error') {
			array_push($this->error_notices, $notice);
		} else {
			array_push($this->notices, $notice);
		}
	}
}
