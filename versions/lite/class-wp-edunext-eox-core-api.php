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
}
