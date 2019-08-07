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
            add_action( 'wp_ajax_refresh_token', array( $this, 'refresh_eox_token' ) );
        }
    }

    /**
     * Hook to add a complete tab in the plugin setting's page
     */
    public function add_admin_settings( $settings ) {
        $settings['eoxapi'] = array(
            'title'       => __( 'EOX API', 'wp-edunext-marketing-site' ),
            'description' => __( '', 'wp-edunext-marketing-site' ),
            'fields'      => array(),
        );
        return $settings;
    }

    /**
     * Renders the custom form in the admin page
     */
    public function eoxapi_settings_custom_html() {
        include __DIR__ . '/templates/exoapi_settings_custom_html.php';
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
     * Produce an authentication token for the eox api using oauth 2.0
     */
    public function get_access_token( $refresh = false ) {
        $base_url     = get_option( 'wpt_lms_base_url', '' );
        $cache_key    = 'wpt_eox_token_' . substr( hash( 'sha256', $base_url ), 0, 10 );
        $token        = get_option( $cache_key, '' );
        $last_checked = get_option( 'last_checked_working', 0 );
        $five_min_ago = time() - 60 * 5;
        if ( $last_checked > $five_min_ago ) {
            return $token;
        }
        if ( $token !== '' ) {
            $url      = $base_url . '/oauth2/access_token/' . $token . '/';
            $response = wp_remote_get( $url );
            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $this->add_notice( 'error', $error_message );
                $error = new WP_Error( 'broke', $error_message, $response );
                return $error;
            }
            $json_reponse = json_decode( $response['body'] );
            if ( ! isset( $json_reponse->error ) ) {
                // Cache the last time it was succesfully checked.
                update_option( 'last_checked_working', time() );
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

}
