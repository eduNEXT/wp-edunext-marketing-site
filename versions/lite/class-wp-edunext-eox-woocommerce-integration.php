<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


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
