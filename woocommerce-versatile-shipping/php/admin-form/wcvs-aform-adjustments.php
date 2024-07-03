<?php
// ------------------------------
// Output part of the form.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>
<h3>Adjustments & Discounts</h3>
<div class="wcvs-admin-description">
    <p>The following options allow for the total shipping prices of certain product types to be universally adjusted or for discounts to be applied.</p>
</div>
<table class="form-table wcvs-form-adjustments">
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_regular_adjustment">Adjust Regular Products </label>
            <?php render_tooltip('Adjust the price of regular products by a percentage. 0.5 would cut the price in half. 2 would double the price.'); ?>
        </th>
        <td>
            <input type="number" step="0.01" min="0" max="5" name="wcvs_admin_regular_adjustment" id="wcvs_admin_regular_adjustment" style="width:75px;" value="<?= $regular_adjustment ?>" placeholder="1">
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_extracare_adjustment">Adjust ExtraCare Products </label>
            <?php render_tooltip('Adjust the price of large or heavy products that normally require an additional handling fee by a percentage. 0.5 would cut the price in half. 2 would double the price.'); ?>
        </th>
        <td>
            <input type="number" step="0.01" min="0" max="5" name="wcvs_admin_extracare_adjustment" id="wcvs_admin_extracare_adjustment" style="width:75px;" value="<?= $extracare_adjustment ?>" placeholder="1">
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_oversized_adjustment">Adjust Oversized Products </label>
            <?php render_tooltip('Adjust the price of products that are overweight or oversized by a percentage. 0.5 would cut the price in half. 2 would double the price.'); ?>
        </th>
        <td>
            <input type="number" step="0.01" min="0" max="5" name="wcvs_admin_oversized_adjustment" id="wcvs_admin_oversized_adjustment" style="width:75px;" value="<?= $oversized_adjustment ?>" placeholder="1">
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_discount_lrgbox_regular">Large Packages Reduce Regular Shipping</label>
            <?php render_tooltip('Each psuedo-box of large or extracare products will reduce the cost of regular shipping products by the dollar amount specified.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="1" min="0" max="100" name="wcvs_admin_discount_lrgbox_regular" id="wcvs_admin_discount_lrgbox_regular" style="width:75px;" value="<?= $discount_lrgbox_regular ?>" placeholder="20">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_discount_lrgbox_regular" id="wcvs_admin_enable_discount_lrgbox_regular" type="checkbox" <?= $enable_discount_lrgbox_regular ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_discount_multiple_lrgbox">Multiple Large Boxes Reduction</label>
            <?php render_tooltip('The total cost of every large box is reduced if there is more than one large box. Each large product type creates an individual box, with secondary instances going in the same box. So this discounts each instance of unique large product types. It does not discount more than one instance of the same product. For example, if there are two large products that cost $100 and this value is 0.25, the cost of each will be $75. It is reduced by the specified percentage.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="1" name="wcvs_admin_discount_multiple_lrgbox" id="wcvs_admin_discount_multiple_lrgbox" style="width:75px;" value="<?= $discount_multiple_lrgbox ?>" placeholder="1">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_discount_multiple_lrgbox" id="wcvs_admin_enable_discount_multiple_lrgbox" type="checkbox" <?= $enable_discount_multiple_lrgbox ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row" class="flex-row">
            <label for="wcvs_admin_discount_secondary_large_products">Secondary Large Products Reduction</label>
            <?php render_tooltip('Secondary instances of the same product are charged at the following price multiplier. For example, if a product costs $100 and this value is set to 0.25, the first instance of the large product is $100, but the second and third instances are charged at $25 each. It is reduced to the specified percentage.'); ?>
        </th>
        <td class="flex-row">
            <input type="number" step="0.01" min="0" max="1" name="wcvs_admin_discount_secondary_large_products" id="wcvs_admin_discount_secondary_large_products" style="width:75px;" value="<?= $discount_secondary_large_products ?>" placeholder="1">
            <div class="wcvs-rightside-checkbox">
                <input name="wcvs_admin_enable_discount_secondary_large_products" id="wcvs_admin_enable_discount_secondary_large_products" type="checkbox" <?= $enable_discount_secondary_large_products ? 'checked' : ''; ?> value="1">
                <span>Enable</span>
            </div>
        </td>
    </tr>
</table>
