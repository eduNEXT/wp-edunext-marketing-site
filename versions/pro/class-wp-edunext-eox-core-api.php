<?php
/**
 * EOX Core API
 */
class WP_EoxCoreApi {



    /**
     * Class Constants
     */
    const API_VERSION             = 'v1';
    const PATH_USER_API           = '/eox-core/api/' . self::API_VERSION . '/user/';
    const PATH_ENROLLMENT_API     = '/eox-core/api/' . self::API_VERSION . '/enrollment/';
    const PATH_USERINFO_API       = '/eox-core/api/' . self::API_VERSION . '/userinfo';
    const PATH_PRE_ENROLLMENT_API = '/eox-core/api/' . self::API_VERSION . '/pre-enrollment/';

    /**
     * Default values used to create a new edxapp user
     *
     * @var array
     */
    private $user_defaults = array(
        'email'         => '',
        'username'      => '',
        'password'      => '',
        'fullname'      => '',
        'is_active'     => false,
        'activate_user' => false,
    );

    /**
     * Default values used to create a new enrollment
     *
     * @var array
     */
    private $enroll_defaults = array(
        'username'  => '',
        'mode'      => '',
        'course_id' => '',
    );

    /**
     * Default values used to create a new pre-enrollment
     *
     * @var array
     */
    private $pre_enroll_defaults = array(
        'email'     => '',
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
    public static function instance( $file = '', $version = '1.2.0' ) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $file, $version );
        }

        return self::$_instance;
    } // End instance ()


    /**
     * Hook actions for initial setup of the plugin
     */
    function __construct() {
        if ( is_admin() ) {
            add_filter( 'wp-edunext-marketing-site_settings_fields', array( $this, 'add_admin_settings' ) );
            add_action( 'eoxapi_after_settings_page_html', array( $this, 'eoxapi_settings_custom_html' ) );
            add_action( 'wp_ajax_save_users_ajax', array( $this, 'save_users_ajax' ) );
            add_action( 'wp_ajax_refresh_token', array( $this, 'refresh_eox_token' ) );
            add_action( 'wp_ajax_get_users_ajax', array( $this, 'get_users_ajax' ) );
            add_action( 'wp_ajax_get_userinfo_ajax', array( $this, 'get_userinfo_ajax' ) );
            add_action( 'wp_ajax_save_enrollments_ajax', array( $this, 'save_enrollments_ajax' ) );
        }
    }

    /**
     * Hook to add a complete tab in the plugin setting's page
     */
    public function add_admin_settings( $settings ) {
        $settings['eoxapi'] = array(
            'title'       => __( 'EOX API', 'wp-edunext-marketing-site' ),
            'description' => __( '', 'wp-edunext-marketing-site' ),
            'fields'      => array(
                array(
                    'id'    => 'dummy option',
                    'label' => '',
                    'type'  => 'empty',
                ),
            ),
        );
        return $settings;
    }

    /**
     * Renders the custom form in the admin page
     */
    public function eoxapi_settings_custom_html() {
        include __DIR__ . '/templates/exoapi_settings_custom_html.php';
    }

    public function handle_ajax_json_input( $input ) {
        check_ajax_referer( 'eoxapi' );
        $json = json_decode( $input );
        if ( is_null( $json ) ) {
            $json = json_decode( stripslashes( $input ) );
        }
        if ( is_null( $json ) ) {
            $this->add_notice( 'error', 'Cannot parse as JSON, make sure to enter a valid JSON' );
        } elseif ( ! is_array( $json ) ) {
            $this->add_notice( 'error', 'An array is needed, got ' . gettype( $json ) . ' instead' );
        } else {
            return $json;
        }
        return null;
    }

    /**
     * Called with AJAX function to POST to enrollment API
     */
    public function save_enrollments_ajax() {
        $new_enrollments = $this->handle_ajax_json_input( $_POST['enrollments'] );
        if ( $new_enrollments ) {
            foreach ( $new_enrollments as $enrollment ) {
                $this->create_enrollment( $enrollment );
            }
        }
        $this->show_notices();
        wp_die();
    }

    /**
     * Called with AJAX function to POST to users API
     */
    public function save_users_ajax() {
        $new_users = $this->handle_ajax_json_input( $_POST['users'] );
        if ( $new_users ) {
            foreach ( $new_users as $user ) {
                $this->create_user( $user );
            }
        }
        $this->show_notices();
        wp_die();
    }

    /**
     * Called with AJAX function to refresh the stored token
     */
    public function refresh_eox_token() {
        $token = $this->get_access_token( true );

        $this->add_notice( 'notice-success', 'A new token ' . substr( $token, 0, 6 ) . '****** was created on ' . date( DATE_ATOM, time() ) );
        $this->show_notices();
        wp_die();
    }

    /**
     * Called with AJAX function to POST to users API
     */
    public function get_users_ajax() {
        $new_users  = $this->handle_ajax_json_input( $_POST['users'] );
        $users_info = [];
        if ( $new_users ) {
            foreach ( $new_users as $user ) {
                $users_info[] = $this->get_user_info( $user );
            }
        }
        $this->add_notice( 'user-info', '<pre>' . json_encode( $users_info, JSON_PRETTY_PRINT ) . '</pre>' );
        $this->show_notices();
        wp_die();
    }

    /**
     * Called with AJAX function to GET usesinfo
     */
    public function get_userinfo_ajax() {
        $userinfo = $this->userinfo();
        if ( is_wp_error( $userinfo ) ) {
            wp_send_json_error( $userinfo, 400 );
        } else {
            $this->show_notices();
            wp_die();
        }
    }

    /**
     * Produce an authentication token for the eox api using oauth 2.0
     */
    public function get_access_token( $refresh = false ) {
        $base_url  = get_option( 'wpt_lms_base_url', '' );
        $hash      = substr( hash( 'sha256', $base_url ), 0, 10 );
        $cache_key = 'wpt_eox_token_' . $hash;
        if ( $refresh ) {
            update_option( $cache_key, '' );
        }
        $token = get_option( $cache_key, '' );
        if ( $token !== '' ) {
            $last_checked = get_option( 'token_last_checked_working', 0 );
            $five_min_ago = time() - 60 * 5;
            if ( $last_checked > $five_min_ago ) {
                return $token;
            }
            $url      = $base_url . self::PATH_USERINFO_API . '/';
            $headers  = array(
                'Authorization' => 'Bearer ' . $token,
            );
            $request  = array(
                'headers' => $headers,
            );
            $response = wp_remote_get( $url, $request );
            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $this->add_notice( 'error', $error_message );
                $error = new WP_Error( 'broke', $error_message, $response );
                return $error;
            }
            $errors = $this->get_response_errors( $response, null );
            if ( empty( $errors ) ) {
                // Cache the last time it was succesfully checked.
                update_option( 'token_last_checked_working', time() );
                // Cached token its still valid, return it.
                return $token;
            }
        }

        $client_id     = get_option( 'wpt_eox_client_id', '' );
        $client_secret = get_option( 'wpt_eox_client_secret', '' );
        $args          = array(
            'body' => array(
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'grant_type'    => 'client_credentials',
            ),
        );
        $url           = $base_url . '/oauth2/access_token/';
        $response      = wp_remote_post( $url, $args );
        $json_reponse  = is_wp_error( $response ) ? false : json_decode( $response['body'] );
        if ( is_wp_error( $response ) || isset( $json_reponse->error ) ) {
            $error_message = is_wp_error( $response ) ? $response->get_error_message() : $json_reponse->error;
            $this->add_notice( 'error', $error_message );
            return new WP_Error( 'broke', __( "Couldn't call the API to get a new", 'eox-core-api' ), $response );
        }
        $token = $json_reponse->access_token;
        update_option( $cache_key, $token );
        return $token;
    }

    /**
     * API calls to get current user info
     */
    public function userinfo() {
        $data            = array();
        $ref             = '';
        $api_url         = self::PATH_USERINFO_API;
        $success_message = 'Userinfo reading ok!';
        $method          = 'GET';
        return $this->api_call( $api_url, $data, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to make a new enrollment
     */
    public function create_enrollment( $args ) {
        $data            = wp_parse_args( $args, $this->enroll_defaults );
        $api_url         = self::PATH_ENROLLMENT_API;
        $ref             = $args['email'] ?: $args['username'] ?: '';
        $success_message = 'Enrollment success!';
        $method          = 'POST';
        return $this->api_call( $api_url, $data, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to get an enrollment
     */
    public function get_enrollment( $args ) {
        $api_url         = self::PATH_ENROLLMENT_API;
        $ref             = $args['email'] ?: $args['username'] ?: '';
        $success_message = 'Enrollment fetched!';
        $api_url        .= '?' . http_build_query( $args );
        $method          = 'GET';
        return $this->api_call( $api_url, null, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to update an enrollment
     */
    public function update_enrollment( $args ) {
        $data            = wp_parse_args( $args, $this->enroll_defaults );
        $api_url         = self::PATH_ENROLLMENT_API;
        $ref             = $args['email'] ?: $args['username'] ?: '';
        $success_message = 'Enrollment updated!';
        $method          = 'PUT';
        return $this->api_call( $api_url, $data, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to delete an enrollment
     */
    public function delete_enrollment( $args ) {
        $api_url         = self::PATH_ENROLLMENT_API;
        $ref             = $args['email'] ?: $args['username'] ?: '';
        $success_message = 'Enrollment deleted!';
        $api_url        .= '?' . http_build_query( $args );
        $method          = 'DELETE';
        return $this->api_call( $api_url, null, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to make a new pre-enrollment
     */
    public function create_pre_enrollment( $args ) {
        $data            = wp_parse_args( $args, $this->pre_enroll_defaults );
        $api_url         = self::PATH_PRE_ENROLLMENT_API;
        $ref             = $args['email'] ?: $args['username'] ?: '';
        $success_message = 'Pre-enrollment success!';
        $method          = 'POST';
        return $this->api_call( $api_url, $data, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to make a new edxapp user
     */
    public function create_user( $args ) {
        $data            = wp_parse_args( $args, $this->user_defaults );
        $api_url         = self::PATH_USER_API;
        $ref             = $data['email'] ?: $data['username'] ?: $data['fullname'];
        $success_message = 'User creation success!';
        $method          = 'POST';
        return $this->api_call( $api_url, $data, $ref, $success_message, $method );
    }

    /**
     * Function to execute the API calls required to get an existing edxapp user
     */
    public function get_user_info( $args ) {
        $args            = (array) $args;
        $api_url         = self::PATH_USER_API;
        $ref             = $args['email'] ?: $args['username'] ?: '';
        $success_message = 'User fetching success!';
        $api_url        .= '?' . http_build_query( $args );
        $method          = 'GET';
        return $this->api_call( $api_url, null, $ref, $success_message, $method );
    }

    /**
     * Generic api call method
     */
    public function api_call( $api_url, $data, $ref, $success_message, $method ) {
        $token = $this->get_access_token();
        if ( ! is_wp_error( $token ) ) {
            $url     = get_option( 'wpt_lms_base_url', '' ) . $api_url;
            $headers = array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            );

            $request = array(
                'headers' => $headers,
                'method'  => $method,
            );

            if ( $method === 'PUT' or $method === 'POST' ) {
                $request['body'] = json_encode( $data );
            }
            $response = wp_remote_request( $url, $request );
            if ( is_wp_error( $response ) ) {
                return $response;
            }
            $errors = $this->get_response_errors( $response, $ref );
            if ( empty( $errors ) ) {
                $this->add_notice( 'notice-success', $success_message . ' <i>(' . $ref . ')</i>' );
                return json_decode( $response['body'] );
            }
            if ( is_array( $errors ) || is_object( $errors ) ) {
                foreach ( $errors as $err ) {
                    $this->add_notice( 'error', $err );
                }
            }

            return new WP_Error( 'eox-api-error', implode( ', ', $errors ) );

        } else {
            return $token;
        }
    }

    public function get_response_errors( $response, $ref ) {
        $response_json = json_decode( $response['body'] );
        $errors        = array();

        if ( is_null( $response_json ) ) {
            $errors[] = 'non-json response, server returned status code ' . $response['response']['code'];
        } elseif ( $response['response']['code'] !== 200 ) {
            $errors = array_merge( $errors, $this->handle_api_errors( $response_json, $ref ) );
        }
        if ( $response['response']['code'] === 202 ) {
            $errors[] = 'Request accepted but not completely processed. Server returned status code 202. Response: ' . $response['body'];
        }
        return $errors;
    }

    /**
     * Handle the errors generated in eox-core api to be properly displayed to the client
     *
     * @param array $json The api response in json format.
     * @return array errors The errors parsed from the response
     */
    public function handle_api_errors( $json ) {
        $errors = [];
        if ( isset( $json->detail ) ) {
            $errors[] = $json->detail;
        }
        if ( isset( $json->non_field_errors ) ) {
            foreach ( $json->non_field_errors as $value ) {
                $errors[] = $value;
            }
        }
        if ( isset( $json->errors ) ) {
            foreach ( $json->errors as $value ) {
                $errors[] = $value;
            }
        }
        $valid_error_keys = array_merge( array_keys( $this->user_defaults ), array_keys( $this->enroll_defaults ) );
        foreach ( $valid_error_keys as $key ) {
            if ( isset( $json->$key ) ) {
                foreach ( $json->$key as $value ) {
                    $errors[] = ucfirst( $key ) . ': ' . $value;
                }
            }
        }

        if ( empty( array_filter( $errors ) ) ) {
            $errors = (array) $json;
        }
        return $errors;
    }

    public function add_notice( $type, $message ) {
        if ( isset( WP_eduNEXT_Marketing_Site()->admin ) ) {
            WP_eduNEXT_Marketing_Site()->admin->add_notice( $type, $message );
        }
    }

    public function show_notices() {
        if ( isset( WP_eduNEXT_Marketing_Site()->admin ) ) {
            WP_eduNEXT_Marketing_Site()->admin->show_notices();
        }
    }

    public function activate_woocommerce_integration() {
        add_action( 'woocommerce_order_status_processing', array( $this, 'handle_payment_successful_result' ), 10, 1 );
    }


    public function handle_payment_successful_result( $order_id ) {
        $order              = wc_get_order( $order_id );
        $items              = $order->get_items();
        $user               = wp_get_current_user();
        $course_items_count = 0;
        foreach ( $items as $item ) {
            $product        = $item->get_product();
            $is_course_item = false;
            $mode           = $product->get_attribute( 'mode' );
            if ( empty( $mode ) ) {
                $mode = 'honor';
            }
            foreach ( [ 'course_id', 'bundle_id' ] as $key ) {
                $attr_course_id = $product->get_attribute( $key );
                if ( ! $attr_course_id ) {
                    $attr_course_id = $product->get_attribute( $key . 's' );
                }
                if ( $attr_course_id ) {
                    $is_course_item = true;
                    $ids            = explode( '|', $attr_course_id );
                    foreach ( $ids as $id ) {
                        $response = WP_EoxCoreApi()->create_enrollment(
                            [
                                'email' => $user->user_email,
                                $key    => trim( $id ),
                                'mode'  => $mode,
                                'force' => true,
                            ]
                        );
                    }
                }
            }
            if ( $is_course_item ) {
                $course_items_count++;
            }
        }
        if ( count( $items ) === $course_items_count ) {
            $order->update_status( 'completed' );
        }
    }


}
