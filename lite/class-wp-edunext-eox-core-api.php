<?php
/**
 * EOX Core API
 */
class WP_EoxCoreApi {

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
     *
     */
    function __construct() {
        if ( is_admin() ) {
            add_filter( 'wp-edunext-marketing-site_settings_fields', array( $this, 'add_admin_settings' ) );
            add_action( 'eoxapi_after_settings_page_html', array( $this, 'eoxapi_settings_custom_html' ) );
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
        include 'templates/exoapi_settings_custom_html.php';
    }

}
