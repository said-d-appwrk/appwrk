<?php defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page. PS really?' ); ?>
<div class="wrap">
<form method="post" action="<?php TTM_SCRIPT_URL.'/'.TTM_PLUGIN_FILE; ?>">
    <input type="hidden" name="security" value="<?php echo self::ttm_get_nonce(); ?>" />
    <?php settings_fields( 'ttm_tawkto_manager_plugin_options' ); ?>
    <?php do_settings_sections( 'ttm_tawkto_manager_plugin_options' ); ?>
    <table class="form-table" style="max-width: 650px;">
        <tr valign="top">
            <th scope="row"><?php echo __( 'Plugin Options and Settings', TTM_TEXTDOMAIN ); ?></th>
            <th></th>
        </tr>
        <tr>
            <td><?php echo __( 'Advanced Mode', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_advanced_mode'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_advanced_mode" id="ttm_advanced_mode" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
</div>



