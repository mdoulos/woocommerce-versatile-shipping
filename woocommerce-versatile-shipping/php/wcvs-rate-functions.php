<?php
// ------------------------------
// Primary Functions to Calculate Rates
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function wcvs_calculate_isolated_box_costs( $rate_name, $zone_name, $carrier, $wcvs_rate_names ) {
    $boxes = $_SESSION['wcvs_boxes'];
    $_SESSION['wcvs_package_info']['large_box_count'] = 0;
    $_SESSION['wcvs_package_info']['heaviest_irregular_box'] = 0;
    $_SESSION['wcvs_' . $rate_name . '_info']['total_cost_irregular'] = 0;
    $_SESSION['wcvs_package_info']['free_shipping_cart'] = false;

    foreach ( $boxes as &$box ) {
        // Rate Construction for Isolated Boxes.
        if ( $box['box_pack_type'] == 'Isolated' ) {
            $has_custom_rate = $box['custom_options']['total_custom_rate'] > 0 ? true : false;

            if ( $has_custom_rate == false ) {
                if ( $box['carrier_codes']['size_category'] == 'Oversized' ) { // Oversize Rate (Isolated Products)
                    $box[$rate_name]['rate_parts']['oversize_base_rate'] = wcvs_construct_oversize_base_rate( $box, $carrier, $zone_name);
                    $box[$rate_name]['rate_parts']['oversize_repeating_cost'] = wcvs_construct_oversize_repeating_cost( $box, $carrier, $zone_name );
                    $box[$rate_name]['rate_parts']['oversize_rate'] = wcvs_combine_oversize_rate( $box[$rate_name]['rate_parts']['oversize_base_rate'], $box[$rate_name]['rate_parts']['oversize_repeating_cost'], $box['number_of_products'] );
                    $_SESSION['wcvs_package_info']['oversized_in_cart'] = 1;
                } else { // Dynamic Rate for products with Additional Handling fees (Isolated Products)
                    $billable_weight = $wcvs_rate_names[0] === $rate_name ? $box['carrier_codes']['billable_weight'] : $box['carrier_codes']['billable_weight_2nd'];
                    $box[$rate_name]['rate_parts']['base_rate'] = wcvs_approximate_base_cost( $billable_weight, $rate_name, $zone_name, $carrier );
                    $box[$rate_name]['rate_parts']['dv_charge'] = wcvs_construct_dv_rate( $box['box_price'], $carrier );
                    $box[$rate_name]['rate_parts']['fuel_cost'] = wcvs_construct_fuel_cost( $box[$rate_name]['rate_parts']['base_rate'], $carrier );
                    $box[$rate_name]['rate_parts']['extracare_fees'] = wcvs_construct_extracare_fees( $box, $carrier );
                    $box[$rate_name]['rate_parts']['peak_surcharge'] = wcvs_construct_peak_surcharge( $box, $carrier );
                    $box[$rate_name]['rate_parts']['dynamic_rate'] = wcvs_calculate_dynamic_rate_for_box( $box, $rate_name );
                    $box[$rate_name]['rate_parts']['additional_large_cost'] = wcvs_calculate_cost_for_additional_large_products( $box, $rate_name);
                }
            }

            if ( $box['custom_options']['is_free_shipping_in_zone_cart'] === 'yes' ) { 
                $_SESSION['wcvs_package_info']['free_shipping_cart'] = true;
            }

            // Record Box Cost
            $box_cost = wcvs_calculate_box_cost( $box, $rate_name );
            $box[$rate_name]['actual_box_cost'] = $box_cost;

            if ( $box['custom_options']['is_free_shipping_in_zone'] === 'yes' && $wcvs_rate_names[0] === $rate_name ) { 
                $box[$rate_name]['box_cost'] = 0; // Free Shipping only for the first rate.
            } else {
                $box[$rate_name]['box_cost'] = $box_cost;
            }

            // Regular products may be isolated if they have a custom rate.
            if ( $box['carrier_codes']['size_category'] != 'Regular' ) {
                $_SESSION['wcvs_package_info']['large_box_count']++;
            }

            $_SESSION['wcvs_' . $rate_name . '_info']['total_cost_irregular'] += $box[$rate_name]['box_cost'];
            $_SESSION['wcvs_package_info']['heaviest_irregular_box'] = max( $_SESSION['wcvs_package_info']['heaviest_irregular_box'], $box['measurements']['actual_weight'] );
        }
    }

    // Save the updated $boxes array back to the session.
    $_SESSION['wcvs_boxes'] = $boxes;
}

function wcvs_calculate_shared_box_costs( $rate_name, $zone_name, $carrier ) {
    // Calculate the Shared Rate (Shared product boxes do not produce individual box rates).
    $base_rate = wcvs_approximate_base_cost( $_SESSION['wcvs_package_info']['shared_weight'], $rate_name, $zone_name, $carrier );
    $dv_charge = wcvs_construct_dv_rate( $_SESSION['wcvs_package_info']['shared_value'], $carrier );
    $fuel_cost = wcvs_construct_fuel_cost( $base_rate, $carrier );
    $shared_dynamic_rate = $base_rate + $dv_charge + $fuel_cost;
    $regular_adjustment = floatval(get_option('wcvs_regular_adjustment', 1));

    $_SESSION['wcvs_' . $rate_name . '_info']['shared_base_rate'] = $base_rate;
    $_SESSION['wcvs_' . $rate_name . '_info']['shared_dv_charge'] = $dv_charge;
    $_SESSION['wcvs_' . $rate_name . '_info']['shared_fuel_cost'] = $fuel_cost;
    $_SESSION['wcvs_' . $rate_name . '_info']['total_cost_regular'] = $shared_dynamic_rate * $regular_adjustment;

    if ( $regular_adjustment < 1 ) {
        $_SESSION['wcvs_package_info']['regular_adjustment_amount'] += $shared_dynamic_rate - ($shared_dynamic_rate * $regular_adjustment);
    } elseif ( $regular_adjustment > 1 ) {
        $_SESSION['wcvs_package_info']['regular_adjustment_amount'] += ($shared_dynamic_rate * $regular_adjustment) - $shared_dynamic_rate;
    }
}

function wcvs_get_shipping_zone_name( $woocommerce_zone_id ) {
    $shipping_zone = WC_Shipping_Zones::get_zone( $woocommerce_zone_id );
    if ($shipping_zone) {
        $zone_data = $shipping_zone->get_data();
        return $zone_data['zone_name'];
    } else {
        return 'Local';
    }
}

function wcvs_approximate_base_cost( $billable_weight, $rate_name, $zone_name, $carrier ) {
    $base_cost = 0;

    if ( $billable_weight <= 0 ) {
        return 0;
    }

    // Define your price list array
    $price_list = array(
        get_option('wcvs_' . $rate_name . '_threshold_1', 1) => get_option('wcvs_' . $zone_name . '_' . $rate_name . '_price_1', 0),
        get_option('wcvs_' . $rate_name . '_threshold_2', 50) => get_option('wcvs_' . $zone_name . '_' . $rate_name . '_price_2', 0),
        get_option('wcvs_' . $rate_name . '_threshold_3', 100) => get_option('wcvs_' . $zone_name . '_' . $rate_name . '_price_3', 0),
        get_option('wcvs_' . $rate_name . '_threshold_4', 150) => get_option('wcvs_' . $zone_name . '_' . $rate_name . '_price_4', 0),
    );

    // Establish the price thresholds to compare the weight against.
    $price_thresholds = array_keys($price_list);

    // Max weight is the highest threshold defined for the rate in the pricelist.
    $max_weight = max($price_thresholds); // For the following examples, assume the max weight is 150 pounds.
    $increments_of_max_weight = floor($billable_weight / $max_weight); // Example, if 678 pounds, increments_of_max_weight = 4.
    $remainder = $billable_weight % $max_weight; // Example, if 678 pounds, remainder = 78.

    // Get the fourth price value (for the max weight threshold) and calculate the base cost.
    $price_for_max = $price_list[$max_weight];
    $base_cost = $increments_of_max_weight * $price_for_max;

    // Calculate the cost of the remainder or the entire weight if under the max weight.
    // If the weight is less than the first threshold, ie the threshold is 10 and the weight is 5, the cost is the first threshold.
    if ( $billable_weight < $price_thresholds[0] ) {
        $base_cost = $price_list[$price_thresholds[0]];
    } else {
        // The weight is greater than the first threshold, so we need to find the correct upper and lower bounds.
        $lower_bound = 1; // Default lower bound.
        $upper_bound = 150; // Default upper bound.

        // Goes through the price list for the zone and finds the correct upper and lower bounds.
        // Example: If the billable weight is 75, the lower bound is 50 and the upper bound is 100.
        foreach ($price_thresholds as $threshold) {
            if ($remainder >= $threshold) {
                $lower_bound = $threshold;
            } else {
                $upper_bound = $threshold;
                break;
            }
        }

        // The weight and price threshold above and below the billable weight.
        $lower_price = $price_list[$lower_bound];
        $upper_price = $price_list[$upper_bound];
        
        // Calculate the approximate cost based on the billable weight.
        $base_cost += $lower_price + (($remainder - $lower_bound) * (($upper_price - $lower_price) / ($upper_bound - $lower_bound)));

        // Example: If the billable weight is 67, the lower bound is 50 and the upper bound is 100.
        // The lower price is 26.17 and the upper price is 66.16.
        // The approximate cost is 
        // $26.17 + ((67 pounds - 50 pounds) * (($66.16 - $26.17) / (100 pounds - 50 pounds)))
        // We're solving for two values: the number of pounds over the lower bound, and the pound difference between bounds.
        // $26.17 + (17 pounds * ($39.99 / 50 pounds)) -- Here we solve that the weight is 17 pounds over the lower bound.
        // $26.17 + (17 pounds * $0.79) -- Here we solve the price per pound in the range, which is $0.79.
        // $26.17 + $13.43
        // Since 50 pounds is $26.17, and we're 17 pounds over with a cost of 0.79 per pound, the cost is $39.60.
        // Cost = $39.60
    }

    if ( $carrier === 'Canpar' ) {
        $base_cost += 1.00; // Add a dollar to the base cost for the base rate - to approximate the Residential Surcharge.
    }
    
    return $base_cost;
}

function wcvs_construct_dv_rate( $box_price, $carrier ) {
    $dv_charge = 0;
    $dv_surcharge = floatval(get_option('wcvs_dv_surcharge', 2));
    $enable_dv_surcharge = get_option('wcvs_enable_dv_surcharge', true);

    if ( $enable_dv_surcharge ) {
        if ( ! isset($dv_surcharge) ) {
            if ( $carrier === 'UPS' ) {
                $dv_surcharge = 1.40;
            } elseif ( $carrier === 'Canpar' ) {
                $dv_surcharge = 4.00;
            } else {
                $dv_surcharge = 2.00;
            }
        }

        if ( $carrier === 'Canpar' ) {
            // If the price is above $100, the charge is $4 per $100. The price is rounded to the next 100.
            if ( $box_price > 100 ) {
                $dv_charge = round ( (ceil( $box_price / 100 ) - 1) * $dv_surcharge, 2 );
            }
        } else {
            $dv_charge = round ( ($box_price / 100) * $dv_surcharge, 2 );
        } 
    }

    return $dv_charge;
}

function wcvs_construct_fuel_cost( $base_rate, $carrier ) {
    $fuel_cost = 0;
    $fuel_surcharge_percentage = floatval(get_option('wcvs_fuel_surcharge', 0.20));
    $enable_fuel_surcharge = get_option('wcvs_enable_fuel_surcharge', true);

    if ( $enable_fuel_surcharge ) {
        if ( ! isset($fuel_surcharge_percentage) ) {
            if ( $carrier === 'UPS' ) {
                $fuel_surcharge_percentage = 0.15;
            } elseif ( $carrier === 'Canpar' ) {
                $fuel_surcharge_percentage = 0.30;
            } else {
                $fuel_surcharge_percentage = 0.20;
            }
        }
    
        $fuel_cost = round ( $base_rate * $fuel_surcharge_percentage, 2 );
    }

    return $fuel_cost;
}

function wcvs_construct_extracare_fees( $box, $carrier ) {
    $extracare_fees = 0;
    $addwt_surcharge = floatval(get_option('wcvs_addwt_surcharge', 100));
    $addsz_surcharge = floatval(get_option('wcvs_addsz_surcharge', 100));
    $enable_addwt_surcharge = get_option('wcvs_enable_addwt_surcharge', true);
    $enable_addsz_surcharge = get_option('wcvs_enable_addsz_surcharge', true);

    if ( $box['carrier_codes']['size_category'] == 'ExtraCare' ) {
        $size_code = $box['carrier_codes']['size_code'];

        if ( isset($addwt_surcharge) && $enable_addwt_surcharge && $size_code == 'ADDWT') {
            $extracare_fees = $addwt_surcharge;
        } elseif ( isset($addsz_surcharge) && $enable_addsz_surcharge && $size_code == 'ADDSZ') {
            $extracare_fees = $addsz_surcharge;
        }
    }

    return $extracare_fees;
}

function wcvs_construct_peak_surcharge( $box, $carrier ) {
    $peak_surcharge = 0;
    $peak_surcharge_addhandling = floatval(get_option('wcvs_peak_surcharge_addhandling', 0));
    $enable_peak_surcharge_addhandling = get_option('wcvs_enable_peak_surcharge_addhandling', true);

    if ( $box['carrier_codes']['size_category'] == 'ExtraCare' ) {
        $size_code = $box['carrier_codes']['size_code'];
    
        if ( isset($peak_surcharge_addhandling) && $enable_peak_surcharge_addhandling ) {
            $peak_surcharge = $peak_surcharge_addhandling;
        }
    }

    return $peak_surcharge;
}

function wcvs_calculate_cost_for_additional_large_products( $box, $rate_name) {
    // Discount Example: Apply a 75% discount to additional instances of an additional handling product. 
    // Example: Charge $20 for a four foot product, Charge $4 for the second, third, and so on
    // This function returns the combined cost of all additional handling products in the box minus the first.
    $additional_large_cost = 0;

    if ( $box['carrier_codes']['size_category'] == 'ExtraCare' ) {
        $quantity = $box['number_of_products'];
        
        if ( $quantity > 1 ) {
            $discount_secondary_large_products = floatval(get_option('wcvs_discount_secondary_large_products', 1));
            $enable_discount_secondary_large_products = get_option('wcvs_enable_discount_secondary_large_products', true);

            if ( $enable_discount_secondary_large_products ) {
                $additional_large_cost = ($box[$rate_name]['rate_parts']['dynamic_rate'] * $discount_secondary_large_products) * ($quantity - 1); 
            } else {
                $additional_large_cost = $box[$rate_name]['rate_parts']['dynamic_rate'] * ($quantity - 1);
            }
        }
    }

    return $additional_large_cost;
}

function wcvs_calculate_box_cost( $box, $rate_name ) {
    if ( $box['custom_options']['total_custom_rate'] > 0 ) { 
        // Overrides all other rates, except free shipping.
        return $box['custom_options']['total_custom_rate'];

    } elseif ( $box[$rate_name]['rate_parts']['oversize_rate'] > 0 ) {
        // Overrides Dynamic Rates, but not custom rates.
        $oversize_rate = $box[$rate_name]['rate_parts']['oversize_rate'];
        $oversized_adjustment = floatval(get_option('wcvs_oversized_adjustment', 1));

        if ( $oversized_adjustment < 1 ) {
            $_SESSION['wcvs_package_info']['oversized_adjustment_amount'] += $oversize_rate - ($oversize_rate * $oversized_adjustment);
        } elseif ( $oversized_adjustment > 1 ) {
            $_SESSION['wcvs_package_info']['oversized_adjustment_amount'] += ($oversize_rate * $oversized_adjustment) - $oversize_rate;
        }

        return $oversize_rate * $oversized_adjustment;

    } elseif ( $box['carrier_codes']['size_category'] == 'ExtraCare' ) {
        // Dynamic Rate is the first instance, additional large cost is the rest of the same product type.
        $extracare_rate = $box[$rate_name]['rate_parts']['dynamic_rate'] + $box[$rate_name]['rate_parts']['additional_large_cost'];
        $extracare_adjustment = floatval(get_option('wcvs_extracare_adjustment', 1));

        if ( $extracare_adjustment < 1 ) {
            $_SESSION['wcvs_package_info']['extracare_adjustment_amount'] += $extracare_rate - ($extracare_rate * $extracare_adjustment);
        } elseif ( $extracare_adjustment > 1 ) {
            $_SESSION['wcvs_package_info']['extracare_adjustment_amount'] += ($extracare_rate * $extracare_adjustment) - $extracare_rate;
        }

        return $extracare_rate * $extracare_adjustment;

    } else {
        return $box[$rate_name]['rate_parts']['dynamic_rate'];
    }
}

function wcvs_calculate_final_rate_price( $rate_name ) {
    $final_rate = 0;
    $discount_lrgbox_regular = floatval(get_option('wcvs_discount_lrgbox_regular', 1));
    $discount_multiple_lrgbox = floatval(get_option('wcvs_discount_multiple_lrgbox', 1));
    $enable_discount_lrgbox_regular = get_option('wcvs_enable_discount_lrgbox_regular', true);
    $enable_discount_multiple_lrgbox = get_option('wcvs_enable_discount_multiple_lrgbox', true);
    
    $regular_cost = isset($_SESSION['wcvs_' . $rate_name . '_info']['total_cost_regular']) ? $_SESSION['wcvs_' . $rate_name . '_info']['total_cost_regular'] : 0;
    $irregular_cost = isset($_SESSION['wcvs_' . $rate_name . '_info']['total_cost_irregular']) ? $_SESSION['wcvs_' . $rate_name . '_info']['total_cost_irregular'] : 0;
    $large_product_box_count = isset($_SESSION['wcvs_package_info']['large_box_count']) ? $_SESSION['wcvs_package_info']['large_box_count'] : 0;
    $applied_regular_discount = 0;
    $applied_irregular_discount = 0;

    // Get discount amounts if available.
    if ( $enable_discount_lrgbox_regular ) {
        // Applies a dollar deduction to discount from regular shipping for each large box.
        $regular_deduction = $large_product_box_count * $discount_lrgbox_regular;
    }
    if ( $enable_discount_multiple_lrgbox ) {
        // Applies a percentage deduction to discount from secondary instances of large boxes.
        $irregular_deduction = ($large_product_box_count > 1) ? $discount_multiple_lrgbox : 0;
    }

    // Apply the regular shipping discount.
    if ( $regular_cost > 0 ) {
        if ( $regular_deduction > $regular_cost ) {
            $applied_regular_discount = $regular_cost;
            $regular_cost = 0;
        } elseif ( $regular_cost > $regular_deduction ) {
            $applied_regular_discount = $regular_deduction;
            $regular_cost = $regular_cost - $regular_deduction;
        }
    }

    // Apply the irregular shipping discount.
    if ( $irregular_cost > 0 ) { // Example: $100
        $applied_irregular_discount = $irregular_cost * $irregular_deduction; // Example: $100 * 0.05 = $5
        $irregular_cost = $irregular_cost - $applied_irregular_discount; // Example: $100 - $5 = $95
    }

    $final_rate = $regular_cost + $irregular_cost;
    $_SESSION['wcvs_' . $rate_name . '_rate'] = $final_rate;
    $_SESSION['wcvs_' . $rate_name . '_info']['regular_discount'] = $applied_regular_discount;
    $_SESSION['wcvs_' . $rate_name . '_info']['irregular_discount'] = $applied_irregular_discount;

    if ( $_SESSION['wcvs_package_info']['free_shipping_cart'] == true ) {
        return 0;
    } else {
        return $final_rate;
    }
}

function wcvs_calculate_dynamic_rate_for_box( $box, $rate_name ) {
    $dynamic_rate = $box[$rate_name]['rate_parts']['base_rate'] + $box[$rate_name]['rate_parts']['dv_charge'] + $box[$rate_name]['rate_parts']['fuel_cost'] + $box[$rate_name]['rate_parts']['extracare_fees'] + $box[$rate_name]['rate_parts']['peak_surcharge'];

    return $dynamic_rate;
}

function wcvs_calculate_custom_rate( $custom_rate, $custom_cost_charge_per, $max_shipping_cost, $quantity ) {
    // Example: quantity is 9, charge per is 8, amount is 1.
    // This means the custom rate would only be charged once for every 8 products.
    $amount = max(1, floor($quantity / $custom_cost_charge_per));
    $total_custom_rate = $custom_rate * $amount;

    // Example: Custom rate is $10 per 8 products, and the max is $50.
    // If there are 100 products, the custom rate would be $120, but the max sets it to $50.
    if ( $total_custom_rate > $max_shipping_cost ) {
        $total_custom_rate = $max_shipping_cost;
    }

    return $total_custom_rate;
}

function wcvs_construct_oversize_base_rate( $box, $carrier, $zone_name) {
    $oversize_base_rate = 0;
    // The oversize base rate is the initial charge if any instance of the oversized product is in the cart.
    // An additional charge is applied for each additional oversized product in the cart- excluding the first.
    // For the repeating charge, see wcvs_construct_oversize_repeating_cost.
    $lc_size_code = strtolower($box['carrier_codes']['size_code']);
    $oversize_base_rate = get_option('wcvs_' . $zone_name . '_' . $lc_size_code, 0);

    return $oversize_base_rate;
}

function wcvs_construct_oversize_repeating_cost( $box, $carrier, $zone_name) {
    $price_per_additional = 0;
    // The price per additional is the charge for each additional oversized product in the cart- excluding the first.
    // See wcvs_construct_oversize_base_rate for the initial charge.
    $lc_size_code = strtolower($box['carrier_codes']['size_code']);
    $price_per_additional = get_option('wcvs_' . $zone_name . '_' . $lc_size_code . '_more', 0);

    return $price_per_additional;
}

function wcvs_combine_oversize_rate( $base_rate, $repeat_cost, $quantity ) {
    if ( $quantity > 1 ) {
        $total_oversize_rate = $base_rate + ($repeat_cost * ($quantity - 1));
    } else {
        $total_oversize_rate = $base_rate;
    }

    return $total_oversize_rate;
}
