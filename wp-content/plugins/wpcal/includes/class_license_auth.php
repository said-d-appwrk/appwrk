<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined('ABSPATH')){ exit; }

class WPCal_License{
	private static $show_notice = false;

	private static $notice_class = 'notice notice-info';
	private static $notice_message = '';

	private static $enable_feature = true;

	private static function do_auth_with_action($creds, $action, $data = false){

		if( empty($creds['email']) || empty($action) || 
		( $action === 'check_validity' && empty($creds['site_token']) ) ){
			throw new WPCal_Exception('invalid_request');
		}

		if(!in_array($action, array('check_validity', 'add_site', 'signup'))){
			throw new WPCal_Exception('invalid_request');
		}

		$url = WPCAL_AUTH_URL;

		$request_data = array();
		if(!empty($data) && is_array($data)){
			$request_data = $data;
		}

		$request_data['email'] =  base64_encode($creds['email']);
		$request_data['action'] =  $action;
		$request_data['site_url'] =  trailingslashit(site_url());
		$request_data['plugin_slug'] =  WPCAL_PLUGIN_SLUG;
		$request_data['plugin_version'] =  WPCAL_VERSION;

		if( isset($creds['password']) ){
			$request_data['password'] =  base64_encode($creds['password']);
		}
		
		if( isset($creds['site_token']) ){
			$request_data['site_token'] =  base64_encode($creds['site_token']);
		}
		
		$body = $request_data;

		$http_args = array(
			'method' => "POST",
			'timeout' => 10,
			'body' => $body
		);

		try{
			$response = wp_remote_request( $url, $http_args );

			//wpcal_debug::log($response,'-----------$license_response----------------');
			$response_data = wpcal_check_and_get_data_from_response_json($response);
		}
		catch(WPCal_Exception $e){
			throw $e;
		}

		if(empty($response_data) || !is_array($response_data)){
			throw new WPCal_Exception('invalid_response'); 
		}

		return $response_data;
	}

	private static function save_creds_info($creds){

		// if(empty($creds['email']) || empty($creds['site_token']) || empty($creds['last_validated'])){
		// 	return false;
		// }
		
		if( isset($creds['site_token']) ){
			$creds['site_token'] = base64_encode($creds['site_token']);
		}

		$whitelist = array('email', 'site_token', 'last_validated', 'last_checked', 'status', 'expiry', 'validity_error', 'issue_deducted', 'plan_slug', 'plan_name');
		$creds = array_intersect_key( $creds, array_flip( $whitelist ) );

		return update_option('wpcal_license_auth_info', $creds);

	}

	public static function get_account_info(){
		$creds = self::get_creds_info();

		$whitelist = array('email', 'status', 'plan_slug', 'plan_name');
		$info = array_intersect_key( $creds, array_flip( $whitelist ) );
		return $info;
	}

	public static function get_site_token_and_account_email(){
		$creds = self::get_creds_info();

		$whitelist = array('email', 'site_token');
		$info = array_intersect_key( $creds, array_flip( $whitelist ) );
		return $info;
	}

	private static function get_creds_info(){

		$creds = get_option('wpcal_license_auth_info');
		if(empty($creds) || empty($creds['email']) || empty($creds['site_token'])){
			return is_array($creds) ? $creds : [];
		}

		$creds['site_token'] = base64_decode($creds['site_token']);

		return $creds;
	}

	public static function signup($creds){
		//santizing of email and password taken care at license side, beware of using it/displaying here.
		$creds['email'] = trim($creds['email']);
		$response_data = self::do_auth_with_action($creds, 'signup');

		if( !isset($response_data['status']) ){
			throw new WPCal_Exception('license__invalid_response');
		}

		if( $response_data['status'] === 'success' && $response_data['success'] === 'user_added' ){
			return true;
		}
		elseif( $response_data['status'] === 'error' && $response_data['error'] ){
			if( isset($response_data['error_msg']) && $response_data['error_msg'] ){
				throw new WPCal_Exception('license__'.$response_data['error'], $response_data['error_msg']); 
			}
			throw new WPCal_Exception('license__'.$response_data['error']); 
		}
		else{
			throw new WPCal_Exception('license__invalid_response');
		}
	}


	public static function login($creds){
		//santizing of email and password taken care at license side, beware of using it/displaying here.
		$creds['email'] = trim($creds['email']);
		$response_data = self::do_auth_with_action($creds, 'add_site');

		if( !isset($response_data['status']) ){
			throw new WPCal_Exception('license__invalid_response');
		}

		if( $response_data['status'] === 'success' && $response_data['success'] === 'added' ){
			$creds_to_save = $creds;
			unset($creds_to_save['password']);
			$creds_to_save['status'] = 'valid';
			$creds_to_save['plan_slug'] = isset($response_data['plan_slug']) ? $response_data['plan_slug'] : '';
			$creds_to_save['plan_name'] = isset($response_data['plan_slug']) ? $response_data['plan_name'] : '';
			$creds_to_save['expiry'] = isset($response_data['expiry']) ? $response_data['expiry'] : '';
			$creds_to_save['last_checked'] = time();
			$creds_to_save['last_validated'] = time();

			if( empty($response_data['site_token']) || !is_string($response_data['site_token']) ){
				throw new WPCal_Exception('license__invalid_token');
			}

			$creds_to_save['site_token'] = $response_data['site_token'];
			unset($creds_to_save['issue_deducted']);
			self::save_creds_info($creds_to_save);
			//wpcal_dev_remove_cron();
			//wpcal_dev_add_cron();
			return true;
		}
		elseif( $response_data['status'] === 'error' && $response_data['error'] ){
			if( isset($response_data['error_msg']) && $response_data['error_msg'] ){
				throw new WPCal_Exception('license__'.$response_data['error'], $response_data['error_msg']); 
			}
			throw new WPCal_Exception('license__'.$response_data['error']); 
		}
		else{
			throw new WPCal_Exception('license__invalid_response');
		}
	}

	public static function check_validity(){//check the license

		try{
			$creds = self::get_creds_info();
			if(empty($creds)){
				return false;
			}

			$response_data = self::do_auth_with_action($creds, 'check_validity');

			if( !isset($response_data['status']) ){
				throw new WPCal_Exception('license__invalid_response');
			}

			if( $response_data['status'] === 'success' && $response_data['success'] === 'valid'){
				$creds['status'] = 'valid';
				$creds['expiry'] = isset($response_data['expiry']) ? $response_data['expiry'] : '';
				$creds['plan_slug'] = isset($response_data['plan_slug']) ? $response_data['plan_slug'] : '';
				$creds['plan_name'] = isset($response_data['plan_slug']) ? $response_data['plan_name'] : '';
				$creds['last_checked'] = time();
				$creds['last_validated'] = time();
				unset($creds['issue_deducted']);
				self::save_creds_info($creds);
				return true;
			}
			elseif( $response_data['status'] === 'error' && in_array( $response_data['error'], array('invalid_user', 'expired', 'not_valid'), true ) ){

				$creds['status'] = 'error';
				$creds['validity_error'] = $response_data['error'];
				if( isset($response_data['expiry']) ){
					$creds['expiry'] = $response_data['expiry'];
				}
				if( $response_data['error'] === 'not_valid'){
					$creds['issue_deducted'] = empty($creds['issue_deducted']) ? time() : $creds['issue_deducted'];
				}
				$creds['last_checked'] = time();
				self::save_creds_info($creds);
				return false;
			}
			else{
				throw new WPCal_Exception('license__invalid_response');
			}
		}
		catch(WPCal_Exception $e){
			$error = $e->getError();
			$error_msg = $e->getErrorMessage();
		}
	}

	public static function is_valid($cache=false){//check the db

		$creds = self::get_creds_info();
		if( !isset( $creds['last_validated']) || !isset( $creds['last_checked']) || !isset($creds['status']) ){
			return false;
		}

		if( $creds['status'] === 'valid' && $creds['last_validated'] >  time() - (12 * 60 * 60) ){
			return true;
		}
		elseif( $cache === false && $creds['last_checked'] <  time() - (12 * 60 * 60) ){
			self::check_validity();
			return self::is_valid(true);//(true) to avoid recurring
		}

		return false;
	}

	public static function is_required_license_login(){
		if( self::is_valid() ){
			return false;
		}
		return true;
	}

	public static function check(){
		if( self::is_valid() ){
			return;
		}
		$notice_class = 'notice notice-info';
		$notice_message = '';
		$show_notice = true;

		$license_login_url = admin_url( 'admin.php?page=wc-settings&tab=wpcal_checkopt_settings&show_license_login=1' );

		$license_setup_notice = sprintf( __( 'WPCal.io - <a href="%s">Setup license now</a>.', 'wpcal' ), $license_login_url );

		$license_mismatch_notice = sprintf( __( 'WPCal.io - License mismatch. Checkout optimizations will be disabled soon. Please <a href="%s">Re-activate your license</a>.', 'wpcal' ), $license_login_url );

		$license_mismatch_features_disabled_notice = sprintf( __( 'WPCal.io - License mismatch. Checkout optimizations are disabled. Please <a href="%s">Re-activate your license</a>.', 'wpcal' ), $license_login_url );

		$expired_within_n_days_notice = sprintf( __( 'WPCal.io - License has expired. Please <a href="%s" target="_blank">Renew your license</a> now. After 15 days of expiry, Checkout optimizations will be disabled.', 'wpcal' ), WPCAL_MY_ACCOUNT_URL );

		$expired_after_n_days_features_disabled_notice = sprintf( __( 'WPCal.io - License has expired. Checkout optimizations are disabled.  Please <a href="%s" target="_blank">Renew your license</a> now.', 'wpcal' ), WPCAL_MY_ACCOUNT_URL );


		$creds = self::get_creds_info();

		if( isset($creds['validity_error']) && in_array($creds['validity_error'], array('invalid_user', 'expired', 'not_valid') ) ){
			if( $creds['validity_error'] === 'invalid_user' ){
				$notice_class = 'notice notice-error';
				$notice_message = $license_mismatch_features_disabled_notice;
				self::$enable_feature = false;
			}
			elseif( $creds['validity_error'] === 'not_valid' ){

				if( isset($creds['issue_deducted']) && is_int($creds['issue_deducted']) && $creds['issue_deducted'] > 0 && time() >= $creds['issue_deducted'] ){
					if( time() < ( $creds['issue_deducted'] + ( 86400 * 15 ) ) ){//from issue deducted time, less than 15 days
						$notice_class = 'notice notice-warning';
						$notice_message = $license_mismatch_notice;
					}
					elseif( time() > ( $creds['issue_deducted'] + ( 86400 * 15 ) ) ){//from issue deducted time, after 15 days
						$notice_class = 'notice notice-error';
						$notice_message = $license_mismatch_features_disabled_notice;
						self::$enable_feature = false;
					}
					else{
						$notice_class = 'notice notice-error';
						$notice_message = $license_mismatch_features_disabled_notice;
						self::$enable_feature = false;
					}
				}
				else{
					$notice_class = 'notice notice-error';
					$notice_message = $license_mismatch_features_disabled_notice;
					self::$enable_feature = false;
				}
			}
			elseif( $creds['validity_error'] === 'expired' ){
				
				if( isset($creds['expiry']) && is_int($creds['expiry']) && $creds['expiry'] > 0 && time() > $creds['expiry'] ){
					if( time() < ( $creds['expiry'] + ( 86400 * 2 ) ) ){//expired, less than 2 days
						$show_notice = false;
					}
					elseif( time() < ( $creds['expiry'] + ( 86400 * 15 ) ) ){//expired, after 2 days less than 15 days
						$notice_class = 'notice notice-error';
						$notice_message = $expired_within_n_days_notice; 
					}
					elseif( time() > ( $creds['expiry'] + ( 86400 * 15 ) ) ){//after 15 days
						$notice_class = 'notice notice-error';
						$notice_message = $expired_after_n_days_features_disabled_notice;
						self::$enable_feature = false;
					}else{
						$notice_class = 'notice notice-error';
						$notice_message = $expired_after_n_days_features_disabled_notice;
						self::$enable_feature = false;
					}
				}
				else{
					$notice_class = 'notice notice-error';
					$notice_message = $expired_after_n_days_features_disabled_notice;
					self::$enable_feature = false;
				}
			}
		}
		elseif( empty($creds) ){
			$notice_class = 'notice notice-info';
			$notice_message = $license_setup_notice;
			self::$enable_feature = false;
		}

		if($show_notice){
			if( empty($notice_message) ){//fall back
				$notice_class = 'notice notice-warning';
				$notice_message = $license_mismatch_notice; 
			}
			self::$show_notice = $show_notice;
			self::$notice_class = $notice_class;
			self::$notice_message = $notice_message;
		}
	}

	public static function maybe_show_license_related_notice(){
		if( !self::$show_notice || empty(self::$notice_message) ){
			return;
		}
		$is_license_login_page = false;
		if( isset($_GET['page']) && isset($_GET['tab']) && isset($_GET['show_license_login']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'wpcal_checkopt_settings' && $_GET['show_license_login'] === '1'){
			$is_license_login_page = true;
		}
		if( self::$notice_class && self::$notice_message && !$is_license_login_page ){
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( self::$notice_class ), self::$notice_message ); 
		}
	}

	public static function init(){
		// self::check();
		// add_action( 'admin_notices', __CLASS__ . '::maybe_show_license_related_notice' );
	}

	public static function is_features_ok(){
		return self::$enable_feature ? true : false;
	}
}

WPCal_License::init();