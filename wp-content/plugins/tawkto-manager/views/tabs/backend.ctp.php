<?php defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' ); ?>
<div class="wrap">
<form method="post" action="<?php TTM_SCRIPT_URL.'/'.TTM_PLUGIN_FILE; ?>">
<input type="hidden" name="security" value="<?php echo self::ttm_get_nonce(); ?>" />
<?php settings_fields( 'ttm_tawkto_manager_plugin_options' ); ?>
<?php do_settings_sections( 'ttm_tawkto_manager_plugin_options' ); ?>
  <table class="form-table" style="max-width: 650px;">
    <tr valign="top">
        <th scope="row"><?php echo __( 'Chat Visibility Options WordPress Dashboard', TTM_TEXTDOMAIN ); ?></th>
        <th></th>
    </tr>        
    <tr>
        <td><?php echo __( 'Always Show Chat', TTM_TEXTDOMAIN ); ?></td>
        <td>
            <?php 
                $checked = ( $ttm_options['ttm_backend_show_always'] ? 'checked' : '' ); 
            ?>
            <input type="checkbox" name="ttm_backend_show_always" id="ttm_backend_show_always" value="on" <?php echo $checked; ?> />
        </td>
    </tr>
    <tr>
        <td><?php echo __( 'Hide for Administrators', TTM_TEXTDOMAIN ); ?></td>
        <td>
            <?php 
                $checked = ( $ttm_options['ttm_backend_hide_admin'] ? 'checked' : '' ); 
            ?>
            <input type="checkbox" name="ttm_backend_hide_admin" id="ttm_backend_hide_admin" value="on" <?php echo $checked; ?> />

        </td>
    </tr>
    <tr>
        <td><?php echo __( 'Show for Editors', TTM_TEXTDOMAIN ); ?></td>
        <td>
            <?php 
                $checked = ( $ttm_options['ttm_backend_show_editors'] ? 'checked' : '' ); 
            ?>
            <input type="checkbox" name="ttm_backend_show_editors" id="ttm_backend_show_editors" value="on" <?php echo $checked; ?> />

        </td>
    </tr>
    <tr>
        <td><?php echo __( 'Show for Authors', TTM_TEXTDOMAIN ); ?></td>
        <td>
            <?php 
                $checked = ( $ttm_options['ttm_backend_show_authors'] ? 'checked' : '' ); 
            ?>
            <input type="checkbox" name="ttm_backend_show_authors" id="ttm_backend_show_authors" value="on" <?php echo $checked; ?> />

        </td>
    </tr>
    <tr>
        <td><?php echo __( 'Show for Subscribers', TTM_TEXTDOMAIN ); ?></td>
        <td>
            <?php 
                $checked = ( $ttm_options['ttm_backend_show_subscribers'] ? 'checked' : '' ); 
            ?>
            <input type="checkbox" name="ttm_backend_show_subscribers" id="ttm_backend_show_subscribers" value="on" <?php echo $checked; ?> />

        </td>
    </tr>
  </table>
  <?php submit_button(); ?>
</form>
<script>
window.onload = function() {
    if (window.jQuery) {
        
         jQuery("#ttm_backend_show_always").change(function()
         {
            var ischecked = document.getElementById('ttm_backend_show_always').checked;
            if(ischecked === true){
                jQuery("#ttm_backend_show_subscribers").prop( "checked",true);
                jQuery("#ttm_backend_show_subscribers").attr("disabled", true); 
                jQuery("#ttm_backend_show_editors").prop( "checked",true);
                jQuery("#ttm_backend_show_editors").attr("disabled", true); 
                jQuery("#ttm_backend_show_authors").prop( "checked",true);
                jQuery("#ttm_backend_show_authors").attr("disabled", true); 
            }else{
                //jQuery("#ttm_backend_hide_admin").removeAttr("disabled");
                jQuery("#ttm_backend_show_subscribers").removeAttr("disabled");
                jQuery("#ttm_backend_show_authors").removeAttr("disabled");
                jQuery("#ttm_backend_show_editors").removeAttr("disabled");
            }
        });
        
        // Enable/disable options based on show always value
        var ischecked = document.getElementById('ttm_backend_show_always').checked;
        if(ischecked === true){
            jQuery("#ttm_backend_show_subscribers").prop( "checked",true);
            jQuery("#ttm_backend_show_subscribers").attr("disabled", true);
            jQuery("#ttm_backend_show_authors").prop( "checked",true);
            jQuery("#ttm_backend_show_authors").attr("disabled", true);
            jQuery("#ttm_backend_show_editors").prop( "checked",true);
            jQuery("#ttm_backend_show_editors").attr("disabled", true);
        }  
    } 
} 
</script>
</div>



