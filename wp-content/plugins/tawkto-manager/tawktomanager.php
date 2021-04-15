<?php
/*
Plugin Name: TawkTo Manager
Plugin URI: http://www.tawktomanager.org/
Description: Description: Manage the tawk.to chat visibility with options for posts, pages, users, WooCommerce and more.
Author: Daniel Mulder
Version: 2.2.2
Author URI: http://www.omnileads.nl/daniel-mulder-all-star/
*/
defined( 'ABSPATH' ) or die( 'You do not have sufficient permissions to access this page.' );

global $ttm; // global plugin handle
$ttm = (object) null;

/*
 * Load constants and definitons of global functions once
*/
if(!defined( 'TTM_ABSPATH')):
    /*
     * Constants 
    */
    define( 'TTM_VERSION', 2.2 );
    define( 'TTM_TEXTDOMAIN', 'tawkto-manager' );
    define( 'TTM_SETTINGS_PAGE', 'ttm-tawkto-manager' );
    define( 'TTM_ABSPATH', WP_PLUGIN_DIR . '/' . TTM_TEXTDOMAIN );
    define( 'TTM_CTRLPATH', TTM_ABSPATH . '/controllers' );
    define( 'TTM_VIEWPATH', TTM_ABSPATH . '/views' );
    define( 'TTM_PLUGIN_FILE', TTM_ABSPATH . '/tawktomanager.php' );
    define('TTM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    define( 'TTM_PLUGIN_URL', WP_PLUGIN_URL.'/'.TTM_TEXTDOMAIN );
    define( 'TTM_SCRIPT_URL', WP_PLUGIN_URL.'/'.TTM_TEXTDOMAIN );
    /* 
     * GLOBAL FUNCTIONS 
    */
    if( !function_exists('ttm_woocommerce_active') ) {
        function ttm_woocommerce_active() 
        {
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                return TRUE;
            }
            return FALSE;
        }
    }
    if( !function_exists('ttm_check_referer') ) 
    {
        /*
         * Check if valid referrer with WordPress home_url() 
        */
        function ttm_check_referer($referer) 
        {
            if( stristr($referer, home_url( '/' )) ) {
                return TRUE;
            }
            return FALSE;
        }
    }
    if( !function_exists('ttm_get_current_url') ) 
    {
        function ttm_get_current_url() 
        {    
            // to fix the issues with IIS
            if (!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
                if (isset($_SERVER['QUERY_STRING'])) {
                    $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
                }
            }
            $reqUrl = $_SERVER['REQUEST_URI'];
            $protocol = empty($_SERVER['HTTPS']) ? "http://" : "https://";
            $port = empty($_SERVER['SERVER_PORT']) ?  "" : (int) $_SERVER['SERVER_PORT'];
            $host =  strtolower($_SERVER['HTTP_HOST']);
            if(!empty($port) && ($port <> 443) && ($port <> 80)){
                    if(strpos($host, ':') === false){ $host .= ':' . $port; }
            }
            $webPath = $protocol.$host.$reqUrl;
            return $webPath;
        }
    }
    if( !function_exists('ttm_get_current_slug') ) 
    {
        function ttm_get_current_slug() 
        {    
            $url = ttm_get_current_url();
            return parse_url($url, PHP_URL_PATH);
        }
    }
endif;

/*
 * Define TTM_Controller class
 * 
*/
if ( !class_exists('TTM_Controller') ):
    
    require_once( 'controllers/ttm.ctrl.php' );
    
endif;

/**
 * If WordPress dashboard / backend request load backend controller  
*/
if ( is_admin() && !class_exists('TTM_BackendController') ):
    
    $ttm = TTM_Controller::__create_controller('TTM_Backend'); 

endif;

/**
 * If frontend page request load frontend controller  
*/
if ( !is_admin() && !class_exists('TTM_FrontendController') ):
    
    $ttm = TTM_Controller::__create_controller('TTM_Frontend');
    
endif;

