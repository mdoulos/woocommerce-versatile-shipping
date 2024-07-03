<?php
/**
* Plugin Name: WooCommerce Versatile Shipping
* Plugin URI: https://www.studiodoulos.com/
* Description: A code repository for improving the woocommerce shipping.
* Version: 1.0
* Author: Micah Doulos
* Author URI: http://www.studiodoulos.com/
**/

/** **/

if ( ! defined ('ABSPATH') ) {
    return;
}

// Enqueue styles for the admin area.
add_action( 'admin_enqueue_scripts', 'enqueue_wcvs_styles' );
function enqueue_wcvs_styles() {
    wp_enqueue_style( 'wcvs-admin', plugin_dir_url( __FILE__ ) . '/css/wcvs-admin-styles.css', array(), '1.0.0' );
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once dirname( __FILE__ ) . '/php/wcvs-construct.php';
}