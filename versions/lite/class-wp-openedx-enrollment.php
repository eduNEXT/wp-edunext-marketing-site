<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Openedx_Enrollment {

    /**
     * The main plugin object.
     *
     * @var     object
     * @access  public
     * @since   1.9.0
     */
    public $parent = null;

    /**
     * The name for the Open edX enrollment custom post type.
     *
     * @var     string
     * @access  public
     * @since   1.9.0
     */
    public $post_type = 'openedx_enrollment';

    /**
     * Constructor function.
     *
     * @access  public
     * @since   1.9.0
     * @return  void
     */
    public function __construct( $parent ) {
        $this->parent = $parent;

        // Add the custom post type.
        $enrollment_cpt_options = array(
            'public'            => false,
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_in_menu'      => $this->parent->_token . '_settings',
            'show_in_nav_menus' => true,
            'supports'          => array( '' ),
            'menu_icon'         => 'dashicons-admin-post',
        );
        $this->parent->register_post_type( 'openedx_enrollment', 'Open edX Enrollment Requests', 'Open edX Enrollment Request', '', $enrollment_cpt_options );

        add_action( 'init', array( $this, 'register_status' ), 10, 3 );
        add_action( 'woocommerce_after_settings_page_html', array( $this, 'overlay' ) );
        add_action( 'manage_openedx_enrollment_posts_columns', array( $this, 'overlay' ) );
    }

    /**
     * Display overlay
     *
     * @return void
     */
    function overlay() {
        $template = __DIR__ . '/templates/overlay_html.php';
        if ( file_exists( $template ) ) {
            include $template;
        }
    }


    /**
     * Creates specific status for the post type
     *
     * @return  void
     */
    function register_status() {
        register_post_status(
            'eor-success',
            array(
                'label'                     => __( 'Success', 'wp-edunext-marketing-site' ),
                'public'                    => false,
                'internal'                  => true,
                'private'                   => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Success <span class="count">(%s)</span>', 'Success <span class="count">(%s)</span>', 'wp-edunext-marketing-site' ),
            )
        );
        register_post_status(
            'eor-pending',
            array(
                'label'                     => __( 'Pending', 'wp-edunext-marketing-site' ),
                'public'                    => false,
                'internal'                  => true,
                'private'                   => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'wp-edunext-marketing-site' ),
            )
        );
        register_post_status(
            'eor-error',
            array(
                'label'                     => __( 'Error', 'wp-edunext-marketing-site' ),
                'public'                    => false,
                'internal'                  => true,
                'private'                   => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                'label_count'               => _n_noop( 'Error <span class="count">(%s)</span>', 'Error <span class="count">(%s)</span>', 'wp-edunext-marketing-site' ),
            )
        );
    }

    /**
     * Adds the cpt columns to the list view
     *
     * @return array $column
     */
    function add_columns_to_list_view( $column ) {
        $column['oer_status']   = 'Status';
        $column['oer_type']     = 'Type';
        $column['date']         = 'Date created';
        $column['oer_messages'] = 'Messages';
        return $column;
    }


    /**
     * Prepare the site to work with the Enrollment object as a CPT
     *
     * @return void
     */
    function set_up_admin() {

        // List view.
        add_filter( 'manage_openedx_enrollment_posts_columns', array( $this, 'add_columns_to_list_view' ) );
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
    public static function instance( $parent ) {
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
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
    } // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
    } // End __wakeup()

}
