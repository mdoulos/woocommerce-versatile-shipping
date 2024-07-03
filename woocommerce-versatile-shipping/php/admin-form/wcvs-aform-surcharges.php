<?php
// ------------------------------
// Output part of the form.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>
<h3>Surcharges</h3>
<div class="wcvs-admin-description">
    <p>The following options adjust regular surcharges for dynamic pricing.</p>
</div>
<table class="form-table wcvs-form-surcharges">
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_fuel_surcharge">Fuel Surcharge (%)</label>
            <?php render_tooltip('The cost of fuel. Charged as a percentage of the base rate. If the base rate is $10 and the charge is 0.3 or 30% then the fuel cost would be $3.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="1" name="wcvs_admin_fuel_surcharge" id="wcvs_admin_fuel_surcharge" style="width:75px;" value="<?= $fuel_surcharge; ?>" placeholder="0.2">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_fuel_surcharge" id="wcvs_admin_enable_fuel_surcharge" type="checkbox" <?= $enable_fuel_surcharge ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_dv_surcharge">Declared Value (per $100)</label>
            <?php render_tooltip('The coverage cost provided by the shipper. The dollar amount is charged per $100. Canpar skips the first $100.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="10" name="wcvs_admin_dv_surcharge" id="wcvs_admin_dv_surcharge" style="width:75px;" value="<?= $dv_surcharge; ?>" placeholder="2">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_dv_surcharge" id="wcvs_admin_enable_dv_surcharge" type="checkbox" <?= $enable_dv_surcharge ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_addwt_surcharge">Additional Handling Due to Weight Surcharge</label>
            <?php render_tooltip('The additional cost for packages considered to require additional handling for being heavy. Example: Products over 50 pounds.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="500" name="wcvs_admin_addwt_surcharge" id="wcvs_admin_addwt_surcharge" style="width:75px;" value="<?= $addwt_surcharge; ?>" placeholder="100">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_addwt_surcharge" id="wcvs_admin_enable_addwt_surcharge" type="checkbox" <?= $enable_addwt_surcharge ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_addsz_surcharge">Additional Handling Due to Size Surcharge</label>
            <?php render_tooltip('The additional cost for packages considered to require additional handling for being large or bulky. Example: Products over 4 feet long.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="500" name="wcvs_admin_addsz_surcharge" id="wcvs_admin_addsz_surcharge" style="width:75px;" value="<?= $addsz_surcharge; ?>" placeholder="100">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_addsz_surcharge" id="wcvs_admin_enable_addsz_surcharge" type="checkbox" <?= $enable_addsz_surcharge ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_peak_surcharge_addhandling">Peak Surcharge for Additional Handling</label>
            <?php render_tooltip('An additional cost for packages that require additional handling during demanding seasons or holidays.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="500" name="wcvs_admin_peak_surcharge_addhandling" id="wcvs_admin_peak_surcharge_addhandling" style="width:75px;" value="<?= $peak_surcharge_addhandling; ?>" placeholder="100">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_peak_surcharge_addhandling" id="wcvs_admin_enable_peak_surcharge_addhandling" type="checkbox" <?= $enable_peak_surcharge_addhandling ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
</table>
