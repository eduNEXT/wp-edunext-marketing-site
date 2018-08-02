<?php

function eox_add_admin_settings($settings) {
		$settings['eoxapi'] = array(
				'title'					=> __( 'EOX API', 'wp-edunext-marketing-site' ),
				'description'			=> __( 'These settings modify the way to interact with the eox api.', 'wp-edunext-marketing-site' ),
				'fields'				=> array(
						array(
								'id' 			=> 'eox_client_id',
								'label'			=> __( 'Client id' , 'wp-edunext-marketing-site' ),
								'description'	=> __( 'Client id of the edx instance API.', 'wp-edunext-marketing-site' ),
								'type'			=> 'text',
								'default'		=> '',
								'placeholder'	=> ''
						),
						array(
								'id' 			=> 'eox_client_secret',
								'label'			=> __( 'Client secret' , 'wp-edunext-marketing-site' ),
								'description'	=> __( 'Client secret of the edx instance API.', 'wp-edunext-marketing-site' ),
								'type'			=> 'text',
								'default'		=> '',
								'placeholder'	=> ''
						)
				)
		);
		return $settings;
}

function eox_fetch_token() {
		$token = get_option('wpt_eox_token', '');
		$base_url = get_option('wpt_lms_base_url', '');
		// $base_url = 'http://172.17.0.1:18000';
		if ($token !== '') {
				$url = $base_url . '/oauth2/access_token/' . $token . '/';
				$response = wp_remote_post($url);
				if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						echo "Something went wrong: $error_message";
				} else {
						$body = json_decode($response['body']);
						if (!isset($body['error'])) {
								return $token;
						}
				}
		}
		$client_id = get_option('wpt_eox_client_id', '');
		$client_secret = get_option('wpt_eox_client_secret', '');
		if ($client_id !== '') {
				$args = array('body' => array(
					'client_id' => $client_id,
					'client_secret' => $client_secret,
					'grant_type' => 'client_credentials'
				));
				$url = $base_url . '/oauth2/access_token/';
				$response = wp_remote_post($url, $args);
				if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						throw new Exception($error_message, 1);
				} else {
						$token_details = json_decode($response['body']);
						$token = $token_details->access_token;
						update_option('wpt_eox_token', $token);
						return $token;
				}
		}
}

function oex_create_new_user() {
		$token = eox_fetch_token();
		$base_url = get_option('wpt_lms_base_url', '');
		// $base_url = 'http://172.17.0.1:18000';
		$url = $base_url . '/eox-core/api/v1/userinfo';
		$response = wp_remote_post($url, array('headers' => 'Authorization: Bearer ' . $token));
		echo $response['body'];
}

add_filter('wp-edunext-marketing-site_settings_fields', 'eox_add_admin_settings');

add_action('admin_footer', 'my_admin_footer_function');
function my_admin_footer_function() {
	oex_create_new_user();
}