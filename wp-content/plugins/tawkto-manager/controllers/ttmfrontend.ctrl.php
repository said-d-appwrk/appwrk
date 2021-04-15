<?php 
/* 
 * Tawk.To Manager plugin for WordPress by OmniLeads.nl
 * Published under GNU license version 2 * 
*/
defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' ); 

class TTM_FrontendController extends TTM_Controller
{
    public function __construct()
    {
        parent::__construct();
        self::__ttm_init_options();
        add_shortcode('tawkto_show', array(__CLASS__, 'ttm_eval_short_code') );             
        add_action( 'wp_head', array(__CLASS__, 'ttm_eval_show') );
    }
    
    /* 
     * Short code eval hide/show chat 
    */
    static function ttm_eval_short_code()
    {   
        if( is_user_logged_in() ) {
            if( self::ttm_eval_show_logged_in() === FALSE ){
                return;
            }
        }
        if(!is_category() && !is_tag()){
            self::ttm_out_script(); 
        }
    }
    
    /* 
     * Evaluate hide/show chat front-end
    */
    static function ttm_eval_show()
    { 
        /* eval for logged in */
        if( is_user_logged_in() ) {
            if( self::ttm_eval_show_logged_in() === FALSE ){
                return;
            }
        }    
        /* if  show_always */
        if(self::$ttm_options['ttm_show_always']){
            self::ttm_out_script();
            return;
        }
        /* front page */
        if(is_front_page() || is_home()){
            if(self::$ttm_options['ttm_show_front_page']){
                self::ttm_out_script();
                return;
            }
        }
        /* category pages */
        if(is_category()){
            if(self::$ttm_options['ttm_show_cat_pages']){
                self::ttm_out_script();
                return;
            }
        }
        /* tag pages */
        if(is_tag()){
            if(self::$ttm_options['ttm_show_tag_pages']){
                self::ttm_out_script();
                return;
            }
        }
        /* 
         * New 2.3
        */
        /* single page */
        if( is_singular('page') )
        {
            if(!is_front_page() && !is_home()){
                if(self::$ttm_options['ttm_show_page']){
                    self::ttm_out_script();
                    return;
                }
            }
        }
        /* single page */
        if(is_singular('post') ){
            if(self::$ttm_options['ttm_show_post']){
                self::ttm_out_script();
                return;
            }
        }
        // end new
        /* WooCommerce options */
        if( ttm_woocommerce_active() )
        {
            if( is_shop() ){
                if(self::$ttm_options['ttm_show_shop_page']){
                    self::ttm_out_script();
                    return;
                }
            }
            if(is_cart() ){
                if(self::$ttm_options['ttm_show_cart_page']){
                    self::ttm_out_script();
                    return;
                }
            }
            if(is_singular('product') ){
                if(self::$ttm_options['ttm_show_single_product']){
                    self::ttm_out_script();
                    return;
                }
            }
            if(is_checkout() ){
                if(self::$ttm_options['ttm_show_checkout_page']){
                    self::ttm_out_script();
                    return;
                }
            }
            if(is_account_page()){
                
                if(self::$ttm_options['ttm_backend_show_myaccount']){
                    self::ttm_out_script();
                    return;
                }

            }
        }
    }
    
    /* 
     * Evaluate hide/show chat for logged in user role
     * @boolean
    */
    
    static function ttm_eval_show_logged_in()
    {   
        $roles = self::ttm_user_roles();
        if($roles === FALSE) { 
            return;
        }
        switch ($roles[0]) 
        {
            case 'administrator':
                    if( self::$ttm_options['ttm_hide_admin'] ) 
                    {
                        return FALSE;
                    }
                break;
            case 'shop_manager':
                    if( self::$ttm_options['ttm_hide_shopmanager'] ) 
                    {
                        return FALSE;
                    }
                break;
            case 'subscriber':
                    if( self::$ttm_options['ttm_hide_logged_in_subscribers'] ) 
                    {
                        return FALSE;
                    }
                break;
            default:
                break;
        }
        return TRUE;            
    }

}
