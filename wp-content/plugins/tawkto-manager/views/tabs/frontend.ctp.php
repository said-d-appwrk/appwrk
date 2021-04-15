<?php defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page. PS really?' ); ?>
<div class="wrap"> 
<form method="post" action="<?php TTM_SCRIPT_URL.'/'.TTM_PLUGIN_FILE; ?>">
    <input type="hidden" name="security" value="<?php echo self::ttm_get_nonce(); ?>" />
    <?php settings_fields( 'ttm_tawkto_manager_plugin_options' ); ?>
    <?php do_settings_sections( 'ttm_tawkto_manager_plugin_options' ); ?>
    <table class="form-table" style="max-width: 650px;">
        <tr valign="top">
        <th scope="row"><?php echo __( 'Visibility on website / blog', TTM_TEXTDOMAIN ); ?></th>
        <th></th>
        </tr>
        <tr>
            <td><?php echo __( 'Always Show Chat', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_always'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_always" id="ttm_show_always" value="on" <?php echo $checked; ?> />
            </td>
        </tr>
        <tr>
            <td><strong><?php echo __( 'Visibility Page and Post types', TTM_TEXTDOMAIN ); ?></strong></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Front Page', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_front_page'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_front_page" id="ttm_show_front_page" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        
         <tr>
            <td><?php echo __( 'Show on All Single Posts', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_post'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_post" id="ttm_show_post" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        
        <tr>
            <td><?php echo __( 'Show on All Single Pages', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_page'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_page" id="ttm_show_page" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        
        
        <tr>
            <td><strong><?php echo __( 'Visibility Category and Tag pages', TTM_TEXTDOMAIN ); ?></strong></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Category Pages', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_cat_pages'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_cat_pages" id="ttm_show_cat_pages" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <tr>
            <td><?php echo __( 'Show on Tag Pages', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_show_tag_pages'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_show_tag_pages" id="ttm_show_tag_pages" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <tr>
            <td><strong><?php echo __( 'User Roles', TTM_TEXTDOMAIN ); ?></strong></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo __( 'Hide For Administrators', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_hide_admin'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_hide_admin" id="ttm_hide_admin" value="on" <?php echo $checked; ?> />
            
            </td>
        </tr>
        <?php if($ttm_options['ttm_advanced_mode']) { ?> 
        <tr>
            <td><?php echo __( 'Hide Logged In Subscribers', TTM_TEXTDOMAIN ); ?></td>
            <td>
                <?php 
                    $checked = ( $ttm_options['ttm_hide_logged_in_subscribers'] ? 'checked' : '' ); 
                ?>
                <input type="checkbox" name="ttm_hide_logged_in_subscribers" id="ttm_show_logged_in_subscribers" value="on" <?php echo $checked; ?> />
            
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
                jQuery("#ttm_show_front_page").prop( "checked",true);
                jQuery("#ttm_show_front_page").attr("disabled", true);
                jQuery("#ttm_show_cat_pages").prop( "checked",true);
                jQuery("#ttm_show_cat_pages").attr("disabled", true);
                jQuery("#ttm_show_tag_pages").prop( "checked",true);
                jQuery("#ttm_show_tag_pages").attr("disabled", true); 
                
                jQuery("#ttm_show_post").prop( "checked",true);
                jQuery("#ttm_show_post").attr("disabled", true); 
                jQuery("#ttm_show_page").prop( "checked",true);
                jQuery("#ttm_show_page").attr("disabled", true); 
                
                jQuery("#ttm_hide_subscribers").prop( "checked",false);
                jQuery("#ttm_hide_subscribers").attr("disabled", true); 
                jQuery("#ttm_show_not_logged").prop( "checked",false);
                jQuery("#ttm_show_not_logged_in").attr("disabled", true); 
                jQuery("#ttm_hide_not_subscriber").prop( "checked",false);
                jQuery("#ttm_hide_not_subscriber").attr("disabled", true); 
                jQuery("#ttm_show_not_logged_in").attr("disabled", true); 
                
            }else{
                jQuery("#ttm_show_front_page").removeAttr("disabled");
                jQuery("#ttm_show_page").removeAttr("disabled");
                jQuery("#ttm_show_post").removeAttr("disabled");
                jQuery("#ttm_show_cat_pages").removeAttr("disabled");
                jQuery("#ttm_show_tag_pages").removeAttr("disabled");
                jQuery("#ttm_hide_not_subscriber").removeAttr("disabled");
                jQuery("#ttm_hide_not_logged_in").removeAttr("disabled"); 
                jQuery("#ttm_show_not_logged_in").removeAttr("disabled");
            }
        });
        // Enable/disable options based on show always value
        var ischecked = document.getElementById('ttm_show_always').checked;
        if(ischecked === true){
            jQuery("#ttm_show_front_page").prop( "checked",true);
            jQuery("#ttm_show_front_page").attr("disabled", true);
            jQuery("#ttm_show_cat_pages").prop( "checked",true);
            jQuery("#ttm_show_cat_pages").attr("disabled", true);
            jQuery("#ttm_show_tag_pages").prop( "checked",true);
            jQuery("#ttm_show_tag_pages").attr("disabled", true);
            
            jQuery("#ttm_show_post").prop( "checked",true);
            jQuery("#ttm_show_post").attr("disabled", true);            
            jQuery("#ttm_show_page").prop( "checked",true);
            jQuery("#ttm_show_page").attr("disabled", true);
            
            jQuery("#ttm_hide_subscribers").prop( "checked",true);
            jQuery("#ttm_hide_subscribers").attr("disabled", true);
            jQuery("#ttm_hide_not_logged_in").prop( "checked",true);
            jQuery("#ttm_hide_not_logged_in").attr("disabled", true); 
            jQuery("#ttm_show_not_logged_in").prop( "checked",true);
            jQuery("#ttm_show_not_logged_in").attr("disabled", true); 
        }  
    } 
} 
</script>
</div>



