<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Admin_Init{

	public static function init(){
		if( !is_admin() ){
			return;
		}

		add_action('admin_enqueue_scripts', 'WPCal_Init::set_js_var_dist_url', -1000);

		self::include_files();
		
		add_action('wp_ajax_wpcal_process_admin_ajax_request', 'WPCal_Admin_Init::process_ajax_request');

		add_action('admin_init', 'WPCal_Admin_Init::process_admin_get_request');

		add_action('admin_menu', 'WPCal_Admin_Init::init_admin_menu');

		add_action('update_option_timezone_string', 'wpcal_on_wp_setting_timezone_changes');

		add_filter( 'plugin_action_links', 'WPCal_Admin_Init::add_plugin_action_links', 10, 4 );

		self::enqueue_styles_and_scripts();
		
		if (get_option('wpcal_first_activation_redirect')) {
			add_action('admin_init', __CLASS__ . '::on_activate_redirect');
		}

		if(isset($_GET['page']) && $_GET['page'] === 'wpcal_admin' ){
			add_filter( 'admin_body_class', function($classes_str){
				$classes_str .= " wpcal-admin-body ";
				return $classes_str;

			} );
		}

	}

	protected static function include_files(){

		//libraries
		include_once( WPCAL_PATH . '/lib/Valitron/Validator.php');

		include_once( WPCAL_PATH . '/includes/class_general_settings.php');

		//functions
		include_once( WPCAL_PATH . '/includes/common_func.php');
		include_once( WPCAL_PATH . '/includes/app_func.php');

		//classes
		include_once( WPCAL_PATH . '/includes/class_license_auth.php');
		include_once( WPCAL_PATH . '/includes/class_date_time_helper.php');
		include_once( WPCAL_PATH . '/includes/class_service.php');
		include_once( WPCAL_PATH . '/includes/class_availability_date.php');
		include_once( WPCAL_PATH . '/includes/class_service_availability_details.php');
		include_once( WPCAL_PATH . '/includes/class_service_availability_slots.php');
		include_once( WPCAL_PATH . '/includes/class_booking.php');
		include_once( WPCAL_PATH . '/includes/class_bookings_query.php');
		include_once( WPCAL_PATH . '/includes/class_admin_data_format.php');

		include_once( WPCAL_PATH . '/includes/tp_calendars/class_tp_calendars_add_event.php');
		include_once( WPCAL_PATH . '/includes/tp/class_tp_resource.php');

		include_once( WPCAL_PATH . '/includes/class_background_tasks.php');
		include_once( WPCAL_PATH . '/includes/class_cron.php');
		include_once( WPCAL_PATH . '/includes/class_mail.php');
	}

	private static function enqueue_styles_and_scripts(){
		add_action('admin_enqueue_scripts', 'WPCal_Admin_Init::enqueue_styles', 10);
		add_action('admin_enqueue_scripts', 'WPCal_Admin_Init::enqueue_scripts', 10);
	}

	public static function enqueue_styles() {

		if ( WPCal_Init::is_dev_env() ) {
			//wp_enqueue_style( 'wpcal_admin_css', WPCAL_PLUGIN_DIST_URL . 'css/admin.css', [], WPCAL_VERSION, false );
		} else {
			wp_enqueue_style( 'wpcal_admin_chunk_css', WPCAL_PLUGIN_DIST_URL . 'css/chunk-vendors.css', [], WPCAL_VERSION, false );
			wp_enqueue_style( 'wpcal_admin_chunk_common_css', WPCAL_PLUGIN_DIST_URL . 'css/chunk-common.css', [], WPCAL_VERSION, false );
			wp_enqueue_style( 'wpcal_admin_css', WPCAL_PLUGIN_DIST_URL . 'css/admin.css', [], WPCAL_VERSION, false );
		}
	}

	public static function enqueue_scripts() {
	
		if(isset($_GET['page']) && $_GET['page'] === 'wpcal_admin' ){
		// if ( WPCal_Init::is_dev_env() ) {
		// 	wp_enqueue_script( 'wpcal_admin_chunk', WPCAL_PLUGIN_DIST_URL . 'js/chunk-vendors.js', [], WPCAL_VERSION, false );
		// 	wp_enqueue_script( 'wpcal_admin_app', WPCAL_PLUGIN_DIST_URL . 'js/admin.js', [], WPCAL_VERSION, false );
		// } else {
			wp_enqueue_script( 'wpcal_admin_chunk', WPCAL_PLUGIN_DIST_URL . 'js/chunk-vendors.js', [], WPCAL_VERSION, false );
			//if ( WPCal_Init::is_dev_env() ) {
				wp_enqueue_script( 'wpcal_admin_chunk_common', WPCAL_PLUGIN_DIST_URL . 'js/chunk-common.js', [], WPCAL_VERSION, false );
			//}
			wp_enqueue_script( 'wpcal_admin_app', WPCAL_PLUGIN_DIST_URL . 'js/admin.js', [], WPCAL_VERSION, false );
		//}
		wp_localize_script('wpcal_admin_app', 'wpcal_ajax', array('ajax_url' => admin_url('admin-ajax.php'), 'admin_url' => admin_url(), 'is_debug' => WPCAL_DEBUG));
		}
	}

	public static function init_admin_menu() {

		add_menu_page($page_title = 'WPCal.io', $menu_title = 'WPCal.io', $capability = 'activate_plugins', $menu_slug = 'wpcal_admin', $function = 'wpcal_admin_page', $icon_url = 'dashicons-calendar-alt', $position = 61);
	
		//add_submenu_page(	$parent_slug = 'wpcal_admin', $page_title = 'WPCal Settings', $menu_title = 'Settings', $capability = 'activate_plugins',  $menu_slug = 'wpcal_admin_settings', $function = 'wpcal_admin_settings_page');

		global $submenu;
		if( current_user_can( 'activate_plugins' ) ){
			$submenu['wpcal_admin'] = !isset($submenu['wpcal_admin']) ? [] : $submenu['wpcal_admin'];
			$submenu['wpcal_admin'][] = array( 'My Bookings', 'manage_options', 'admin.php?page=wpcal_admin#/bookings' );

			$submenu['wpcal_admin'][] = array( 'Event Types', 'manage_options', 'admin.php?page=wpcal_admin#/event-types' );

			$submenu['wpcal_admin'][] = array( 'Settings', 'manage_options', 'admin.php?page=wpcal_admin#/settings' );
		}

		if(defined('WPCAL_DEBUG') && WPCAL_DEBUG){
			add_submenu_page(	$parent_slug = 'wpcal_admin', $page_title = 'WPCal Test', $menu_title = 'Test(Debug only)', $capability = 'activate_plugins',  $menu_slug = 'wpcal_admin_test', $function = 'wpcal_admin_test_page');
		}	
	}

	public static function on_activate_redirect() {

		if(get_option('wpcal_first_activation_redirect')) {
			update_option('wpcal_first_activation_redirect', false);//don't change to delete_option, as we are using add_option it will add only if slug not exisits that maintain 1 time use
	
			//in rare case lets redirect to respective dev and prod page
			if(!isset($_GET['activate-multi'])){
				wp_redirect(admin_url( 'admin.php?page=wpcal_admin#/bookings' ));
				exit();
			}
		}
	}

	public static function add_plugin_action_links($actions, $plugin_file, $plugin_data, $context){

		static $plugin;

		if(!$plugin){
			$plugin = plugin_basename(WPCAL_PATH.'/wpcal.php' );
		}

		if ($plugin != $plugin_file) {
			return $actions;
		}

		$support_link = array('support' => '<a href="mailto:support@wpcal.io?body=WPCal Plugin v'.WPCAL_VERSION.'" target="_blank">Get support</a>');

		$actions = array_merge($support_link, $actions);

		return $actions;

	}

	public static function process_ajax_request() {
		$response = [];
        if (!isset($_POST['wpcal_request']) || !is_array($_POST['wpcal_request']) ) {
            echo wpcal_prepare_response($response);
            exit();
		}

		if( !current_user_can( 'activate_plugins' ) ){//admin ajax call check
			echo wpcal_prepare_response($response);
            exit();
		}

		$wpcal_request_result = [];
		foreach($_POST['wpcal_request'] as $action => $action_request_data){
			try{
				if(WPCal_Init::has_common_request_processor($action)){
					$wpcal_request_result[$action] = WPCal_Init::process_single_action($action, $action_request_data, 'admin_end');
				}
				else{
					$wpcal_request_result[$action] = self::process_single_action($action, $action_request_data);
				}
			}
			catch(WPCal_Exception $e){
				$single_action_result = wpcal_prepare_single_action_exception_result($e);
				$wpcal_request_result[$action] = $single_action_result;
			}
			catch(Exception $e){
				$single_action_result = [
					'status' => 'error',
					'error' => 'unknow_error',
					'error_msg' => $e->getMessage(),
					'error_data' => [(string) $e],
				];
				$wpcal_request_result[$action] = $single_action_result;
			}
		}

        if (!empty($wpcal_request_result)) {
			$junk = ob_get_clean();
			$wpcal_request_result['junk'] = $junk;
            echo wpcal_prepare_response($wpcal_request_result);
			exit();
		}
	}

	private static function process_single_action($action, $request_data){
		$response = [];

		if($action === 'edit_service'){
			
			$service_id = sanitize_text_field($request_data['service_id']);

			wpcal_is_current_admin_owns_resource('service', $service_id);

			$service_obj = wpcal_get_service($service_id);

			$service_data = $service_obj->get_data_for_admin_client();

			//get and set 'default_availability_details' 
			$service_data['default_availability_details'] = wpcal_get_default_availability_details_for_admin_client($service_obj);
			
			if(!empty($service_data)){
				$response['status'] = 'success';
				$response['service_data'] = $service_data;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'update_service'){
			
			
			$service_id = sanitize_text_field($request_data['service_id']);

			wpcal_is_current_admin_owns_resource('service', $service_id);

			$data = $request_data['service_data'];

			$service_obj = wpcal_update_service($data, $service_id);

			$is_proper_obj = $service_obj instanceof WPCal_Service;

			$result = false;
			if( $is_proper_obj && $service_obj->get_id()){
				$result = true;
			}
			
			if($result){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}

		}
		elseif($action === 'add_service'){
			
			
			$_service_id = sanitize_text_field($request_data['service_id']);
			if( is_numeric($_service_id) ){//checking this because update service will be sending service_id both via same function in js
				throw new WPCal_Exception('invalid_input');
			}

			$data = $request_data['service_data'];

			$service_obj = wpcal_add_service($data);

			$is_proper_obj = $service_obj instanceof WPCal_Service;

			$result = false;

			$service_id = $service_obj->get_id();
			if( $is_proper_obj && $service_id){
				$result = true;
			}

			if($is_proper_obj && $service_id){
				$response['status'] = 'success';
				$response['service_id'] = $service_id;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'edit_service_availability'){
			
			
			$service_id = sanitize_text_field($request_data['service_id']);

			wpcal_is_current_admin_owns_resource('service', $service_id);

			$service_obj = wpcal_get_service($service_id);

			$service_availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

			$default_availability_obj = $service_availability_details_obj->get_default_availability();

			$service_min_date = $default_availability_obj->get_min_date();
			$service_max_date = $default_availability_obj->get_max_date();

			$_from_date = new DateTime('now', wp_timezone());
			$_from_date->setTime(0, 0, 0);
			if($_from_date > $service_max_date){
				$response['status'] = 'error';
				$response['error'] = 'expired';
				return $response;
			}
			elseif($_from_date < $service_min_date){
				//say service service_min_date in future let that be from_date
				$_from_date = clone $service_min_date;
			}
			$year = $_from_date->format('Y');
			$month = $_from_date->format('m');

			$from_date = new DateTime($year.'-'.$month.'-01', wp_timezone());
			$total_days_of_month = $from_date->format('t');
			$to_date = new DateTime($year.'-'.$month.'-'.$total_days_of_month, wp_timezone());

			$service_availability_data = $service_availability_details_obj->get_availability_by_date_range_for_admin_client($from_date, $to_date);

			//format data for calendar
			$service_availability_data = WPCal_Admin_Data_Format::format_service_availability_for_cal($service_availability_data);

			if(!empty($service_availability_data)){
				$response['status'] = 'success';
				$response['service_availability_data'] = $service_availability_data;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'edit_service_availability_by_month'){
			
			$month = $year = '';
			if(isset($request_data['current_month_view']['month'])){
				$month = sanitize_text_field($request_data['current_month_view']['month']);
			}
			if(isset($request_data['current_month_view']['year'])){
				$year = sanitize_text_field($request_data['current_month_view']['year']);
			}

			if(!$month || !$year){
				$response['status'] = 'error';
				return $response;
			}

			$service_id = sanitize_text_field($request_data['service_id']);

			wpcal_is_current_admin_owns_resource('service', $service_id);

			$service_obj = wpcal_get_service($service_id);

			$from_date = new DateTime($year.'-'.$month.'-01', wp_timezone());
			$total_days_of_month = $from_date->format('t');
			$to_date = new DateTime($year.'-'.$month.'-'.$total_days_of_month, wp_timezone());

			$service_availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

			$service_availability_data = $service_availability_details_obj->get_availability_by_date_range_for_admin_client($from_date, $to_date);

			//format data for calendar
			$service_availability_data = WPCal_Admin_Data_Format::format_service_availability_for_cal($service_availability_data);

			if(!empty($service_availability_data)){
				$response['status'] = 'success';
				$response['service_availability_data'] = $service_availability_data;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'customize_service_availability'){
			
			
			$service_id = sanitize_text_field($request_data['service_id']);

			wpcal_is_current_admin_owns_resource('service', $service_id);

			$data = $request_data['request_data'];

			$result = wpcal_customize_availability_dates($service_id, $data);

			if($result){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		// elseif($action === 'get_periods_for_prefill_for_marking_available'){
			
			
		// 	$service_id = sanitize_text_field($request_data['service_id']);

		// 	$date = sanitize_text_field($request_data['date']);			

		// 	$result = wpcal_get_periods_for_prefill_for_marking_available_by_date($service_id, $date);		

		// 	if($result){
		// 		$response['status'] = 'success';
		// 		$response['availability_data'] =  $result;
		// 	}
		// 	else{
		// 		$response['status'] = 'error';
		// 	}
		// }
		elseif($action === 'get_upcoming_bookings'){

			$allowed_keys = [
				'page',
				'view_base_timing',
			];

			$options = wpcal_get_allowed_fields($request_data['options'], $allowed_keys);

			$options = wpcal_sanitize_all($options);

			$result = 			WPCal_Bookings_Query::get_upcoming_bookings_for_admin_client($options);

			if($result){
				$response['status'] = 'success';
				$response['bookings_data'] =  $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_past_bookings'){

			$allowed_keys = [
				'page',
				'view_base_timing',
			];

			$options = wpcal_get_allowed_fields($request_data['options'], $allowed_keys);

			$options = wpcal_sanitize_all($options);

			$result = 			WPCal_Bookings_Query::get_past_bookings_for_admin_client($options);

			if($result){
				$response['status'] = 'success';
				$response['bookings_data'] =  $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'cancel_booking'){
			$booking_id = sanitize_text_field($request_data['booking_id']);

			wpcal_is_current_admin_owns_resource('booking', $booking_id);

			$result = wpcal_cancel_booking($booking_id);
			if($result){
				$response['status'] = 'success';
				$response['bookings_data'] =  $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_services_of_current_admin'){

			$service_list = wpcal_get_services_of_current_admin();

			// if(!empty($booking_id)){

				$response['status'] = 'success';
				$response['service_list'] =  $service_list;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'update_service_status_of_current_admin'){

			$status = sanitize_text_field($request_data['status']);
			$service_id = sanitize_text_field($request_data['service_id']);

			wpcal_is_current_admin_owns_resource('service', $service_id);

			//NEED to validate for service belogs to this admin

			if($status == -2){
				$result = wpcal_delete_service($service_id);
			}
			else{
				$result = wpcal_update_service_status($status, $service_id);
			}

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_calendar_accounts_details_of_current_admin'){

			$calendar_accounts_details = wpcal_get_calendar_accounts_details_of_current_admin();

			// if(!empty($booking_id)){

				$response['status'] = 'success';
				$response['calendar_accounts_details'] =  $calendar_accounts_details;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'get_add_bookings_to_calendar_of_current_admin'){

			$add_bookings_to_calendar = wpcal_get_add_bookings_to_calendar_of_current_admin();

			// if(!empty($booking_id)){

				$response['status'] = 'success';
				$response['add_bookings_to_calendar'] =  $add_bookings_to_calendar;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'get_conflict_calendar_ids_of_current_admin'){

			$conflict_calendar_ids = wpcal_get_conflict_calendar_ids_of_current_admin();

			// if(!empty($booking_id)){

				$response['status'] = 'success';
				$response['conflict_calendar_ids'] =  $conflict_calendar_ids;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'update_add_bookings_to_calendar_id_for_current_admin'){

			$add_bookings_to_calendar_id = sanitize_text_field($request_data['add_bookings_to_calendar_id']);

			$calendar_account_ids = wpcal_get_unique_calendar_account_ids_by_calendar_ids([$add_bookings_to_calendar_id]);
			foreach($calendar_account_ids as $calendar_account_id){
				wpcal_is_current_admin_owns_resource('calendar_account', $calendar_account_id);
			}

			$is_updated = wpcal_update_add_bookings_to_calendar_id_for_current_admin($add_bookings_to_calendar_id);

			if(!empty($is_updated)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'update_conflict_calendar_ids_for_current_admin'){

			if( !isset($request_data['conflict_calendar_ids']) ){
				$request_data['conflict_calendar_ids'] = [];
			}
			$conflict_calendar_ids = 
			array_map('sanitize_text_field', $request_data['conflict_calendar_ids']);

			$calendar_account_ids = wpcal_get_unique_calendar_account_ids_by_calendar_ids($conflict_calendar_ids);
			foreach($calendar_account_ids as $calendar_account_id){
				wpcal_is_current_admin_owns_resource('calendar_account', $calendar_account_id);
			}

			$conflict_calendar_ids_length = 
			sanitize_text_field($request_data['conflict_calendar_ids_length']);

			$is_updated = wpcal_update_conflict_calendar_ids_for_current_admin($conflict_calendar_ids, $conflict_calendar_ids_length);

			if(!empty($is_updated)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'disconnect_calendar_by_id_for_current_admin'){

			$calendar_account_id = 
			sanitize_text_field($request_data['calendar_account_id']);
			$provider = 
			sanitize_text_field($request_data['provider']);

			wpcal_is_current_admin_owns_resource('calendar_account', $calendar_account_id);

			//to do verify admin have right on this 

			$result = wpcal_disconnect_calendar_by_id($calendar_account_id, $provider);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		// elseif($action === 'get_general_setting'){

		// 	$option = sanitize_text_field($request_data['general_setting']);
		// 	$general_setting = WPCal_General_Settings::get($option);

		// 	//if(!empty($general_settings)){
		// 		$response['status'] = 'success';
		// 		$response['general_setting'] = $general_setting;
		// 	// }
		// 	// else{
		// 	// 	$response['status'] = 'error';
		// 	// }
		// }
		elseif($action === 'get_general_settings'){

			$general_settings = WPCal_General_Settings::get_all();

			if(!empty($general_settings)){
				$response['status'] = 'success';
				$response['general_settings'] = $general_settings;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'update_general_settings'){

			$general_settings = $request_data['general_settings'];

			$result = WPCal_General_Settings::update_all($general_settings);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'license_auth_login'){

			$result = WPCal_License::login($request_data);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'license_signup'){

			$result = WPCal_License::signup($request_data);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'license_status'){

			$result = WPCal_License::get_account_info();

			if(isset($result)){
				$response['status'] = 'success';
				$response['license_info'] = $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_wpcal_admin_users_details'){

			$result = wpcal_get_wpcal_admin_users_details_for_admin_client();

			//if(isset($result)){
				$response['status'] = 'success';
				$response['admin_users_details'] = $result;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'initial_admin_data'){

			$result = [];
			$result['current_admin_details'] = wpcal_get_admin_details_of_current_admin();
			$result['current_admin_notices'] = wpcal_get_notices_for_current_admin();
			$result['wpcal_site_urls'] = [
				'lost_pass_url' => WPCAL_SITE_LOST_PASS_URL
			];


			if(!empty($result)){
				$response['status'] = 'success';
				$response['data'] = $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'update_admin_notices'){

			$result = wpcal_update_notices_for_current_admin($request_data);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_admin_notices'){

			$current_admin_notices = wpcal_get_notices_for_current_admin();

			//if(!empty($result)){
				$response['status'] = 'success';
				$response['current_admin_notices'] = $current_admin_notices;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'get_tp_accounts_of_current_admin'){

			$tp_accounts = wpcal_get_tp_accounts_of_current_admin();

			//if(!empty($result)){
				$response['status'] = 'success';
				$response['tp_accounts'] = $tp_accounts;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'disconnect_tp_account_by_id_for_current_admin'){

			$tp_account_id = 
			sanitize_text_field($request_data['tp_account_id']);
			$provider = 
			sanitize_text_field($request_data['provider']);
			$_force = 
			sanitize_text_field($request_data['force']);
			$force = $_force === 'force_remove' ? true : false;

			wpcal_is_current_admin_owns_resource('tp_account', $tp_account_id);

			//to do verify admin have right on this 

			$result = wpcal_disconnect_tp_account_by_id($tp_account_id, $provider, $force);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_tp_locations_details_for_current_admin'){

			$response['status'] = 'success';
			$response['tp_locations'] = wpcal_get_tp_locations_for_current_admin();
		}
		elseif($action === 'check_auth_if_fails_remove_tp_accounts_for_current_admin'){

			$response['status'] = 'success';
			$response['check_auth_tp_accounts'] = wpcal_check_auth_if_fails_remove_tp_accounts_for_current_admin();
		}
	
		return $response;
	}

	public static function process_admin_get_request(){
		//IMPROVE code using nonce
	
		if( !isset($_GET['wpcal_action']) || !isset($_GET['page']) || $_GET['page'] != 'wpcal_admin' ){
			return;
		}
	
		if( $_GET['wpcal_action'] === 'add_calendar_account' && isset($_GET['provider']) && !empty($_GET['provider']) ){
			wpcal_add_calendar_account_redirect($_GET['provider']);
		}
		elseif( $_GET['wpcal_action'] === 'google_calendar_receive_token' ){
			wpcal_google_calendar_receive_token_and_add_account();
		}
		elseif( $_GET['wpcal_action'] === 'add_tp_account' && isset($_GET['provider']) && !empty($_GET['provider']) ){
			wpcal_add_tp_account_redirect($_GET['provider']);
		}
		elseif( $_GET['wpcal_action'] === 'tp_account_receive_token' && isset($_GET['provider']) && !empty($_GET['provider']) ){
			wpcal_tp_account_receive_token_and_add_account($_GET['provider']);
		}
	}

}
