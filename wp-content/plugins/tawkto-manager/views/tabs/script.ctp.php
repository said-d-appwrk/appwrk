<?php defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page. PS really?' ); ?>
<div class="wrap">
    <form method="post" action="<?php TTM_SCRIPT_URL.'/'.TTM_PLUGIN_FILE; ?>">
        <input type="hidden" name="security" value="<?php echo $nonce; ?>" />    
        <?php settings_fields( 'ttm_tawkto_manager_plugin_options' ); ?>
        <?php do_settings_sections( 'ttm_tawkto_manager_plugin_options' ); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php echo __( 'Your Tawk.To Script', TTM_TEXTDOMAIN ); ?></th>
            <tr>
                <td><?php echo __( 'Your Tawk.To Script', TTM_TEXTDOMAIN ); ?>*</td>
                <td>
                    <textarea name="ttm_tawktoscript" rows="14" cols="80"><?php echo wp_unslash($ttm_options['ttm_tawktoscript']); ?></textarea>
                </td>
            </tr>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    <br />
    <p><strong>* <?php echo __( 'Copy and paste the whole script from tawk.to', TTM_TEXTDOMAIN ); ?></strong> <?php echo __( 'or read', TTM_TEXTDOMAIN ); ?> 
        <a href="https://www.tawk.to/knowledgebase/getting-started/adding-a-widget-to-your-website/" target="_blank"> 
        <?php echo __( 'Adding a widget to your website on', TTM_TEXTDOMAIN ); ?></a> tawk.to.
    </p>  
</div>



