<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'vendor/parser.php';

class WP_eduNEXT_Woocommerce_Integration {

    /**
     * Userinfo cache
     *
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $eox_user_info;

    /**
     * The main plugin object.
     *
     * @var     object
     * @access  public
     * @since   1.9.0
     */
    public $parent = null;

    /**
     * Constructor function.
     *
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct( $parent ) {
        $this->parent = $parent;

        if ( get_option( 'wpt_enable_woocommerce_prefill_v1' ) ) {
            add_action( 'woocommerce_checkout_get_value', array( $this, 'prefill_with_eox_core_data' ), 20, 2 );
        }

        $this->register_woocommerce_actions_and_callback();

        $this->alter_the_wc_flow_using_js();

    }

    /**
     * Connects the woocommerce integration according to the flexible logic
     *
     * @return void
     */
    public function register_woocommerce_actions_and_callback() {

        $actions_to_connect_array = get_option( 'wpt_woocommerce_action_to_connect' );

        foreach ( $actions_to_connect_array as $key => $action ) {
            if ( $action === 'custom_string' ) {
                $action = get_option( 'wpt_custom_action_to_connect' );
            }
            add_action( $action, array( $this, 'process_woo_order' ), 20, 2 );
        }
    }

    /**
     * Registers the necessary interventions to the woocommerce cart and checkout pages to guarantee a better
     * openedx course sale.
     *
     * @return void
     */
    public function alter_the_wc_flow_using_js() {
        if ( get_option( 'wpt_enable_wc_cart_loggedin_intervention' ) ) {
            add_action( 'woocommerce_after_cart', array( $this, 'action_openedx_flow_cart_intervention' ), 20, 2 );
        }
        if ( get_option( 'wpt_enable_wc_checkout_loggedin_intervention' ) ) {
            add_action( 'woocommerce_after_checkout_form', array( $this, 'action_assert_logged_openedx_checkout' ), 20, 2 );
        }
        wp_register_script( 'wc-workflow', esc_url( $this->parent->assets_url ) . 'js/wcWorkflow' . $this->parent->script_suffix . '.js', array( 'jquery' ), $this->parent->_version );
    }

    /**
     * Loads a javascript function on the cart page that validates the session on the openedx service.
     * If it does not find a valid session redirects the page to the configured login page with a custom next param.
     *
     * @return void
     */
    public function action_openedx_flow_cart_intervention() {
        wp_enqueue_script( 'wc-workflow' );
        wp_localize_script(
            'wc-workflow',
            'ENEXT_SRV',
            array(
                'run_functions'      => array( 'assertOpenEdxLoggedIn' ),
                'lms_base_url'       => get_option( 'wpt_lms_base_url' ),
                'lms_wp_return_path' => get_option( 'wpt_lms_wp_return_path_cart', '/cart' ),
                'lms_login_path'     => get_option( 'wpt_advanced_login_location' ),
            )
        );
    }

    /**
     * Loads a javascript function on the checkout page that validates the session on the openedx service.
     * If it does not find a valid session redirects the page to the configured login page with a custom next param.
     *
     * @return void
     */
    public function action_assert_logged_openedx_checkout() {
        wp_enqueue_script( 'wc-workflow' );
        wp_localize_script(
            'wc-workflow',
            'ENEXT_SRV',
            array(
                'run_functions'      => array( 'assertOpenEdxLoggedIn' ),
                'lms_base_url'       => get_option( 'wpt_lms_base_url' ),
                'lms_wp_return_path' => get_option( 'wpt_lms_wp_return_path_checkout', '/checkout' ),
                'lms_login_path'     => get_option( 'wpt_advanced_login_location' ),
            )
        );
    }

    /**
     * Callback to pre-fill.
     * Turning on the wpt_enable_woocommerce_prefill_v1 advanced setting will cause that the checkout form uses
     * this function to call the eox-core user API with the user's email. If it finds information that WordPress
     * did not have before, then it will pre-fill it in the form.
     * It is also possible to configure the mappings using an advanced field.
     *
     * @access public
     * @since  1.0.0
     * @param  string $value The value to show of the HTML input.
     * @param  string $input Description of post type.
     * @return string
     */
    public function prefill_with_eox_core_data( $value, $input ) {

        $current_user        = wp_get_current_user();
        $fields              = array(
            /* $woocommerce_name => $edx_name */
            'email'             => 'email',
            'billing_country'   => 'country',
            'billing_address_1' => 'mailing_address',
        );
        $extra_fields_mapped = array(
            /* $woocommerce_name => $edx_name */
            'billing_company'    => 'company',
            'billing_state'      => 'state',
            'billing_first_name' => 'first_name',
            'billing_last_name'  => 'last_name',
            'billing_city'       => 'city',
            'billing_postcode'   => 'zip',
        );

        $mappings      = get_option( 'wpt_eox_client_wc_field_mappings', '' );
        $json_mappings = json_decode( $mappings, true );
        if ( $json_mappings ) {
            $extra_fields_mapped = array_merge( $json_mappings, $extra_fields_mapped );
        }

        if ( $current_user->ID !== 0 && empty( $value ) ) {
            if ( empty( $this->eox_user_info ) ) {
                $this->eox_user_info = WP_EoxCoreApi()->get_user_info( [ 'email' => $current_user->user_email ] );
                if ( is_wp_error( $this->eox_user_info ) ) {
                    echo '<div id="message" class="error" style="display: none;"><p>' . $this->eox_user_info->get_error_message() . '</p></div>';
                    return $value;
                }
            }

            foreach ( $fields as $woocommerce_name => $edx_name ) {
                if ( $woocommerce_name === $input && ! empty( $this->eox_user_info->$edx_name ) ) {
                    $value = $this->eox_user_info->$edx_name;
                }
            }

            foreach ( $this->eox_user_info->extended_profile as $attr ) {
                foreach ( $extra_fields_mapped as $woocommerce_name => $edx_name ) {
                    if ( $attr->field_name === $edx_name && $input === $woocommerce_name ) {
                        $value = esc_attr( $attr->field_value );
                    }
                }
            }

            $processing_name_part = $input === 'billing_first_name' || $input === 'billing_last_name';

            if ( $processing_name_part && empty( $value ) && ! empty( $this->eox_user_info->name ) ) {
                $parser = new FullNameParser();
                $parsed = $parser->parse_name( $this->eox_user_info->name );
                $key    = $input === 'billing_first_name' ? 'fname' : 'lname';
                if ( ! empty( $parsed[ $key ] ) ) {
                    $value = $parsed[ $key ];
                }
            }
        }
        return $value;
    }

    /**
     * Possible action to perform
     *
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function process_woo_order( $order_or_status, $order_or_null = null ) {

        // Get the parameters right coming from payment_complete or order_status.
        $order_id = $order_or_status;
        if ( $order_or_null ) {
            $order_id = $order_or_null;
        }
        $order = new WC_Order( $order_id );

        // We need to get the User info first.
        $billing_email = $order->get_billing_email();  // this is what comes from the form.
        $wp_user_email = $order->get_user()->email;  // this the wp-user that made the purchase.
        // $custom_field_email = $order->get_billing_email());  //  this comes from a custom field.

        foreach ( $order->get_items() as $key => $item ) {

            $product = $item->get_product();

            $course_id = $product->get_attribute( 'course_id' );
            $bundle_id = $product->get_attribute( 'bundle_id' );

            if ( ! $course_id && ! $bundle_id ) {
                // This product does not require any work from us.
                break;
            }

            $course_mode = $product->get_attribute( 'course_mode' );
            if ( empty( $course_mode ) ) {
                $course_mode = 'audit';
            }

            $fulfillment_action = $product->get_attribute( 'fulfillment_action' );
            if ( empty( $fulfillment_action ) ) {
                $fulfillment_action = get_option( 'wpt_oer_action_for_fulfillment' );
                if ( $fulfillment_action === 'custom_fulfillment_function' ) {
                    $fulfillment_action = get_option( 'wpt_custom_action_for_fulfillment' );
                }
            }

            // Time to create the OER POST.
            $oer_action = 'custom_action';
            if ( 'do_nothing' === $fulfillment_action ) {
                $oer_action = '';
            }
            if ( 'oer_process' === $fulfillment_action ) {
                $oer_action = 'oer_process';
            }
            if ( 'oer_force' === $fulfillment_action ) {
                $oer_action = 'oer_force';
            }
            if ( 'oer_no_pre' === $fulfillment_action ) {
                $oer_action = 'oer_no_pre';
            }
            if ( 'oer_no_pre_force' === $fulfillment_action ) {
                $oer_action = 'oer_no_pre_force';
            }
            if ( 'oer_sync' === $fulfillment_action ) {
                $oer_action = 'oer_sync';
            }

            $oerarr = array(
                'oer_course_id'    => sanitize_text_field( $course_id ),
                'bundle_id'        => sanitize_text_field( $bundle_id ),
                'oer_mode'         => sanitize_text_field( $course_mode ),
                'oer_email'        => sanitize_text_field( $billing_email ),
                'oer_username'     => null,
                'oer_request_type' => 'enroll',
            );
            $post   = $this->parent->openedx_enrollment->insert_new( $oerarr, $oer_action );

            if ( $oer_action === 'custom_action' ) {
                // Call $fulfillment_action as a global passing the $oearr and the $post.
                wp_die( 'Not implemented yet.' );
            }
        }
    }
}
