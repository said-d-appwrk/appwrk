<?php 
/* 
 * Tawk.To Manager plugin for WordPress by OmniLeads.nl
 * Published under GNU license version 2 * 
*/
defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' ); 
/*
 * Plugin controller base class with higher orbit functions
*/
class TTM_Controller
{
    public static    $msg;
    public static    $active_tab;
    protected static $data;
    protected static $request;
    protected static $ajax_nonce;
    protected static $ttm_options;
    protected static $transient = 'ttm_options';
    protected static $ttm_version;
            
    function __construct()
    {
        self::ttm_load_textdomain();
        self::__ttm_init_options();
    }
    
    /*
     * Init i18 localization text domain
    */
    public static function ttm_load_textdomain()
    {
       load_plugin_textdomain( TTM_TEXTDOMAIN, false, TTM_TEXTDOMAIN . '/languages' ); 
    }
    
    /* 
     * Get values from WordPress options for option values to use
     * @ttm_options 
    */
    protected static function __ttm_init_options()
    {
        if ( false === ($options=get_transient(self::$transient)) ) 
        {   // Set transient to limit file inclusions
            include(TTM_ABSPATH.'/includes/options.php');
            // expires after x*y seconds or when saving options
            set_transient( self::$transient, $options, 60 * 240 ); 
        }
        foreach ($options['wp'] as $option) 
        {
            self::$ttm_options[$option] = get_option($option);
        }
        // WooCommerce visibility options
        if( ttm_woocommerce_active() ) {   
            foreach ($options['wc'] as $option) 
            {
                self::$ttm_options[$option] = get_option($option);
            }
        }
    }
    
    /* 
     * Print out the tawkto script and eval if visible
     * Outputs inline to html source where called
    */
    public static function ttm_out_script()
    {  
        if( self::$ttm_options['ttm_tawktoscript'] == '' ) return;
        echo '<script>'.PHP_EOL; 
        echo wp_unslash(self::$ttm_options['ttm_tawktoscript']).PHP_EOL;
        echo '</script>'.PHP_EOL;
    }
       
    /* 
     * Secure callback create nonce for options page in admin area
    */

    protected static function ttm_create_nonce()
    {
        if(self::is_admin_logged_in()){
            self::$ajax_nonce = wp_create_nonce( "sec-callback" );
            return self::$ajax_nonce;
        }
    }

    /* 
     * Secure callback get nonce for reading options page in admin area
     * 
    */
    public static function ttm_get_nonce()
    {
        if(self::is_admin_logged_in()){
            return self::$ajax_nonce;
        }
    }

    /* 
     * Determine if user is logged in with admin rights
     * @bolean 
     * 
    */
    protected static function is_admin_logged_in()
    {
        $userInfo = wp_get_current_user();
        if (in_array( 'administrator', (array) $userInfo->roles)){
            return true;
        }
        return false;
    }     
    
    /* 
     * Determine user role or return false if not logged in
     * @bolean 
     * 
    */
    protected static function ttm_user_roles()
    {
        if( !is_user_logged_in() ) {
            return FALSE;
        }
        $userInfo = wp_get_current_user();
        return $userInfo->roles;
    }

    /* 
     * Determine if user is logged and confirm role or return roles
     * @array (count(array) is 0 for false or contains user roles) 
     * 
    */
    protected static function role_is_logged_in($role='')
    {
        if( is_user_logged_in() && !self::is_admin_logged_in() ){
            if($role != ''){
                $userInfo = wp_get_current_user();
                if (in_array( $role, (array) $userInfo->roles)){
                    return (bool) true;
                }
            }
            else{
                $userInfo = wp_get_current_user();
                return (array) $userInfo->roles;
            }
        }
        return (array) $null;
    }
        
    /* 
    * Protect basic sql injection / no need to check for input from here on 
    * when using request vars inside this class
    */
    public static function ttm_get_request(){
        if( is_array(self::$request) ){
            return self::$request;
        }
        # to prevent sql and script injection
        if(!empty($_SERVER['REQUEST_METHOD'])){
            # merge all post and get elements
            foreach (array_merge($_GET, $_POST) AS $name => $value) {
                # if not a numeric parameter
                if (is_string($value) && !empty($value) && !is_numeric($value)) {
                    # Search for patterns in the value of the parameter that indicate an SQL injection
                    $pattern = '/(and|or)[\s\(\)\/\*]+(update|delete|select)\W|(select|update).+\.(password|email)|(select|update|delete).+users|'
                               .'<script>|^<script type=.*\>|<!--Start of Tawk.to Script-->|<!--End of Tawk.to Script-->|<\/script>/im';
                    # replace all matched strings
                    while (preg_match($pattern, $value)) {
                        if (isset($_GET[$name])) {
                            $value = $_GET[$name] = $_REQUEST[$name] = preg_replace($pattern, '', $value);
                        } else {
                            $value = $_POST[$name] = $_REQUEST[$name] = preg_replace($pattern, '', $value);
                        }
                    }
                }
            }
        }
        // Contruct final request array
        self::$request = array_merge($_GET, $_POST);
        if( !isset($_REQUEST) ){
            self::$request=array();
        }
        if( !empty($_SERVER['HTTP_REFERER']) ){
             self::$request['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
        }
	return self::$request;
    }
    
    /* 
    * Create cotroller object
    */
    public static function __create_controller($ctrlName) {
       $ctrlFileName = preg_replace('/(_)/', '', $ctrlName);
       include_once(TTM_ABSPATH.'/controllers/' . strtolower($ctrlFileName).".ctrl.php" );
       $ctrlName .= "Controller"; 
       $controllerObj = new $ctrlName();
       return $controllerObj;
    }
}  
