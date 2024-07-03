<?php
// ------------------------------
// Output the form to the admin page.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>
<div class="wrap wcvs-admin">
    <h2>Versatile Shipping for WooCommerce</h2>
    <hr class="wcvs-admin-hr">
    <form method="post" class="wcvs-admin-form">
        <nav class="nav-tab-wrapper">
            <a href="#" class="nav-tab nav-tab-active">General</a>
            <a href="#" class="nav-tab">Price List</a>
        </nav>
        <div class="wcvs-admin__general-page wcvs-active-page">
            <?php include 'admin-form/wcvs-aform-surcharges.php'; ?>
            <?php include 'admin-form/wcvs-aform-measurements.php'; ?>
            <?php include 'admin-form/wcvs-aform-adjustments.php'; ?>
            <?php include 'admin-form/wcvs-aform-debugger.php'; ?>
        </div>
        <div class="wcvs-admin__pricelist-page">
            <?php include 'admin-form/wcvs-aform-pricelist.php'; ?>
            <?php include 'admin-form/wcvs-aform-oversizelist.php'; ?>
        </div>
        <p class="submit">
            <button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Save changes</button>
            <?php wp_nonce_field( 'update_wcvs_settings', 'wcvs_settings_nonce' ); ?>
        </p>
    </form>
    <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').click(function() {
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.wcvs-active-page').removeClass('wcvs-active-page');
                var tabName = $(this).text().toLowerCase().replace(/\s+/g, '');
                $('.wcvs-admin__' + tabName + '-page').addClass('wcvs-active-page');
            });
        });
    </script>
</div>
