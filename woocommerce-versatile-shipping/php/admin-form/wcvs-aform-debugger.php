<?php
// ------------------------------
// Output part of the form.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?>
<h3>Debugging</h3>
<div class="wcvs-admin-description">
    <p>Debugging tools are visible at checkout. It is recommended to keep the debugging string enabled. Both are visible to customers, so keeping the detailed debugger disabled is recommended.</p>
</div>
<table class="form-table wcvs-form-debugger">
    <tbody>
        <tr class="show_options_if_checked">
            <th scope="row" class="titledesc">Enable Debugging Tools</th>
            <td class="forminp forminp-checkbox ">
                <fieldset>
                    <label for="wcvs_admin_enable_full_debugger">
                        <input name="wcvs_admin_enable_full_debugger" id="wcvs_admin_enable_full_debugger" type="checkbox" value="1" <?= $enable_full_debugger ? 'checked' : ''; ?>>
                        <span>Enable Full Detail Debugger</span>
                    </label>
                </fieldset>
                <fieldset class="hidden_option">
                    <label for="wcvs_admin_enable_debugger_string">
                        <input name="wcvs_admin_enable_debugger_string" id="wcvs_admin_enable_debugger_string" type="checkbox" value="1" <?= $enable_debugger_string ? 'checked' : ''; ?>>
                        <span>Enable Discreet Debug String</span>
                    </label>
                </fieldset>
            </td>
        </tr>
    </tbody>
</table>
