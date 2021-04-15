<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Init{

	public static function init(){//dedicated for this class
		
		include_once( WP_PLUGIN_DIR . '/' . basename(dirname(dirname(__FILE__))) . '/includes/constants.php' );

		include_once( WPCAL_PATH . '/includes/class_db_manage.php');

		register_activation_hook( 'wpcal/wpcal.php', 'WPCal_Init::on_plugin_activate');

		register_deactivation_hook( 'wpcal/wpcal.php', 'WPCal_Init::on_plugin_deactivate');

		add_action('init', 'WPCal_DB_Manage::update');
		
		if( is_admin() ){
			if( wp_doing_ajax() && isset($_POST['action']) && $_POST['action'] === 'wpcal_process_user_ajax_request' ){
				include_once( WPCAL_PATH . '/includes/class_init_user.php');
				WPCal_User_Init::ajax_only_init();
			}
			else{
				include_once( WPCAL_PATH . '/includes/class_init_admin.php');
				WPCal_Admin_Init::init();
			}
		}
		else{
			include_once( WPCAL_PATH . '/includes/class_init_user.php');
			WPCal_User_Init::init();
		}
	}

	public static function is_dev_env(){
		return defined('WPCAL_ENV') && WPCAL_ENV === 'DEV';
	}

	public static function on_plugin_activate(){
		//going for wordpress option because autoload will be optimized and it will not overide if already exists
		add_option('wpcal_first_activation_redirect', true);
		
		WPCal_DB_Manage::on_plugin_activate();
		wpcal_may_add_sample_services_on_plugin_activation();
	}

	public static function on_plugin_deactivate(){
		WPCal_Cron::on_plugin_deactivate();
	}

	public static function set_js_var_dist_url(){
		?>
<script>
var __wpcal_dist_url = "<?php echo WPCAL_PLUGIN_DIST_URL; ?>";
</script>
		<?php
	}

	public static function has_common_request_processor($action){
		static $supported_processors = [
			'get_service_details_for_booking',
			'get_current_user_details_for_booking',
			'get_initial_service_availabile_slots',
			'get_booking_by_unique_link',
			'get_service_availabile_slots_by_month',
			'add_booking',
			'reschedule_booking',
			'save_user_tz',
			'get_user_tz',
			'get_general_settings_by_options',
			'run_background_task',
			'initial_common_data',
			'run_booking_background_tasks_by_unique_link',
			'get_is_debug'
		];

		if(in_array($action, $supported_processors)){
			return true;
		}
		return false;
	}

	public static function process_single_action($action, $request_data, $initiated_for){
		if(!self::has_common_request_processor($action)){
			return false;
		}
		
		$response = [];

		if($action === 'get_service_details_for_booking'){
			
			$service_id = sanitize_text_field($request_data['service_id']);

			$service_obj = wpcal_get_service($service_id);

			$service_data = $service_obj->get_data_for_user_client();
			
			if(!empty($service_data)){
				$response['status'] = 'success';
				$response['service_data'] = $service_data;
				$response['service_admin_data'] = $service_obj->get_owner_admin_details();
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_current_user_details_for_booking'){

			$user_data = wpcal_get_current_user_for_booking_in_user_client();
			
			if(!empty($user_data)){
				$response['status'] = 'success';
				$response['user_data'] = $user_data;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_initial_service_availabile_slots'){

			$service_id = sanitize_text_field($request_data['service_id']);

			$service_obj = wpcal_get_service($service_id);

			$exclude_booking_id = null;
			if( isset($request_data['exclude_booking_unique_link']) ){
				$exclude_booking_unique_link = sanitize_text_field($request_data['exclude_booking_unique_link']);
				
				$old_booking_obj = wpcal_get_booking_by_unique_link($exclude_booking_unique_link);//throws exception on failure

				$exclude_booking_id = $old_booking_obj->get_id();
			}

			$availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

			$default_availability_obj = $availability_details_obj->get_default_availability();

			//initial load - 2 months
			//minus one day from from_date and add one day to to_date to cover all timezone
			//for intial load minus one day from from_date not necessary

			$service_min_date = $default_availability_obj->get_min_date();
			$service_max_date = $default_availability_obj->get_max_date();

			$_from_date = new DateTime('now', wp_timezone());
			$_from_date->setTime(0, 0, 0);
			if($_from_date < $service_min_date){
				//say service service_min_date in future let that be from_date
				$_from_date = clone $service_min_date;
			}

			$_to_date = clone $_from_date;
			$_to_date->modify( '+62 days' );

			list($available_from_date, $available_to_date) = WPCal_Service_Availability_Details::get_final_from_and_to_dates($_from_date, $_to_date, $service_min_date, $service_max_date);

			$from_date = clone $available_from_date;
			$to_date = clone $from_date;
			$to_date->modify( '+62 days' );
			
			$service_availability_slots_obj = new WPCal_Service_Availability_Slots($service_obj, $exclude_booking_id);
			
			$all_slots = $service_availability_slots_obj->get_slots($from_date, $to_date);
			
			if(is_array($all_slots)){
				$response['status'] = 'success';
				$response['availabile_slots_details'] = [];
				$response['availabile_slots_details']['slots'] = $all_slots;

				// $details = [];
				// $details['available_min_date'] = WPCal_DateTime_Helper::DateTime_Obj_to_Date_DB($service_min_date);
				// $details['available_max_date'] = WPCal_DateTime_Helper::DateTime_Obj_to_Date_DB($service_min_date);
				
				// $response['availabile_slots_details']['details'] = $details;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_service_availabile_slots_by_month'){

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
			$service_obj = wpcal_get_service($service_id);

			$exclude_booking_id = null;
			if( isset($request_data['exclude_booking_unique_link']) ){
				$exclude_booking_unique_link = sanitize_text_field($request_data['exclude_booking_unique_link']);

				$old_booking_obj = wpcal_get_booking_by_unique_link($exclude_booking_unique_link);//throws exception on failure
				
				$exclude_booking_id = $old_booking_obj->get_id();
			}

			//initial load - 2 months
			//minus one day from from_date and add one day to to_date to cover all timezone

			$service_availability_slots_obj = new WPCal_Service_Availability_Slots($service_obj, $exclude_booking_id);
			$from_date = new DateTime($year.'-'.$month.'-01', wp_timezone());
			$total_days_of_month = $from_date->format('t');
			$to_date = new DateTime($year.'-'.$month.'-'.$total_days_of_month, wp_timezone());

			$from_date->modify( '-1 day' );
			$to_date->modify( '+1 day' );

			$all_slots = $service_availability_slots_obj->get_slots($from_date, $to_date);
			
			if(is_array($all_slots)){
				$response['status'] = 'success';
				$response['month_availabile_slots_details'] = [];
				$response['month_availabile_slots_details']['slots'] = $all_slots;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'add_booking'){

			$booking_id = wpcal_add_booking($request_data['form']);
			
			if(!empty($booking_id)){
				$booking_obj = new WPCal_Booking($booking_id);
				$booking_data = array('unique_link' => $booking_obj->get_unique_link());

				$response['status'] = 'success';
				$response['booking_data'] = $booking_data;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'reschedule_booking'){

			$old_booking_id = null;
			$old_booking_unique_link = sanitize_text_field($request_data['old_booking_unique_link']);
			$old_booking_obj = wpcal_get_booking_by_unique_link($old_booking_unique_link);//throws exception on failure
			
			$old_booking_id = $old_booking_obj->get_id();

			if(!$old_booking_id){
				$response['status'] = 'error';
				return;
			}
			
			//$old_booking_id = sanitize_text_field($request_data['old_booking_id']);
			$new_booking_data = $request_data['form'];
	
			$booking_id = wpcal_reschedule_booking($old_booking_id, $new_booking_data);

			if(!empty($booking_id)){

				$booking_obj = wpcal_get_booking($booking_id);
				$response['status'] = 'success';
				$response['new_booking_data'] =  $booking_obj->get_data_for_admin_client();
				$response['old_booking_id'] =  $old_booking_id;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_booking_by_unique_link'){

			$unique_link = sanitize_text_field($request_data['unique_link']);
			$booking_obj = wpcal_get_booking_by_unique_link($unique_link);//throws exception on failure

			$result = $booking_obj->get_data_for_user_client();
			
			if(!empty($result)){
				$response['status'] = 'success';
				$response['booking_data'] = $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'save_user_tz'){

			$tz = sanitize_text_field($request_data['tz']);

			$result = WPCal_Manage_User_Timezone::save($tz);

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_user_tz'){

			$result = WPCal_Manage_User_Timezone::get();

			//if(!empty($result)){
				$response['status'] = 'success';
				$response['tz'] = $result;
			// }
			// else{
			// 	$response['status'] = 'error';
			// }
		}
		elseif($action === 'get_general_settings_by_options'){
			$options = $request_data['options'];
			$general_settings = WPCal_General_Settings::get_all_by_options($options);

			if(!empty($general_settings)){
				$response['status'] = 'success';
				$response['general_settings'] = $general_settings;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'run_background_task'){
			WPCal_Cron::run_api_tasks();
			$result = true;

			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'initial_common_data'){
			$result = [];
			$result['site_tz'] = wp_timezone()->getName();

			if(!empty($result)){
				$response['status'] = 'success';
				$response['data'] = $result;
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'run_booking_background_tasks_by_unique_link'){
			$result = [];
			$unique_link = sanitize_text_field($request_data['unique_link']);
			$booking_obj = wpcal_get_booking_by_unique_link($unique_link);

			$result = WPCal_Background_Tasks::run_tasks_by_main_args('booking_id', $booking_obj->get_id());

			if( !wpcal_is_time_out(10) ){
				//if not even 10 secs reached
				WPCal_Background_Tasks::run_booking_based_tasks();
			}
			
			if(!empty($result)){
				$response['status'] = 'success';
			}
			else{
				$response['status'] = 'error';
			}
		}
		elseif($action === 'get_is_debug'){
			$response['status'] = 'success';
			$response['is_debug'] = WPCAL_DEBUG;
		}


		return $response;
	}

}