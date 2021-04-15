<?php 
/* 
 * Tawk.To Manager plugin for WordPress by OmniLeads.nl
 * Published under GNU license version 2 * 
*/
defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' ); 

class TTM_BackendController extends TTM_Controller{
    
    private static $tabs=array('frontend','backend','woocommerce','script','settings','help');
    protected $optionsCtrl;
    
    public function __construct() 
    {  
        parent::__construct();
        add_action( 'admin_menu', array(__CLASS__, 'ttm_tawkto_manager_plugin_menu') ); // wp dash menu
        add_action( 'admin_init', array(__CLASS__, 'ttm_admin_init') ); // options page
        add_action( 'admin_head', array(__CLASS__, 'ttm_admin_head') ); // add tawkto script to html header
        if( empty(self::$ttm_options['ttm_version']) || self::$ttm_options['ttm_version'] < TTM_VERSION ) {
            register_activation_hook( TTM_PLUGIN_FILE, array(__CLASS__,'__ttm_install' ));
        }
    }
    
    /* 
     * Register WordPress settings for admin area
    */
    static function ttm_admin_init()
    {
        self::__register_settings();
        self::ttm_remove_footer(); 
    }
    
    /* 
     * Callback activation hook for new install
    */
    static function __ttm_install()
    {
        // Update version to new
        self::$ttm_options['ttm_version'] = TTM_VERSION;
        update_option('ttm_version', self::$ttm_options['ttm_version']);
        // Check if previous version is installed 
        if( get_option('ttm_hide_subscribers') === TRUE ) {
            delete_option('ttm_hide_subscribers'); // Option depreciated
        }
    }
    
    /* 
     * Register plugin options and backward compatible WP 4.6 and lower
    */
    protected static function __register_settings()
    {
        global $wp_version;
        if ( $wp_version >= 4.7 ) {
            /** register wp settings WordPress 4.7 and up */
            $args = array('show_in_rest'=> '','type' =>'string','default' =>'',);            
            foreach (self::$ttm_options as $i => $option) 
            {
                register_setting( 'ttm_tawkto_manager_plugin_options', $option, $args );
            }
        }else {
            /** register wp settings WordPress 4.6 and lower */
            foreach (self::$ttm_options as $i => $option)         
            {
                register_setting( 'ttm_tawkto_manager_plugin_options', array(__CLASS__, $option ) );
            }
        }
    }
    
    /* 
     * Render plugin options page with separation of view content and page layout 
    */
    static function ttm_render($viewFile='tabs/frontend', $layout='tabs')
    {
        if(count(self::$data) > 0)
        {
            foreach (self::$data as $varName => $varValue)
            {
                $$varName = $varValue;
            }
        }
        ob_start(); 
        include_once(TTM_VIEWPATH."/".$viewFile.".ctp.php");
        $viewContent = ob_get_contents(); // Get view content from view file
        ob_end_clean();
        ob_start(); // Create final render within template
        include_once(TTM_VIEWPATH."/layout/".$layout.".ctp.php"); 
    }
    
    /* 
     * Set class variable to local view var
    */
    static function set($varName, $varValue)
    {
        self::$data[$varName] = $varValue;
    }

    /* 
     * Get class variable from local view var
    */
    static function get($varName)
    {
        return self::$data[$varName];
    }
    
    /* 
     * Route request by user role in admin section
     * Prints script to view
    */
    public static function ttm_admin_head() 
    {
        $roles = self::ttm_user_roles();
        if($roles === FALSE) { 
            return;
        }
        switch ($roles[0]) 
        {
            case 'administrator':
                    if( !self::$ttm_options['ttm_backend_hide_admin'] && self::$ttm_options['ttm_backend_show_always'] ) 
                    {
                        self::ttm_out_script();
                    }
                break;
                case 'shop_manager':
                    if( !self::$ttm_options['ttm_backend_hide_shopmanager'] || self::$ttm_options['ttm_backend_show_always'] ) 
                    {
                        self::ttm_out_script();
                    }
                break;
            case 'editor':
                    if( self::$ttm_options['ttm_backend_show_editors'] || self::$ttm_options['ttm_backend_show_always'] ) 
                    {
                        self::ttm_out_script();
                    }
                break;
            case 'author':
                    if( self::$ttm_options['ttm_backend_show_authors'] || self::$ttm_options['ttm_backend_show_always'] ) 
                    {
                        self::ttm_out_script();
                    }
                break;
            case 'subscriber':
                    if( self::$ttm_options['ttm_backend_show_subscribers'] || self::$ttm_options['ttm_backend_show_always'] ) 
                    {
                        self::ttm_out_script();
                    }
                break;
            default:
                break;
        }
    }
    
    /* 
     * Create admin options menu 
    */
    static function ttm_tawkto_manager_plugin_menu() 
    {
        add_options_page(
            'TawkTo Manager Plugin Options',
            __('TawkTo settings', TTM_TEXTDOMAIN ),'manage_options',
            TTM_SETTINGS_PAGE,array( __CLASS__, 
            'ttm_tawkto_manager_plugin_options' ) 
        );
    }

    /* 
     * Admin menu callback for option handling and tabs
    */
    static function ttm_tawkto_manager_plugin_options() 
    {
	self::ttm_get_request(); //clean request
        /** check if admin */
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
        /** 
         * Set default tab or valid other tab 
        */
        self::$active_tab = self::$tabs[0];
        if( !empty(self::$request['tab']) ) { // Needs to be the array with valid tabs
            if( in_array(self::$request['tab'], self::$tabs) ){
                self::$active_tab = self::$request['tab'];
            }
        }
        if( self::$active_tab == 'backend' && empty(self::$ttm_options['ttm_advanced_mode']) ) {
            self::$active_tab = self::$tabs[0];
            self::$request['tab'] = self::$active_tab;
        }
        /** 
         * process settings form on submit 
        */
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {  
            /** Validate secure referrer nonce  */
            $nonce = self::$request['security'];
            if ( !wp_verify_nonce( $nonce, 'sec-callback' ) ) {
                wp_die(); // nonce not valid so die
                return;
            } 
            /** Validate secure referrer  */
            if( ttm_check_referer(self::$request['HTTP_REFERER']) === FALSE ) {
                wp_die(); // http referer not valid so die
                return;
            }
            
            // Delete temp options store
            delete_transient(self::$transient);  
            
            // Init options controller storing post requests data
            $optionsCtrl = TTM_Controller::__create_controller('TTM_Options'); 
            
            // Update tawkto script and cleaned by ttm_get_request()
            if( self::$active_tab == 'script' ) 
            {
                    if( isset(self::$request['ttm_tawktoscript']) ) 
                    {
                        self::$ttm_options['ttm_tawktoscript'] =  trim(self::$request['ttm_tawktoscript']);
                        update_option('ttm_tawktoscript', self::$ttm_options['ttm_tawktoscript'] ); // automatically cleans input
                    }
            }else{  
                    // Switch request tab to process posted options
                    switch ( self::$active_tab ) 
                    {
                        case 'settings':
                                $optionsCtrl->ttm_set_plugin_options();
                            break;
                        case 'frontend':
                                $optionsCtrl->ttm_set_frontend_options();
                            break;
                        case 'backend':
                                $optionsCtrl->ttm_set_backend_options();
                            break;
                        case 'woocommerce':
                                $optionsCtrl->ttm_set_woocommerce_options();
                            break;
                        default:
                                $optionsCtrl->ttm_set_frontend_options();
                            break;
                    }    
            }
        }
        // Set no script warning message
        if(self::$ttm_options['ttm_tawktoscript'] == '') {
            self::set('msg', 1);
        }
        // Set view vars and render 
        $viewFile = 'tabs/'.self::$active_tab;
        $nonce = self::ttm_create_nonce();
        self::set('nonce', $nonce);
        self::set('active_tab', self::$active_tab);
        self::set('ttm_options', self::$ttm_options);
        self::ttm_render($viewFile);
    }
    
    /* 
     * Remove footer in admin options section
    */
    public static function ttm_remove_footer()
    {
        remove_filter( 'update_footer', 'core_update_footer' );
        add_filter( 'admin_footer_text', '__return_empty_string', 11 ); 
    }
    
} 
