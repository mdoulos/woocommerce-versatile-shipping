<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class WC_WooCommerceVersatileShipping_Plugin {
            
    public function __construct() {
        add_action( 'woocommerce_loaded', array( $this, 'load_plugin') );
    }

    public function load_plugin() {
        require_once dirname(__FILE__).'/wcvs-functions.php';
        require_once dirname(__FILE__).'/wcvs-helping-functions.php';
        require_once dirname(__FILE__).'/wcvs-sorting-functions.php';
        require_once dirname(__FILE__).'/wcvs-boxing-functions.php';
        require_once dirname(__FILE__).'/wcvs-price-lists.php';
        require_once dirname(__FILE__).'/wcvs-rate-functions.php';
        require_once dirname(__FILE__).'/wcvs-measure-functions.php';
        require_once dirname(__FILE__).'/wcvs-debug-functions.php';
        require_once dirname(__FILE__).'/wcvs-admin-options.php';
    }

}

new WC_WooCommerceVersatileShipping_Plugin();