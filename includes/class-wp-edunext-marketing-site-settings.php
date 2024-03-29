<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_eduNEXT_Marketing_Site_Settings {

    /**
     * The single instance of WP_eduNEXT_Marketing_Site_Settings.
     *
     * @var     object
     * @access  private
     * @since   1.0.0
     */
    private static $_instance = null;

    /**
     * The main plugin object.
     *
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $parent = null;

    /**
     * Prefix for plugin settings.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     *
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();

    /**
     * Available settings for plugin.
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $active_tab = '';

    public function __construct( $parent ) {
        $this->parent = $parent;

        $this->base = 'wpt_';

        // Initialize settings.
        add_action( 'init', array( $this, 'init_settings' ), 11 );

        // Register plugin settings.
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Add settings page to menu.
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_head', array( $this, 'menu_highlight' ) );

        // Add settings link to plugins page.
        add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ), array( $this, 'add_settings_link' ) );
    }

    /**
     * Initialize settings
     *
     * @return void
     */
    public function init_settings() {
        $this->settings = $this->settings_fields();
    }

    /**
     * Add settings page to admin menu
     *
     * @return void
     */
    public function add_admin_menu() {
        $settings_page_slug = $this->parent->_token . '_settings';

        include 'templates/edunext_logo_base64.php';

        $page = add_menu_page( __( 'Open edX WP-Integrator', 'wp-edunext-marketing-site' ), __( 'Open edX WP-Integrator', 'wp-edunext-marketing-site' ), 'manage_options', $settings_page_slug, array( $this, 'settings_page' ), $edunext_logo_base64 );

        add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );

        foreach ( $this->settings as $section => $data ) {
            add_submenu_page( $this->parent->_token . '_settings', $data['title'], $data['title'], 'manage_options', 'admin.php?page=' . $settings_page_slug . '&tab=' . $section, null );
        }
    }

    /**
     * Highlights the correct top level admin menu item for post type add screens.
     */
    public function menu_highlight() {
        global $parent_file, $submenu_file, $post_type;

        if ( $parent_file === $this->parent->_token . '_settings' ) {
            if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
                $tab          = $_GET['tab'];
                $submenu_file = 'admin.php?page=' . $parent_file . '&tab=' . $tab;
            }
        }
    }

    /**
     * Load settings JS & CSS
     *
     * @return void
     */
    public function settings_assets() {

        // We're including the farbtastic script & styles here because they're needed for the colour picker.
        // If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below.
        wp_enqueue_style( 'farbtastic' );
        wp_enqueue_script( 'farbtastic' );

        // We're including the WP media scripts here because they're needed for the image upload field.
        // If you're not including an image upload then you can leave this function call out.
        wp_enqueue_media();
        $this->parent->enqueue_commons_script();
    }

    /**
     * Add settings link to plugin list table
     *
     * @param  array $links Existing links.
     * @return array        Modified links
     */
    public function add_settings_link( $links ) {
        $settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wp-edunext-marketing-site' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Build settings fields
     *
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields() {

        $settings['general'] = array(
            'title'  => __( 'General settings', 'wp-edunext-marketing-site' ),
            'fields' => array(
                array(
                    'id'          => 'lms_base_url',
                    'label'       => __( 'Base domain for the Open edX LMS', 'wp-edunext-marketing-site' ),
                    'description' => __( 'The url where your Open edX courses are located.', 'wp-edunext-marketing-site' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => __( 'https://mylms.edunext.io', 'wp-edunext-marketing-site' ),
                ),
                array(
                    'id'          => 'is_logged_in_cookie_name',
                    'label'       => __( 'Session cookie name', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Name of the shared user session cookie. If you are hosting your Open edX site with one of eduNEXT cloud subscriptions, you don´t need to change this. For standalone open edX installations it usually requires `edxloggedin`' ),
                    'type'        => 'text',
                    'default'     => 'edunextloggedin',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'user_info_cookie_name',
                    'label'       => __( 'User info cookie name', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Name of the shared cookie that holds the logged user information. If you are hosting your Open edX site with one of eduNEXT cloud subscriptions, you don´t need to change this. For standalone open edX installations usually `edx-user-info`' ),
                    'type'        => 'text',
                    'default'     => 'edunext-user-info',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'eox_client_id',
                    'label'       => __( 'Client id', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Client id of the open edX instance API.', 'wp-edunext-marketing-site' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'eox_client_secret',
                    'label'       => __( 'Client secret', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Client secret of the open edX instance API.', 'wp-edunext-marketing-site' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'               => 'enrollment_api_location',
                    'label'            => __( 'Enrollment API Location', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'This is an advanced feature only supported for eduNEXT customers. Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
                    'type'             => 'text',
                    'default'          => '/api/enrollment/v1/',
                    'placeholder'      => '/api/enrollment/v1/',
                    'advanced_setting' => true,
                ),
                array(
                    'id'               => 'enable_session_existence_sync',
                    'label'            => __( 'Log out of WP when Open edX is not logged in', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'This will force the users to login on Open edX to mantain a valid session on WordPress.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox',
                    'default'          => false,
                    'placeholder'      => '',
                    'advanced_setting' => false,
                ),
            ),
        );

        $settings['navigation'] = array(
            'title'  => __( 'Navigation Settings', 'wp-edunext-marketing-site' ),
            'fields' => array(
                array(
                    'id'               => 'advanced_login_location',
                    'label'            => __( 'Login handler', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Location of the login handler. This is an advanced setting, mostly for eduNEXT customers. Only change this if you know exactly what you are doing.' ),
                    'type'             => 'text',
                    'default'          => 'login',
                    'placeholder'      => '',
                    'advanced_setting' => true,
                ),
                array(
                    'id'               => 'advanced_registration_location',
                    'label'            => __( 'Registration handler', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Location of the registration handler. This is an advanced setting, mostly for eduNEXT customers. Only change this if you know exactly what you are doing.' ),
                    'type'             => 'text',
                    'default'          => 'register',
                    'placeholder'      => '',
                    'advanced_setting' => true,
                ),
                array(
                    'id'               => 'advanced_dashboard_location',
                    'label'            => __( 'Dashboard handler', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Location of the Dashboard handler. This is an advanced setting, mostly for eduNEXT customers. Only change this if you know exactly what you are doing.' ),
                    'type'             => 'text',
                    'default'          => 'dashboard',
                    'placeholder'      => '',
                    'advanced_setting' => true,
                ),
                array(
                    'id'          => 'client_menu_render',
                    'label'       => __( 'Client-side menu rendering', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Renders the menu on the client-side instead of server-side to avoid cache ghosts.' ),
                    'type'        => 'checkbox',
                    'default'     => false,
                    'placeholder' => '',
                ),
            ),
        );

        $settings['enrollment'] = array(
            'title'  => __( 'Course buttons', 'wp-edunext-marketing-site' ),
            'fields' => array(
                // Button Generic.
                array(
                    'id'          => 'header_generic',
                    'label'       => __( 'Default settings for ALL buttons', 'wp-edunext-marketing-site' ),
                    'description' => __( 'These settings can be applied to all buttons, and will be overwriten by the button specific settings in case they are also set.', 'wp-edunext-marketing-site' ),
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'button_class_generic',
                    'label'       => __( 'CSS classes for the link element of the buttons', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'color_class_generic',
                    'label'       => __( 'Additional CSS classes for the link element of the buttons', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'container_class_generic',
                    'label'       => __( 'CSS classes for the container of the buttons', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'separator_generic',
                    'label'       => '---------------------------------',
                    'description' => '',
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                // #1 Enroll to course button.
                array(
                    'id'          => 'header_enroll',
                    'label'       => __( 'ENROLL BUTTON', 'wp-edunext-marketing-site' ),
                    'description' => __( 'This button will trigger the enrollment in the course. It will be visible when the course is available for enrollments and the user is not yet enrolled, or there is not a user session yet.', 'wp-edunext-marketing-site' ),
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'label_enroll',
                    'label'       => __( 'Label', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => __( 'Enroll to this course', 'wp-edunext-marketing-site' ),
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'button_class_enroll',
                    'label'       => __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'color_class_enroll',
                    'label'       => __( 'Additional CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => __( '', 'wp-edunext-marketing-site' ),
                ),
                array(
                    'id'          => 'container_class_enroll',
                    'label'       => __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'               => 'user_enrollment_url',
                    'label'            => __( 'URL for the enrollment endpoint', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'This is an advanced featured for eduNEXT customers. Normally you don\'t need to change it. Defaults to: /register?course_id=%course_id%&enrollment_action=enroll.', 'wp-edunext-marketing-site' ),
                    'type'             => 'text',
                    'default'          => '/register?course_id=%course_id%&enrollment_action=enroll',
                    'placeholder'      => '',
                    'advanced_setting' => true,
                ),
                array(
                    'id'          => 'separator_enroll',
                    'label'       => '---------------------------------',
                    'description' => '',
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                // #2 Button Go To Course.
                array(
                    'id'          => 'header_go_to_course',
                    'label'       => __( 'GO TO COURSE BUTTON', 'wp-edunext-marketing-site' ),
                    'description' => __( 'This button will take the user to the course content. It will be visible when the course is open and the user is already enrolled.', 'wp-edunext-marketing-site' ),
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'label_go_to_course',
                    'label'       => __( 'Label', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => __( 'Go to course', 'wp-edunext-marketing-site' ),
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'button_class_go_to_course',
                    'label'       => __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'color_class_go_to_course',
                    'label'       => __( 'Additional CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => __( '', 'wp-edunext-marketing-site' ),
                ),
                array(
                    'id'          => 'container_class_go_to_course',
                    'label'       => __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'separator_go_to_course',
                    'label'       => '---------------------------------',
                    'description' => '',
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                // #3 Button Course Has Not started.
                array(
                    'id'          => 'header_course_has_not_started',
                    'label'       => __( 'COURSE IS CLOSED BUTTON', 'wp-edunext-marketing-site' ),
                    'description' => __( 'This button doesn\'t have any action. It will be visible when the course in Open edX is closed based on the course start and end dates.', 'wp-edunext-marketing-site' ),
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'label_course_has_not_started',
                    'label'       => __( 'Label', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => __( 'This course is closed', 'wp-edunext-marketing-site' ),
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'button_class_course_has_not_started',
                    'label'       => __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'color_class_course_has_not_started',
                    'label'       => __( 'Additional CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => __( '', 'wp-edunext-marketing-site' ),
                ),
                array(
                    'id'          => 'container_class_course_has_not_started',
                    'label'       => __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'               => 'course_has_not_started_url',
                    'label'            => __( 'URL for courses not yet started', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'URL to direct used to direct users to when course has not yet started. Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
                    'type'             => 'text',
                    'default'          => '/dashboard',
                    'placeholder'      => __( '/dashboard', 'wp-edunext-marketing-site' ),
                    'advanced_setting' => true,
                ),
                array(
                    'id'          => 'separator_course_has_not_started',
                    'label'       => '---------------------------------',
                    'description' => '',
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                // #4 Button Invitation Only.
                array(
                    'id'          => 'header_invitation_only',
                    'label'       => __( 'INVITATION ONLY BUTTON', 'wp-edunext-marketing-site' ),
                    'description' => __( 'This button doesn\'t have any action. It will be visible when the course in Open edX is set to be for enrollments by Invitation only.', 'wp-edunext-marketing-site' ),
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'label_invitation_only',
                    'label'       => __( 'Label', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => __( 'This course is by invitation only', 'wp-edunext-marketing-site' ),
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'button_class_invitation_only',
                    'label'       => __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'color_class_invitation_only',
                    'label'       => __( 'Additional CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => __( '', 'wp-edunext-marketing-site' ),
                ),
                array(
                    'id'          => 'container_class_invitation_only',
                    'label'       => __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'separator_invitation_only',
                    'label'       => '---------------------------------',
                    'description' => '',
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                // #5 Button Enrollment Closed.
                array(
                    'id'          => 'header_enrollment_closed',
                    'label'       => __( 'ENROLLMENT CLOSED BUTTON', 'wp-edunext-marketing-site' ),
                    'description' => __( 'This button doesn\'t have any action and will be visible when the course in Open edX is closed for enrollments as set by the enrollments start and end dated.', 'wp-edunext-marketing-site' ),
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'label_enrollment_closed',
                    'label'       => __( 'Label', 'wp-edunext-marketing-site' ),
                    'description' => '',
                    'type'        => 'text',
                    'default'     => __( 'Enrollment for this course is closed', 'wp-edunext-marketing-site' ),
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'button_class_enrollment_closed',
                    'label'       => __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'color_class_enrollment_closed',
                    'label'       => __( 'Additional CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => __( '', 'wp-edunext-marketing-site' ),
                ),
                array(
                    'id'          => 'container_class_enrollment_closed',
                    'label'       => __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
                    'description' => __( '' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => '',
                ),
                array(
                    'id'          => 'separator_enrollment_closed',
                    'label'       => '---------------------------------',
                    'description' => '',
                    'type'        => '',
                    'default'     => '',
                    'placeholder' => '',
                ),
            ),
        );

        $settings['woocommerce'] = array(
            'title'  => __( 'Woocommerce Integration', 'wp-edunext-marketing-site' ),
            'fields' => array(
                array(
                    'id'               => 'enable_woocommerce_integration',
                    'label'            => __( 'Enable the woocommerce integration', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Allows you to sell your courses using Woocommerce.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox',
                    'default'          => false,
                    'placeholder'      => '',
                    'advanced_setting' => false,
                ),
                array(
                    'id'               => 'woocommerce_action_to_connect',
                    'label'            => __( 'Woocommerce trigger action', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Which woocomerce action should be used to launch the enrollment.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox_multi',
                    'default'          => '',
                    'options'          => array(
                        'woocommerce_payment_complete' => 'woocommerce_payment_complete',
                        'woocommerce_payment_complete_order_status' => 'woocommerce_payment_complete_order_status',
                        'custom_string'                => 'Use custom action in the next field',
                    ),
                    'placeholder'      => '',
                    'advanced_setting' => false,
                ),
                array(
                    'id'          => 'custom_action_to_connect',
                    'label'       => __( 'Write a custom action to trigger the enrollment', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Action that will be used for the add_action function. Only change this if you know exactly what you are doing.' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => 'Optional only if you selected custom action',
                ),
                array(
                    'id'               => 'oer_action_for_fulfillment',
                    'label'            => __( 'Default enrollment fulfillment action', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'The process that will fulfill the enrollment. You can change at the product using the fulfillment_action variable.' ),
                    'type'             => 'select',
                    'default'          => '',
                    'options'          => array(
                        'do_nothing'                  => 'Nothing',
                        'oer_process'                 => 'Process request (recommended)',
                        'oer_force'                   => 'Process request --force',
                        'oer_no_pre'                  => 'Process request with no pre-enrollment',
                        'oer_no_pre_force'            => 'Process request with no pre-enrollment --force',
                        'custom_fulfillment_function' => 'Use custom action in the next field',
                    ),
                    'placeholder'      => '',
                    'advanced_setting' => false,
                ),
                array(
                    'id'          => 'custom_action_for_fulfillment',
                    'label'       => __( 'Write a custom action that will be triggered when the payment is done', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Callback that will execute when the trigger action runs. Only change this if you know exactly what you are doing.' ),
                    'type'        => 'text',
                    'default'     => '',
                    'placeholder' => 'Optional only if you selected custom function',
                ),
                array(
                    'id'               => 'enable_woocommerce_prefill_v1',
                    'label'            => __( 'Enable Checkout fields pre-filling', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'This is an advanced feature only supported for eduNEXT customers.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox',
                    'default'          => false,
                    'placeholder'      => '',
                    'advanced_setting' => true,
                ),
                array(
                    'id'               => 'eox_client_wc_field_mappings',
                    'label'            => __( 'User Profile fields mapping', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'This is an advanced feature only supported for eduNEXT customers. Mapping of user fields for pre-filling, from Open-edX (extended_profile) to Woocommerce (chekout)', 'wp-edunext-marketing-site', 'wp-edunext-marketing-site' ),
                    'type'             => 'textarea',
                    'default'          => '',
                    'placeholder'      => '{"woocommerce_field": "openedx_extended_profile_field" , "billing_company": "company", "billing_city": "city", ...}',
                    'advanced_setting' => true,
                ),
                array(
                    'id'               => 'enable_wc_cart_loggedin_intervention',
                    'label'            => __( 'Assert users are logged in to see the cart', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Allows you to make sure that users have a valid session when they access the woocommerce cart.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox',
                    'default'          => false,
                    'placeholder'      => '',
                    'advanced_setting' => false,
                ),
                array(
                    'id'          => 'lms_wp_return_path_cart',
                    'label'       => __( 'Lms redirect path for the cart', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Path that passed into the lms ?next= parameter takes the user back to the woocommerce cart.' ),
                    'type'        => 'text',
                    'default'     => '/cart',
                    'placeholder' => '/cart',
                ),
                array(
                    'id'               => 'enable_wc_checkout_loggedin_intervention',
                    'label'            => __( 'Get username information from Open edX into the checkout page', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Make sure that users have a valid session when they access the woocommerce checkout form. The username for said session is then used for the order fulfillment.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox',
                    'default'          => false,
                    'placeholder'      => '',
                    'advanced_setting' => false,
                ),
                array(
                    'id'          => 'lms_wp_return_path_checkout',
                    'label'       => __( 'Lms redirect path for the checkout form', 'wp-edunext-marketing-site' ),
                    'description' => __( 'Path that passed into the lms ?next= parameter takes the user back to the woocommerce checkout form.' ),
                    'type'        => 'text',
                    'default'     => '/checkout',
                    'placeholder' => '/cart',
                ),
                array(
                    'id'               => 'enable_wc_checkout_client_prefill',
                    'label'            => __( 'Enable Checkout client-side fields pre-filling', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Enables the pre-filling of the checkout form fields by the calls to the Open edX accounts API. Requires the users to have a valid Open edX session.', 'wp-edunext-marketing-site' ),
                    'type'             => 'checkbox',
                    'default'          => false,
                    'placeholder'      => '',
                    'advanced_setting' => true,
                ),
                array(
                    'id'               => 'enable_wc_checkout_client_prefill_mappings',
                    'label'            => __( 'User Profile fields mapping for client-side pre-filling', 'wp-edunext-marketing-site' ),
                    'description'      => __( 'Mapping of user fields for pre-filling, from the accounts api in Open edX to the to Woocommerce chekout form fields.', 'wp-edunext-marketing-site', 'wp-edunext-marketing-site' ),
                    'type'             => 'textarea',
                    'default'          => '{"billing_email": "email" , "billing_first_name": "name"}',
                    'placeholder'      => '{"billing_email": "email" , "billing_first_name": "name", ...}',
                    'advanced_setting' => true,
                ),
            ),
        );

        $settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

        return $settings;
    }

    /**
     * Register plugin settings
     *
     * @return void
     */
    public function register_settings() {
        if ( is_array( $this->settings ) ) {

            // Check posted/selected tab.
            $current_section = '';
            if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
                $current_section = $_POST['tab'];
            } else {
                if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
                    $current_section = $_GET['tab'];
                }
            }

            foreach ( $this->settings as $section => $data ) {

                if ( $current_section && $current_section !== $section ) {
                    continue;
                }

                // Add section to page.
                $this->active_tab = $section;
                add_settings_section( $section, '', array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

                foreach ( $data['fields'] as $field ) {

                    // Validation callback for field.
                    $validation = '';
                    if ( isset( $field['callback'] ) ) {
                        $validation = $field['callback'];
                    }

                    // Register field.
                    $option_name = $this->base . $field['id'];
                    register_setting( $this->parent->_token . '_settings', $option_name, $validation );

                    // Add field to page.
                    add_settings_field(
                        $field['id'],
                        $field['label'],
                        array( $this->parent->admin, 'display_field' ),
                        $this->parent->_token . '_settings',
                        $section,
                        array(
                            'field'  => $field,
                            'prefix' => $this->base,
                        )
                    );
                }

                if ( ! $current_section ) {
                    break;
                }
            }
        }
    }

    public function settings_section( $section ) {
        if ( isset( $this->settings[ $section['id'] ]['description'] ) ) {
            $html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
            echo $html;
        }
    }

    /**
     * Load settings page content
     *
     * @return void
     */
    public function settings_page() {

        // Build page HTML.
        $html  = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
        $html .= '<h2>' . __( 'Open edX - WordPress Integrator | by ', 'wp-edunext-marketing-site' ) . '<a href="https://www.edunext.co?utm_source=WP&utm_medium=web&utm_campaign=integrator" target="_blank"><img style="margin-bottom: -7px;" src="https://d1uwn6yupg8lfo.cloudfront.net/logos/logo-small.png" alt="' . __( 'eduNEXT - World class Open edX services provider | www.edunext.co' ) . '"></a></h2>' . "\n";

        $tab = '';
        if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
            $tab .= $_GET['tab'];
        }

        // Show page tabs.
        if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

            $html .= '<h2 class="nav-tab-wrapper">' . "\n";

            $c = 0;
            foreach ( $this->settings as $section => $data ) {

                // Set tab class.
                $class = 'nav-tab';
                if ( ! isset( $_GET['tab'] ) ) {
                    if ( 0 === $c ) {
                        $class .= ' nav-tab-active';
                    }
                } else {
                    if ( isset( $_GET['tab'] ) && $section === $_GET['tab'] ) {
                        $class .= ' nav-tab-active';
                    }
                }

                // Set tab link.
                $tab_link = add_query_arg( array( 'tab' => $section ) );
                if ( isset( $_GET['settings-updated'] ) ) {
                    $tab_link = remove_query_arg( 'settings-updated', $tab_link );
                }

                // Output tab.
                $html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

                ++$c;
            }

            $html .= '</h2>' . "\n";
        }

        $html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

        ob_start();
        // Get settings page header.
        $this->parent->admin->render_settings_page_header( $this->active_tab );
        $this->parent->admin->show_advance_settings_toggle();

        // Add the WP settings section.
        settings_fields( $this->parent->_token . '_settings' );
        do_settings_sections( $this->parent->_token . '_settings' );
        do_action( $this->active_tab . '_after_settings_page_html' );

        $html .= ob_get_clean();
        $html .= '<p class="submit">' . "\n";
        $html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
        $html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings', 'wp-edunext-marketing-site' ) ) . '" />' . "\n";
        $html .= '</p>' . "\n";
        $html .= '</form>' . "\n";

        $html .= '</div>' . "\n";

        echo $html;
    }

    /**
     * Main WP_eduNEXT_Marketing_Site_Settings Instance
     *
     * Ensures only one instance of WP_eduNEXT_Marketing_Site_Settings is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WP_eduNEXT_Marketing_Site()
     * @return Main WP_eduNEXT_Marketing_Site_Settings instance
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
