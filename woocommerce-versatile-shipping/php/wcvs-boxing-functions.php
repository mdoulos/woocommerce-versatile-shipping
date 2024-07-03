<?php
// ------------------------------
// Primary Functions to Split a Package into Boxes
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// There are two types of boxes: Isolated and Shared. Isolated boxes contain only one product type, while Shared boxes contain multiple product types.
// These two box types have 3 different content types:
// - Isolated (Fixed Cost): The cost of the contents is based on a custom rate or default oversized product rate + a discount for additional instances.
// - Isolated (Variable Cost): The cost of the contents is based on the dynamic calculation of the first instance of the product + a discount for additional instances.
// - Shared: The cost of the contents is based on the dynamic calculation of the combined products in the box. Always generalized to a cube for simplicity.


function wcvs_pack_boxes( $products ) {
    $_SESSION['wcvs_boxes'] = array(); // Reset the session variable that holds the boxes.
    $boxes = array(); // The array to hold the boxes.
    $open_box = array(); // The box currently being packed.
    $open_box['box_cost_type'] = 0;

    // Isolated Products
    // Loop through the isolated_products sub-array in the $products array.
    foreach ( $products['isolated_products'] as $product ) {
        $quantity = $product['quantity'];

        setup_a_new_box( $open_box, $boxes );

        // All instances of the product type are packed in one box.
        for ($i = 0; $i < $quantity; $i++) {
            $open_box = add_product_to_box( $product, $open_box, 'Isolated' );
        }

        setup_a_new_box( $open_box, $boxes );
    }

    // Shared Products
    // Loop through the shared_products sub-array in the $products array. This array is sorted from longest to shortest.
    foreach ( $products['shared_products'] as $product ) {
        $quantity = $product['quantity'];

        // Instances from different product types are packed in the same box.
        for ($i = 0; $i < $quantity; $i++) {
            if ( can_product_be_added_to_box( $product, $open_box ) ) {
                $open_box = add_product_to_box( $product, $open_box, 'Shared');
            } else {
                setup_a_new_box( $open_box, $boxes );
                $open_box = add_product_to_box( $product, $open_box, 'Shared');
            }
        }
    }

    // If the box is still open after going through all of the products, close it.
    setup_a_new_box( $open_box, $boxes );

    foreach ( $boxes as $box ) {
        $_SESSION['wcvs_boxes'][] = $box;
        
        if ( $box['box_pack_type'] == 'Shared' ) {
            $_SESSION['wcvs_package_info']['shared_weight'] += $box['carrier_codes']['billable_weight'];
            $_SESSION['wcvs_package_info']['shared_value'] += $box['box_price'];
        }
    }
}


function can_product_be_added_to_box( $current_product, &$open_box ) {
    // Limitations for shared boxes. Products packed individually aren't subject to these limitations.
    $actual_weight_limit = 49; // The maximum actual weight of a shared box.
    $volume_limit = 5832; // Based on an 18x18x18 inch cube box. Has an L+G of 90 inches and a UPS Dimensional Weight of 42.

    // If the box is empty, the product can be added to it.
    if ( $open_box['number_of_products'] == 0 ) {
        return true;
    } else {
        // Packing Qualification 1: If the open box weight + product weight > limit, start a new box.
        if ( $open_box['measurements']['actual_weight'] + $current_product['measurements']['actual_weight'] > $actual_weight_limit ) {
            return false;
        } elseif ( $open_box['measurements']['volume'] + $current_product['measurements']['volume'] > $volume_limit ) {
            return false;
        } else {
            return true;
        }
    }
}


function add_product_to_box( $current_product, $open_box, $pack_type ) {
    add_basic_product_info_to_box( $current_product, $open_box );

    // Define shared and initial box values with the first product.
    if ( $open_box['number_of_products'] == 1) {
        if ( $pack_type == 'Shared' ) {
            $open_box['box_pack_type'] = "Shared";
            $open_box['box_cost_type'] = 2;
        } else {
            $open_box['box_pack_type'] = "Isolated";
            $open_box['box_cost_type'] = $current_product['cost_type'];
        }
        add_first_product_values_to_box( $current_product, $open_box );
    } elseif ( $pack_type == 'Shared' ) {
        add_product_measurements_to_box( $current_product, $open_box);
    }
    
    return $open_box;
}


function add_basic_product_info_to_box( &$current_product, &$open_box ) {
    $open_box['number_of_products'] += 1;
    $open_box['product_names'][] = $current_product['product_name'];
    $open_box['product_info'][] = $current_product['product_info'];
    $open_box['box_price'] += $current_product['product_price'];
}

function add_product_measurements_to_box( &$current_product, &$open_box ) {
    $open_box['measurements']['actual_weight'] += $current_product['measurements']['actual_weight'];
    $open_box['carrier_codes']['dimensional_weight'] += $current_product['carrier_codes']['dimensional_weight'];
    $open_box['carrier_codes']['dimensional_weight_2nd'] += $current_product['carrier_codes']['dimensional_weight_2nd'];

    $open_box['measurements']['volume'] += $current_product['measurements']['volume'];
    $open_box['measurements']['length'] = max( $open_box['measurements']['length'], $current_product['measurements']['length'] );
    $open_box['measurements']['width'] = max( $open_box['measurements']['width'], $current_product['measurements']['width'] );
    $open_box['measurements']['height'] = max( $open_box['measurements']['height'], $current_product['measurements']['height'] );
    $open_box['measurements']['length_plus_girth'] = max( $open_box['measurements']['length_plus_girth'], $current_product['measurements']['length_plus_girth'] );
}


function add_first_product_values_to_box( &$current_product, &$open_box ) {
    $open_box['woocommerce_zone_id'] = $current_product['woocommerce_zone_id'];

    // Measurements
    $open_box['measurements']['actual_weight'] = $current_product['measurements']['actual_weight'];
    $open_box['measurements']['volume'] = $current_product['measurements']['volume'];
    $open_box['measurements']['length'] = $current_product['measurements']['length'];
    $open_box['measurements']['width'] = $current_product['measurements']['width'];
    $open_box['measurements']['height'] = $current_product['measurements']['height'];
    $open_box['measurements']['length_plus_girth'] = $current_product['measurements']['length_plus_girth'];

    // Custom Product Options
    $open_box['custom_options']['custom_rate'] = $current_product['custom_options']['custom_rate'];
    $open_box['custom_options']['custom_cost_charge_per'] = $current_product['custom_options']['custom_cost_charge_per'];
    $open_box['custom_options']['max_shipping_cost'] = $current_product['custom_options']['max_shipping_cost'];
    $open_box['custom_options']['total_custom_rate'] = $current_product['custom_options']['total_custom_rate'];
    $open_box['custom_options']['is_free_shipping_in_zone'] = $current_product['custom_options']['is_free_shipping_in_zone'];
    $open_box['custom_options']['is_free_shipping_in_zone_cart'] = $current_product['custom_options']['is_free_shipping_in_zone_cart'];
    $open_box['custom_options']['disable_shipping'] = $current_product['custom_options']['disable_shipping'];
    $open_box['custom_options']['has_custom_option'] = $current_product['custom_options']['has_custom_option'];

    // Carrier Classifications
    $open_box['carrier_codes']['dimensional_weight'] = $current_product['carrier_codes']['dimensional_weight'];
    $open_box['carrier_codes']['dimensional_weight_2nd'] = $current_product['carrier_codes']['dimensional_weight_2nd'];
    $open_box['carrier_codes']['size_code'] = $current_product['carrier_codes']['size_code'];
    $open_box['carrier_codes']['size_category'] = $current_product['carrier_codes']['size_category'];
}


function setup_a_new_box( &$open_box, &$boxes ) {
    if ( $open_box['number_of_products'] > 0 ) {
        $closed_box = close_current_box( $open_box );
        $boxes[] = $closed_box;
        $open_box = array(); // Reset the open box.
        $open_box['box_cost_type'] = 0;
    }
}

function close_current_box( &$open_box ) {
    // An Isolated box is closed normally, but a Shared box requires additional calculations.
    if ( $open_box['box_pack_type'] == 'Shared' ) {
        // L, W, and H represent only the longest product dimensions in the box.
        $length = $open_box['measurements']['length'];
        $width = $open_box['measurements']['width'];
        $height = $open_box['measurements']['height'];
        $volume = $open_box['measurements']['volume'];
        $cube_side = 0;

        if ( within_bounds(4914, $volume, 5832) ) {
            $cube_side = 18; // Volume = 5832
        } elseif ( within_bounds(4097, $volume, 4913) ) {
            $cube_side = 17; // Volume = 4913
        } elseif ( within_bounds(3376, $volume, 4096) ) {
            $cube_side = 16; // Volume = 4096
        } elseif ( within_bounds(2745, $volume, 3375) ) {
            $cube_side = 15; // Volume = 3375
        } elseif ( within_bounds(2198, $volume, 2744) ) {
            $cube_side = 14; // Volume = 2744
        } elseif ( within_bounds(1729, $volume, 2197) ) {
            $cube_side = 13; // Volume = 2197
        } elseif ( within_bounds(1332, $volume, 1728) ) {
            $cube_side = 12; // Volume = 1728
        } elseif ( within_bounds(1001, $volume, 1331) ) {
            $cube_side = 11; // Volume = 1331
        } elseif ( within_bounds(730, $volume, 1000) ) {
            $cube_side = 10; // Volume = 1000
        } elseif ( within_bounds(513, $volume, 729) ) {
            $cube_side = 9; // Volume = 729
        } elseif ( within_bounds(344, $volume, 512) ) {
            $cube_side = 8; // Volume = 512
        } elseif ( within_bounds(217, $volume, 343) ) {
            $cube_side = 7; // Volume = 343
        } else {
            $cube_side = 6; // Volume = 216
        }

        // Because the actual dimensions don't effect the pricing, the box is a cube for simplicity.
        $open_box['measurements']['length'] = $cube_side;
        $open_box['measurements']['width'] = $cube_side;
        $open_box['measurements']['height'] = $cube_side;
        $open_box['measurements']['length_plus_girth'] = $cube_side + ($cube_side * 2) + ($cube_side * 2);

    }

    round_box_values( $open_box );

    $closed_box = $open_box;
    return $closed_box;
}

function round_box_values( &$box ) {
    // Record the actual measurements before rounding.
    $box['measurements']['actual_weight_real'] = $box['measurements']['actual_weight'];
    $box['measurements']['volume_real'] = $box['measurements']['volume'];
    $box['measurements']['length_real'] = $box['measurements']['length'];
    $box['measurements']['width_real'] = $box['measurements']['width'];
    $box['measurements']['height_real'] = $box['measurements']['height'];
    $box['measurements']['length_plus_girth_real'] = $box['measurements']['length_plus_girth'];

    // Round the measurements up to the nearest whole number.
    $box['measurements']['actual_weight'] = ceil( $box['measurements']['actual_weight'] );
    $box['measurements']['volume'] = ceil( $box['measurements']['volume'] );
    $box['measurements']['length'] = ceil( $box['measurements']['length'] );
    $box['measurements']['width'] = ceil( $box['measurements']['width'] );
    $box['measurements']['height'] = ceil( $box['measurements']['height'] );
    $box['measurements']['length_plus_girth'] = ceil( $box['measurements']['length_plus_girth'] );
    $box['carrier_codes']['dimensional_weight'] = ceil( $box['carrier_codes']['dimensional_weight'] );
    $box['carrier_codes']['dimensional_weight_2nd'] = ceil( $box['carrier_codes']['dimensional_weight_2nd'] );

    // The billable weight is the greater of the actual weight and the dimensional weight.
    $box['carrier_codes']['billable_weight'] = ceil( max( $box['measurements']['actual_weight'], $box['carrier_codes']['dimensional_weight'] ) );
    $box['carrier_codes']['billable_weight_2nd'] = ceil( max( $box['measurements']['actual_weight'], $box['carrier_codes']['dimensional_weight_2nd'] ) );
}
