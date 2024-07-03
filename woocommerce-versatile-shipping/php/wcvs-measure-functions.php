<?php
// ------------------------------
// Functions used to find and define values for the package and products.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function wcvs_measure_product_values( $values, $woocommerce_zone_id ) {
    $product = $values['data'];

    // General Product Details
    $product_name = $product->get_name();
    $clipped_name = strlen($product_name) > 28 ? substr($product_name, 0, 25) . "..." : $product_name;
    $product_price = round( $product->get_price(), 2 ); // Value per instance of the product.
    $quantity = $values['quantity']; // The number of instances of the product in the cart.

    // Product Dimensions
    $measurements = wcvs_measure_dimensions( $product ); // Returns an array of measurements for the product.
    $product_info = '$' . $product_price . ' - ' . $measurements['actual_weight'] . 'lbs, ' . $measurements['length'] . 'x' . $measurements['width'] . 'x' . $measurements['height'] . ' - ';

    // Custom Product Options
    $custom_options = wcvs_get_custom_options( $values, $woocommerce_zone_id ); // Returns an array of custom options for the product.

    // Carrier Classifications
    $carrier_codes = wcvs_assign_carrier_codes( $measurements, $custom_options ); // Returns an array of carrier codes for the product.

    // Basic Types for Behavior and Cost
    $pack_type = wcvs_determine_pack_type( $carrier_codes['size_category'], $custom_options['has_custom_option'] );
    $cost_type = wcvs_determine_product_cost_type( $custom_options, $carrier_codes['size_category'] );

    $product_values = array(
        // Product Details
        'woocommerce_zone_id' => $woocommerce_zone_id,
        'product_name' => $clipped_name,
        'product_price' => $product_price,
        'product_info' => $product_info,
        'quantity' => $quantity,

        // Product Classifications
        'measurements' => $measurements,
        'custom_options' => $custom_options, 
        'carrier_codes' => $carrier_codes,

        // Basic Types
        'pack_type' => $pack_type,
        'cost_type' => $cost_type
    );

    return $product_values;
}

function wcvs_measure_dimensions( $product ) {
    $weight = $product->get_weight(); // Actual weight per instance of the product.
    // Sort the dimensions in descending order from longest to shortest.
    // Length is considered the longest regardless of the actual specifications.
    $dimensions = [$product->get_length(), $product->get_width(), $product->get_height()];
    rsort($dimensions); // Sort the dimensions in descending order.

    $length = $dimensions[0]; // Longest side
    $width = $dimensions[1]; // Second longest side
    $height = $dimensions[2]; // Shortest side
    $volume = $length * $width * $height;
    $length_plus_girth = $length + ($width * 2) + ($height * 2);

    return array(
        'actual_weight' => $weight,
        'length' => $length,
        'width' => $width,
        'height' => $height,
        'length_plus_girth' => $length_plus_girth,
        'volume' => $volume
    );
}

function wcvs_get_custom_options( $values, $woocommerce_zone_id ) {
    $product_id = $values['product_id'];
    $quantity = $values['quantity'];
    $custom_rate = get_post_meta( $product_id, 'custom_cost_' . $woocommerce_zone_id, true );
    $custom_cost_charge_per = get_post_meta( $product_id, 'custom_cost_charge_per_' . $woocommerce_zone_id, true );
    $max_shipping_cost = get_post_meta( $product_id, 'max_shipping_cost_' . $woocommerce_zone_id, true );
    $free_shipping_if_quantity = get_post_meta( $product_id, 'free_shipping_if_quantity_' . $woocommerce_zone_id, true );
    $is_free_shipping_in_zone = get_post_meta( $product_id, 'free_shipping_' . $woocommerce_zone_id, true );
    $is_free_shipping_in_zone_cart = get_post_meta( $product_id, 'free_shipping_cart_' . $woocommerce_zone_id, true );
    $disable_shipping = get_post_meta( $product_id, 'disable_shipping_' . $woocommerce_zone_id, true );
    // $disable_shipping_residential = get_post_meta( $product_id, 'disable_shipping_residential_' . $woocommerce_zone_id, true );
    // $disable_shipping_commercial = get_post_meta( $product_id, 'disable_shipping_commercial_' . $woocommerce_zone_id, true );

    // Defines default values for the custom rate and free shipping if they are not set.
    if ( ! is_numeric( $custom_rate ) ) { $custom_rate = 0; }
    if ( ! is_numeric( $custom_cost_charge_per ) ) { $custom_cost_charge_per = 1000; }
    if ( ! is_numeric( $max_shipping_cost ) ) { $max_shipping_cost = 100000; }
    if ( ! is_numeric( $free_shipping_if_quantity ) ) { $free_shipping_if_quantity = 1000000; }
    if ( $is_free_shipping_in_zone !== 'yes' ) { $is_free_shipping_in_zone = 'no'; }
    if ( $quantity >= $free_shipping_if_quantity ) { $is_free_shipping_in_zone = 'yes'; }
    if ( $is_free_shipping_in_zone_cart !== 'yes' ) { $is_free_shipping_in_zone_cart = 'no'; }
    if ( $disable_shipping !== 'yes' ) { $disable_shipping = 'no'; }
    // if ( $disable_shipping_residential !== 'yes' ) { $disable_shipping_residential = 'no'; }
    // if ( $disable_shipping_commercial !== 'yes' ) { $disable_shipping_commercial = 'no'; }

    // has_custom_option is a boolean that determines if the product has any custom options.
    $has_custom_option = $custom_rate > 0 || $is_free_shipping_in_zone === 'yes' || $is_free_shipping_in_zone_cart === 'yes' || $disable_shipping === 'yes';

    return array(
        'custom_rate' => $custom_rate,
        'custom_cost_charge_per' => $custom_cost_charge_per,
        'max_shipping_cost' => $max_shipping_cost,
        'free_shipping_if_quantity' => $free_shipping_if_quantity,
        'total_custom_rate' => wcvs_calculate_custom_rate( $custom_rate, $custom_cost_charge_per, $max_shipping_cost, $quantity ),
        'is_free_shipping_in_zone' => $is_free_shipping_in_zone,
        'is_free_shipping_in_zone_cart' => $is_free_shipping_in_zone_cart,
        'disable_shipping' => $disable_shipping,
        // 'disable_shipping_residential' => $disable_shipping_residential,
        // 'disable_shipping_commercial' => $disable_shipping_commercial,
        'has_custom_option' => $has_custom_option
    );
}

function wcvs_assign_carrier_codes( $measurements, $custom_options ) {
    $carrier = $_SESSION['wcvs_package_info']['carrier'];
    $dim_factor = floatval(get_option('wcvs_dim_factor', 139)); // Dimensional weight factor for the first rate.
    $dim_factor_2nd = floatval(get_option('wcvs_dim_factor_2nd', 115)); // Dimensional weight factor for secondary rates.

    $dimensional_weight = round( $measurements['volume'] / $dim_factor, 2 );
    $dimensional_weight_2nd = round( $measurements['volume'] / $dim_factor_2nd, 2 );
    $size_code = wcvs_assign_size_code( $measurements );
    $size_category = wcvs_assign_size_category( $size_code );

    return array(
        'dimensional_weight' => $dimensional_weight,
        'dimensional_weight_2nd' => $dimensional_weight_2nd,
        'size_code' => $size_code, // Size Code for Carrier: OW150, OS108, OS097, ADDWT, ADDSZ, or REGLR.
        'size_category' => $size_category // Size Category for Carrier: Regular, ExtraCare, or Oversized.
    );
}

function wcvs_assign_size_code( $measurements ) {
    // The cost consideration for each type overrides the previous types in order.
    $weight = $measurements['actual_weight'];
    $length = $measurements['length'];
    $width = $measurements['width'];
    $length_plus_girth = $measurements['length_plus_girth'];

    if ( $weight > 150 ) { return 'OW150'; } // Oversized due to weight being over 150 lbs.
    if ( $length >= 108 ) { return 'OS108'; } // Oversized due to length being over 108 inches.
    if ( within_bounds(97, $length, 107) || $length_plus_girth > 130 ) { return 'OS097'; } // Oversized due to length being within 97 and 107 inches.

    // Fetch Options from the Admin Panel.
    $addwt_weight = intval(get_option('wcvs_addwt_weight', 50)); // At what weight does the Additional Handling fee apply.
    $addsz_length = intval(get_option('wcvs_addsz_length', 48)); // At what length does the Additional Handling fee apply.
    $addsz_width = intval(get_option('wcvs_addsz_width', 30)); // At what width does the Additional Handling fee apply.
    $addsz_lpg = intval(get_option('wcvs_addsz_lpg', 105)); // At what length plus girth does the Additional Handling fee apply.
    $enable_addwt_weight = get_option('wcvs_enable_addwt_weight', true); // Enable Additional Handling due to weight.
    $enable_addsz_length = get_option('wcvs_enable_addsz_length', true); // Enable Additional Handling due to length.
    $enable_addsz_width = get_option('wcvs_enable_addsz_width', true); // Enable Additional Handling due to width.
    $enable_addsz_lpg = get_option('wcvs_enable_addsz_lpg', true); // Enable Additional Handling due to length plus girth.

    if ( $enable_addwt_weight && $weight > $addwt_weight ) { return 'ADDWT'; } // Additional Handling due to weight.
    if ( $enable_addsz_length && $length > $addsz_length ) { return 'ADDSZ'; } // Additional Handling due to length.
    if ( $enable_addsz_width && $width > $addsz_width ) { return 'ADDSZ'; } // Additional Handling due to width.
    if ( $enable_addsz_lpg && $length_plus_girth > $addsz_lpg ) { return 'ADDSZ'; } // Additional Handling due to length plus girth.
    
    return 'REGLR'; // Regular Package.
}

function wcvs_assign_size_category( $size_code ) {
    if ( $size_code === 'REGLR' ) { return 'Regular'; }
    if ( $size_code === 'ADDWT' || $size_code === 'ADDSZ' ) { return 'ExtraCare'; } // Includes Additional Handling.
    return 'Oversized'; // Includes Oversized 097, Oversized 108, and Oversized Weight.
}

function wcvs_determine_product_cost_type( $custom_options, $size_category ) {
    // Cost Type is used for debugging purposes and shows which order the cost types are overridden.
    // There are 5 cost types. Each overrides all previous types in order: 
    // 1 is Free Shipping, 2 is Regular, 3 is ExtraCare, 4 is Oversized, 5 is Custom, 6 is Free Shipping in Cart, 7 is Disabled Shipping.
    // Default cost type is Free Shipping, it is overwritten by everything else. 
    // Free Shipping does disable Regular rates for this product only. Not the other products in the cart.
    if ( $custom_options['is_free_shipping_in_zone'] === 'yes' ) { $cost_type = 1; } else { $cost_type = 2; }
    if ( $size_category == 'ExtraCare' ) { $cost_type = 3; }
    if ( $size_category == 'Oversized' ) { $cost_type = 4; }
    if ( $custom_options['custom_rate'] > 0 ) { $cost_type = 5; }
    if ( $custom_options['is_free_shipping_in_zone_cart'] === 'yes' ) { $cost_type = 6; }
    if ( $custom_options['disable_shipping'] === 'yes' ) { $cost_type = 7; }

    return $cost_type;
}

function wcvs_determine_pack_type( $size_category, $has_custom_option ) {
    // There are 2 pack types. Isolated and Shared.
    // Shared is used for product types that can be combined with other product types to produce dynamic rates.
    // Only regular products that produce dynamic rates are shared.
    // All other product types are Isolated and are packed in separate boxes. Each instance (quantity) of the product is packed together.
    if ( $has_custom_option == true ) { 
        return 'Isolated'; 
    } elseif ( $size_category != 'Regular' ) { 
        return 'Isolated'; 
    } else { 
        return 'Shared'; 
    }
}