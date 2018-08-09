<?php
/**
 * EOX Core API
 */
class WP_EoxCoreApi

{

	/**
	 * Class Constants
	 */
	const API_VERSION = 'v1';
	const PATH_USER_API = '/eox-core/api/' . self::API_VERSION . '/user/';
	const PATH_ENROLLMENT_API = '/eox-core/api/' . self::API_VERSION . '/enrollment/';

	/**
	 * Default values used to create a new edxapp user
	 */
	private $user_defaults = array(
		'email' => '',
		'username' => '',
		'password' => '',
		'fullname' => '',
		'is_active' => False,
		'activate_user' => False,
	);

	/**
	 * Default values used to create a new enrollemnt
	 */
	private $enroll_defaults = array(
		'username' => '',
		'mode' => '',
		'course_id' => '',
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
	function __construct() {
		if ( is_admin() ) {
			add_filter('wp-edunext-marketing-site_settings_fields', array($this, 'add_admin_settings'));
			add_action('eoxapi_after_settings_page_html', array($this, 'eoxapi_settings_custom_html'));
			add_action('wp_ajax_save_users_ajax', array($this, 'save_users_ajax'));
			add_action('wp_ajax_save_enrollments_ajax', array($this, 'save_enrollments_ajax'));
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

	public function handle_ajax_json_input($input) {
		check_ajax_referer('eoxapi');
		$json = json_decode($input);
		if (is_null($json)) {
			$json = json_decode(stripslashes($input));
		}
		if (is_null($json)) {
			$this->add_notice('error', 'Cannot parse as JSON, make sure to enter a valid JSON');
		} else if (!is_array($json)) {
			$this->add_notice('error', 'An array is needed, got ' . gettype($json) . ' instead');
		} else {
			return $json;
		}
		return null;
	}

	/**
	 * Called with AJAX function to POST to users API
	 */
	public function save_enrollments_ajax() {
		$new_enrollments = $this->handle_ajax_json_input($_POST['enrollments']);
		if ($new_enrollments) {
			foreach ($new_enrollments as $enrollment) {
				$this->eox_create_enrollment($enrollment);
			}
		}
		$this->show_notices();
		wp_die();
	}

	/**
	 * Called with AJAX function to POST to users API
	 */
	public function save_users_ajax() {
		$new_users = $this->handle_ajax_json_input($_POST['users']);
		if ($new_users) {
			foreach ($new_users as $user) {
				$this->eox_create_user($user);
			}
		}
		$this->show_notices();
		wp_die();
	}

	/**
	 * Produce an authentication token for the eox api using oauth 2.0
	 */
	public function get_access_token() {
		$token = get_option('wpt_eox_token', '');
		$last_checked = get_option('last_checked_working', 0);
		$five_min_ago = time() - 60 * 5;
		if ($last_checked  > $five_min_ago) {
			return $token;
		}
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
				// Cache the last time it was succesfully checked
				$token = update_option('last_checked_working', time());
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
	public function eox_create_enrollment($args) {
		$token = $this->get_access_token();
		if (!is_wp_error($token)) {

			$data = wp_parse_args($args, $this->enroll_defaults);
			$base_url = get_option('wpt_lms_base_url', '');
			$url = $base_url . self::PATH_ENROLLMENT_API;
			$response = wp_remote_post($url, array(
				'headers' => 'Authorization: Bearer ' . $token,
				'body' => $data
			));
			$ref = $data['username'];
			$errors = $this->check_response_errors($response, $ref);
			if (!$errors) {
				$this->add_notice('notice-success', 'Enrollment success! <i>(' . $ref . ')</i>');
			}
		}
	}

	/**
	 * Function to execute the API calls required to make a new edxapp user
	 */
	public function eox_create_user($args) {
		$token = $this->get_access_token();
		if (!is_wp_error($token)) {

			$data = wp_parse_args($args, $this->user_defaults);
			$base_url = get_option('wpt_lms_base_url', '');
			$url = $base_url . self::PATH_USER_API;
			$response = wp_remote_post($url, array(
				'headers' => 'Authorization: Bearer ' . $token,
				'body' => $data
			));
			$ref = $data['email'] ?: $data['username'] ?: $data['fullname'];
			$errors = $this->check_response_errors($response, $ref);
			if (!$errors) {
				$this->add_notice('notice-success', 'User creation success! <i>(' . $ref . ')</i>');
			}
		}
	}

	public function check_response_errors($response, $ref)
	{
		$response_json = json_decode($response['body']);
		if (is_null($response_json) && $response['response']['code'] === 404) {
			$this->add_notice('error', '404 - eox-core is likely not installed on the remote server' . $response['body']);
		}
		else if (is_null($response_json)) {
			$this->add_notice('error', 'non-json response, server returned status code ' . $response['response']['code']);
		}
		else if ($response['response']['code'] !== 200) {
			$this->handle_api_errors($response_json, $ref);
		} else {
			return false;
		}
		return true;
	}

	/**
	 *
	 */
	public function handle_api_errors($json, $ref) {
		if (isset($json->detail)) {
			$this->add_notice('error', $json->detail . ' (' . $ref . ')');
		}
		if (isset($json->non_field_errors)) {
			foreach ($json->non_field_errors as $value) {
				$this->add_notice('error', $value . ' (' . $ref . ')');
			}
		}
		foreach (array_keys($this->user_defaults) as $key) {
			if (isset($json->$key)) {
				foreach ($json->$key as $value) {
					$this->add_notice('error', ucfirst($key) . ': ' . $value . ' <i>(' . $ref . ')</i>');
				}
			}
		}
	}

	public function add_notice($type, $message) {
		WP_eduNEXT_Marketing_Site()->admin->add_notice($type, $message);
	}

	public function show_notices() {
		WP_eduNEXT_Marketing_Site()->admin->show_notices();
	}

}
