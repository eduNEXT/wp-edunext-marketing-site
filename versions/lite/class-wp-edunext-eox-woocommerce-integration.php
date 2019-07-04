<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'vendor/parser.php';

class WP_eduNEXT_Woocommerce_Integration {

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
    }

}
