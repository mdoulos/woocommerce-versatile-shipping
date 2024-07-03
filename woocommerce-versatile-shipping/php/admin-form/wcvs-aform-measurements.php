<?php
// ------------------------------
// Output part of the form.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>
<h3>Measurements</h3>
<div class="wcvs-admin-description">
    <p>The following options define various measurement variables and define what counts as overweight or oversized.</p>
</div>
<table class="form-table wcvs-form-measurements">
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_dim_factor">Dim Factor (for the First Rate)</label>
            <?php render_tooltip('The dimensional factor is a multiplier used to determine the dimensional weight of a package. The formula for dimensional weight is (L x W x H) / Dim Factor. The default value is 139.'); ?>
        </th>
        <td>
            <input type="number" step="0.01" min="100" max="200" name="wcvs_admin_dim_factor" id="wcvs_admin_dim_factor" style="width:75px;" value="<?= $dim_factor; ?>" placeholder="0.2">
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_dim_factor">Dim Factor (for Secondary Rates)</label>
            <?php render_tooltip('Some carriers like Canpar use one Dimensional Weight factor for the Ground rate, and another for their faster rates. This Dim Factor will be used to calculate the dimensional weight for all rates other than the first rate.'); ?>
        </th>
        <td>
            <input type="number" step="0.01" min="100" max="200" name="wcvs_admin_dim_factor_2nd" id="wcvs_admin_dim_factor_2nd" style="width:75px;" value="<?= $dim_factor_2nd; ?>" placeholder="0.2">
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_addwt_weight">Additional Handling - Weight</label>
            <?php render_tooltip('At what weight, in pounds, a product requires an additional handling charge due to weight according to the carrier.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="1" min="0" max="150" name="wcvs_admin_addwt_weight" id="wcvs_admin_addwt_weight" style="width:75px;" value="<?= $addwt_weight; ?>" placeholder="50">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_addwt_weight" id="wcvs_admin_enable_addwt_weight" type="checkbox" <?= $enable_addwt_weight ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_addsz_length">Additional Handling - Size (Length)</label>
            <?php render_tooltip('At what length, a product requires an additional handling charge due to size according to the carrier.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="1" min="0" max="150" name="wcvs_admin_addsz_length" id="wcvs_admin_addsz_length" style="width:75px;" value="<?= $addsz_length; ?>" placeholder="48">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_addsz_length" id="wcvs_admin_enable_addsz_length" type="checkbox" <?= $enable_addsz_length ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_addsz_width">Additional Handling - Size (Width)</label>
            <?php render_tooltip('At what width, a product requires an additional handling charge due to size according to the carrier.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="1" min="0" max="150" name="wcvs_admin_addsz_width" id="wcvs_admin_addsz_width" style="width:75px;" value="<?= $addsz_width; ?>" placeholder="30">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_addsz_width" id="wcvs_admin_enable_addsz_width" type="checkbox" <?= $enable_addsz_width ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_addsz_lpg">Additional Handling - Size (Length + Girth)</label>
            <?php render_tooltip('At what Length plus Girth, a product requires an additional handling charge due to size according to the carrier. Length plus girth is Length + (Width x 2) + (Height x 2)'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="1" min="0" max="150" name="wcvs_admin_addsz_lpg" id="wcvs_admin_addsz_lpg" style="width:75px;" value="<?= $addsz_lpg; ?>" placeholder="105">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_addsz_lpg" id="wcvs_admin_enable_addsz_lpg" type="checkbox" <?= $enable_addsz_lpg ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <?php
    $rate_num = 0;
    foreach ($wcvs_rates as $rate_name => $zones) {
        $rate_num++;
        if ($rate_num != 1) {
            $lc_rate_name = preg_replace('/[^A-Za-z0-9]/', '_', strtolower($rate_name));
            $max_weight = get_option('wcvs_maxwt_' . $lc_rate_name, 75);
            $enable_max_weight = get_option('wcvs_enable_maxwt_' . $lc_rate_name, 1);
            ?>
            <tr>
                <th scope="row" class="flex-row">
                    <label for="wcvs_admin_maxwt_<?= $lc_rate_name ?>">Max. Weight For <?= $rate_name ?> Rate</label>
                    <?php render_tooltip('At what weight any individual box must be before the specified secondary rate is disabled. Select and Express for example, are disabled when a product is equal to or over 75 pounds.'); ?>
                </th>
                <td class="flex-row">
                    <input type="number" step="1" min="0" max="500" name="wcvs_admin_maxwt_<?= $lc_rate_name ?>" id="wcvs_admin_maxwt_<?= $lc_rate_name ?>" style="width:75px;" value="<?= $max_weight ?>" placeholder="75">
                    <div class="wcvs-rightside-checkbox">
                        <input name="wcvs_admin_enable_maxwt_<?= $lc_rate_name ?>" id="wcvs_admin_enable_maxwt_<?= $lc_rate_name ?>" type="checkbox" <?= $enable_max_weight ? 'checked' : ''; ?> value="1">
                        <span>Enable</span>
                    </div>
                </td>
            </tr>
        <?php }
    } ?>
</table>
