<?php
// ------------------------------
// Primary Function to Add Shipping Rates to WooCommerce
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Plugin Overview:
// wcvs_modify_shipping_rate is called each time a shipping rate is added to the cart or changed in the checkout.
// Therefore if there are 4 available rates: Ground, Select, Express, and Local Pickup, this function will be called 4 times.
// The rates are modified in the order they are listed in the WooCommerce settings.
// Many calculations such as measuring each product happen only once, during the first call to this function.

// On the first call to wcvs_modify_shipping_rate, the following happens:
// 1. The package information is saved and reset.
// 2. The products in the package are measured and sorted into isolated and shared piles.
// 3. The products are packed into pseudo-boxes.

// On all calls to wcvs_modify_shipping_rate, the following happens:
// 1. The rate type is identified.
// 2. Flat rates have their labels and prices modified.

// ------------------------------
// Modifies the shipping rates in the package to reflect the dynamic shipping rates.
add_filter( 'woocommerce_shipping_method_add_rate_args', 'wcvs_modify_shipping_rate', 10, 2 ); // Executes when adding to cart and changing address in checkout.

// Primary function to modify the shipping rates in the cart.
// woocommerce_shipping_method_add_rate_args is called each time a shipping rate is added to the cart or changed in the checkout.
function wcvs_modify_shipping_rate( $args ) {
    // Static variable maintains the value across multiple calls to the function.
    static $wcvs_process_started = false;
    static $has_free_shipping_coupon = false;
    static $wcvs_rate_names = array();
    $rate_type = wcvs_identify_rate_type( $args['id'] ); // The rate type is the first part of the rate ID. Ex: 'flat_rate' or 'free_shipping'.

    // Start the wcvs process. This function will only be called once per cart calculation.
    // Step 1) Save and reset Package Information, Step 2) Measure and Sort Products, Step 3) Pack Boxes.
    if ( $wcvs_process_started === false ) {
        $wcvs_process_started = true;
        $package = $args['package']; // The package of products in the cart, ie all items in the art.
        $woocommerce_zone_id = wcvs_get_woocommerce_zone_id( $package ); // The ID of the shipping zone.

        if ( ! session_id() ) { session_start(); } // Start the customer's session if it isn't already started. This is necessary to store information.
        wcvs_register_package_information( $package, $woocommerce_zone_id ); // Registers the package information for use in other functions.
        $products = wcvs_sort_products( $package, $woocommerce_zone_id ); // Sorts the products in the cart based on their packing behavior.
        wcvs_pack_boxes( $products ); // The cart products are split into different "boxes" for shipping based upon different criteria.
    }

    // Process individual rate prices and labels.
    // If the rate is a flat rate, do the following steps:
    // 1) Reset the rate information for the rate and record the rate name.
    // 2) Calculate the isolated box (large, oversized, or custom shipping price products) costs for the rate.
    // 3) Calculate the shared box (regular shipping price products) costs for the rate.
    // 4) Calculate the final rate price for the rate and modify the rate cost.
    // 5) Determine the visibility of the rate.
    // 6) Modify the rate label.

    if ( $rate_type === 'free_shipping' ) {
        $has_free_shipping_coupon = true;
    } elseif ( $rate_type === 'flat_rate' ) { // WCVS modifies Flat Rates defined in the WooCommerce UI.
        $rate_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($args['label'])); // Ex: UPS Ground -> ups_ground.
        wcvs_reset_rate_information( $rate_name ); // Otherwise the information will persist and augment between cart refreshes.

        // Store the rate names in a session variable anytime a new rate is encountered.
        if ( ! in_array( $rate_name, $wcvs_rate_names ) ) {
            $wcvs_rate_names[] = $rate_name;
            $_SESSION['wcvs_package_info']['rate_names'] = $wcvs_rate_names;
        }

        $final_rate_price = 0;
        $zone_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($_SESSION['wcvs_package_info']['shipping_zone_name']));
        $carrier = $_SESSION['wcvs_package_info']['carrier'];

        wcvs_calculate_isolated_box_costs( $rate_name, $zone_name, $carrier, $wcvs_rate_names ); // Assigns rates to the boxes based on the products in them.
        wcvs_calculate_shared_box_costs( $rate_name, $zone_name, $carrier ); // Calculates the shared rate for the shared boxes.
        $final_rate_price = wcvs_calculate_final_rate_price( $rate_name ); // Combines the final rate for each box based on the rates assigned to it.
        $args['cost'] = $final_rate_price;

        // Determines which shipping rate types will appear to the customer.
        $method_visibility = wcvs_get_method_visibility( $has_free_shipping_coupon, $rate_name, $wcvs_rate_names, $final_rate_price );

        // Hide the rate if method_visibility is 'Disabled'.
        if ( $method_visibility === 'Disabled' ) {
            return false; // Completely remove the rate
        } else {
            // Label Modifications
            $custom_prefix = get_option('wcvs_' . $rate_name . '_rate_prefix', '');
            $custom_name = get_option('wcvs_' . $rate_name . '_rate_name', $rate_name);
            $custom_time = get_option('wcvs_' . $zone_name . '_' . $rate_name . '_transit_time', '');
            $label_prefix = ! empty($custom_prefix) ? $custom_prefix . ' ' : ''; // Example: "UPS".
            $rate_label = ! empty($custom_name) ? $custom_name : $args['label']; // Example: "Ground".
            $transit_time = ! empty($custom_time) && is_checkout() ? ' - ' . $custom_time : ''; // Example: " - 2-3 days".

            if ( $method_visibility == 'Dynamic' ) {
                $args['label'] = $label_prefix . $rate_label . $transit_time;
            } else {
                // Defines two behaviors:
                // 1a) If the first rate is Free Shipping, then secondary rates are visible for express options.
                // 1b) If the Free Shipping is the result of a coupon, then the first rate is hidden- because the separate Free Shipping rate appears.
                // 2) If the first rate is not Free Shipping, then secondary rates are hidden.

                error_log( 'Method Visibility: ' . $method_visibility );
                error_log( 'Rate Name: ' . $rate_name );

                if ( $wcvs_rate_names[0] === $rate_name ) { // First Rate (Ground)
                    if ( $method_visibility === 'HasCoupon' ) {
                        return false; // Completely remove the first rate if there is a free shipping coupon.
                    } else {
                        $rate_label = $method_visibility . ' Shipping'; // Otherwise rename the first rate, ex: "Free Shipping", "Standard Shipping", etc.
                    }
                } else { // Secondary Rates (Select, Express, etc.)
                    if ( $method_visibility != 'Free' && $method_visibility != 'HasCoupon' ) {
                        error_log( 'Secondary Rate Removed: ' . $rate_name );
                        error_log( 'Method Visibility: ' . $method_visibility );
                        return false; // Completely remove the secondary rates unless the first rate is Free Shipping.
                    }
                }
                
                $args['label'] = $rate_label;
            }
        }
    }

    return $args;
}

// ------------------------------
// Adds a set of Custom Shipping options for each shipping zone to the Product Edit Page
add_action('woocommerce_product_options_shipping', 'wcvs_add_custom_shipping_options');
function wcvs_add_custom_shipping_options() {
    global $post;
    $shipping_zones = WC_Shipping_Zones::get_zones();

    echo '<div class="wcvs_edit_zone_options">';

    foreach ($shipping_zones as $zone) {
        $zone_name = $zone['zone_name'];
        echo '<div class="wcvs_edit_options_group">';
            echo '<p><strong>Zone: ' . $zone_name . '</strong><span> (Zone ID: ' . $zone['zone_id'] . ')</span></p>';
            echo '<div class="wcvs_edit_options_group_row flex-row">';
                // Disable Shipping for Zone
                woocommerce_wp_checkbox(array(
                    'id' => 'disable_shipping_' . $zone['zone_id'],
                    'label' => __('Disable Shipping', 'wcvs'),
                    'default' => 'no', // Unchecked by default
                    'value' => get_post_meta($post->ID, 'disable_shipping_' . $zone['zone_id'], true),
                ));
                // // Disable Shipping for Zone, if residential address
                // woocommerce_wp_checkbox(array(
                //     'id' => 'disable_shipping_residential_' . $zone['zone_id'],
                //     'label' => __('Disable Shipping (Residential)', 'wcvs'),
                //     'default' => 'no', // Unchecked by default
                //     'value' => get_post_meta($post->ID, 'disable_shipping_residential_' . $zone['zone_id'], true),
                // ));
                // // Disable Shipping for Zone, if commercial address
                // woocommerce_wp_checkbox(array(
                //     'id' => 'disable_shipping_commercial_' . $zone['zone_id'],
                //     'label' => __('Disable Shipping (Commercial)', 'wcvs'),
                //     'default' => 'no', // Unchecked by default
                //     'value' => get_post_meta($post->ID, 'disable_shipping_commercial_' . $zone['zone_id'], true),
                // ));
                // Free Shipping for Zone, for this product only, if this is enabled.
                woocommerce_wp_checkbox(array(
                    'id' => 'free_shipping_' . $zone['zone_id'],
                    'label' => __('Free Shipping (This Product)', 'wcvs'),
                    'default' => 'no', // Unchecked by default
                    'value' => get_post_meta($post->ID, 'free_shipping_' . $zone['zone_id'], true),
                ));
                // Free Shipping for Zone, for all items in the cart if this is enabled.
                woocommerce_wp_checkbox(array(
                    'id' => 'free_shipping_cart_' . $zone['zone_id'],
                    'label' => __('Free Shipping (Entire Cart)', 'wcvs'),
                    'default' => 'no', // Unchecked by default
                    'value' => get_post_meta($post->ID, 'free_shipping_cart_' . $zone['zone_id'], true),
                ));
            echo '</div>';
            echo '<div class="wcvs_edit_options_group_row flex-row">';
                // Custom Cost for Zone
                woocommerce_wp_text_input(array(
                    'id' => 'custom_cost_' . $zone['zone_id'],
                    'label' => __('Custom Cost for Zone', 'wcvs'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'description' => __('Enter custom cost for this shipping zone.', 'wcvs'),
                    'value' => get_post_meta($post->ID, 'custom_cost_' . $zone['zone_id'], true),
                ));
                // Charge Custom Cost per #
                woocommerce_wp_text_input(array(
                    'id' => 'custom_cost_charge_per_' . $zone['zone_id'],
                    'label' => __('Charge Cost per', 'wcvs'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'description' => __('Leave blank to charge the single custom cost regardless of the quantity. Otherwise, a number like 8 will charge the custom cost per 8 instances of the product in the cart.', 'wcvs'),
                    'value' => get_post_meta($post->ID, 'custom_cost_charge_per_' . $zone['zone_id'], true),
                ));
                // Max Shipping Cost for Zone
                woocommerce_wp_text_input(array(
                    'id' => 'max_shipping_cost_' . $zone['zone_id'],
                    'label' => __('Max Cost for Zone', 'wcvs'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'description' => __('Enter the maximum shipping cost for this shipping zone.', 'wcvs'),
                    'value' => get_post_meta($post->ID, 'max_shipping_cost_' . $zone['zone_id'], true),
                ));
                // Free Shipping if Shipping More than Specified Quantity
                woocommerce_wp_text_input(array(
                    'id' => 'free_shipping_if_quantity_' . $zone['zone_id'],
                    'label' => __('Free Shipping if Quantity is', 'wcvs'),
                    'type' => 'number',
                    'desc_tip' => true,
                    'description' => __('If the product quantity is at least the specified amount, then the product will ship for free. This refers to this product type only, not the entire cart.', 'wcvs'),
                    'value' => get_post_meta($post->ID, 'free_shipping_if_quantity_' . $zone['zone_id'], true),
                ));
            echo '</div>';
        echo '</div>';
    }

    echo '</div>';
}

// Save custom fields data
add_action('woocommerce_process_product_meta', 'wcvs_save_custom_shipping_options');
function wcvs_save_custom_shipping_options($product_id) {
    foreach (WC_Shipping_Zones::get_zones() as $zone) {
        $disable_shipping = isset($_POST['disable_shipping_' . $zone['zone_id']]) ? 'yes' : 'no';
        update_post_meta($product_id, 'disable_shipping_' . $zone['zone_id'], $disable_shipping);

        // $disable_shipping_residential = isset($_POST['disable_shipping_residential_' . $zone['zone_id']]) ? 'yes' : 'no';
        // update_post_meta($product_id, 'disable_shipping_residential_' . $zone['zone_id'], $disable_shipping_residential);

        // $disable_shipping_commercial = isset($_POST['disable_shipping_commercial_' . $zone['zone_id']]) ? 'yes' : 'no';
        // update_post_meta($product_id, 'disable_shipping_commercial_' . $zone['zone_id'], $disable_shipping_commercial);

        $free_shipping = isset($_POST['free_shipping_' . $zone['zone_id']]) ? 'yes' : 'no';
        update_post_meta($product_id, 'free_shipping_' . $zone['zone_id'], $free_shipping);

        $free_shipping_cart = isset($_POST['free_shipping_cart_' . $zone['zone_id']]) ? 'yes' : 'no';
        update_post_meta($product_id, 'free_shipping_cart_' . $zone['zone_id'], $free_shipping_cart);

        $custom_cost = isset($_POST['custom_cost_' . $zone['zone_id']]) ? sanitize_text_field($_POST['custom_cost_' . $zone['zone_id']]) : '';
        update_post_meta($product_id, 'custom_cost_' . $zone['zone_id'], $custom_cost);

        $custom_cost_charge_per = isset($_POST['custom_cost_charge_per_' . $zone['zone_id']]) ? sanitize_text_field($_POST['custom_cost_charge_per_' . $zone['zone_id']]) : '';
        update_post_meta($product_id, 'custom_cost_charge_per_' . $zone['zone_id'], $custom_cost_charge_per);

        $max_shipping_cost = isset($_POST['max_shipping_cost_' . $zone['zone_id']]) ? sanitize_text_field($_POST['max_shipping_cost_' . $zone['zone_id']]) : '';
        update_post_meta($product_id, 'max_shipping_cost_' . $zone['zone_id'], $max_shipping_cost);

        $free_shipping_if_quantity = isset($_POST['free_shipping_if_quantity_' . $zone['zone_id']]) ? sanitize_text_field($_POST['free_shipping_if_quantity_' . $zone['zone_id']]) : '';
        update_post_meta($product_id, 'free_shipping_if_quantity_' . $zone['zone_id'], $free_shipping_if_quantity);
    }
}

function wcvs_get_woocommerce_zone_id( $package ) {
    $woocommerce_zone_id = 0;
    $shipping_zones = WC_Shipping_Zones::get_zones();
    $shipping_state = $package['destination']['state'];

    // Get the zone ID of the package
    $zone = WC_Shipping_Zones::get_zone_matching_package( $package );
    if ( $zone ) {
        $woocommerce_zone_id = $zone->get_id();
    }

    // If the zone ID is 0, the zone is not known or there is an error.
    if ($woocommerce_zone_id == 0) {
        if (empty($shipping_state)) {
            return $woocommerce_zone_id;
        } else {
            foreach ((array) $shipping_zones as $key => $the_zone ) {
                $zone_locations = $the_zone['zone_locations'];
    
                foreach ($zone_locations as $location_key => $location) {
                    // Access the state code from the 'code' property, will be country:state such as US:TX.
                    $code = $location->code;
    
                    if (!empty($code)) {
                        // Extract the state portion from the code, such as 'TX' from 'US:TX'.
                        $state_code = explode(':', $code)[1];
                        if ($state_code === $shipping_state) {
                            $woocommerce_zone_id = $the_zone['zone_id'];
                            break 2; // Break both loops
                        }
                    }
                }
            }
        }
    }

    return $woocommerce_zone_id;
}

function wcvs_get_method_visibility( $has_free_shipping_coupon, $rate_name, $wcvs_rate_names, $final_rate_price) {
    $boxes = $_SESSION['wcvs_boxes'];
    // There are several ways that methods may appear in the cart or at checkout.
    // Dynamic, Custom, Oversized, and Disabled.
    // This function returns one of these values as a string.
    // Rates will be renamed or hidden based on this value.
    $max_weight = get_option('wcvs_maxwt_' . $rate_name, 75);
    $enable_max_weight = get_option('wcvs_enable_maxwt_' . $rate_name, 1);

    $is_custom = false;
    $is_oversized = false;
    $is_disabled = false;

    // Loop through the box rates to determine visibility.
    foreach ($boxes as $box) {
        if ($box['custom_options']['total_custom_rate'] > 0) { $is_custom = true; }
        if ($box[$rate_name]['rate_parts']['oversize_rate'] > 0) { $is_oversized = true; }
        if ($box['custom_options']['disable_shipping'] === 'yes') { $is_disabled = true; }
    }

    if ( $enable_max_weight && $_SESSION['wcvs_package_info']['heaviest_irregular_box'] >= $max_weight ) {
        $is_oversized = true;
    }

    if ( $has_free_shipping_coupon ) { // The coupon overrides all other concerns.
        return 'HasCoupon';
    } elseif ($is_disabled) { // If any box is disabled, all methods are hidden.
        return 'Disabled';
    } elseif ($final_rate_price == 0) { // If the final rate price is 0.
        return 'Free';
    } elseif ($is_oversized) { // If any box is oversized or overweight, all other methods are hidden.
        return 'Oversized';
    } elseif ($is_custom) { // If any box has a custom rate, all other methods are hidden.
        return 'Standard';
    } else {
        return 'Dynamic'; // If none of the above conditions are met, all methods are visible.
    }
}

function wcvs_register_package_information( $package, $woocommerce_zone_id ) {
    if (!isset($_SESSION['wcvs_package_info'])) {
        $_SESSION['wcvs_package_info'] = array(
            'product_count' => 0,
            'shipping_postcode' => 0,
            'shipping_state' => "",
            'carrier' => "",
            'shipping_zone_name' => '',
            'large_box_count' => 0,
            'shared_weight' => 0,
            'shared_value' => 0,
            'regular_adjustment_amount' => 0,
            'extracare_adjustment_amount' => 0,
            'oversized_adjustment_amount' => 0,
            'oversized_in_cart' => 0,
            'heaviest_irregular_box' => 0,
            'free_shipping_cart' => false
        );
    }

    $_SESSION['wcvs_package_info']['product_count'] = count($package['contents']);
    $_SESSION['wcvs_package_info']['shipping_postcode'] = $package['destination']['postcode'];
    $_SESSION['wcvs_package_info']['shipping_state'] = $package['destination']['state'];
    $_SESSION['wcvs_package_info']['shipping_zone_name'] = wcvs_get_shipping_zone_name( $woocommerce_zone_id );

    if ($package['destination']['country'] === 'US') {
        $_SESSION['wcvs_package_info']['carrier'] = 'UPS';
    } else {
        $_SESSION['wcvs_package_info']['carrier'] = 'Canpar';
    }

    // Reset package information.
    $_SESSION['wcvs_package_info']['shared_weight'] = 0;
    $_SESSION['wcvs_package_info']['shared_value'] = 0;
    $_SESSION['wcvs_package_info']['regular_adjustment_amount'] = 0;
    $_SESSION['wcvs_package_info']['extracare_adjustment_amount'] = 0;
    $_SESSION['wcvs_package_info']['oversized_adjustment_amount'] = 0;
    $_SESSION['wcvs_package_info']['oversized_in_cart'] = 0;
}

function wcvs_reset_rate_information( $rate_name ) {
    if (!isset($_SESSION['wcvs_' . $rate_name . '_info'])) {
        $_SESSION['wcvs_' . $rate_name . '_info'] = array(
            'total_cost_regular' => 0,
            'total_cost_irregular' => 0,
            'shared_base_rate' => 0,
            'shared_dv_charge' => 0,
            'shared_fuel_cost' => 0,
            'regular_discount' => 0,
            'irregular_discount' => 0
        );
    }

    $_SESSION['wcvs_' . $rate_name . '_info'] = array(
        'total_cost_regular' => 0,
        'total_cost_irregular' => 0,
        'shared_base_rate' => 0,
        'shared_dv_charge' => 0,
        'shared_fuel_cost' => 0,
        'regular_discount' => 0,
        'irregular_discount' => 0
    );
}

function render_tooltip($tooltip_text) {
    echo '<div class="wcvs-tooltip">
            <span class="wcvs-help-tip">?</span>
            <span class="wcvs-help-tip-content">'. htmlspecialchars($tooltip_text) .'</span>
        </div>';
}

function wcvs_identify_rate_type( $id ) {
    // The rate's $args['id'] is used to determine the rate type.
    // The rate idea will be something like 'flat_rate:6' or 'free_shipping:7'.
    // The rate type is the first part of the ID, such as 'flat_rate' or 'free_shipping'.
    $rate_type = explode(':', $id)[0];
    return $rate_type;
}


