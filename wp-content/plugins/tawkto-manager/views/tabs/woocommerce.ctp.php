<?php defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' ); ?>
<div class="wrap">
<form method="post" action="<?php TTM_SCRIPT_URL.'/'.TTM_PLUGIN_FILE; ?>">
    <input type="hidden" name="security" value="<?php echo self::ttm_get_nonce(); ?>" />
    <?php settings_fields( 'ttm_tawkto_manager_plugin_options' ); ?>
    <?php do_settings_sections( 'ttm_tawkto_manager_plugin_options' ); ?>
    
    <?php if( ttm_woocommerce_active() ) { ?>
    <table class="form-table" style="max-width: 650px;">
        <tr valign="top">
            <th scope="row"><?php echo __( 'Visibility WooCommerce Pages', TTM_TEXTDOMAIN ); ?></th>
            <th></th>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Shop Page', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_shop_page'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_shop_page" id="ttm_show_shop_page" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Cart Page', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_cart_page'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_cart_page" id="ttm_show_cart_page" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Checkout Page', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_checkout_page'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_checkout_page" id="ttm_show_checkout_page" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Single Product Pages', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_single_product'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_single_product" id="ttm_show_single_product" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <?php if($ttm_options['ttm_advanced_mode']) { ?> 
        <tr>
            <td><?php echo __( 'Show for Customers on My Account Page', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_backend_show_myaccount'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_backend_show_myaccount" id="ttm_backend_show_myaccount" value="on" <?php echo $checked; ?> />

            </td>
        </tr>
        <tr>
            <td><?php echo __( 'Hide for WooCommerce Shop Manager', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_hide_shopmanager'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_hide_shopmanager" id="ttm_hide_shopmanager" value="on" <?php echo $checked; ?> />

            </td>
        </tr>
        <?php } ?> 
    </table>
    <table class="form-table" style="max-width: 650px;">
        <tr valign="top">
            <th scope="row"><?php echo __( 'WooCommerce Backend', TTM_TEXTDOMAIN ); ?></th>
            <th></th>
        </tr>
            <tr>
                <td><?php echo __( 'Hide for WooCommerce Shop Manager', TTM_TEXTDOMAIN ); ?></td>
                <td>
                    <?php 
                        $checked = ( $ttm_options['ttm_backend_hide_shopmanager'] ? 'checked' : '' ); 
                    ?>
                    <input type="checkbox" name="ttm_backend_hide_shopmanager" id="ttm_backend_hide_shopmanager" value="on" <?php echo $checked; ?> />

                </td>
            </tr>
            <?php } ?>
    </table>    

    <?php submit_button(); ?>
</form>
<script>
window.onload = function() {
    if (window.jQuery) {  
         jQuery("#ttm_show_always").change(function(){
            var ischecked = document.getElementById('ttm_show_always').checked;
            if(ischecked === true){
                jQuery("#ttm_show_single_product").prop( "checked",false);
                jQuery("#ttm_show_single_product").attr("disabled", true); 
                jQuery("#ttm_show_shop_page").prop( "checked",false);
                jQuery("#ttm_show_shop_page").attr("disabled", true); 
                jQuery("#ttm_show_cart_page").prop( "checked",false);
                jQuery("#ttm_show_cart_page").attr("disabled", true); 
                jQuery("#ttm_show_checkout_page").prop( "checked",false);
                jQuery("#ttm_show_checkout_page").attr("disabled", true); 
                
            }else{
                jQuery("#ttm_show_single_product").removeAttr("disabled");
                jQuery("#ttm_show_shop_page").removeAttr("disabled");
                jQuery("#ttm_show_cart_page").removeAttr("disabled");
                jQuery("#ttm_show_checkout_page").removeAttr("disabled");
            }
        });
        // Enable/disable options based on show always value
        var ischecked = document.getElementById('ttm_show_always').checked;
        if(ischecked === true){
            jQuery("#ttm_show_single_product").attr("disabled", true); 
            jQuery("#ttm_show_shop_page").attr("disabled", true); 
            jQuery("#ttm_show_cart_page").attr("disabled", true); 
            jQuery("#ttm_show_checkout_page").attr("disabled", true); 
        }  
    } 
} 
</script>
</div>



