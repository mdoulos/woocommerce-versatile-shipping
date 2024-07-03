<?php
// ------------------------------
// Primary Functions to Add WCVS Controls to the Admin Area to Manage Shipping
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Function to add a new admin menu item
function add_wcvs_shipping_menu_item() {
    // Add menu item
    add_menu_page(
        'Shipping',                   // Page title
        'Shipping',                   // Menu title
        'manage_options',             // Capability required to access the menu item
        'wcvs_shipping_page',              // Menu slug
        'wcvs_shipping_page_content',      // Callback function to display page content
        'dashicons-airplane',         // Icon for the menu item (you can choose from dashicons)
        56                            // Position of the menu item
    );
}

function wcvs_shipping_page_content() {
    // Get Debugger options
    $enable_full_debugger = get_option('wcvs_enable_full_debugger', true);
    $enable_debugger_string = get_option('wcvs_enable_debugger_string', true);

    // Get Surcharge options
    $fuel_surcharge = floatval(get_option('wcvs_fuel_surcharge', 0.2));
    $dv_surcharge = floatval(get_option('wcvs_dv_surcharge', 2));
    $addwt_surcharge = floatval(get_option('wcvs_addwt_surcharge', 100));
    $addsz_surcharge = floatval(get_option('wcvs_addsz_surcharge', 100));
    $peak_surcharge_addhandling = floatval(get_option('wcvs_peak_surcharge_addhandling', 0));
    $enable_fuel_surcharge = get_option('wcvs_enable_fuel_surcharge', true);
    $enable_dv_surcharge = get_option('wcvs_enable_dv_surcharge', true);
    $enable_addwt_surcharge = get_option('wcvs_enable_addwt_surcharge', true);
    $enable_addsz_surcharge = get_option('wcvs_enable_addsz_surcharge', true);
    $enable_peak_surcharge_addhandling = get_option('wcvs_enable_peak_surcharge_addhandling', true);

    // Get Measurement options
    $dim_factor = floatval(get_option('wcvs_dim_factor', 139));
    $dim_factor_2nd = floatval(get_option('wcvs_dim_factor_2nd', 115));
    $addwt_weight = intval(get_option('wcvs_addwt_weight', 50));
    $addsz_length = intval(get_option('wcvs_addsz_length', 48));
    $addsz_width = intval(get_option('wcvs_addsz_width', 30));
    $addsz_lpg = intval(get_option('wcvs_addsz_lpg', 105));
    $enable_addwt_weight = get_option('wcvs_enable_addwt_weight', true);
    $enable_addsz_length = get_option('wcvs_enable_addsz_length', true);
    $enable_addsz_width = get_option('wcvs_enable_addsz_width', true);
    $enable_addsz_lpg = get_option('wcvs_enable_addsz_lpg', true);

    // Get Adjustments & Discounts options
    $regular_adjustment = floatval(get_option('wcvs_regular_adjustment', 1));
    $extracare_adjustment = floatval(get_option('wcvs_extracare_adjustment', 1));
    $oversized_adjustment = floatval(get_option('wcvs_oversized_adjustment', 1));
    $discount_lrgbox_regular = floatval(get_option('wcvs_discount_lrgbox_regular', 1));
    $discount_multiple_lrgbox = floatval(get_option('wcvs_discount_multiple_lrgbox', 1));
    $discount_secondary_large_products = floatval(get_option('wcvs_discount_secondary_large_products', 1));
    $enable_discount_lrgbox_regular = get_option('wcvs_enable_discount_lrgbox_regular', true);
    $enable_discount_multiple_lrgbox = get_option('wcvs_enable_discount_multiple_lrgbox', true);
    $enable_discount_secondary_large_products = get_option('wcvs_enable_discount_secondary_large_products', true);

    // Shipping Zones and Rate Names
    $shipping_zones = WC_Shipping_Zones::get_zones();
    $wcvs_rates = [];
    foreach ($shipping_zones as $zone) {
        $shipping_methods = $zone['shipping_methods'];
        $zone_name = $zone['zone_name'];

        foreach ($shipping_methods as $method) {
            if ($method->id === 'flat_rate') {
                $method_title = $method->title;

                if (!array_key_exists($method_title, $wcvs_rates)) {
                    $wcvs_rates[$method_title] = [];
                }

                if (!in_array($zone_name, $wcvs_rates[$method_title])) {
                    $wcvs_rates[$method_title][] = $zone_name;
                }
            }
        }
    }

    include 'wcvs-admin-form.php';

    // Handle form submission
    if (isset($_POST['save'])) {
        // Save Debugging options
        update_option('wcvs_enable_full_debugger', isset($_POST['wcvs_admin_enable_full_debugger']));
        update_option('wcvs_enable_debugger_string', isset($_POST['wcvs_admin_enable_debugger_string']));

        // Save Surcharge options
        update_option('wcvs_fuel_surcharge', floatval($_POST['wcvs_admin_fuel_surcharge']));
        update_option('wcvs_dv_surcharge', floatval($_POST['wcvs_admin_dv_surcharge']));
        update_option('wcvs_addwt_surcharge', floatval($_POST['wcvs_admin_addwt_surcharge']));
        update_option('wcvs_addsz_surcharge', floatval($_POST['wcvs_admin_addsz_surcharge']));
        update_option('wcvs_peak_surcharge_addhandling', floatval($_POST['wcvs_admin_peak_surcharge_addhandling']));
        update_option('wcvs_enable_fuel_surcharge', isset($_POST['wcvs_admin_enable_fuel_surcharge']));
        update_option('wcvs_enable_dv_surcharge', isset($_POST['wcvs_admin_enable_dv_surcharge']));
        update_option('wcvs_enable_addwt_surcharge', isset($_POST['wcvs_admin_enable_addwt_surcharge']));
        update_option('wcvs_enable_addsz_surcharge', isset($_POST['wcvs_admin_enable_addsz_surcharge']));
        update_option('wcvs_enable_peak_surcharge_addhandling', isset($_POST['wcvs_admin_enable_peak_surcharge_addhandling']));

        // Save Measurement options
        update_option('wcvs_dim_factor', floatval($_POST['wcvs_admin_dim_factor']));
        update_option('wcvs_dim_factor_2nd', floatval($_POST['wcvs_admin_dim_factor_2nd']));
        update_option('wcvs_addwt_weight', intval($_POST['wcvs_admin_addwt_weight']));
        update_option('wcvs_addsz_length', intval($_POST['wcvs_admin_addsz_length']));
        update_option('wcvs_addsz_width', intval($_POST['wcvs_admin_addsz_width']));
        update_option('wcvs_addsz_lpg', intval($_POST['wcvs_admin_addsz_lpg']));
        update_option('wcvs_enable_addwt_weight', isset($_POST['wcvs_admin_enable_addwt_weight']));
        update_option('wcvs_enable_addsz_length', isset($_POST['wcvs_admin_enable_addsz_length']));
        update_option('wcvs_enable_addsz_width', isset($_POST['wcvs_admin_enable_addsz_width']));
        update_option('wcvs_enable_addsz_lpg', isset($_POST['wcvs_admin_enable_addsz_lpg']));

        // Save Adjustments & Discounts options
        update_option('wcvs_regular_adjustment', floatval($_POST['wcvs_admin_regular_adjustment']));
        update_option('wcvs_extracare_adjustment', floatval($_POST['wcvs_admin_extracare_adjustment']));
        update_option('wcvs_oversized_adjustment', floatval($_POST['wcvs_admin_oversized_adjustment']));
        update_option('wcvs_discount_lrgbox_regular', floatval($_POST['wcvs_admin_discount_lrgbox_regular']));
        update_option('wcvs_discount_multiple_lrgbox', floatval($_POST['wcvs_admin_discount_multiple_lrgbox']));
        update_option('wcvs_discount_secondary_large_products', floatval($_POST['wcvs_admin_discount_secondary_large_products']));
        update_option('wcvs_enable_discount_lrgbox_regular', isset($_POST['wcvs_admin_enable_discount_lrgbox_regular']));
        update_option('wcvs_enable_discount_multiple_lrgbox', isset($_POST['wcvs_admin_enable_discount_multiple_lrgbox']));
        update_option('wcvs_enable_discount_secondary_large_products', isset($_POST['wcvs_admin_enable_discount_secondary_large_products']));

        $rate_num = 0;

        // Save Price List options
        foreach ($wcvs_rates as $rate_name => $zones) {
            $lc_rate_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($rate_name));
            $rate_num++;

            // Save the rate prefix, rate name and enabled status
            update_option('wcvs_' . $lc_rate_name . '_rate_prefix', sanitize_text_field($_POST[$lc_rate_name . '_rate_prefix']));
            update_option('wcvs_' . $lc_rate_name . '_rate_name', sanitize_text_field($_POST[$lc_rate_name . '_rate_name']));
            update_option('wcvs_enable_' . $lc_rate_name . '_rate', isset($_POST['wcvs_admin_enable_' . $lc_rate_name . '_rate']));

            // Save the threshold weight values for each rate
            update_option('wcvs_' . $lc_rate_name . '_threshold_1', intval($_POST[$lc_rate_name . '_threshold_1']));
            update_option('wcvs_' . $lc_rate_name . '_threshold_2', intval($_POST[$lc_rate_name . '_threshold_2']));
            update_option('wcvs_' . $lc_rate_name . '_threshold_3', intval($_POST[$lc_rate_name . '_threshold_3']));
            update_option('wcvs_' . $lc_rate_name . '_threshold_4', intval($_POST[$lc_rate_name . '_threshold_4']));

            // Save the max weight value for each rate, except the first one.
            if ($rate_num != 1) {
                update_option('wcvs_maxwt_' . $lc_rate_name, intval($_POST['wcvs_admin_maxwt_' . $lc_rate_name]));
                update_option('wcvs_enable_maxwt_' . $lc_rate_name, isset($_POST['wcvs_admin_enable_maxwt_' . $lc_rate_name]));
            }
            
            // Save the threshold price values for each zone available for each rate
            foreach ($zones as $zone) {
                $lc_zone_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($zone));
                update_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_1', floatval($_POST[$lc_zone_name . '_' . $lc_rate_name . '_price_1']));
                update_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_2', floatval($_POST[$lc_zone_name . '_' . $lc_rate_name . '_price_2']));
                update_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_3', floatval($_POST[$lc_zone_name . '_' . $lc_rate_name . '_price_3']));
                update_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_4', floatval($_POST[$lc_zone_name . '_' . $lc_rate_name . '_price_4']));
                update_option('wcvs_enable_' . $lc_rate_name . '_rate_in_' . $lc_zone_name, isset($_POST['wcvs_admin_enable_' . $lc_rate_name . '_rate_in_' . $lc_zone_name]));
                update_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_transit_time', sanitize_text_field($_POST[$lc_zone_name . '_' . $lc_rate_name . '_transit_time']));
            }
        }

        // Save Oversized Price list options
        foreach ($shipping_zones as $zone) {
            $unedited_zone_name = $zone['zone_name'];
            $lc_zone_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($zone['zone_name']));

            update_option('wcvs_' . $lc_zone_name . '_os097', floatval($_POST['os097_' . $lc_zone_name]));
            update_option('wcvs_' . $lc_zone_name . '_os097_more', floatval($_POST['os097_more_' . $lc_zone_name]));
            update_option('wcvs_' . $lc_zone_name . '_os108', floatval($_POST['os108_' . $lc_zone_name]));
            update_option('wcvs_' . $lc_zone_name . '_os108_more', floatval($_POST['os108_more_' . $lc_zone_name]));
            update_option('wcvs_' . $lc_zone_name . '_ow150', floatval($_POST['ow150_' . $lc_zone_name]));
            update_option('wcvs_' . $lc_zone_name . '_ow150_more', floatval($_POST['ow150_more_' . $lc_zone_name]));
        }

        // Redirect to the same page after form submission
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }
}

// Hook to add the shipping menu item
add_action('admin_menu', 'add_wcvs_shipping_menu_item');
