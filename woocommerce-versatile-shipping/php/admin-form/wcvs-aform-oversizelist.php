<?php
// ------------------------------
// Output part of the form.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>
<h3>Oversized Items Price List</h3>
<div class="wcvs-admin-description">
    <p>Define the default price list for oversized items in different shipping zones. Each item's individual price can be adjusted on the product edit page. Two prices are defined here, the price of the first instance of the oversized or overweight product, and the price for secondary instances of the product. OS097 = Products over 97 inches but under 108. OS108 = Products equal to or over 108 inches. OW150 = Products that weigh over 150 pounds.</p>
</div>
<table class="form-table wcvs-form-pricelist wcvs-form-oversizelist">
    <thead>
        <tr>
            <th>Zone Name</th>
            <th>OS097 (1st)</th>
            <th>OS097 (2 or more)</th>
            <th>OS108 (1st)</th>
            <th>OS108 (2 or more)</th>
            <th>OW150 (1st)</th>
            <th>OW150 (2 or more)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($shipping_zones as $zone) {
            $unedited_zone_name = $zone['zone_name'];
            $lc_zone_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($zone['zone_name']));

            $wcvs_osrate[$lc_zone_name] = [
                'os097' => get_option('wcvs_' . $lc_zone_name . '_os097', 0),
                'os097_more' => get_option('wcvs_' . $lc_zone_name . '_os097_more', 0),
                'os108' => get_option('wcvs_' . $lc_zone_name . '_os108', 0),
                'os108_more' => get_option('wcvs_' . $lc_zone_name . '_os108_more', 0),
                'ow150' => get_option('wcvs_' . $lc_zone_name . '_ow150', 0),
                'ow150_more' => get_option('wcvs_' . $lc_zone_name . '_ow150_more', 0),
            ];
            ?>
            <tr>
                <td><?= $unedited_zone_name; ?></td>
                <td>
                    <input type="number" step="0.01" name="os097_<?= $lc_zone_name; ?>" id="os097_<?= $lc_zone_name; ?>" style="width: 75px;" value="<?= $wcvs_osrate[$lc_zone_name]['os097'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="os097_more_<?= $lc_zone_name; ?>" id="os097_more_<?= $lc_zone_name; ?>" style="width: 75px;" value="<?= $wcvs_osrate[$lc_zone_name]['os097_more'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="os108_<?= $lc_zone_name; ?>" id="os108_<?= $lc_zone_name; ?>" style="width: 75px;" value="<?= $wcvs_osrate[$lc_zone_name]['os108'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="os108_more_<?= $lc_zone_name; ?>" id="os108_more_<?= $lc_zone_name; ?>" style="width: 75px;" value="<?= $wcvs_osrate[$lc_zone_name]['os108_more'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="ow150_<?= $lc_zone_name; ?>" id="ow150_<?= $lc_zone_name; ?>" style="width: 75px;" value="<?= $wcvs_osrate[$lc_zone_name]['ow150'] ?>">
                </td>
                <td>
                    <input type="number" step="0.01" name="ow150_more_<?= $lc_zone_name; ?>" id="ow150_more_<?= $lc_zone_name; ?>" style="width: 75px;" value="<?= $wcvs_osrate[$lc_zone_name]['ow150_more'] ?>">
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
