<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


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
     * Flag to mark the shortcode script as enqueued already.
     *
     * @var     boolean
     * @access  public
     * @since   2.3.0
     */
    public $wc_workflow_enqueued = null;

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

        add_shortcode( 'edunext_account_info', array( $this, 'account_information_shortcode' ) );
    }

    /**
     * Connects the woocommerce integration according to the flexible logic
     *
     * @return void
     */
    public function register_woocommerce_actions_and_callback() {

        $actions_to_connect_array = get_option( 'wpt_woocommerce_action_to_connect' );
        if ( ! $actions_to_connect_array ) {
            return;
        }

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
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_custom_checkout_openedx_hidden_fields' ), 20, 2 );
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
        $functions_array = array( 'assertOpenEdxLoggedInWithData', 'addInfoToCheckout' );

        if ( get_option( 'wpt_enable_wc_checkout_client_prefill' ) ) {
            array_push( $functions_array, 'prefillVisibleFields' );
        }

        wp_localize_script(
            'wc-workflow',
            'ENEXT_SRV',
            array(
                'run_functions'      => $functions_array,
                'lms_base_url'       => get_option( 'wpt_lms_base_url' ),
                'lms_wp_return_path' => get_option( 'wpt_lms_wp_return_path_checkout', '/checkout' ),
                'lms_login_path'     => get_option( 'wpt_advanced_login_location' ),
                'prefill_mappings'   => get_option( 'wpt_enable_wc_checkout_client_prefill_mappings' ),
            )
        );
    }

    /**
     * Stores the custom hidden fields that are added to the checkout form via JS.
     *
     * @return void
     */
    public function save_custom_checkout_openedx_hidden_fields( $order_id ) {
        if ( ! empty( $_POST['wpt_oer_username'] ) ) {
            update_post_meta( $order_id, 'oer_username', sanitize_text_field( $_POST['wpt_oer_username'] ) );
        }
        if ( ! empty( $_POST['wpt_oer_name'] ) ) {
            update_post_meta( $order_id, 'oer_name', sanitize_text_field( $_POST['wpt_oer_name'] ) );
        }
        if ( ! empty( $_POST['wpt_oer_email'] ) ) {
            update_post_meta( $order_id, 'oer_email', sanitize_text_field( $_POST['wpt_oer_email'] ) );
        }
    }

    /**
     * Adds a span in the page that will be replaced by data from the user/accounts api call.
     *
     * @return string
     */
    public function account_information_shortcode( $atts ) {
        if ( ! $this->wc_workflow_enqueued ) {
            wp_enqueue_script( 'wc-workflow' );
            wp_localize_script(
                'wc-workflow',
                'ENEXT_SC',
                array(
                    'run_shortcode' => array( 'replaceShortcodeFields' ),
                    'lms_base_url'  => get_option( 'wpt_lms_base_url' ),
                )
            );
            $this->wc_workflow_enqueued = true;
        }
        $atts = shortcode_atts(
            array(
                'placeholder' => '{ field }',
                'field'       => '',
            ),
            $atts,
            'account_information_shortcode'
        );
        return '<span class="js-order-info-sc" data-field="' . $atts['field'] . '">' . $atts['placeholder'] . '</span>';
    }

    /**
     * Callback to pre-fill.
     * Turning on the wpt_enable_woocommerce_prefill_v1 advanced setting will cause that the checkout form uses
     * this function to call the eox-core user API with the user's email to gather the user info. It will pre-fill
     * the checkout form fields with the user info using a mapping 'checkout field -> Open edX user info'.
     * The mapping must be provided in the plugin settings.
     *
     * @access public
     * @since  1.0.0
     * @param  string $value The value to show of the HTML input.
     * @param  string $input Description of post type.
     * @return string
     */
    public function prefill_with_eox_core_data( $value, $input ) {

        $current_user = wp_get_current_user();
        $fields       = json_decode( get_option( 'wpt_eox_client_wc_field_mappings', '' ), true );

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
                foreach ( $fields as $woocommerce_name => $edx_name ) {
                    if ( $attr->field_name === $edx_name && $input === $woocommerce_name ) {
                        $value = esc_attr( $attr->field_value );
                    }
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
        $billing_email    = $order->get_billing_email();  // this is what comes from the form.
        $openedx_username = get_post_meta( $order_id, 'oer_username', true );  // this is the result of calling the user/v1/me API.
        $openedx_email    = get_post_meta( $order_id, 'oer_email', true );  // this is the result of calling the user/v1/accounts API.

        $oer_email = $openedx_email;
        if ( ! $oer_email ) {
            $oer_email = $billing_email;
        }

        foreach ( $order->get_items() as $key => $item ) {

            $product = $item->get_product();

            $course_id          = $product->get_attribute( 'course_id' );
            $bundle_id          = $product->get_attribute( 'bundle_id' );
            $fulfillment_action = $product->get_attribute( 'fulfillment_action' );

            // If the product is a variation and the attributes are still empty
            // then we try to get them from the parent.
            if ( $product->get_type() === 'variation' ) {
                $_pf            = new WC_Product_Factory();
                $parent_product = $_pf->get_product( $product->get_parent_id() );

                if ( empty( $course_id ) ) {
                    $course_id = $parent_product->get_attribute( 'course_id' );
                }

                if ( empty( $bundle_id ) ) {
                    $bundle_id = $parent_product->get_attribute( 'bundle_id' );
                }

                if ( empty( $fulfillment_action ) ) {
                    $fulfillment_action = $parent_product->get_attribute( 'fulfillment_action' );
                }
            }

            if ( ! $course_id && ! $bundle_id ) {
                // This product does not require any work from us.
                continue;
            }

            $course_mode = $product->get_attribute( 'course_mode' );
            if ( empty( $course_mode ) ) {
                $course_mode = 'audit';
            }

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
                'oer_email'        => sanitize_text_field( $oer_email ),
                'oer_username'     => sanitize_text_field( $openedx_username ),
                'oer_request_type' => 'enroll',
                'oer_order_id'     => $order_id,
            );
            $post   = $this->parent->openedx_enrollment->insert_new( $oerarr, $oer_action );

            if ( $oer_action === 'custom_action' ) {
                // Call $fulfillment_action as a global passing the $oearr and the $post.
                wp_die( 'Not implemented yet.' );
            }

            do_action( 'openedx_enrollment_request_fulfilled', $oerarr, $post, $oer_action, $product, $order_id );

            /*
            Inline example on how to use this action.

            function function_of_customer( $oerarr, $oer_obj, $oer_action, $product, $order_id ) {
                // Write your code here.
            }
            add_action( 'openedx_enrollment_request_fulfilled', 'function_of_customer', 10, 5 );
            */
        }
    }
}
