<?php
/**
 * EOX Core API
 */
class WP_EoxCoreApi

{
	private $user_messages = array();
	private static $_instance;
	/**
	 * Main WP_EoxCoreApi Instance
	 *
	 * Ensures only one instance of WP_EoxCoreApi is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_EoxCoreApi()
	 * @return Main WP_EoxCoreApi instance
	 */
	public static function instance($file = '', $version = '1.0.1') {
		if (is_null(self::$_instance)) {
			self::$_instance = new self($file, $version);
		}

		return self::$_instance;
	} // End instance ()
	
	public function show_messages() {
		foreach ($this->user_messages as $message) {
			?>
			<div class="<?= $message['type'] ?> notice">
		        	<p><?= __($message['message'], 'eox-core-api') ?></p>
		    </div>
		    <?php
		}
	}

	function __construct() {
		add_filter('wp-edunext-marketing-site_settings_fields', array($this, 'add_admin_settings'));
		add_action('admin_notices', array($this, 'show_messages'));
	}

	public function add_admin_settings($settings) {
		$settings['eoxapi'] = array(
			'title' => __('EOX API', 'wp-edunext-marketing-site') ,
			'description' => __('These settings modify the way to interact with the eox api.', 'wp-edunext-marketing-site') ,
			'fields' => array(
				array(
					'id' => 'eox_client_id',
					'label' => __('Client id', 'wp-edunext-marketing-site') ,
					'description' => __('Client id of the edx instance API.', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => ''
				) ,
				array(
					'id' => 'eox_client_secret',
					'label' => __('Client secret', 'wp-edunext-marketing-site') ,
					'description' => __('Client secret of the edx instance API.', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => ''
				)
			)
		);
		return $settings;
	}

	public function get_access_token() {
		$token = get_option('wpt_eox_token', '');
		$base_url = get_option('wpt_lms_base_url', '');
		if ($token !== '') {
			$url = $base_url . '/oauth2/access_token/' . $token . '/';
			$response = wp_remote_post($url);
			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$this->add_message('error', $error_message);
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
			$this->add_message('error', $error_message);
			$error = new WP_Error('broke', __("Couln't call the API to get a new", "eox-core-api") , $response);
		}

		$token_details = json_decode($response['body']);
		$token = $token_details->access_token;
		update_option('wpt_eox_token', $token);
		return $token;
	}

	public function eox_create_new_user($args) {
		$defaults = array(
			'email' => '',
			'username' => '',
			'password' => '',
			'fullname' => '',
			'is_active' => False,
			'is_staff' => False,
			'is_superuser' => False,
			'activate_user' => False,
		);
		$data = wp_parse_args($args, $defaults);
		$token = $this->get_access_token();
		if (!is_wp_error($token)) {
			$base_url = get_option('wpt_lms_base_url', '');
			$url = $base_url . '/eox-core/api/v1/user/';
			$response = wp_remote_post($url, array(
				'headers' => 'Authorization: Bearer ' . $token,
				'body' => $data
			));
			$response_json = json_decode($response['body']);
			if (isset($response_json->non_field_errors)) {
				$this->handle_api_errors($response_json);
			}
			else {
				$this->add_message('update', 'User creation success!');
			}
		}
	}

	public function handle_api_errors($json) {
		foreach($json->non_field_errors as $value) {
			$this->add_message('error', $value);
		}
	}

	public function add_message($type, $message) {
		$user_message = array(
			'type' => $type,
			'message' => $message
		);
		array_push($this->user_messages, $user_message);
	}
}
