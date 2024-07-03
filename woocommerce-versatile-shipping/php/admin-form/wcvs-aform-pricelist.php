<?php
// ------------------------------
// Output part of the form.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>

<h3>Price List</h3>
<div class="wcvs-admin-description">
    <p>Define the price range for the base price in different shipping zones. The base price examines the distance between the lower and upper bounds or thresholds of the price range and calculates accordingly. Once you've set the names of your flat rates in the WooCommerce UI, it is not recommended to change the name as the prices will need to be reset. Instead change the name here. As such, it is better to name your flat rates in the WooCommerce Shipping Zone UI simple names such as Ground, Select, Express, etc.</p>
</div>

<?php
foreach ($wcvs_rates as $rate_name => $zones) {
    $lc_rate_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($rate_name));

    // Get the rate prefix, rate name, and enabled status
    $wcvs_rate[$rate_name]['rate_prefix'] = get_option('wcvs_' . $lc_rate_name . '_rate_prefix', '');
    $wcvs_rate[$rate_name]['rate_name'] = get_option('wcvs_' . $lc_rate_name . '_rate_name', $rate_name);
    $wcvs_rate[$rate_name]['enabled'] = get_option('wcvs_enable_' . $lc_rate_name . '_rate', 1);

    // Get the threshold weight values for each rate
    $wcvs_rate[$rate_name]['threshold_1'] = get_option('wcvs_' . $lc_rate_name . '_threshold_1', 1);
    $wcvs_rate[$rate_name]['threshold_2'] = get_option('wcvs_' . $lc_rate_name . '_threshold_2', 50);
    $wcvs_rate[$rate_name]['threshold_3'] = get_option('wcvs_' . $lc_rate_name . '_threshold_3', 100);
    $wcvs_rate[$rate_name]['threshold_4'] = get_option('wcvs_' . $lc_rate_name . '_threshold_4', 150);

    ?>
    <div class="wcvs-form-pricelist__rate-row flex-row">
        <h4><?= $rate_name ?></h4>
        <label for="<?= $lc_rate_name ?>_rate_prefix">Prefix</label>
        <input type="text" name="<?= $lc_rate_name ?>_rate_prefix" id="<?= $lc_rate_name ?>_rate_prefix" value="<?= $wcvs_rate[$rate_name]['rate_prefix'] ?>" placeholder="">
        <label for="<?= $lc_rate_name ?>_rate_name">Custom Name</label>
        <input type="text" name="<?= $lc_rate_name ?>_rate_name" id="<?= $lc_rate_name ?>_rate_name" value="<?= $wcvs_rate[$rate_name]['rate_name'] ?>" placeholder="<?= $rate_name ?>">
        <div class="wcvs-rightside-checkbox">
            <input name="wcvs_admin_enable_<?= $lc_rate_name ?>_rate" id="wcvs_admin_enable_<?= $lc_rate_name ?>_rate" type="checkbox" value="1" <?= $wcvs_rate[$rate_name]['enabled'] ? 'checked' : '' ?>>
            <span>Enable Rate</span>
        </div>
    </div>
    <table class="form-table wcvs-form-pricelist">
        <thead>
            <tr>
                <th>Zone Name</th>
                <th><input type="number" step="1" name="<?= $lc_rate_name ?>_threshold_1" id="<?= $lc_rate_name ?>_threshold_1" style="width: 75px;" value="<?= $wcvs_rate[$rate_name]['threshold_1'] ?>" placeholder="1"> lbs</th>
                <th><input type="number" step="1" name="<?= $lc_rate_name ?>_threshold_2" id="<?= $lc_rate_name ?>_threshold_2" style="width: 75px;" value="<?= $wcvs_rate[$rate_name]['threshold_2'] ?>" placeholder="50"> lbs</th>
                <th><input type="number" step="1" name="<?= $lc_rate_name ?>_threshold_3" id="<?= $lc_rate_name ?>_threshold_3" style="width: 75px;" value="<?= $wcvs_rate[$rate_name]['threshold_3'] ?>" placeholder="100"> lbs</th>
                <th><input type="number" step="1" name="<?= $lc_rate_name ?>_threshold_4" id="<?= $lc_rate_name ?>_threshold_4" style="width: 75px;" value="<?= $wcvs_rate[$rate_name]['threshold_4'] ?>" placeholder="150"> lbs</th>
                <th>In Zone</th>
                <th>Transit Time Text</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($zones as $zone) {
                $lc_zone_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($zone));

                // Get the threshold price values for each zone available for each rate
                $wcvs_rate[$rate_name][$lc_zone_name] = [
                    'price_1' => get_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_1', 0),
                    'price_2' => get_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_2', 0),
                    'price_3' => get_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_3', 0),
                    'price_4' => get_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_price_4', 0),
                    'enabled' => get_option('wcvs_enable_' . $lc_rate_name . '_rate_in_' . $lc_zone_name, 1),
                ];
                ?>
                <tr>
                    <td><?= $zone ?></td>
                    <td>
                        <input type="number" step="0.01" name="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_1" id="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_1" style="width: 83px;" value="<?= $wcvs_rate[$rate_name][$lc_zone_name]['price_1'] ?>" placeholder="1 lb">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_2" id="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_2" style="width: 83px;" value="<?= $wcvs_rate[$rate_name][$lc_zone_name]['price_2'] ?>" placeholder="50 lbs">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_3" id="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_3" style="width: 83px;" value="<?= $wcvs_rate[$rate_name][$lc_zone_name]['price_3'] ?>" placeholder="100 lbs">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_4" id="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_price_4" style="width: 83px;" value="<?= $wcvs_rate[$rate_name][$lc_zone_name]['price_4'] ?>" placeholder="150 lbs">
                    </td>
                    <td class="flex-row">
                        <div class="wcvs-rightside-checkbox">
                            <input name="wcvs_admin_enable_<?= $lc_rate_name ?>_rate_in_<?= $lc_zone_name ?>" id="wcvs_admin_enable_<?= $lc_rate_name ?>_rate_in_<?= $lc_zone_name ?>" type="checkbox" value="1" <?= $wcvs_rate[$rate_name][$lc_zone_name]['enabled'] ? 'checked' : '' ?>>
                            <span>Enable</span>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_transit_time" id="<?= $lc_zone_name ?>_<?= $lc_rate_name ?>_transit_time" value="<?= get_option('wcvs_' . $lc_zone_name . '_' . $lc_rate_name . '_transit_time', '') ?>" placeholder="2-3 days">
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php }
