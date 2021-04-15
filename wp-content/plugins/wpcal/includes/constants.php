<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

class WPCal_Constants{

	public static  function init(){
		self::define( 'WPCAL_START_TIME', microtime(true) );
		self::may_load_dev_config();
		self::general();
		self::env();
		self::versions();
		self::path();
		self::debug();
	}

	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private static function may_load_dev_config(){
		$_wpcal__plugin_dir = dirname(dirname(__FILE__));
		if(file_exists($_wpcal__plugin_dir.'/_dev_config.php')){
			@include_once($_wpcal__plugin_dir.'/_dev_config.php');
		}
		unset($_wpcal__plugin_dir);
	}

	private static function general(){
		self::define( 'WPCAL_PLUGIN_SLUG', 'wpcal' );
		self::define( 'WPCAL_TIMEOUT', 15 );
	}

	private static function versions(){
		self::define( 'WPCAL_VERSION', '0.9.2.0' );
	}

	private static function debug(){
		self::define( 'WPCAL_DEBUG', false );
	}

	private static function env(){
		self::define( 'WPCAL_ENV', 'PROD' );
	}

	private static function path(){

		self::define( 'WPCAL_PATH', untrailingslashit(WP_PLUGIN_DIR . '/' . basename(dirname(dirname(__FILE__)))) );
		self::define( 'WPCAL_PLUGIN_URL', plugin_dir_url( WPCAL_PATH.'/wpcal.php' ) );
		

		self::define( 'WPCAL_SITE_URL', 'https://wpcal.io/' );
		self::define( 'WPCAL_AUTH_URL', WPCAL_SITE_URL.'app-auth/' );
		self::define( 'WPCAL_MY_ACCOUNT_URL', WPCAL_SITE_URL.'my-account/' );
		self::define( 'WPCAL_SITE_LOST_PASS_URL', WPCAL_SITE_URL.'my-account/lost-password/' );

		self::define( 'WPCAL_CRON_URL', 'https://cron.wpcal.io/' );//change to https Improve Later


		$dist_url = WPCAL_PLUGIN_URL.'dist/';
		if( defined('WPCAL_ENV') && WPCAL_ENV === 'DEV' ){
			$dist_url = 'https://localhost:8080/';
		}
		self::define( 'WPCAL_PLUGIN_DIST_URL', $dist_url );

		self::define( 'WPCAL_GOOGLE_OAUTH_REDIRECT_SITE_URL', WPCAL_SITE_URL );
		self::define( 'WPCAL_ZOOM_OAUTH_REDIRECT_SITE_URL', WPCAL_SITE_URL );
		self::define( 'WPCAL_GOTOMEETING_OAUTH_REDIRECT_SITE_URL', WPCAL_SITE_URL );
	}
}

WPCal_Constants::init();