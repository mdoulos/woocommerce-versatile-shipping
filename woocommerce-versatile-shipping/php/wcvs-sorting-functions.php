<?php
// ------------------------------
// Primary Functions to Separate Products into Groups based on how their rates will be calculated.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function wcvs_sort_products( $package, $woocommerce_zone_id ) {
    $isolated_products = array(); // Product types that will be packed separately from other product types.
    $shared_products = array(); // Product types that can be packed together with other product types.
    $package = $package['contents'];

    foreach ( $package as $item_id => $values ) {
        $current_product = wcvs_measure_product_values( $values, $woocommerce_zone_id );
        $pack_type = $current_product['pack_type']; // The packing behavior of the product. 'Isolated' or 'Shared'.

        if ( $pack_type == 'Isolated') {
            $isolated_products[] = $current_product;
        } else {
            $shared_products[] = $current_product;
        }
    }

    // Sort the shared products in order from longest to shortest.
    usort( $shared_products, function( $a, $b ) {
        return $b['length'] - $a['length'];
    }); 

    return array(
        'isolated_products' => $isolated_products,
        'shared_products' => $shared_products
    );
}