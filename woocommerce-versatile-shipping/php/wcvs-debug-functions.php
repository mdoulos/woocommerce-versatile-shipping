<?php
// ------------------------------
// Outputs the Values needed for Debugging at Checkout.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_action('checkout_debug_container', 'inject_content_into_debug_container');

function inject_content_into_debug_container() {
    if ( ! session_id() ) { session_start(); }
    if (isset($_SESSION['wcvs_boxes']) && is_array($_SESSION['wcvs_boxes'])) {
        $wcvs_boxes = $_SESSION['wcvs_boxes'];
        $package_info = $_SESSION['wcvs_package_info'];
        $wcvs_rate_names = $_SESSION['wcvs_package_info']['rate_names'];

        // Fetch if the debuggers should be enabled or not.
        $wcvs_enable_discreet_debugger = get_option('wcvs_enable_debugger_string');
        $wcvs_enable_detailed_debugger = get_option('wcvs_enable_full_debugger');

        // This function outputs a discrete customer-facing string that is used to encode the debug information.
        if ($wcvs_enable_discreet_debugger) {
            wcvs_create_debug_encoding($wcvs_boxes);
        }

        // Only enable this function for debugging. This customer-facing debug container should be hidden by default.
        if ($wcvs_enable_detailed_debugger) {
            wcvs_display_all_debug_information($wcvs_boxes, $package_info, $wcvs_rate_names);
        }
    }
}

function wcvs_create_debug_encoding($wcvs_boxes) {
    // This string appears faintly to all customers, so that debug information is not visible to them, but is visible to the developer.
    // Order: Province/State - Ground Rate - Postal Code - # of Products - # of Boxes
    // Go Through All Boxes and Record: -- Item Count -- Pack Type -- Cost Type -- Cost -- Volume
    $debug_encoding_string = "";
    $delimeter = "l";

    $package_info = $_SESSION['wcvs_package_info'];
    $shipping_state_letters = str_split($package_info['shipping_state']);
    $shipping_state_number_1 = wcvs_encode_letters_as_number($shipping_state_letters[0]);
    $shipping_state_number_2 = wcvs_encode_letters_as_number($shipping_state_letters[1]);

    $debug_encoding_string .= $shipping_state_number_1 . $shipping_state_number_2; // Shipping State, Example: Texas = 2026
    $debug_encoding_string .= wcvs_encode_postal_code($package_info['shipping_postcode']) . $delimeter;
    $debug_encoding_string .= wcvs_encode_number_as_letter($package_info['product_count']) . $delimeter;
    $debug_encoding_string .= count($wcvs_boxes) . $delimeter;

    if (count($wcvs_boxes) > 0) {
        foreach ($wcvs_boxes as $box_index => $box) {
            $debug_encoding_string .= wcvs_encode_number_as_letter($box['number_of_products']) . $delimeter;
            $debug_encoding_string .= wcvs_encode_pack_type($box['box_pack_type']);
            $debug_encoding_string .= $box['box_cost_type'];
            $debug_encoding_string .= wcvs_encode_number_as_letter(number_format($box['box_cost'], 0));
            $debug_encoding_string .= number_format($box['measurements']['volume'], 0) . $delimeter;
        }
    }

    // Change the inner html of checkoutDebugInformationCode to the debug encoding string.
    echo '<script>
            if (document.getElementById("checkout-debug-information-code")) {
                document.getElementById("checkout-debug-information-code").innerHTML = "System: ' . $debug_encoding_string . '";
            }
          </script>';
}

function wcvs_encode_number_as_letter($number) {
    $number = (string) $number;
    $number = str_replace('.', '', $number);
    $number = str_replace('0', 'k', $number);
    $number = str_replace('1', 'a', $number);
    $number = str_replace('2', 'b', $number);
    $number = str_replace('3', 'c', $number);
    $number = str_replace('4', 'd', $number);
    $number = str_replace('5', 'e', $number);
    $number = str_replace('6', 'f', $number);
    $number = str_replace('7', 'g', $number);
    $number = str_replace('8', 'h', $number);
    $number = str_replace('9', 'i', $number);
    return $number;
}

function wcvs_encode_letters_as_number($letters) {
    $letters = strtolower($letters);
    $translation_table = [
        'a' => '1', 'b' => '2', 'c' => '3', 'd' => '4', 'e' => '5',
        'f' => '6', 'g' => '7', 'h' => '8', 'i' => '9', 'j' => '10',
        'k' => '11', 'l' => '12', 'm' => '13', 'n' => '14', 'o' => '15',
        'p' => '16', 'q' => '17', 'r' => '18', 's' => '19', 't' => '20',
        'u' => '21', 'v' => '22', 'w' => '23', 'x' => '24', 'y' => '25',
        'z' => '26'
    ];

    return strtr($letters, $translation_table);
}

function wcvs_encode_pack_type($pack_type) {
    if ($pack_type == 'Isolated') {
        return '1';
    } else {
        return '2';
    }
    return $pack_type;
}

function wcvs_encode_postal_code($postal_code) {
    $postal_code = strtolower($postal_code);
    $postal_code = str_replace(' ', '', $postal_code);
    $postal_code = str_replace('-', '', $postal_code);
    $postal_code = str_replace('1', 'a', $postal_code);
    $postal_code = str_replace('2', 'b', $postal_code);
    $postal_code = str_replace('3', 'c', $postal_code);
    $postal_code = str_replace('4', 'd', $postal_code);
    $postal_code = str_replace('5', 'e', $postal_code);
    $postal_code = str_replace('6', 'f', $postal_code);
    $postal_code = str_replace('7', 'g', $postal_code);
    $postal_code = str_replace('8', 'h', $postal_code);
    $postal_code = str_replace('9', 'i', $postal_code);
    return $postal_code;
}

function wcvs_display_all_debug_information($wcvs_boxes, $package_info, $wcvs_rate_names) {
    $first_rate_name = reset($wcvs_rate_names); // Get the first rate name
    $regular_adjustment_amount = $_SESSION['wcvs_package_info']['regular_adjustment_amount'];
    $extracare_adjustment_amount = $_SESSION['wcvs_package_info']['extracare_adjustment_amount'];
    $oversized_adjustment_amount = $_SESSION['wcvs_package_info']['oversized_adjustment_amount'];
    $regular_adjustment = floatval(get_option('wcvs_regular_adjustment', 1));
    $extracare_adjustment = floatval(get_option('wcvs_extracare_adjustment', 1));
    $oversized_adjustment = floatval(get_option('wcvs_oversized_adjustment', 1));
    $regular_sign = $regular_adjustment > 1 ? '+' : '-';
    $extracare_sign = $extracare_adjustment > 1 ? '+' : '-';
    $oversized_sign = $oversized_adjustment > 1 ? '+' : '-';
    $wcvs_type_strings = [
        'Not Set',
        'Free Shipping',
        'Regular',
        'ExtraCare',
        'Oversized',
        'Custom Rate',
        'Free Shipping Cart',
        'Disable'
    ];
    echo '<div class="checkout-debug-container">';
    echo '<div class="debug-h5row"><h5>Shipping Summary</h5><div></div></div>';
    echo '<table class="checkout-debug-table wcvs-boxes-debug">';
        echo '<tbody>';
            echo '<tr>';
                echo '<td>Zone: ' . $package_info['shipping_zone_name'] . ', State: ' . $package_info['shipping_state'] . ', P. Code: ' . $package_info['shipping_postcode'] .'</td>';
                echo '<td></td>';
            echo '</tr>';
            echo '<tr>';
                echo '<td>Carrier: ' . $package_info['carrier'] . '</td>';
                echo '<td></td>';
            echo '</tr>';
        echo '</tbody>';
    echo '</table>';
    echo '<table class="checkout-debug-table wcvs-boxes-debug">';
        echo '<tbody>';
            echo '<tr>';
                echo '<td>Number of Boxes to Ship</td>';
                echo '<td>' . count($wcvs_boxes) . '</td>';
            echo '</tr>';
            $large_box_count = $_SESSION['wcvs_package_info']['large_box_count'];
            if ($large_box_count > 0) {
                echo '<tr>';
                    echo '<td>Number of Large Boxes (Heavy, Big, or Oversized)</td>';
                    echo '<td>' . $large_box_count . '</td>';
                echo '</tr>';
            }
            if ( $_SESSION['wcvs_package_info']['shared_weight'] > 0 ) {
                echo '<tr>';
                    echo '<td>Shared Boxes - Billable Weight</td>';
                    echo '<td>' . $_SESSION['wcvs_package_info']['shared_weight'] . '<span>lbs</span></td>';
                echo '</tr>';
            }
            echo '</tbody>';
    echo '</table>';
    foreach ( $wcvs_rate_names as $rate_name ) {
        if ( $wcvs_rate_names[0] == $rate_name && $_SESSION['wcvs_package_info']['oversized_in_cart'] == 1 || $_SESSION['wcvs_package_info']['oversized_in_cart'] == 0 ) { // Only output the Ground Rate if oversized.
            $rate_info = $_SESSION['wcvs_' . $rate_name . '_info'];
            if (count($wcvs_boxes) > 1) {
                echo '<table class="checkout-debug-table wcvs-boxes-debug">';
                        echo '<tbody>';
                            echo '<tr class="debug-alignleft debug-highlight">';
                                echo '<td>' . ucfirst($rate_name) . ' Box Costs</td>';
                            echo '</tr>';
                        foreach ($wcvs_boxes as $box_index => $box) {
                            $carrier_code = $box['carrier_codes']['size_code'] == 'REGLR' ? '' : ', ' . $box['carrier_codes']['size_code'];
                            if ($box['box_pack_type'] == 'Shared') {
                                echo '<tr>';
                                    echo '<td>Box ' . ($box_index + 1) . ' (Type = ' . $wcvs_boxes[$box_index]['box_pack_type'] . ', ' .  $wcvs_type_strings[$wcvs_boxes[$box_index]['box_cost_type']] . $carrier_code . ')</td>';
                                    echo '<td>-</td>';
                                echo '</tr>';
                            } else {
                                echo '<tr>';
                                    echo '<td>Box ' . ($box_index + 1) . ' (Type = ' . $wcvs_boxes[$box_index]['box_pack_type'] . ', ' .  $wcvs_type_strings[$wcvs_boxes[$box_index]['box_cost_type']] . $carrier_code . ')</td>';
                                    echo '<td>$' . number_format($box[$rate_name]['box_cost'], 2) . '</td>';
                                echo '</tr>';
                            }
                        }
                    echo '</tbody>';
                echo '</table>';
            }
            if ( $rate_info['shared_base_rate'] > 0 ) {
                echo '<table class="checkout-debug-table wcvs-boxes-debug">';
                    echo '<tbody>';
                            echo '<tr class="debug-alignleft debug-highlight">';
                                echo '<td>' . ucfirst($rate_name) . ' Rate Parts</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Shared Boxes - ' . ucfirst($rate_name) . ' Base Rate</td>';
                                echo '<td>$' . number_format($rate_info['shared_base_rate'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Shared Boxes - ' . ucfirst($rate_name) . ' DV Charge</td>';
                                echo '<td>$' . number_format($rate_info['shared_dv_charge'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Shared Boxes - ' . ucfirst($rate_name) . ' Fuel Cost</td>';
                                echo '<td>$' . number_format($rate_info['shared_fuel_cost'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Total Regular ' . ucfirst($rate_name) . ' Cost</td>';
                                echo '<td>$' . number_format($rate_info['total_cost_regular'], 2) . '</td>';
                            echo '</tr>';
                    if ($rate_info['regular_discount'] > 0 || $rate_info['irregular_discount'] > 0 || $regular_adjustment_amount > 0 || $extracare_adjustment_amount > 0 || $oversized_adjustment_amount > 0) {
                            echo '<tr>';
                                echo '<td>Total Cost (Regular Shipping)</td>';
                                echo '<td>$' . number_format($rate_info['total_cost_regular'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Total Cost (Irregular Shipping)</td>';
                                echo '<td>$' . number_format($rate_info['total_cost_irregular'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Total Cost (Before Discounts)</td>';
                                echo '<td>$' . number_format($rate_info['total_cost_regular'] + $rate_info['total_cost_irregular'], 2) . '</td>';
                            echo '</tr>';
                        if ($rate_info['regular_discount'] > 0) {
                            echo '<tr>';
                                echo '<td>Discount: Large Items Reduce Small Item Cost</td>';
                                echo '<td>-$' . number_format($rate_info['regular_discount'], 2) . '</td>';
                            echo '</tr>';
                        }
                        if ($rate_info['irregular_discount'] > 0) {
                            echo '<tr>';
                                echo '<td>Discount: Multiple Large Item Types in Cart</td>';
                                echo '<td>-$' . number_format($rate_info['irregular_discount'], 2) . '</td>';
                            echo '</tr>';
                        }
                        if ($regular_adjustment_amount > 0) {
                            echo '<tr>';
                                echo '<td>Adjustment: Regular Adjusted by ' . $regular_adjustment . '</td>';
                                echo '<td>' . $regular_sign . '$' . number_format($regular_adjustment_amount, 2) . '</td>';
                            echo '</tr>';
                        }
                        if ($extracare_adjustment_amount > 0) {
                            echo '<tr>';
                                echo '<td>Adjustment: ExtraCare Adjusted by ' . $extracare_adjustment . '</td>';
                                echo '<td>' . $extracare_sign . '$' . number_format($extracare_adjustment_amount, 2) . '</td>';
                            echo '</tr>';
                        }
                        if ($oversized_adjustment_amount > 0) {
                            echo '<tr>';
                                echo '<td>Adjustment: Oversized Adjusted by ' . $oversized_adjustment . '</td>';
                                echo '<td>' . $oversized_sign . '$' . number_format($oversized_adjustment_amount, 2) . '</td>';
                            echo '</tr>';
                        }
                    }
                        echo '</tbody>';
                echo '</table>';
            }
        }
    }
    echo '<table class="checkout-debug-table wcvs-boxes-debug">';
        echo '<tbody>';
            echo '<tr class="debug-alignleft debug-highlight">';
                echo '<td>Total Costs</td>';
            echo '</tr>';
        foreach ( $wcvs_rate_names as $rate_name ) {
            if ( $wcvs_rate_names[0] == $rate_name && $_SESSION['wcvs_package_info']['oversized_in_cart'] == 1 || $_SESSION['wcvs_package_info']['oversized_in_cart'] == 0 ) { // Only output the Ground Rate if oversized.
                echo '<tr>';
                    echo '<td>Total Shipping Cost (' . ucfirst($rate_name) . ' Rate)</td>';
                    echo '<td>$' . number_format($_SESSION['wcvs_' . $rate_name . '_rate'], 2) . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
    echo '</table>';

    // For Each Box in the Session, Output the Box Information
    foreach ($wcvs_boxes as $box_index => $box) {
        $box_number = $box_index + 1;
        $quantity = $box['number_of_products'];
        $dimension_title_string = $quantity === 1 || $box['box_pack_type'] == 'Isolated' ? 'Product Values' : 'Combined Product Values';
        $dimension_string = $quantity === 1 || $box['box_pack_type'] == 'Isolated' ? 'Product ' : 'Combined ';
        $box_string = $box['box_pack_type'] == 'Isolated' ? '1st Instance ' : 'Generalized-Cube ';
        $product_string = $quantity === 1 ? ' item' : ' items';
        $additional_handling_string = $box['carrier_codes']['size_code'] == 'ADDWT' ? ' (Due to Weight)' : ' (Due to Size)';
        $carrier_code = $box['carrier_codes']['size_code'] == 'REGLR' ? '' : ', ' . $box['carrier_codes']['size_code'];

        echo '<div class="debug-h5row"><h5>Box ' . $box_number . ' (' . $box['box_pack_type'] . ', ' . $wcvs_type_strings[$wcvs_boxes[$box_index]['box_cost_type']] . $carrier_code . ')</h5><div></div></div>';
        if ($box['custom_options']['has_custom_option']) {
            echo '<table class="checkout-debug-table wcvs-boxes-debug">';
                echo '<tbody>';
                if ($box['custom_options']['disable_shipping'] === "yes") {
                    echo '<tr class="debug-alignleft debug-highlight">';
                        echo '<td>Shipping Disabled</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Shipping Disabled in Zone</td>';
                        echo '<td>Yes</td>';
                    echo '</tr>';
                } elseif ($box['custom_options']['is_free_shipping_in_zone'] === "yes") {
                    echo '<tr class="debug-alignleft debug-highlight">';
                        echo '<td>Free Shipping</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Free Shipping in Zone</td>';
                        echo '<td>Yes</td>';
                    echo '</tr>';
                } elseif ($box['custom_options']['custom_rate'] > 0) {
                    echo '<tr class="debug-alignleft debug-highlight">';
                        echo '<td>Fixed Cost Shipping</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Custom Rate</td>';
                        echo '<td>$' . number_format($box['custom_options']['custom_rate'], 2) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Charged for Every ' . number_format($box['custom_options']['custom_cost_charge_per'], 2) . ' Products</td>';
                        echo '<td>Quantity: ' . $box['number_of_products'] . '/ ' . number_format($box['custom_options']['custom_cost_charge_per'], 2) . ' = ' . max(1, floor($box['number_of_products'] / $box['custom_options']['custom_cost_charge_per'])) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Maximum Custom Rate</td>';
                        echo '<td>$' . number_format($box['custom_options']['max_shipping_cost'], 2) . '</td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>Total Custom Rate</td>';
                        echo '<td>$' . number_format($box['custom_options']['total_custom_rate'], 2) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
            echo '</table>';
        }
        if ( $box['carrier_codes']['size_category'] == 'Oversized' ) {
            echo '<table class="checkout-debug-table wcvs-boxes-debug">';
                echo '<tbody>';
                    echo '<tr class="debug-alignleft debug-highlight">';
                        echo '<td>Product is too big for Dynamic Rates</td>';
                    echo '</tr>';
                    if ( $box['carrier_codes']['size_code'] == 'OW150' ) {
                        echo '<tr>';
                            echo '<td>Over 150 Pounds?</td>';
                            echo '<td>Yes</td>';
                        echo '</tr>';
                    }
                    if ( $box['carrier_codes']['size_code'] == 'OS097' ) {
                        echo '<tr>';
                            echo '<td>Over 97" in Length?</td>';
                            echo '<td>Yes</td>';
                        echo '</tr>';
                    }
                    if ( $box['carrier_codes']['size_code'] == 'OS108' ) {
                        echo '<tr>';
                            echo '<td>Over 108" in Length?</td>';
                            echo '<td>Yes</td>';
                        echo '</tr>';
                    }
                    if ($box['number_of_products'] > 1) {
                        echo '<tr>';
                            echo '<td>Base Rate</td>';
                            echo '<td>$' . number_format($box[$first_rate_name ]['rate_parts']['oversize_base_rate'], 2) . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<td>Repeating Cost</td>';
                            echo '<td>$' . number_format($box[$first_rate_name ]['rate_parts']['oversize_repeating_cost'], 2)  . ' (x' . ($box['number_of_products'] - 1) . ')</td>';
                        echo '</tr>';
                        echo '<tr>';
                            echo '<td>Total Oversized Rate</td>';
                            echo '<td>$' . number_format($box[$first_rate_name ]['rate_parts']['oversize_rate'], 2) . '</td>';
                        echo '</tr>';
                    } else {
                        echo '<tr>';
                            echo '<td>Oversized Rate</td>';
                            echo '<td>$' . number_format($box[$first_rate_name ]['rate_parts']['oversize_rate'], 2) . '</td>';
                        echo '</tr>';
                    }
                echo '</tbody>';
            echo '</table>';
        }
        if ( $box['carrier_codes']['size_category'] != 'Oversized' && $box['box_pack_type'] == 'Isolated' && $box['custom_options']['total_custom_rate'] == 0 ) {
            foreach ( $wcvs_rate_names as $rate_name ) {
                if ( $wcvs_rate_names[0] == $rate_name && $_SESSION['wcvs_package_info']['oversized_in_cart'] == 1 || $_SESSION['wcvs_package_info']['oversized_in_cart'] == 0 ) { // Only output the Ground Rate if oversized.
                    echo '<table class="checkout-debug-table wcvs-boxes-debug">';
                        echo '<tbody>';
                            echo '<tr class="debug-alignleft debug-highlight">';
                                echo '<td>' . ucfirst($rate_name) . ' Rate Calculations</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Base Rate</td>';
                                echo '<td>$' . number_format($box[$rate_name]['rate_parts']['base_rate'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Fuel Cost</td>';
                                echo '<td>$' . number_format($box[$rate_name]['rate_parts']['fuel_cost'], 2) . '</td>';
                            echo '</tr>';
                            echo '<tr>';
                                echo '<td>Declared Value</td>';
                                echo '<td>$' . number_format($box[$rate_name]['rate_parts']['dv_charge'], 2) . '</td>';
                            echo '</tr>';
                            if ( $box['carrier_codes']['size_code'] == 'ADDWT' || $box['carrier_codes']['size_code'] == 'ADDSZ') {
                                echo '<tr>';
                                    echo '<td>Additional Handling' . $additional_handling_string . '</td>';
                                    echo '<td>$' . number_format($box[$rate_name]['rate_parts']['extracare_fees'], 2) . '</td>';
                                echo '</tr>';
                            }
                            if ($box[$rate_name]['rate_parts']['peak_surcharge'] > 0) {
                                echo '<tr>';
                                    echo '<td>Peak Surcharge (Lrg Pkg or Add. Handling)</td>';
                                    echo '<td>$' . number_format($box[$rate_name]['rate_parts']['peak_surcharge'], 2) . '</td>';
                                echo '</tr>';
                            }
                            echo '<tr>';
                                echo '<td>Total Dynamic Rate</td>';
                                echo '<td>$' . number_format($box[$rate_name]['rate_parts']['dynamic_rate'], 2) . '</td>';
                            echo '</tr>';
                        echo '</tbody>';
                    echo '</table>';
                    if ($box['carrier_codes']['size_category'] == 'ExtraCare' && $box[$rate_name]['rate_parts']['additional_large_cost'] > 0) {
                        echo '<table class="checkout-debug-table wcvs-boxes-debug">';
                            echo '<tbody>';
                                echo '<tr class="debug-alignleft debug-highlight">';
                                    echo '<td>Multiple Instances of Same Large Product</td>';
                                echo '</tr>';
                                echo '<tr>';
                                    echo '<td>Secondary Instances Total Cost</td>';
                                    echo '<td>$' . number_format($box[$rate_name]['rate_parts']['additional_large_cost'], 2) . '</td>';
                                echo '</tr>';
                            echo '</tbody>';
                        echo '</table>';
                    }
                }
            }
        }
        echo '<table class="checkout-debug-table wcvs-boxes-debug">';
            echo '<tbody>';
                echo '<tr class="debug-alignleft debug-highlight">';
                    echo '<td>' . $dimension_title_string . '</td>';
                echo '</tr>';

                echo '<tr>';
                    echo '<td>Actual | Dim. Weight 1st | 2nd</td>';
                    echo '<td>' . $box['measurements']['actual_weight'] . '<span>lbs</span> | ' . $box['carrier_codes']['dimensional_weight'] . '<span>lbs</span> | ' . $box['carrier_codes']['dimensional_weight_2nd'] . '<span>lbs</span></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>Billable Weights (1st | 2nd)</td>';
                    echo '<td>' . $box['carrier_codes']['billable_weight'] . ' | ' . $box['carrier_codes']['billable_weight_2nd'] . '<span>lbs</span></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>' . $dimension_string . 'Volume</td>';
                    echo '<td>' . $box['measurements']['volume'] . '<span>in<sup>3</sup></span></td>';
                echo '</tr>';
                if ($box['box_pack_type'] == 'Shared') {
                    echo '<tr>';
                        echo '<td>' . $box_string . 'Volume</td>';
                        echo '<td>' . ($box['measurements']['length'] * $box['measurements']['length'] * $box['measurements']['length']) . '<span>in<sup>3</sup></span></td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>' . $box_string . 'Sides</td>';
                        echo '<td>' . $box['measurements']['length'] . 'x' . $box['measurements']['width'] . 'x' . $box['measurements']['height'] . '<span>in</span></td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                        echo '<td>' . $box_string . 'Length</td>';
                        echo '<td>' . $box['measurements']['length'] . '<span>in</span></td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>' . $box_string . 'Width</td>';
                        echo '<td>' . $box['measurements']['width'] . '<span>in</span></td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td>' . $box_string . 'Height</td>';
                        echo '<td>' . $box['measurements']['height'] . '<span>in</span></td>';
                    echo '</tr>';
                }
                echo '<tr>';
                    echo '<td>' . $box_string . 'Length + Girth</td>';
                    echo '<td>' . $box['measurements']['length_plus_girth'] . '<span>in</span></td>';
                echo '</tr>';
            echo '</tbody>';
        echo '</table>';
        echo '<table class="checkout-debug-table wcvs-boxes-debug">';
            echo '<tbody>';
                echo '<tr class="debug-alignleft debug-highlight">';
                    echo '<td>Carrier Classifications</td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>Size Code</td>';
                    echo '<td>' . $box['carrier_codes']['size_code'] . '</td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td>Size Category</td>';
                    echo '<td>' . $box['carrier_codes']['size_category'] . '</td>';
                echo '</tr>';
            echo '</tbody>';
        echo '</table>';
        echo '<table class="checkout-debug-table wcvs-boxes-debug">';
            echo '<tbody>';
                echo '<tr class="debug-alignleft debug-highlight">';
                    echo '<td>Box Contents (' . $box['number_of_products'] . $product_string .', Retail Value: $' . number_format($box['box_price'], 2) . ')</td>';
                echo '</tr>';
            for ($i = 0; $i < $box['number_of_products']; $i++) {
                echo '<tr class="debug-alignleft">';
                    echo '<td>' . $box['product_info'][$i] . $box['product_names'][$i] . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
        echo '</table>';
    }
    echo '</div>';
}
