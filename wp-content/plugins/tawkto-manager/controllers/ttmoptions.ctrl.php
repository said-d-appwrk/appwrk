<?php
/* 
 * Tawk.To Manager plugin for WordPress by OmniLeads.nl
 * Published under GNU license version 2
*/
defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' ); 
/*
 * Controller for storing options from backend on save
*/
class TTM_OptionsController extends TTM_Controller 
{
    /* 
     * Handle post request at save options for settings tab
    */
    public static function ttm_set_plugin_options() 
    {
        // Eval and persist wordpress options
        if(isset(self::$request['ttm_advanced_mode']) && self::$request['ttm_advanced_mode'] == "on" ){
            self::ttm_unset_advanced_options();
            self::$ttm_options['ttm_advanced_mode'] = "on";
            update_option('ttm_advanced_mode', self::$ttm_options['ttm_advanced_mode']);
        }else{
            self::ttm_unset_advanced_options();
            self::$ttm_options['ttm_advanced_mode'] = '';
            update_option('ttm_advanced_mode', self::$ttm_options['ttm_advanced_mode']);
        }
    }
    /* 
     * Unset advanced options if mode is deactivaded
    */
    public static function ttm_unset_advanced_options() 
    {
        unset(self::$ttm_options['ttm_hide_logged_in_subscribers']);
        delete_option('ttm_hide_logged_in_subscribers');
        
        unset(self::$ttm_options['ttm_backend_show_subscribers']);
        delete_option('ttm_backend_show_subscribers');
        
        unset(self::$ttm_options['ttm_backend_show_editors']);
        delete_option('ttm_backend_show_editors');
        unset(self::$ttm_options['ttm_backend_show_authors']);
        delete_option('ttm_backend_show_authors');
        unset(self::$ttm_options['ttm_backend_show_always']);
        delete_option('ttm_backend_show_always');
        unset(self::$ttm_options['ttm_backend_hide_admin']);
        delete_option('ttm_backend_hide_admin');        
        unset(self::$ttm_options['ttm_backend_show_myaccount']);
        delete_option('ttm_backend_show_myaccount');
        unset(self::$ttm_options['ttm_hide_shopmanager']);
        delete_option('ttm_hide_shopmanager');
    }
    /* 
     * Handle post request at save options for front-end tab
    */
    public static function ttm_set_frontend_options() 
    {
        // Eval and persist wordpress options
        if(isset(self::$request['ttm_show_always']) && self::$request['ttm_show_always'] == "on" ){
            self::$ttm_options['ttm_show_always'] = "on";
            update_option('ttm_show_always', self::$ttm_options['ttm_show_always']);
        }else{
            self::$ttm_options['ttm_show_always'] = '';
            update_option('ttm_show_always', self::$ttm_options['ttm_show_always']);
        }
        if(isset(self::$request['ttm_show_front_page']) && self::$request['ttm_show_front_page'] == "on" ){
            self::$ttm_options['ttm_show_front_page'] = "on";
            update_option('ttm_show_front_page', self::$ttm_options['ttm_show_front_page']);
        }else{
            self::$ttm_options['ttm_show_front_page'] = '';
            update_option('ttm_show_front_page', self::$ttm_options['ttm_show_front_page']);
        }
        if(isset(self::$request['ttm_show_cat_pages']) && self::$request['ttm_show_cat_pages'] == "on" ){
            self::$ttm_options['ttm_show_cat_pages'] = "on";
            update_option('ttm_show_cat_pages', self::$ttm_options['ttm_show_cat_pages']);
        }else{
            self::$ttm_options['ttm_show_cat_pages'] = '';
            update_option('ttm_show_cat_pages', self::$ttm_options['ttm_show_cat_pages']);
        }
        if(isset(self::$request['ttm_show_tag_pages']) && self::$request['ttm_show_tag_pages'] == "on" ){
            self::$ttm_options['ttm_show_tag_pages'] = "on";
            update_option('ttm_show_tag_pages', self::$ttm_options['ttm_show_tag_pages']);
        }else{
            self::$ttm_options['ttm_show_tag_pages'] = '';
            update_option('ttm_show_tag_pages', self::$ttm_options['ttm_show_tag_pages']);
        }
        
        // New with update 2.3
        
        if(isset(self::$request['ttm_show_page']) && self::$request['ttm_show_page'] == "on" ){
            self::$ttm_options['ttm_show_page'] = "on";
            update_option('ttm_show_page', self::$ttm_options['ttm_show_page']);
        }else{
            self::$ttm_options['ttm_show_page'] = '';
            update_option('ttm_show_page', self::$ttm_options['ttm_show_page']);
        }
        
        if(isset(self::$request['ttm_show_post']) && self::$request['ttm_show_post'] == "on" ){
            self::$ttm_options['ttm_show_post'] = "on";
            update_option('ttm_show_post', self::$ttm_options['ttm_show_post']);
        }else{
            self::$ttm_options['ttm_show_post'] = '';
            update_option('ttm_show_post', self::$ttm_options['ttm_show_post']);
        }
        
        
        if(isset(self::$request['ttm_hide_admin']) && self::$request['ttm_hide_admin'] == "on" ){
            self::$ttm_options['ttm_hide_admin'] = "on";
            update_option('ttm_hide_admin', self::$ttm_options['ttm_hide_admin']);
        }else{
            self::$ttm_options['ttm_hide_admin'] = '';
            update_option('ttm_hide_admin', self::$ttm_options['ttm_hide_admin']);
        }
        if(isset(self::$request['ttm_hide_logged_in_subscribers']) && self::$request['ttm_hide_logged_in_subscribers'] == "on" ){
            self::$ttm_options['ttm_hide_logged_in_subscribers'] = "on";
            update_option('ttm_hide_logged_in_subscribers', self::$ttm_options['ttm_hide_logged_in_subscribers']);
        }else{
            self::$ttm_options['ttm_hide_logged_in_subscribers'] = '';
            update_option('ttm_hide_logged_in_subscribers', self::$ttm_options['ttm_hide_logged_in_subscribers']);
        }
        if(isset(self::$request['ttm_show_logged_in_subscribers']) && self::$request['ttm_show_logged_in_subscribers'] == "on" ){
            self::$ttm_options['ttm_show_logged_in_subscribers'] = "on";
            update_option('ttm_show_logged_in_subscribers', self::$ttm_options['ttm_show_logged_in_subscribers']);
        }else{
            self::$ttm_options['ttm_show_logged_in_subscribers'] = '';
            update_option('ttm_show_logged_in_subscribers', self::$ttm_options['ttm_show_logged_in_subscribers']);
        }
    }
    /* 
     * Handle post request at save options for back-end tab
    */
    public static function ttm_set_backend_options() 
    {
        // Eval and persist as wordpress options
        if(isset(self::$request['ttm_backend_show_always']) && self::$request['ttm_backend_show_always'] == "on" ){
            self::$ttm_options['ttm_backend_show_always'] = "on";
            update_option('ttm_backend_show_always', self::$ttm_options['ttm_backend_show_always']);
        }else{
            self::$ttm_options['ttm_backend_show_always'] = '';
            update_option('ttm_backend_show_always', self::$ttm_options['ttm_backend_show_always']);
        }
        if(isset(self::$request['ttm_backend_hide_admin']) && self::$request['ttm_backend_hide_admin'] == "on" ){
            self::$ttm_options['ttm_backend_hide_admin'] = "on";
            update_option('ttm_backend_hide_admin', self::$ttm_options['ttm_backend_hide_admin']);
        }else{
            self::$ttm_options['ttm_backend_hide_admin'] = '';
            update_option('ttm_backend_hide_admin', self::$ttm_options['ttm_backend_hide_admin']);
        }
        if(isset(self::$request['ttm_backend_show_subscribers']) && self::$request['ttm_backend_show_subscribers'] == "on" ){
            self::$ttm_options['ttm_backend_show_subscribers'] = "on";
            update_option('ttm_backend_show_subscribers', self::$ttm_options['ttm_backend_show_subscribers']);
        }else{
            self::$ttm_options['ttm_backend_show_subscribers'] = '';
            update_option('ttm_backend_show_subscribers', self::$ttm_options['ttm_backend_show_subscribers']);
        }
        if(isset(self::$request['ttm_backend_show_authors']) && self::$request['ttm_backend_show_authors'] == "on" ){
            self::$ttm_options['ttm_backend_show_authors'] = "on";
            update_option('ttm_backend_show_authors', self::$ttm_options['ttm_backend_show_authors']);
        }else{
            self::$ttm_options['ttm_backend_show_authors'] = '';
            update_option('ttm_backend_show_authors', self::$ttm_options['ttm_backend_show_authors']);
        }
        if(isset(self::$request['ttm_backend_show_editors']) && self::$request['ttm_backend_show_editors'] == "on" ){
            self::$ttm_options['ttm_backend_show_editors'] = "on";
            update_option('ttm_backend_show_editors', self::$ttm_options['ttm_backend_show_editors']);
        }else{
            self::$ttm_options['ttm_backend_show_editors']= '';
            update_option('ttm_backend_show_editors', self::$ttm_options['ttm_backend_show_editors']);
        }
    }
    /* 
     * Handle post request at save options for WooCommerce tab
    */
    public static function ttm_set_woocommerce_options() 
    {
        if(isset(self::$request['ttm_hide_shopmanager']) && self::$request['ttm_hide_shopmanager'] == "on" ){
            self::$ttm_options['ttm_hide_shopmanager'] = "on";
            update_option('ttm_hide_shopmanager', self::$ttm_options['ttm_hide_shopmanager']);
        }else{
            self::$ttm_options['ttm_hide_shopmanager'] = '';
            update_option('ttm_hide_shopmanager', self::$ttm_options['ttm_hide_shopmanager']);
        }
        if(isset(self::$request['ttm_backend_hide_shopmanager']) && self::$request['ttm_backend_hide_shopmanager'] == "on" ){
            self::$ttm_options['ttm_backend_hide_shopmanager'] = "on";
            update_option('ttm_backend_hide_shopmanager', self::$ttm_options['ttm_backend_hide_shopmanager']);
        }else{
            self::$ttm_options['ttm_backend_hide_shopmanager'] = '';
            update_option('ttm_backend_hide_shopmanager', self::$ttm_options['ttm_backend_hide_shopmanager']);
        }
        if(isset(self::$request['ttm_backend_show_myaccount']) && self::$request['ttm_backend_show_myaccount'] == "on" ){
            self::$ttm_options['ttm_backend_show_myaccount'] = "on";
            update_option('ttm_backend_show_myaccount', self::$ttm_options['ttm_backend_show_myaccount']);
        }else{
            self::$ttm_options['ttm_backend_show_myaccount'] = '';
            update_option('ttm_backend_show_myaccount', self::$ttm_options['ttm_backend_show_myaccount']);
        }
         if(isset(self::$request['ttm_show_single_product']) && self::$request['ttm_show_single_product'] == "on" ){
            self::$ttm_options['ttm_show_single_product'] = "on";
            update_option('ttm_show_single_product', self::$ttm_options['ttm_show_single_product']);
        }else{
            self::$ttm_options['ttm_show_single_product'] = '';
            update_option('ttm_show_single_product', self::$ttm_options['ttm_show_single_product']);
        }
        if(isset(self::$request['ttm_show_shop_page']) && self::$request['ttm_show_shop_page'] == "on" ){
            self::$ttm_options['ttm_show_shop_page']= "on";
            update_option('ttm_show_shop_page', self::$ttm_options['ttm_show_shop_page']);
        }else{
            self::$ttm_options['ttm_show_shop_page']= '';
            update_option('ttm_show_shop_page', self::$ttm_options['ttm_show_shop_page']);
        }
        if(isset(self::$request['ttm_show_cart_page']) && self::$request['ttm_show_cart_page'] == "on" ){
            self::$ttm_options['ttm_show_cart_page'] = "on";
            update_option('ttm_show_cart_page', self::$ttm_options['ttm_show_cart_page']);
        }else{
            self::$ttm_options['ttm_show_cart_page'] = '';
            update_option('ttm_show_cart_page', self::$ttm_options['ttm_show_cart_page']);
        }
        if(isset(self::$request['ttm_show_checkout_page']) && self::$request['ttm_show_checkout_page'] == "on" ){
            self::$ttm_options['ttm_show_checkout_page'] = "on";
            update_option('ttm_show_checkout_page', self::$ttm_options['ttm_show_checkout_page']);
        }else{
            self::$ttm_options['ttm_show_checkout_page'] = '';
            update_option('ttm_show_checkout_page', self::$ttm_options['ttm_show_checkout_page']);
        }
    }

}