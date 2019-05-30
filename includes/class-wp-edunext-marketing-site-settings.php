<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_eduNEXT_Marketing_Site_Settings {

	/**
	 * The single instance of WP_eduNEXT_Marketing_Site_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Available settings for plugin.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $active_tab = '';

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpt_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Open edX Wordpress Integrator', 'wp-edunext-marketing-site' ) , __( 'Open edX Wordpress Integrator', 'wp-edunext-marketing-site' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );

		// We're including the WP media scripts here because they're needed for the image upload field
		// If you're not including an image upload then you can leave this function call out
		wp_enqueue_media();
		$this->parent->enqueue_commons_script();
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wp-edunext-marketing-site' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['general'] = array(
			'title'					=> __( 'General settings', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				array(
					'id' 			=> 'lms_base_url',
					'label'			=> __( 'Base domain for the Open edX LMS' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'The url where your Open edX courses are located.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'https://mylms.edunext.io', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'is_logged_in_cookie_name',
					'label'			=> __( 'Session cookie name' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Name of the shared user session cookie. If you are hosting your Open edX site with one of eduNEXT cloud subscriptions, you don´t need to change this. For standalone open edX installations it usually requires `edxloggedin`' ),
					'type'			=> 'text',
					'default'		=> 'edunextloggedin',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'user_info_cookie_name',
					'label'			=> __( 'User info cookie name' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Name of the shared cookie that holds the logged user information. If you are hosting your Open edX site with one of eduNEXT cloud subscriptions, you don´t need to change this. For standalone open edX installations usually `edx-user-info`' ),
					'type'			=> 'text',
					'default'		=> 'edunext-user-info',
					'placeholder'	=> ''
				),
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
				) ,
				array(
					'id' 				=> 'enrollment_api_location',
					'label'				=> __( 'Enrollment API Location' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'This is an advanced feature only supported for eduNEXT customers. Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
					'type'				=> 'text',
					'default'			=> '/api/enrollment/v1/',
					'placeholder'		=> '',
					'advanced_setting' 	=> true
				)
			)
		);

		$settings['navigation'] = array(
			'title'					=> __( 'Navigation Settings', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				array(
					'id' 			=> 'advanced_login_location',
					'label'			=> __( 'Login handler' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Location of the login handler. This is an advanced setting, mostly for eduNEXT customers. Only change this if you know exactly what you are doing.' ),
					'type'			=> 'text',
					'default'		=> 'login',
					'placeholder'	=> '',
					'advanced_setting' 	=> true
				),
				array(
					'id' 			=> 'advanced_registration_location',
					'label'			=> __( 'Registration handler' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Location of the registration handler. This is an advanced setting, mostly for eduNEXT customers. Only change this if you know exactly what you are doing.' ),
					'type'			=> 'text',
					'default'		=> 'register',
					'placeholder'	=> '',
					'advanced_setting' 	=> true
				),
				array(
					'id' 			=> 'advanced_dashboard_location',
					'label'			=> __( 'Dashboard handler' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Location of the Dashboard handler. This is an advanced setting, mostly for eduNEXT customers. Only change this if you know exactly what you are doing.' ),
					'type'			=> 'text',
					'default'		=> 'dashboard',
					'placeholder'	=> '',
					'advanced_setting' 	=> true
				)
			)
		);

		$settings['enrollment'] = array(
			'title'					=> __( 'Course buttons', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				// Button Generic
				array(
					'id' 			=> 'header_generic',
					'label'			=> __( 'Default settings for all buttons', 'wp-edunext-marketing-site' ),
					'description'	=> __( 'These settings can be applied to all buttons, and will be overwriten by the button specific settings in case they are also set.', 'wp-edunext-marketing-site' ),
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class_generic',
					'label'			=> __( 'CSS classes for the link element of the buttons' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'color_class_generic',
					'label'			=> __( 'Additional CSS classes for the link element of the button' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'container_class_generic',
					'label'			=> __( 'CSS classes for the container of the buttons' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'separator_generic',
					'label'			=> '---------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),
				// #1 Enroll to course button
				array(
					'id' 			=> 'header_enroll',
					'label'			=> __( 'Enroll to course', 'wp-edunext-marketing-site' ),
					'description'	=> __( 'This button will be visible when the course is available for enrollments and the user is not yet enrolled, or there is not a user session yet.', 'wp-edunext-marketing-site' ),
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'label_enroll',
					'label'			=> __( 'Label' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> __( 'Enroll to this course', 'wp-edunext-marketing-site' ),
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class_enroll',
					'label'			=> __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'color_class_enroll',
					'label'			=> __( 'Additional CSS classes for the link element of the button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_enroll',
					'label'			=> __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 				=> 'user_enrollment_url',
					'label'				=> __( 'URL for the enrollment endpoint' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'This is an advanced featured for eduNEXT customers. Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
					'type'				=> 'text',
					'default'			=> '/register?course_id=%course_id%&enrollment_action=enroll',
					'placeholder'		=> '',
					'advanced_setting' 	=> true
				),
				array(
					'id' 			=> 'separator_enroll',
					'label'			=> '---------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),
				// #2 Button Go To Course
				array(
					'id' 			=> 'header_go_to_course',
					'label'			=> __( 'Go to course', 'wp-edunext-marketing-site' ),
					'description'	=> __( 'This button will be visible when the course is open and the user is already enrolled.', 'wp-edunext-marketing-site' ),
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'label_go_to_course',
					'label'			=> __( 'Label' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> __( 'Go to course', 'wp-edunext-marketing-site' ),
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class_go_to_course',
					'label'			=> __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'color_class_go_to_course',
					'label'			=> __( 'Additional CSS classes for the link element of the button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_go_to_course',
					'label'			=> __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'separator_go_to_course',
					'label'			=> '---------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),
				// #3 Button Course Has Not started
				array(
					'id' 			=> 'header_course_has_not_started',
					'label'			=> __( 'Course is closed', 'wp-edunext-marketing-site' ),
					'description'	=> __( 'This button will be visible when the course is closed based on the course start and end dated set in Open edX.', 'wp-edunext-marketing-site' ),
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'label_course_has_not_started',
					'label'			=> __( 'Label' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> __( 'This course is closed', 'wp-edunext-marketing-site' ),
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class_course_has_not_started',
					'label'			=> __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'color_class_course_has_not_started',
					'label'			=> __( 'Additional CSS classes for the link element of the button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_course_has_not_started',
					'label'			=> __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 				=> 'course_has_not_started_url',
					'label'				=> __( 'URL for courses not yet started' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'URL to direct used to direct users to when course has not yet started. Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
					'type'				=> 'text',
					'default'			=> '/dashboard',
					'placeholder'		=> __( '', 'wp-edunext-marketing-site' ),
					'advanced_setting' 	=> true
				),
				array(
					'id' 			=> 'separator_course_has_not_started',
					'label'			=> '---------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),
				// #4 Button Invitation Only
				array(
					'id' 			=> 'header_invitation_only',
					'label'			=> __( 'Invitatin only', 'wp-edunext-marketing-site' ),
					'description'	=> __( 'This button will be visible when the course is set to be for enrollments by Invitation only in Open edX.', 'wp-edunext-marketing-site' ),
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'label_invitation_only',
					'label'			=> __( 'Label' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> __( 'This course by invitation only', 'wp-edunext-marketing-site' ),
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class_invitation_only',
					'label'			=> __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'color_class_invitation_only',
					'label'			=> __( 'Additional CSS classes for the link element of the button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_invitation_only',
					'label'			=> __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'separator_invitation_only',
					'label'			=> '---------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),
				// #5 Button Enrollment Closed
				array(
					'id' 			=> 'header_enrollment_closed',
					'label'			=> __( 'Enrollment closed', 'wp-edunext-marketing-site' ),
					'description'	=> __( 'This button will be visible when the course is closed for enrollments as set by the enrollments start and end dated in Open edX.', 'wp-edunext-marketing-site' ),
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'label_enrollment_closed',
					'label'			=> __( 'Label' , 'wp-edunext-marketing-site' ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> __( 'Enrollment for this course is closed', 'wp-edunext-marketing-site' ),
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'button_class_enrollment_closed',
					'label'			=> __( 'CSS classes for the link element of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'color_class_enrollment_closed',
					'label'			=> __( 'Additional CSS classes for the link element of the button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_enrollment_closed',
					'label'			=> __( 'CSS classes for the container of the button', 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'separator_enrollment_closed',
					'label'			=> '---------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				)
			)
		);

		$settings['woocommerce'] = array(
			'title'					=> __( 'Woocommerce Integration', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				array(
					'id' 				=> 'enable_woocommerce_integration',
					'label'				=> __( 'Enable Eox-core Woocommerce integrations' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'This is an advanced feature only supported for eduNEXT customers. Features: Checkout pre-filling', 'wp-edunext-marketing-site' ),
					'type'				=> 'checkbox',
					'default'			=> false,
					'placeholder'		=> '',
					'advanced_setting' 	=> true
				),
				array(
					'id' => 'eox_client_wc_field_mappings',
					'label' => __('User fields mappings', 'wp-edunext-marketing-site') ,
					'description' => __('Mapping of user fields for pre-filling, from Open-edX (extended_profile) to Woocommerce', 'wp-edunext-marketing-site', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => '{"wc_example": "example"}',
					'advanced_setting' 	=> true
				),
			)
		);


		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				$this->active_tab = $section;
				add_settings_section( $section, '', array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field(
						$field['id'],
						$field['label'],
						array( $this->parent->admin, 'display_field' ),
						$this->parent->_token . '_settings',
						$section,
						array( 'field' => $field, 'prefix' => $this->base )
					);
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
		$html .= '<h2>' . __( 'Open edX Wordpress Integrator Settings | By eduNEXT' , 'wp-edunext-marketing-site' ) . '</h2>' . "\n";

		$tab = '';
		if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
			$tab .= $_GET['tab'];
		}

		// Show page tabs
		if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

			$html .= '<h2 class="nav-tab-wrapper">' . "\n";

			$c = 0;
			foreach ( $this->settings as $section => $data ) {

				// Set tab class
				$class = 'nav-tab';
				if ( ! isset( $_GET['tab'] ) ) {
					if ( 0 == $c ) {
						$class .= ' nav-tab-active';
					}
				} else {
					if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
						$class .= ' nav-tab-active';
					}
				}

				// Set tab link
				$tab_link = add_query_arg( array( 'tab' => $section ) );
				if ( isset( $_GET['settings-updated'] ) ) {
					$tab_link = remove_query_arg( 'settings-updated', $tab_link );
				}

				// Output tab
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

				++$c;
			}

			$html .= '</h2>' . "\n";
		}

		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";


		ob_start();
		// Get settings page header
		$this->parent->admin->render_settings_page_header($this->active_tab);
		$this->parent->admin->show_advance_settings_toggle();

		// Add the WP settings section
		settings_fields( $this->parent->_token . '_settings' );
		do_settings_sections( $this->parent->_token . '_settings' );
		do_action($this->active_tab . '_after_settings_page_html');


		$html .= ob_get_clean();
		$html .= '<p class="submit">' . "\n";
		$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
		$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'wp-edunext-marketing-site' ) ) . '" />' . "\n";
		$html .= '</p>' . "\n";
		$html .= '</form>' . "\n";

		$html .= '<a class="footer-logo edunext col-12" href="https://www.edunext.co" target="_self">
				 <img src="https://d1uwn6yupg8lfo.cloudfront.net/logos/logo-small.png" alt="eduNEXT - World class open edX services provider | www.edunext.co">
				 </a>' . "\n";


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
	public static function instance ( $parent ) {
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
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
