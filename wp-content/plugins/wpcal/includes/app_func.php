<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

function wpcal_add_service($data){
	return wpcal_update_service($data);
}

function wpcal_update_service($update_data, $service_id = 0){
	global $wpdb;

	if($service_id){
		$update = true;
	}
	else{
		$update = false;
	}

	$allowed_keys = [
		'name',
		'status',
		'locations',
		'descr',
		//'post_id',
		'color',
		'relationship_type',
		'duration',
		'display_start_time_every',
		'max_booking_per_day',
		'min_schedule_notice',
		'event_buffer_before',
		'event_buffer_after',
		'invitee_questions',
		'default_availability_details'
	];

	$default_data = [
		'status' => 1,
		'max_booking_per_day' => NULL,
		'min_schedule_notice' => ["type" => "units", "time_units" => 4, "time_units_in" => "hrs", "days_before_time" => "23:59:59", "days_before" => 1],
		'event_buffer_before' => 0,
		'event_buffer_after' => 0,
	];

	$data = array_merge($default_data, $update_data);

	$_fix_default = ['max_booking_per_day',
	'min_schedule_notice',
	'event_buffer_before',
	'event_buffer_after'];

	foreach($_fix_default as $_fix_key){
		if( $data[$_fix_key] == '' ){
			$data[$_fix_key] = $default_data[$_fix_key];
		}
	}

	$data = wpcal_get_allowed_fields($data, $allowed_keys);

	if( isset($data['locations']) ){
		$data['locations'] = wpcal_service_locations_get_allowed_fields($data['locations']);
	}

	$sanitize_rules = [
		'descr' => 'sanitize_textarea_field',
		'locations' => [
			'*' => [
				'form' => ['location_extra']
			]
		]
	];

	$data = wpcal_sanitize_all($data, $sanitize_rules);

	if(!$update){
		$data['status'] = 1;
	}

	$validate_obj = new WPCal_Validate($data);
	$validate_obj->rules([
		'required' => [
			'name',
			'status',
			'relationship_type',
			'color',
			'duration',
			'display_start_time_every',
			//'max_booking_per_day',
			'min_schedule_notice.type',
			'event_buffer_before',
			'event_buffer_after',
			'default_availability_details.date_range_type'
		],
		'requiredWithIf' => [
			['default_availability_details.from_date', ['default_availability_details.date_range_type' => 'from_to']],
			['default_availability_details.to_date', ['default_availability_details.date_range_type' => 'from_to']],
			['default_availability_details.date_misc', ['default_availability_details.date_range_type' => 'relative']],
			['min_schedule_notice.time_units', ['min_schedule_notice.type' => 'units']],
			['min_schedule_notice.time_units_in', ['min_schedule_notice.type' => 'units']],
			['min_schedule_notice.days_before_time', ['min_schedule_notice.type' => 'time_days_before']],
			['min_schedule_notice.days_before', ['min_schedule_notice.type' => 'time_days_before']]//Is it working???
		],
		'integer' => [
			'duration',
			'display_start_time_every',
			'max_booking_per_day',
			'event_buffer_before',
			'event_buffer_after'
		],
		'lengthMin' => [
			['invitee_questions.questions.*.question', 1]
		],
		// 'lengthMax' => [
		// 	['locations.*.form.location', 500],
		// 	['locations.*.form.location_extra', 500]
		// ],
		'min' => [
			['status', -10],
			['duration', 1],
			['display_start_time_every', 1],
			['invitee_questions.questions.*.is_enabled', 0],
			['invitee_questions.questions.*.is_required', 0],
			['min_schedule_notice.time_units', 0],
			['min_schedule_notice.days_before', 0]
		],
		'max' => [
			['status', 10],
			['duration', 1440],
			['display_start_time_every', 1440],
			['invitee_questions.questions.*.is_enabled', 1],
			['invitee_questions.questions.*.is_required', 1],
			['min_schedule_notice.days_before', 7]
		],
		'in' =>[
			['relationship_type', ['1to1','1ton']],
			['invitee_questions.questions.*.answer_type' => ['textarea']],
			['min_schedule_notice.type' => ['none', 'units', 'time_days_before']],
			['min_schedule_notice.time_units_in' => ['mins', 'hrs', 'days']],
			// ['locations.*.type', ['physical', 'phone', 'googlemeet_meeting', 'zoom_meeting', 'gotomeeting_meeting', 'custom', 'ask_invitee']],
			// ['locations.*.form.who_calls', ['admin', 'invitee']]
		],
		'arrayHasKeys' => [
			['invitee_questions' => ['questions']],
			['min_schedule_notice' => ['type', 'time_units', 'time_units_in', 'days_before_time', 'days_before']],
		],
		'dateFormat' => [
			['default_availability_details.from_date', 'Y-m-d'],
			['default_availability_details.to_date', 'Y-m-d'],
			['default_availability_details.periods.*.from_time', 'H:i:s'],
			['default_availability_details.periods.*.to_time', 'H:i:s'],
			['min_schedule_notice.days_before_time', 'H:i:s'],
		],
		'toDateAfterFromDate' => [
			['default_availability_details.from_date', 'default_availability_details.to_date']
		],
		'periodsToTimeAfterFromTime' => [
			['default_availability_details.periods']
		],
		'periodsCheckCollide' => [
			['default_availability_details.periods']
		],
		'checkDateMisc' => [
			['default_availability_details.date_misc']
		]
	]);

	unset($data['default_availability_details']);//currently just to pass validation so that after service table insertion/updatation it should not show error

	$validation_errors = [];
	if( !$validate_obj->validate() ){
		$validation_errors = $validate_obj->errors();
	}

	if( isset($data['locations']) ){
		$locations_validation_result = wpcal_validate_service_locations($data['locations']);
		if( is_array($locations_validation_result) ){
			$validation_errors = array_merge($validation_errors, $locations_validation_result);
		}
	}

	if( !empty( $validation_errors ) ){
		throw new WPCal_Exception('validation_errors', '', $validation_errors);
	}

	$data['updated_ts'] = time();
	if(!$update){
		$data['status'] = 1;
		$data['added_ts'] = time();
	}

	if( isset($data['invitee_questions']) ){
		unset($data['invitee_questions']['__questions_count']);//workaround to post this array if 'questions' array is empty, currently that is only key
		$data['invitee_questions'] = json_encode($data['invitee_questions']);
	}

	if( !empty($data['min_schedule_notice']) ){
		$data['min_schedule_notice'] = json_encode($data['min_schedule_notice']);
	}

	if( empty($data['locations']) || !is_array($data['locations']) ){
		$data['locations'] = [];
	}
	$data['locations'] = array_values($data['locations']);//to reset keys
	foreach($data['locations'] as $location_key => $location){
		if( isset($data['locations'][$location_key]['form']) ){
			if( !empty($location['type']) && $location['type'] == 'phone' && !empty($location['form']['who_calls']) && $location['form']['who_calls'] == 'admin' ){
				$data['locations'][$location_key]['form']['location'] = '';
			}
		}
	}
	$data['locations'] = json_encode($data['locations']);

	if( empty($data['max_booking_per_day']) ){
		$data['max_booking_per_day'] = NULL;
	}

	$table_service = $wpdb->prefix . 'wpcal_services';

	if($update){
		$result = $wpdb->update($table_service, $data, array('id' => $service_id));
	}
	else{
		$result = $wpdb->insert($table_service, $data);
		if( $result !== false ){
			$service_id = $wpdb->insert_id;
			if( !$service_id ){
				throw new WPCal_Exception('db_error_insert_id_missing');
			}
		}
	}

	if($result !== false){
		$service_obj = new WPCal_Service($service_id);
	}
	else{
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}

	$admin_user_id = get_current_user_id();
	wpcal_connect_service_admin_user($service_id, $admin_user_id);

	wpcal_add_or_update_default_availability_details($service_obj, $update_data['default_availability_details'], $update);
	
	wpcal_service_may_add_page($service_obj);

	wpcal_service_availability_slots_mark_refresh_cache($service_id);
	
	return $service_obj;

}

function wpcal_service_locations_get_allowed_fields($locations){
	if( !is_array($locations) ){
		return $locations;
	}
	$locations = array_values($locations);//resetting any keys here
	foreach($locations as $keys => $location){
		$locations[$keys] = wpcal_service_location_get_allowed_fields($location);
	}
	return $locations;
}

function wpcal_service_location_get_allowed_fields($location){
	$allowed_keys = [
		'type',
		'form'
	];
	$location = wpcal_get_allowed_fields($location, $allowed_keys);

	$form_allowed_keys = [
		'location',
		'location_extra',
	];
	$form_allowed_keys_w_phone = [
		'location',
		'location_extra',
		'who_calls'
	];

	$form_allowed_keys_final = $location['type'] === 'phone' ? $form_allowed_keys_w_phone : $form_allowed_keys;

	if( !empty($location['form']) && is_array($location['form']) ){
		$location['form'] = wpcal_get_allowed_fields($location['form'], $form_allowed_keys_final);
	}
	return $location;
}

function wpcal_validate_service_locations($locations){
	if( empty($locations) || !is_array($locations) ){
		return true;//will reset before saving
	}

	$errors = [];

	if( count($locations) > 10 ){
		$errors['locations'][] = 'Max 10 locations only allowed';
	}

	$location_type_count = [];
	$allowed_location_types = ['physical', 'phone', 'googlemeet_meeting', 'zoom_meeting', 'gotomeeting_meeting', 'custom', 'ask_invitee'];

	$types_more_than_one_allowed = ['physical', 'custom'];

	foreach($locations as $key => $location){
		!isset($location_type_count[$location['type']]) ? $location_type_count[$location['type']] = 0 : ''; 
		$location_type_count[$location['type']]++;
		$validation_result = wpcal_validate_service_location($location);
		if($validation_result === true){
			continue;
		}
		elseif( is_array($validation_result) ){
			foreach($validation_result as $error_key => $error_details){
				!isset($errors['locations.'.$key.'.'.$error_key]) ? $errors['locations.'.$key.'.'.$error_key] = [] : '';
				foreach($error_details as $_error_index => $_error){
					$errors['locations.'.$key.'.'.$error_key][$_error_index] = 'Location.'.$key.'.'.$_error;
				}
			}
		}
	}
	foreach($location_type_count as $location_type => $count){
		if( !in_array($location_type, $allowed_location_types) ){
			continue;//this error will handled by wpcal_validate_service_location()
		}
		if( $count > 1 && !in_array($location_type, $types_more_than_one_allowed) ){
			$errors['locations'][] = 'Location type '.$location_type.' allowed only once';
		}
	}

	return empty($errors) ? true : $errors;
}

function wpcal_validate_service_location($location){

	$validate_obj = new WPCal_Validate($location);
	$validation_rules = [
		'required' => [
			'type',
		],
		'lengthMax' => [
			['form.location', 500],
			['form.location_extra', 500]
		],
		'in' =>[
			['type', ['physical', 'phone', 'googlemeet_meeting', 'zoom_meeting', 'gotomeeting_meeting', 'custom', 'ask_invitee']],
			['form.who_calls', ['admin', 'invitee']]
		],
	];
	if( !empty($location['type']) && in_array($location['type'],  ['physical', 'phone', 'custom', 'ask_invitee']) ){
		$validation_rules['required'][] = 'form';
		if( $location['type'] === 'phone' ){
			if( 
				!empty($location['type']['form']) &&
				!empty($location['type']['form']['who_calls']) &&
				$location['type']['form']['who_calls'] === 'invitee' 
			){
				$validation_rules['required'][] = 'form.location';
			}
		}
		elseif( in_array($location['type'],  ['physical', 'custom']) ){
			$validation_rules['required'][] = 'form.location';
		}
	}
	$validate_obj->rules($validation_rules);

	if( !$validate_obj->validate() ){
		$validation_errors = $validate_obj->errors();
		return $validation_errors;
	}
	return true;
}

function wpcal_service_may_add_page($service_obj){

	$post_id = $service_obj->get_post_id();
	if(is_numeric($post_id)){
		//already post created
		return;
	}

	$service_name = $service_obj->get_name();
	$service_id = $service_obj->get_id();

	if(!$service_id || empty($service_name)){
		throw new WPCal_Exception('invalid_input_service_page');
	}

	$add_page_data = [
		'post_author' => 1,
		'post_title' => $service_name,
		'post_name' => $service_name, //slug WP take care converting normal text into unique slug
		'post_status' => 'publish',
		'post_content' => '[wpcal id='.$service_id.']',
		'post_type' => 'page',
		'post_parent' => 0,
		'comment_status' => 'close',
		'ping_status' => 'close',
	];

	add_filter('option_nav_menu_options', 'wpcal_temperoraily_disable_auto_adding_new_page_to_menu', 10, 2);

	$page_id = wp_insert_post($add_page_data);

	remove_filter('option_nav_menu_options', 'wpcal_temperoraily_disable_auto_adding_new_page_to_menu', 10);

	if( !is_numeric($page_id) || !$page_id || is_wp_error($post_id) ){//on fail 0 or WP_Error
		$_error_msg = is_wp_error($post_id) ? $post_id->get_error_message() : '';
		throw new WPCal_Exception('service_page_insert_error', '', $_error_msg);
	}

	global $wpdb;
	$table_service = $wpdb->prefix . 'wpcal_services';
	$result = $wpdb->update($table_service, array('post_id' => $page_id), array('id' => $service_id));
	return $result !== false;
}

function wpcal_temperoraily_disable_auto_adding_new_page_to_menu($value, $option){
	$menu = $value;
	if ( empty( $menu ) || ! is_array( $menu ) || ! isset( $menu['auto_add'] ) ) {
		//already no auto add
		return $value;
	}
	$auto_add = $menu['auto_add'];
	if ( empty( $auto_add ) || ! is_array( $auto_add ) ) {
		//already no auto add
		return $value;
	}

	$menu['auto_add'] = '';
	return $menu;
}

function wpcal_connect_service_admin_user($service_id, $admin_user_id){
	global $wpdb;

	$table_service_admins = $wpdb->prefix . 'wpcal_service_admins';

	$query = "SELECT `id` FROM `$table_service_admins` WHERE `service_id` = '".$service_id."'";
	$is_admin_user_already_assigned = $wpdb->get_var($query);
	if($is_admin_user_already_assigned){
		return;
	}

	$insert_row = ['service_id' => $service_id, 'admin_user_id' => $admin_user_id];
	return $wpdb->insert($table_service_admins, $insert_row);
}

function wpcal_add_or_update_default_availability_details($service_obj, $update_data, $is_update){
	global $wpdb;

	//validation already done in wpcal_update_service()

	//basic check
	if( empty($update_data) || !isset($update_data['date_range_type']) ){
		throw new WPCal_Exception('invalid_input_default_availability');
	}

	$allowed_keys = [
		'day_index_list',
		'date_range_type',
		'from_date',
		'to_date',
		'date_misc',
		// 'type',
		// 'is_available',
		// 'added_ts',
		// 'updated_ts',
	];

	$data = wpcal_get_allowed_fields($update_data, $allowed_keys);

	$data['updated_ts'] = time();

	if( !$is_update && empty($data['day_index_list']) ){
		$data['day_index_list'] = WPCal_General_Settings::get('working_days');
	}

	if(!empty($data['day_index_list']) && is_array($data['day_index_list'])){
		$data['day_index_list'] = implode(',', $data['day_index_list']);
	}

	if( $data['date_range_type'] === 'from_to' ){
		$data['date_misc'] = NULL;
	}
	elseif( $data['date_range_type'] === 'relative' ){
		$data['from_date'] = NULL;
		$data['to_date'] = NULL;
	}
	elseif( $data['date_range_type'] === 'infinite' ){
		$data['date_misc'] = NULL;
		$data['from_date'] = NULL;
		$data['to_date'] = NULL;
	}

	$table_availability_dates = $wpdb->prefix . 'wpcal_availability_dates';

	if( $is_update ){

		
		$service_availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

		$default_availability_details = $service_availability_details_obj->get_default_availability();

		if( empty($default_availability_details) || !($default_availability_details instanceof WPCal_Availability_Date) ){//mostly this check may not require. As it will throwed already.
			throw new WPCal_Exception('service_default_availability_data_missing');
		}

		$default_availability_date_id = $default_availability_details->get_id();

		$update_where = array('id' => $default_availability_date_id);	
		$update_result = $wpdb->update($table_availability_dates, $data, $update_where );
		if($update_result === false){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
	}
	else{
		$data['added_ts'] = $data['updated_ts'];
		$data['type'] = 'default';

		$insert_result = $wpdb->insert($table_availability_dates, $data);
		if($insert_result === false){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
		$default_availability_date_id = $wpdb->insert_id;

		$table_service_availability = $wpdb->prefix . 'wpcal_service_availability';

		$service_id = $service_obj->get_id();

		$link_service_availability = [
			'service_id' => $service_id,
			'availability_dates_id' => $default_availability_date_id
		];

		$link_insert_result = $wpdb->insert($table_service_availability, $link_service_availability);
		if( $link_insert_result === false ){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
	}

	$update_data_periods = $update_data['periods'];
	wpcal_update_default_availability_periods($service_obj, $update_data_periods, $is_update);
}

function wpcal_update_default_availability_periods($service_obj, $update_data_periods, $is_update){

	global $wpdb;

	//validation already done in wpcal_update_service()

	if( !is_array($update_data_periods) ){
		throw new WPCal_Exception('invalid_input_default_availability');
	}

	$service_availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

	if($is_update){

		$default_availability_details = $service_availability_details_obj->get_default_availability();

		$saved_periods_objs = $default_availability_details->get_periods();
		$default_availability_date_id = $default_availability_details->get_id();
	}
	else{
		$saved_periods_objs = [];
		$default_availability_date_id = $service_availability_details_obj->get_default_availability_id();
	}

	foreach($update_data_periods as &$period){
		$period['from_time'] = WPCal_DateTime_Helper::get_Time_obj($period['from_time']);
		$period['to_time'] = WPCal_DateTime_Helper::get_Time_obj($period['to_time']);
	}
	unset($period);

	$delete_periods_ids = [];
	$add_periods = [];

	foreach($saved_periods_objs as $saved_period_obj){
		$saved_period_id = $saved_period_obj->get_id();
		$saved_from_time = $saved_period_obj->get_from_time();
		$saved_to_time = $saved_period_obj->get_to_time();
		$is_exists = false;

		foreach($update_data_periods as $_key => $update_period){
			if( $update_period['from_time'] == $saved_from_time && $update_period['to_time'] == $saved_to_time){
				$is_exists = true;
				unset($update_data_periods[$_key]);
				break;
			}
		}
		
		if(!$is_exists){
			$delete_periods_ids[] = $saved_period_id;
		}
	}

	$add_periods = $update_data_periods;
	$table_availability_periods = $wpdb->prefix . 'wpcal_availability_periods';

	if(!empty($delete_periods_ids)){
		
		$query = "DELETE FROM `$table_availability_periods` WHERE `id` IN(".implode(', ', $delete_periods_ids).") AND `availability_date_id` = '".$default_availability_date_id."'";

		$query_result = $wpdb->query($query);

		if( $query_result === false ){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
	}

	if(!empty($add_periods)){

		foreach($add_periods as $add_period){
			$insert_data = [];
			$insert_data['from_time'] = $add_period['from_time']->DB_format();
			$insert_data['to_time'] = $add_period['to_time']->DB_format();
			$insert_data['availability_date_id'] = $default_availability_date_id;
			$insert_result = $wpdb->insert($table_availability_periods, $insert_data);
			if( $insert_result === false ){
				throw new WPCal_Exception('db_error', '', $wpdb->last_error);
			}
		}
		
	}
	
}

function wpcal_get_default_availability_details_for_admin_client($service_obj){
	$service_availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

	$default_availability_obj = $service_availability_details_obj->get_default_availability();

	$periods = $default_availability_obj->get_data_for_admin_client();
	return $periods;
}

function wpcal_customize_availability_dates($service_id, $data){

	global $wpdb;

	//$service_obj = new WPCal_Service($service_id);


	//check service is editable by current user
	//validate nonce
	/**
	 * validate data
	 * 1) check the date(s) are within default availability date range
	 * 2) check periods with 24 hrs
	 * 3) check correct inputs date_range_type 'from_to', from_date & to_date should be present, similarly for all cases
	 * */
	/**
	 * What to do with old customized availability details
	 * 1) How to verify it is old and no longer useful
	 * 2) What to do for already expired customization
	 * 3) Do we need old data for history purpose
	 */

	//is_multiple for multiple dates

	//days
	//dates - single date is also fine.
	//apply_to_days_or_dates - 'days' | 'dates'


	if( 
		isset($data['is_available']) && 
		$data['is_available'] == 1 && 
		isset($data['use_previous_periods']) &&
		$data['use_previous_periods'] == 1 && 
		!empty($data['dates']) &&
		is_array($data['dates'])
	){
		unset($data['periods']);
		$_availability_details = wpcal_get_periods_for_prefill_for_marking_available_by_date($service_id, $data['dates'][0]);
		if(isset($_availability_details['periods'])){
			$data['periods'] = $_availability_details['periods'];
		}
		//no need to worry about $data['periods'] if not properly set, validation will take care. even for non default sunday it will give default period, if not it should be out of availability I guess
		unset($_availability_details);
	}

	$validate_obj = new WPCal_Validate($data);
	$validate_obj->rules([
		'required' =>[
            'apply_to_days_or_dates',
            'is_available'
        ],
		'requiredWithIf' => [
            ['dates', ['apply_to_days_or_dates' => 'dates']],
            ['day_index_list', ['apply_to_days_or_dates' => 'days']],
            ['from_date', ['apply_to_days_or_dates' => 'days']],
			['periods', ['is_available' => '1']],
			
        ],
		'min' => [
			['is_available', 0]
		],
		'max' => [
			['is_available', 1]
		],
		'in' =>[
			['apply_to_days_or_dates', ['days','dates']],
		],
		'subset' =>[
			['day_index_list', [1,2,3,4,5,6,7]]
		],
		'array' => [
			'dates',
			'day_index_list'
		],
		'dateFormat' => [
			['from_date', 'Y-m-d'],
			['dates.*', 'Y-m-d'],
			['periods.*.from_time', 'H:i:s'],
			['periods.*.to_time', 'H:i:s'],
		],
		'periodsToTimeAfterFromTime' => [
			['periods']
		],
		'periodsCheckCollide' => [
			['periods']
		]
	]);

	if( !$validate_obj->validate() ){
		$validation_errors = $validate_obj->errors();
		throw new WPCal_Exception('validation_errors', '', $validation_errors);
	}

	$allowed_keys = [
		'day_index_list',
		'date_range_type',
		'from_date',
		'to_date',
		'date_misc',
		'type',
		'is_available',
	];

	$allowed_keys_periods = [
		'from_time',
		'to_time'
	];


	$save_data = $data;
	//unset($save_data['apply_to_days_or_dates'], $save_data['from_dates'], $save_data['current_view_month']);

	$save_data = wpcal_get_allowed_fields($data, $allowed_keys);

	$save_data = wpcal_sanitize_all($save_data);

	$save_data['type'] = 'custom';
	$save_data['updated_ts'] = $save_data['added_ts'] = time();

	$save_data_array = [];
	if($data['apply_to_days_or_dates'] === 'dates'){
		$save_data['date_range_type'] = 'from_to';

		foreach($data['dates'] as $date){
			$row = $save_data;
			$row['to_date'] = $row['from_date'] = $date;
			unset($row['day_index_list']);
			$save_data_array[] = $row;
		}
	}
	elseif($data['apply_to_days_or_dates'] === 'days'){
		$save_data['date_range_type'] = 'infinite';
		$save_data['day_index_list'] =  implode(',', $save_data['day_index_list']);
		unset($save_data['to_date']);
		$save_data_array[] = $save_data;
	}

	$save_periods =  (isset($data['periods']) && is_array($data['periods'])) ? $data['periods'] : [];

	$table_availability_dates = $wpdb->prefix . 'wpcal_availability_dates';
	$table_availability_periods = $wpdb->prefix . 'wpcal_availability_periods';
	$table_service_availability = $wpdb->prefix . 'wpcal_service_availability';

	wpcal_service_availability_slots_mark_refresh_cache($service_id);

	foreach($save_data_array as $save_data_row){
		$dates_insert_result = $wpdb->insert($table_availability_dates, $save_data_row);
		if( $dates_insert_result === false ){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}

		$availability_dates_id = $wpdb->insert_id;
		if( !$availability_dates_id ){
			throw new WPCal_Exception('db_error_insert_id_missing');
		}

		foreach($save_periods as $save_period){
			$save_period_final = wpcal_get_allowed_fields($save_period, $allowed_keys_periods);

			$save_period_final['availability_date_id'] = $availability_dates_id;
			$period_insert_result = $wpdb->insert($table_availability_periods, $save_period_final);
			if( $period_insert_result === false ){
				throw new WPCal_Exception('db_error', '', $wpdb->last_error);
			}
		}

		$link_service_availability = [
			'service_id' => $service_id,
			'availability_dates_id' => $availability_dates_id
		];
		$link_insert_result = $wpdb->insert($table_service_availability, $link_service_availability);
		if( $link_insert_result === false ){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
	}

	return true;
}

function wpcal_get_periods_for_prefill_for_marking_available_by_date($service_id, $date){
	$service_obj = wpcal_get_service($service_id);
	$availability_details_obj = new WPCal_Service_Availability_Details($service_obj);
	$date_obj = WPCal_DateTime_Helper::Date_DB_to_DateTime_obj($date);
	$result = $availability_details_obj->get_availability_by_date_except_not_available_for_admin_client($date_obj);
	return $result;
}

function wpcal_update_service_status($status, $service_id){
	global $wpdb;

	$allowed_status_change = [1, -1, -2];
	if( !in_array($status, $allowed_status_change) )	{
		throw new WPCal_Exception('invalid_input_service_status');
	}

	$service_obj = wpcal_get_service($service_id);
	$service_current_status = $service_obj->get_status();
	//can check current status before updating new status
	
	$table_service = $wpdb->prefix . 'wpcal_services';
	$data = ['status' => $status];
	$result = $wpdb->update($table_service, $data, array('id' => $service_id));
	if($result === false){
		return false;
	}
	return true;
}

function wpcal_delete_service($service_id){
	return wpcal_update_service_status($status=-2, $service_id);
}

function wpcal_admin_page(){
	include( WPCAL_PATH . '/templates/admin_page.php');
}

function wpcal_admin_test_page(){
	include( WPCAL_PATH . '/includes/_test.php');
}

function wpcal_get_service($service_id){
	$service_obj = new WPCal_Service($service_id);
	return $service_obj;
}

function wpcal_service_booking_shortcode_cb($attr){
	$args = shortcode_atts( array(
		'id' => null,
		'service_id' => null,
	), $attr );

	if(!$args['id'] && !$args['service_id']){
		return;
	}

	if(!empty($args['service_id'])){
		$service_id = $args['service_id'];
	}elseif(!empty($args['id'])){
		$service_id = $args['id'];
	}
	else{
		return;
	}

	$service_id = sanitize_text_field($service_id);

	if(empty($service_id)){
		return;
	}

	$license_info = WPCal_License::get_account_info();
	if( empty($license_info) || !isset($license_info['email']) || !isset($license_info['status']) ){
		return;
	}

	try{
		$service_obj = wpcal_get_service($service_id);
	}
	catch(WPCal_Exception $e){
		return;
	}

	$is_proper_obj = $service_obj instanceof WPCal_Service;
	if(!$is_proper_obj){
		return;
	}

	$post_id = '';
	global $post;
	if(is_object($post)){
		$post_id = $post->ID;
	}

	//check service is active and availability is still available
	/* Similar loading code in php client/src/assets/css/user_booking.css */
	$return = '
	<script type="text/javascript">
	var wpcal_booking_service_id = "'.$service_id.'";
	var wpcal_post_id = "'.$post_id.'";
	</script>
	<style>
	@keyframes rotation {
		from {
		  -webkit-transform: rotate(0deg);
		}
		to {
		  -webkit-transform: rotate(359deg);
		}
	}
	#wpcal_user_app.loading-indicator-initial::before {
		content: "";
		position: absolute;
		width: 20px;
		height: 20px;
		border-radius: 50%;
		border: 4px solid rgba(var(--accentClrRGB, 86, 123, 243), 1);
		border-top-color: #fff;
		background-color: #fff;
		box-shadow: 0 0 0px 3px #fff, 0 0 7px rgba(0, 0, 0, 0.9);
		left: 50%;
		z-index: 2;
		margin-left: -10px;
		margin-top: 20px;
		-webkit-animation: rotation 0.8s infinite linear;
		animation: rotation 0.8s infinite linear;
	}
	</style>
	';
	$return .= '<div id="wpcal_user_app" class="loading-indicator-initial"></div>';

	//following scripts and styles needed only if wpcal content page
	WPCal_User_Init::enqueue_styles();
	WPCal_User_Init::enqueue_scripts();
	return $return;
}

function wpcal_get_current_user_for_booking_in_user_client(){
	$_user_data = [];
	$_user_data['id'] = 0;
	if( is_user_logged_in() ){
		$user = wp_get_current_user();
		$_user_data['id'] = $user->ID;
		$_user_data['name'] = trim($user->user_firstname.' '.$user->user_lastname);
		$_user_data['email'] = $user->user_email;
	}
	return $_user_data;
}

// This is commented no longer used
// function wpcal_get_service_admin_id($service_id){
// 	global $wpdb;
// 	$table_service_admins =  $wpdb->prefix . 'wpcal_service_admins';

// 	$query =  "SELECT `admin_user_id` FROM `$table_service_admins` WHERE `service_id` = '".$service_id."' ORDER BY `id` LIMIT 1";
// 	$admin_id = $wpdb->get_var($query);
// 	if( empty($admin_id) ){
// 		throw new WPCal_Exception('service_admin_user_id_missing');
// 	}
// 	return $admin_id;
// }

// This is commented no longer used
// function wpcal_get_service_admin_details($service_id){
// 	$admin_user_id = wpcal_get_service_admin_id($service_id);
// 	$admin_user_details = wpcal_get_admin_details($admin_user_id);
// 	return $admin_user_details;
// }

function wpcal_get_admin_details($admin_user_id){
	$admin_user_data = [];
	$admin_user = get_user_by( 'id', $admin_user_id );
	if(!$admin_user){
		return $admin_user_data;
	}

	$name = trim($admin_user->user_firstname.' '.$admin_user->user_lastname);

	$admin_user_data['id'] = $admin_user->ID;
	$admin_user_data['username'] = $admin_user->user_login;
	$admin_user_data['first_name'] = $admin_user->user_firstname;
	$admin_user_data['last_name'] = $admin_user->user_lastname;
	$admin_user_data['email'] = $admin_user->user_email;
	$admin_user_data['name'] = $name;
	$admin_user_data['display_name'] = $admin_user->display_name;
	$admin_user_data['profile_picture'] = get_avatar_url($admin_user, array('size' => 75));
	return $admin_user_data;
}

function wpcal_get_admin_details_of_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		return [];
	}
	//current_user_can( 'activate_plugins' ) --> admin and super_admin 

	$admin_user_details = wpcal_get_admin_details($admin_user_id);
	return $admin_user_details;
}

function wpcal_get_notices_for_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		return [];
	}
	//current_user_can( 'activate_plugins' ) --> admin and super_admin 

	$admin_notices = get_user_meta($admin_user_id, 'wpcal_admin_notices', true);
	if(empty($admin_notices)){
		$admin_notices = [];
	}
	return $admin_notices;
}

function wpcal_update_notices_for_current_admin($options){
	//need validation Improve later
	$options = wpcal_sanitize_all($options);

	$admin_notices = wpcal_get_notices_for_current_admin();
	$final_admin_notices = array_merge($admin_notices, $options);

	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		return [];
	}
	//current_user_can( 'activate_plugins' ) --> admin and super_admin 

	update_user_meta( $admin_user_id, 'wpcal_admin_notices', $final_admin_notices );//if no change it will returen false

	return true;
}


function wpcal_add_booking($input_data, $old_booking_id=null){
	global $wpdb;

	$allowed_keys = [
		'service_id',
		//'unique_link',
		//'admin_user_id',
		//'invitee_wp_user_id',
		'invitee_name',
		'invitee_email',
		'invitee_question_answers',
		'invitee_tz',
		//'booking_from_time',
		//'booking_to_time',
		//'booking_ip',
		'booking_slot',
		'location',
		'booking_page_current_url',
		'booking_page_post_id'
	];

	//$default_data = [];

	//$data = array_merge($default_data, $input_data);

	$data = wpcal_get_allowed_fields($input_data, $allowed_keys);

	$sanitize_rules = [
		'invitee_question_answers' => [
			'*' => [
				'answer' => 'sanitize_textarea_field'
			]
		],
	];
	
	$data = wpcal_sanitize_all($data, $sanitize_rules);
 
	$validate_obj = new WPCal_Validate($data);
	$validation_rules = [
		'required' => [
			'service_id',
			'invitee_name',
			'invitee_email',
			'booking_slot',
		],
		'email' => [
			['invitee_email']
		],
		'integer' => [
			'service_id',
		],
		'dateFormat' => [
			['booking_slot.from_time', 'U'],
			['booking_slot.to_time', 'U'],
		],
		'arrayHasKeys' => [
			['invitee_question_answers.*', ['question', 'answer', 'is_required']],
			['location', ['type']],
		 ],
		 'in' =>[
			['location.type', ['physical', 'phone', 'googlemeet_meeting', 'zoom_meeting', 'gotomeeting_meeting', 'custom', 'ask_invitee']],
			['location.form.who_calls', ['admin', 'invitee']]
		],
		 //following commented because not working, NEED TO IMPROVE
		// 'requiredWithIf' => [
		// 	['invitee_question_answers.*.answer', ['invitee_question_answers.*.is_required' => '1']]
		// ], 
	];

	if( !empty($data['location']['type']) ){
		if( 
			$data['location']['type'] === 'ask_invitee' 
			||
			(
				$data['location']['type'] === 'phone' &&
				is_array($data['location']['type']['form']) &&
				!empty($data['location']['type']['form']['who_calls']) &&
				$data['location']['type']['form']['who_calls'] === 'admin'
			)
		){
			$validation_rules['required'][] = 'location.form.location';
		}

		if( $data['location']['type'] === 'ask_invitee' ){
			$validation_rules['lengthMax'][] = ['location.form.location', 500];
		}
	}
	
	$validate_obj->rules($validation_rules);

	if( !$validate_obj->validate() ){
		$validation_errors = $validate_obj->errors();
		throw new WPCal_Exception('validation_errors', '', $validation_errors);
	}

	$old_booking_obj = null;
	if(!empty($old_booking_id)){
		$old_booking_obj = wpcal_get_booking($old_booking_id);
	}

	$_current_user_id = get_current_user_id();
	$_current_user_id = $_current_user_id > 0 ? $_current_user_id : null;

	if( !empty($data['booking_page_current_url']) ){
		$tmp_booking_page_current_url = explode('#', $data['booking_page_current_url']);
		$data['booking_page_current_url'] = $tmp_booking_page_current_url[0];
		unset($tmp_booking_page_current_url);
	}
	$page_used_for_booking = [
		'url' => !empty($data['booking_page_current_url']) ? $data['booking_page_current_url'] : '',
		'post_id' => !empty($data['booking_page_post_id']) ? $data['booking_page_post_id'] : '',
	];
	$page_used_for_booking = array_filter($page_used_for_booking);
	
	$row_data = $data;
	unset($row_data['booking_slot']);
	unset($row_data['booking_page_current_url']);
	unset($row_data['booking_page_post_id']);

	$service_id = $data['service_id'];
	$service_obj = wpcal_get_service($service_id);

	$row_data['status'] = 1;
	$row_data['admin_user_id'] = $service_obj->get_owner_admin_id();
	$row_data['invitee_wp_user_id'] = $_current_user_id;
	$row_data['booking_ip'] = $_SERVER['REMOTE_ADDR'];
	$row_data['added_ts'] = time();
	$row_data['updated_ts'] = time();
	$row_data['booking_from_time'] = $data['booking_slot']['from_time'];
	$row_data['booking_to_time'] = $data['booking_slot']['to_time'];

	if( !empty($page_used_for_booking) ){
		$row_data['page_used_for_booking'] = json_encode($page_used_for_booking);
	}

	if( !empty($row_data['invitee_question_answers']) ){
		$row_data['invitee_question_answers'] = json_encode($row_data['invitee_question_answers']);
	}

	if( empty($row_data['location']) || !is_array($row_data['location']) ){
		$row_data['location'] = [];
	}
	$row_data['location'] = wpcal_get_allowed_fields($row_data['location'], ['type', 'form']);
	if( isset($row_data['location']['form']) && is_array($row_data['location']['form']) ){
		$row_data['location']['form'] = wpcal_get_allowed_fields($row_data['location']['form'], ['location', 'location_extra', 'who_calls']);
	}
	if( !empty($row_data['location']) ){
		$row_data['location'] = json_encode($row_data['location']);
	}
	else{
		$row_data['location'] = NULL;
	}

	if(!empty($old_booking_obj)){//to reuse the existing tp calendar event created(this also to avoid another mail from tp calendar provider)
		$row_data['event_added_calendar_id'] = $old_booking_obj->get_event_added_calendar_id();
		$row_data['event_added_tp_cal_id'] = $old_booking_obj->get_event_added_tp_cal_id();
		$row_data['event_added_tp_event_id'] = $old_booking_obj->get_event_added_tp_event_id();
	}

	if(!empty($old_booking_obj)){//to reuse the existing tp meeting url if same location type
		if( 
			isset($data['location']['type']) &&
			$old_booking_obj->is_location_needs_tp_account_service() &&
			!empty($old_booking_obj->get_meeting_tp_resource_id()) && $old_booking_obj->get_location_type() === $data['location']['type']
		){
			$row_data['meeting_tp_resource_id'] = $old_booking_obj->get_meeting_tp_resource_id();
		}
	}

	//Validate the slot availability and service is active

	if( $old_booking_obj == null && !$service_obj->is_new_booking_allowed() ){
		throw new WPCal_Exception('service_new_booking_not_allowed');
	}
	else if( $old_booking_obj != null && !$service_obj->is_reschedule_booking_allowed() ){
		throw new WPCal_Exception('service_reschedule_booking_not_allowed');
	}

	$service_availability_slots_obj = new WPCal_Service_Availability_Slots($service_obj);

	$booking_date = WPCal_DateTime_Helper::unix_to_DateTime_obj($row_data['booking_from_time']);
	$booking_date->setTime(0, 0);
	
	$is_max_booking_per_day_reached = $service_availability_slots_obj->is_max_booking_per_day_reached(clone $booking_date);

	if($is_max_booking_per_day_reached){
		throw new WPCal_Exception('service_max_booking_per_day_reached');
	}

	$is_slot_still_available = $service_availability_slots_obj->is_slot_still_available($data['booking_slot']);

	if(!$is_slot_still_available){
		throw new WPCal_Exception('service_booking_slot_not_avaialble');
	}

	$table_bookings = $wpdb->prefix . 'wpcal_bookings';

	$result = $wpdb->insert($table_bookings, $row_data);
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	
	$booking_id = $wpdb->insert_id;
	if( !$booking_id ){
		throw new WPCal_Exception('db_error_insert_id_missing');
	}

	$service_admin_user_id = $service_obj->get_owner_admin_id();
	wpcal_service_availability_slots_mark_refresh_cache_by_admin($service_admin_user_id);
	wpcal_booking_assign_unique_link($booking_id);

	$booking_obj= wpcal_get_booking($booking_id);
	$booking_action = 'new';
	
	if(!empty($old_booking_obj)){
		$booking_action = 'reschedule';
		wpcal_update_new_booking_id_in_rescheduled_booking($old_booking_obj, $booking_obj);
	}
	
	wpcal_after_add_booking_add_background_tasks($booking_obj, $booking_action);

	return $booking_id;	
}

function wpcal_booking_assign_unique_link($booking_id){//its as unique string will be used in the link
	global $wpdb;
	$table_bookings = $wpdb->prefix . 'wpcal_bookings';

	$query = "SELECT * FROM `$table_bookings` WHERE `id` = '".$booking_id."'";
	$booking_data = $wpdb->get_row($query, ARRAY_A);
	if( empty($booking_data) ){
		throw new WPCal_Exception('invalid_booking_id');
	}
	if( !empty($booking_data['unique_link']) ){
		//invalid unique_link already assigned
		return false;
	}

	$i = 0;
	while($i<100){
		$unique_link = sha1(implode('|', array($booking_data['id'], $booking_data['service_id'], $booking_data['booking_from_time'], uniqid('', true))));

		$query2 = "SELECT `id` FROM `$table_bookings` WHERE `unique_link` = '".$unique_link."'";
		$same_unique_link_data = $wpdb->get_row($query2);
		if( empty($same_unique_link_data) ){
			break;
		}
		$i++;
		if( $i >= 100){
			$unique_link = '';
			throw new WPCal_Exception('booking_unable_to_find_unique_link');
		}
	}

	if( empty($unique_link) ){
		throw new WPCal_Exception('booking_unique_link_missing');
	}
	
	$update_result = $wpdb->update($table_bookings, array('unique_link' => $unique_link), array('id' => $booking_data['id']));

	if( $update_result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return true;
}

function wpcal_after_add_booking_add_background_tasks(WPCal_Booking $booking_obj, $booking_action){

	if($booking_action !== 'new' && $booking_action !== 'reschedule' ){
		throw new WPCal_Exception('invalid_action');
	}

	$from_time_obj = $booking_obj->get_booking_from_time();
	$expiry_ts = WPCal_DateTime_Helper::DateTime_Obj_to_unix($from_time_obj) - (5 * 60);//(5 * 60) 5 mins before event starts

	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$dependant_task_id = null;

	$is_location_needs_tp_account_service = $booking_obj->is_location_needs_tp_account_service();
	if($is_location_needs_tp_account_service){
		
		$provider = $booking_obj->get_location_type();
		
		$tp_account = wpcal_get_active_tp_account_by_admin_and_provider($admin_user_id , $provider);

		if( !empty($tp_account) ){
			$task_details = [
				'task_name' => 'add_or_update_online_meeting_for_booking',
				'main_arg_name' => 'booking_id',
				'main_arg_value' => $booking_obj->get_id(),
				'expiry_ts' => $expiry_ts
			];
			$added_task_id = WPCal_Background_Tasks::add_task($task_details);
			if($added_task_id){
				$dependant_task_id = $added_task_id;
			}
		}
		
	}

	$cal_details = wpcal_get_add_events_calendar_by_admin($admin_user_id);

	if(!empty($cal_details)){
		$task_details = [
			'task_name' => 'add_or_update_booking_to_tp_calendar',
			'main_arg_name' => 'booking_id',
			'main_arg_value' => $booking_obj->get_id(),
			'expiry_ts' => $expiry_ts,
			'dependant_id' => $dependant_task_id,
		];
		$added_task_id = WPCal_Background_Tasks::add_task($task_details);
		if( $cal_details->provider === 'google_calendar' && $booking_obj->get_location_type() === 'googlemeet_meeting' ){

			$dependant_task_id = $added_task_id;

			$task_details = [
				'task_name' => 'get_and_set_meeting_url_from_google_calendar',
				'main_arg_name' => 'booking_id',
				'main_arg_value' => $booking_obj->get_id(),
				'expiry_ts' => $expiry_ts,
				'dependant_id' => $dependant_task_id,
			];
			$added_task_id = WPCal_Background_Tasks::add_task($task_details);
			$dependant_task_id = $added_task_id;
		}
	}
	else{

		$task_name = 'send_invitee_booking_confirmation_mail';
		if($booking_action === 'reschedule'){
			$task_name = 'send_invitee_reschedule_booking_confirmation_mail';
		}

		$task_details = [
			'task_name' => $task_name,
			'main_arg_name' => 'booking_id',
			'main_arg_value' => $booking_obj->get_id(),
			'expiry_ts' => $expiry_ts,
			'dependant_id' => $dependant_task_id,
		];
		WPCal_Background_Tasks::add_task($task_details);
	}

	$task_details = [
		'task_name' => 'send_invitee_booking_reminder_mail',
		'main_arg_name' => 'booking_id',
		'main_arg_value' => $booking_obj->get_id(),
		'expiry_ts' => $expiry_ts,
		'dependant_id' => $dependant_task_id,
	];
	WPCal_Background_Tasks::add_task($task_details);

	$task_name = 'send_admin_new_booking_info_mail';
	if($booking_action === 'reschedule'){
		$task_name = 'send_admin_reschedule_booking_info_mail';
	}
	$task_details = [
		'task_name' => $task_name,
		'main_arg_name' => 'booking_id',
		'main_arg_value' => $booking_obj->get_id(),
		'expiry_ts' => $expiry_ts,
		'dependant_id' => $dependant_task_id,
	];
	WPCal_Background_Tasks::add_task($task_details);
}

function wpcal_after_cancel_booking_add_background_tasks(WPCal_Booking $booking_obj, $booking_action){

	if($booking_action !== 'cancel' && $booking_action !== 'reschedule' ){
		throw new WPCal_Exception('invalid_action');
	}

	$from_time_obj = $booking_obj->get_booking_from_time();
	$expiry_ts = WPCal_DateTime_Helper::DateTime_Obj_to_unix($from_time_obj) - (5 * 60);//(5 * 60) 5 mins before event starts

	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$meeting_tp_resource_id = $booking_obj->get_meeting_tp_resource_id();
	if( !empty($meeting_tp_resource_id) && $booking_action === 'cancel' ){
		$task_details = [
			'task_name' => 'delete_online_meeting_for_booking',
			'main_arg_name' => 'booking_id',
			'main_arg_value' => $booking_obj->get_id(),
		];
		WPCal_Background_Tasks::add_task($task_details);
	}

	$cal_details = wpcal_get_add_events_calendar_by_admin($admin_user_id);

	if(!empty($cal_details)){
		if($booking_action === 'cancel'){
			$task_details = [
				'task_name' => 'delete_booking_to_tp_calendar',
				'main_arg_name' => 'booking_id',
				'main_arg_value' => $booking_obj->get_id(),
			];
			WPCal_Background_Tasks::add_task($task_details);
		}
	}
	else{
		if($booking_action === 'cancel'){
			$task_details = [
				'task_name' => 'send_invitee_booking_cancellation_mail',
				'main_arg_name' => 'booking_id',
				'main_arg_value' => $booking_obj->get_id(),
			];
			WPCal_Background_Tasks::add_task($task_details);
		}
	}

	$task_details = [
		'task_name' => 'delete_invitee_booking_reminder_mail',
		'main_arg_name' => 'booking_id',
		'main_arg_value' => $booking_obj->get_id(),
	];
	WPCal_Background_Tasks::add_task($task_details);

	if($booking_action === 'cancel'){
		$task_details = [
			'task_name' => 'send_admin_booking_cancellation_mail',
			'main_arg_name' => 'booking_id',
			'main_arg_value' => $booking_obj->get_id(),
		];
		WPCal_Background_Tasks::add_task($task_details);
	}
}

// function wpcal_booking_may_run_add_or_update_online_meeting_task(WPCal_Booking $booking_obj){
// 	$is_location_needs_tp_account_service = $booking_obj->is_location_needs_tp_account_service();
// 	if($is_location_needs_tp_account_service){
// 		WPCal_Background_Tasks::run_task_by_task_and_main_args('add_or_update_online_meeting_for_booking', 'booking_id', $booking_obj->get_id());
// 	}
// }

function wpcal_get_booking($booking){
	if($booking instanceof WPCal_Booking){
		return $booking;
	}
	else{
		$booking_obj = new WPCal_Booking($booking);
		return $booking_obj;	
	}
}

function wpcal_get_booking_by_unique_link($unique_link){
	global $wpdb;
	$table_bookings = $wpdb->prefix . 'wpcal_bookings';

	$query2 = "SELECT `id` FROM `$table_bookings` WHERE `unique_link` = '".$unique_link."'";
	$booking_id = $wpdb->get_var($query2);
	if($booking_id){
		return wpcal_get_booking($booking_id);
	}
	throw new WPCal_Exception('booking_unable_to_get_booking_id');
}

function wpcal_get_booking_unique_link_by_id($booking_id){
	$booking_obj = wpcal_get_booking($booking_id);
	$link = $booking_obj->get_unique_link();
	return $link;
}

function wpcal_service_availability_slots_mark_refresh_cache($service_id, $do='on'){
	global $wpdb;
	$table_service = $wpdb->prefix . 'wpcal_services';

	if($do === 'on'){
		$refresh_cache = '1';
	}
	elseif($do === 'off'){
		$refresh_cache = '0';
	}
	else{
		throw new WPCal_Exception('invalid_input');
	}

	$result = $wpdb->update($table_service, array('refresh_cache' => $refresh_cache), array('id' => $service_id));

	return $result;	
}

function wpcal_service_availability_slots_mark_refresh_cache_for_all(){
	global $wpdb;
	$table_service = $wpdb->prefix . 'wpcal_services';

	$refresh_cache = '1';

	$result = $wpdb->update($table_service, array('refresh_cache' => $refresh_cache), array('refresh_cache' => 0));

	return $result;	
}

function wpcal_service_availability_slots_mark_refresh_cache_by_admin($admin_user_id, $do='on'){
	$services = wpcal_get_services_of_by_admin($admin_user_id);
	if( empty($services) ){
		return true;
	}

	foreach( $services as $service ){
		wpcal_service_availability_slots_mark_refresh_cache($service->id, $do);
	}
	return true;
}

function wpcal_service_availability_slots_mark_refresh_cache_for_current_admin($do='on'){
	$admin_user_id = get_current_user_id();
	wpcal_service_availability_slots_mark_refresh_cache_by_admin($admin_user_id, $do);
	return true;
}

function wpcal_on_wp_setting_timezone_changes(){
	wpcal_service_availability_slots_mark_refresh_cache_for_all();
}

function wpcal_cancel_booking($booking, $cancel_reason=null, $cancel_type=null ){
	global $wpdb;

	$booking_obj = wpcal_get_booking($booking);

	if( !$booking_obj->is_active() ){
		throw new WPCal_Exception('booking_is_not_active');
	}

	//check service is cancellable
	$service_id = $booking_obj->get_service_id();
	$service_obj = wpcal_get_service($service_id);
	if( !$service_obj->is_cancellation_allowed() ){
		throw new WPCal_Exception('booking_cancellation_not_allowed');
	}

	$table_bookings = $wpdb->prefix . 'wpcal_bookings';

	$cancel_status = -1;
	if($cancel_type === 'reschedule'){
		$cancel_status = -5;
	}
	elseif($cancel_type === 'invitee_cancel_via_tp_cal'){
		$cancel_status = -2;
	}

	$current_user_id = get_current_user_id();
	$action_time = time();

	$update_data = [
		'status' => $cancel_status,
		'reschedule_cancel_reason' => $cancel_reason,
		'reschedule_cancel_user_id' => $current_user_id ? $current_user_id : null,
		'reschedule_cancel_action_ts' => $action_time,
		'updated_ts' => $action_time
	];
	$result = $wpdb->update($table_bookings, $update_data, array('id' => $booking_obj->get_id()));
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}

	$admin_user_id = $booking_obj->get_admin_user_id();
	wpcal_service_availability_slots_mark_refresh_cache_by_admin($admin_user_id);

	if( $cancel_status === -1 ){//normal cancellation
		wpcal_after_cancel_booking_add_background_tasks($booking_obj, $booking_action='cancel');
	}
	elseif( $cancel_status === -5 ){//reschedule cancellation
		wpcal_after_cancel_booking_add_background_tasks($booking_obj, $booking_action='reschedule');
	}
	return true;
}

function wpcal_update_new_booking_id_in_rescheduled_booking(WPCal_Booking $old_booking_obj, WPCal_Booking $new_booking_obj){
	global $wpdb;

	if( !$old_booking_obj->is_rescheduled() ){
		throw new WPCal_Exception('booking_not_a_rescheduled');
	}

	$update_data = [
		'rescheduled_booking_id' => $new_booking_obj->get_id(),
	];
	$table_bookings = $wpdb->prefix . 'wpcal_bookings';

	$result = $wpdb->update($table_bookings, $update_data, array('id' => $old_booking_obj->get_id()));
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return true;
}

function wpcal_get_old_booking_if_rescheduled($booking_id){
	global $wpdb;

	$table_bookings = $wpdb->prefix . 'wpcal_bookings';
	$query = "SELECT `id` FROM `$table_bookings` WHERE `rescheduled_booking_id` = '".$booking_id."'";
	$old_booking_id = $wpdb->get_var($query);
	if(!empty($old_booking_id)){
		return wpcal_get_booking($old_booking_id);
	}
	return false;
}

function wpcal_reschedule_booking($old_booking_id, $new_booking_data){
	$old_booking_obj = wpcal_get_booking($old_booking_id);

	//check service is reschedulable
	$service_id = $old_booking_obj->get_service_id();
	$service_obj = wpcal_get_service($service_id);
	if( !$service_obj->is_reschedule_booking_allowed() ){
		throw new WPCal_Exception('service_reschedule_booking_not_allowed');
	}

	if( !$old_booking_obj->is_active() ){
		throw new WPCal_Exception('booking_old_not_active');
	}

	$is_cancelled = wpcal_cancel_booking($old_booking_obj,  $cancel_reason=null,  $cancel_type='reschedule');

	if( !$is_cancelled ){
		throw new WPCal_Exception('booking_not_cancelled');
	}

	return wpcal_add_booking($new_booking_data, $old_booking_id);
}

function wpcal_get_add_events_calendar_by_admin($admin_user_id){
	global $wpdb;
	
	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';

	$query = "SELECT `cals`.`id` as `calendar_id`, `cals`.`calendar_account_id`, `cal_accs`.`provider`, `cals`.`tp_cal_id` FROM `$table_calendar_accounts` as `cal_accs` JOIN `$table_calendars` as `cals` ON `cal_accs`.`id` = `cals`.`calendar_account_id` WHERE `cal_accs`.`status` = '1' AND `cals`.`status` = '1' AND `cals`.`is_add_events_calendar` = '1' AND `cal_accs`.`admin_user_id` = '".$admin_user_id."'";
	$result = $wpdb->get_row($query);
	
	return $result;
}

function wpcal_may_add_or_update_booking_to_tp_calendar(WPCal_Booking $booking_obj){
	
	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$cal_details = wpcal_get_add_events_calendar_by_admin($admin_user_id);

	if(empty($cal_details)){
		//no calendar
		return false;
	}

	$tp_calendar_class = wpcal_include_and_get_tp_calendar_class($cal_details->provider);

	$tp_calendar_object = new $tp_calendar_class($cal_details->calendar_account_id);

	if( !empty($booking_obj->get_event_added_tp_event_id()) ){
		wpcal_may_update_or_delete_booking_to_tp_calendar($booking_obj, $do='update');
	}
	else{
		$tp_calendar_object->api_add_event($cal_details, $booking_obj);
	}
}

function wpcal_may_update_or_delete_booking_to_tp_calendar(WPCal_Booking $booking_obj, $do){
	global $wpdb;
	
	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$cal_details = wpcal_get_add_events_calendar_by_admin($admin_user_id);

	if(empty($cal_details)){
		//no calendar
		return false;
	}

	$tp_event_id = $booking_obj->get_event_added_tp_event_id();
	$calendar_id = $booking_obj->get_event_added_calendar_id();

	if( empty($tp_event_id) || empty($calendar_id) ){
		//no calendar event added
		return false;
	}

	//Need to validate do we still have access (read and write) to the calendar_account, calendar. is_still_have_access TO DO Improve Code
	//if admin setting for add events to calendar changed it ok. let the previously booked active bookings still go through old calendar to avoid anothe event in end user calendar

	if( $cal_details->calendar_id != $calendar_id ){

		$table_calendars = $wpdb->prefix . 'wpcal_calendars';
		$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';

		$query = "SELECT `calendar_account_id` FROM `$table_calendars` WHERE `id` = '". $calendar_id ."'";

		$calendar_account_id = $wpdb->get_var($query);
		if( empty($calendar_account_id) ){
			//calendar no longer exists
			return false;
		}
		else{
			$query2 = "SELECT `id` FROM `$table_calendar_accounts` WHERE `id` = '". $calendar_account_id ."'";

			$is_exists = $wpdb->get_var($query2);
			if( empty($is_exists) ){
				//calendar no longer exists
				return false;
			}
		}
	}


	$tp_calendar_class = wpcal_include_and_get_tp_calendar_class($cal_details->provider);

	$tp_calendar_object = new $tp_calendar_class($cal_details->calendar_account_id);

	if( $do == 'update' ){
		$tp_calendar_object->api_update_event($cal_details, $booking_obj);
	}
	elseif( $do == 'delete' ){
		$tp_calendar_object->api_delete_event($cal_details, $booking_obj);
	}
}

function wpcal_may_delete_booking_to_tp_calendar(WPCal_Booking $booking_obj){
	return wpcal_may_update_or_delete_booking_to_tp_calendar($booking_obj, $do='delete');
}

function wpcal_get_and_set_meeting_url_from_google_calendar(WPCal_Booking $booking_obj){

	$location_details = $booking_obj->get_location();
	if(!empty($location_details['form']['location'])){
		return true; //location already updated
	}

	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$cal_details = wpcal_get_add_events_calendar_by_admin($admin_user_id);

	if(empty($cal_details)){
		//no calendar
		return false;
	}

	$tp_event_id = $booking_obj->get_event_added_tp_event_id();
	$calendar_id = $booking_obj->get_event_added_calendar_id();

	if( empty($tp_event_id) || empty($calendar_id) ){
		//no calendar event added
		return false;
	}

	$tp_calendar_class = wpcal_include_and_get_tp_calendar_class($cal_details->provider);

	$tp_calendar_object = new $tp_calendar_class($cal_details->calendar_account_id);

	$tp_calendar_object->get_and_set_meeting_url_from_event($cal_details, $booking_obj);
}

function wpcal_include_and_get_tp_calendar_class($provider){
	$list_providers = [
		'google_calendar'
	];

	$provider_class = [
		'google_calendar' => 'WPCal_TP_Google_Calendar'
	];

	if( !in_array( $provider, $list_providers, true ) ){
		throw new WPCal_Exception('invalid_tp_calendar_provider');
	}

	$include_file = WPCAL_PATH . '/includes/tp_calendars/class_'.$provider.'.php';
	include_once($include_file);

	return $provider_class[$provider];
}

function wpcal_booking_update_tp_calendar_event_details($booking_id, $calendar_id, $tp_cal_id, $tp_event_id){
	global $wpdb;

	$booking_obj = new WPCal_Booking($booking_id);

	$table_bookings = $wpdb->prefix . 'wpcal_bookings';

	$update_data = [
		'event_added_calendar_id' => $calendar_id,
		'event_added_tp_cal_id' => $tp_cal_id,
		'event_added_tp_event_id' => $tp_event_id
	];
	$result = $wpdb->update($table_bookings, $update_data, array('id' => $booking_obj->get_id()));
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return true;
}

function wpcal_get_services_of_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		return [];
	}
	//current_user_can( 'activate_plugins' ) --> admin and super_admin 

	return wpcal_get_services_of_by_admin($admin_user_id);
}

function wpcal_get_services_of_by_admin($admin_user_id){
	$options = [
		'admin_user_id' => $admin_user_id
	];

	return wpcal_get_services($options);
}

function wpcal_get_services($options=[]){
	global $wpdb;

	$table_services = $wpdb->prefix . 'wpcal_services';
	$table_service_admins = $wpdb->prefix . 'wpcal_service_admins';

	$statuses = array(1, -1);
	if( isset($options['statuses']) ){
		$statuses = (array) $options['statuses'];
	}

	$query = "SELECT `service`.`id`, `service`.`name`, `service`.`status`, `service`.`duration`, `service`.`relationship_type`, `service`.`post_id`, `service`.`color` FROM `$table_services` as `service` JOIN `$table_service_admins` as `service_admin` ON `service`.`id` = `service_admin`.`service_id` WHERE `service`.`status`IN (". implode(', ', $statuses).")";

	if( isset($options['admin_user_id']) ){
		$query .= " AND `service_admin`.`admin_user_id` = '".$options['admin_user_id']."'";
	}

	$results = $wpdb->get_results($query);
	if( empty($results) ){
		return [];
	}
	foreach($results as $key => $service){
		$results[$key]->post_details = WPCal_Service::get_post_details_by_post_id($service->post_id);
	}
	return $results;
}

function wpcal_add_calendar_account_redirect($provider){

	// if( wpcal_is_calendar_accounts_limit_reached_of_current_admin() ){
	// 	echo "Max calendar account limit reached.";
	// 	exit;
	// }

	$list = [ 'google_calendar' ];
	if(!in_array($provider, $list) ){
		throw new WPCal_Exception('invalid_tp_calendar_provider');
	}
	
	//verify plan limits before going down
	$tp_calendar_class = wpcal_include_and_get_tp_calendar_class($provider);
	$tp_calendar_obj = new $tp_calendar_class(0);
	$url = $tp_calendar_obj->get_add_account_url();

	if(empty($url)){
		throw new WPCal_Exception('tp_calendar_auth_url_data_missing');
	}

	$redirect_site_url = [ 'google_calendar' => WPCAL_GOOGLE_OAUTH_REDIRECT_SITE_URL ];

	$final_url = $redirect_site_url[$provider].'cal-api/?calendar_provider=google_calendar&passed_data='.urlencode( base64_encode($url) );
	
	wp_redirect($final_url);
	exit;
}

function wpcal_google_calendar_receive_token_and_add_account(){
	//having temporarily saving add request and use it again when auth code comes  it will be useful

	// if( wpcal_is_calendar_accounts_limit_reached_of_current_admin() ){
	// 	echo "Max calendar account limit reached.";
	// 	exit;
	// }

	//verify plan limits before going down
	$tp_calendar_class = wpcal_include_and_get_tp_calendar_class('google_calendar');
	$tp_calendar_obj = new $tp_calendar_class(0);
	$tp_calendar_obj->add_account_after_auth();

	$calendar_page_link = 'admin.php?page=wpcal_admin#/settings/calendars';
	wp_redirect($calendar_page_link);
	exit;
}

function wpcal_disconnect_calendar_by_id($calendar_account_id, $provider){
	$list = [ 'google_calendar'];
	if(!in_array($provider, $list) ){
		throw new WPCal_Exception('invalid_tp_calendar_provider');
	}
	
	//verify plan limits before going down
	$tp_calendar_class = wpcal_include_and_get_tp_calendar_class($provider);
	$tp_calendar_obj = new $tp_calendar_class($calendar_account_id);
	
	$result = $tp_calendar_obj->revoke_access_and_delete_its_data();
	return $result;
}

function wpcal_get_calendar_accounts_details_of_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		return [];
	}
	//current_user_can( 'activate_plugins' ) --> admin and super_admin 

	$options = [
		'admin_user_id' => $admin_user_id
	];

	return wpcal_get_calendar_accounts_details($options);
}

function wpcal_get_calendar_accounts_details($options=[]){
	global $wpdb;

	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';

	$query = "SELECT `id`, `admin_user_id`, `provider`, `status`, `account_email` FROM `$table_calendar_accounts` WHERE `status` = '1'";

	if( isset($options['admin_user_id']) ){
		$query .= " AND `admin_user_id` = '".$options['admin_user_id']."'";
	}

	$calendar_accounts = $wpdb->get_results($query, OBJECT_K);
	if( empty($calendar_accounts) ){
		return [];
	}
	
	foreach($calendar_accounts as $key => $calendar_account){

		$query2 = "SELECT `id`, `calendar_account_id`, `name`, `tp_cal_id`, `is_conflict_calendar`, `is_add_events_calendar`, `is_primary`, `is_writable` FROM `$table_calendars` WHERE `status` = '1' AND `calendar_account_id` = '".$calendar_account->id."'";

		$calendars = $wpdb->get_results($query2, OBJECT_K);
		if( empty($calendars) ){
			//no calendars - very case - not sure it is an error
			$calendar_accounts[$key]->calendars =  [];
			continue;
		}
		$calendar_accounts[$key]->calendars = $calendars;
	}
	return $calendar_accounts;
}

function wpcal_get_count_of_calendar_accounts_of_current_admin(){
	global $wpdb;

	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		return [];
	}
	//current_user_can( 'activate_plugins' ) --> admin and super_admin 

	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$query = "SELECT count(`id`) FROM `$table_calendar_accounts` WHERE `admin_user_id` = '".$admin_user_id."'";

	$result = $wpdb->get_var($query);
	return $result;
}

function wpcal_is_calendar_accounts_limit_reached_of_current_admin(){
	$current_count = wpcal_get_count_of_calendar_accounts_of_current_admin();
	if( $current_count >= 6){
		return true;
	}
	return false;
}

function wpcal_get_add_bookings_to_calendar_of_current_admin(){

	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('current_admin_id_missing_or_doesnt_have_enough_privilege');
	}

	return wpcal_get_add_bookings_to_calendar_by_admin($admin_user_id);
}

function wpcal_get_add_bookings_to_calendar_by_admin($admin_user_id){
	global $wpdb;
	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';
	
	$query = "SELECT `calendars`.`id` as `calendar_id`, `calendars`.`calendar_account_id`, `calendar_accounts`.`provider`, `calendar_accounts`.`status` as `calendar_account_status` FROM `$table_calendars` as `calendars` JOIN `$table_calendar_accounts` as `calendar_accounts` ON `calendar_accounts`.`id` = `calendars`.`calendar_account_id` WHERE `calendar_accounts`.`status` = '1' AND `calendars`.`status` = '1' AND `calendars`.`is_add_events_calendar` = '1' AND `calendar_accounts`.`admin_user_id` = '".$admin_user_id."'";
	$add_bookings_to_calendar = $wpdb->get_row($query);

	return $add_bookings_to_calendar;
}

function wpcal_get_conflict_calendar_ids_of_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('current_admin_id_missing_or_doesnt_have_enough_privilege');
	}

	return wpcal_get_conflict_calendar_ids_by_admin($admin_user_id);
}

function wpcal_get_conflict_calendar_ids_by_admin($admin_user_id){
	global $wpdb;
	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';
	
	$query = "SELECT `calendars`.`id` FROM `$table_calendars` as `calendars` JOIN `$table_calendar_accounts` as `calendar_accounts` ON `calendar_accounts`.`id` = `calendars`.`calendar_account_id` WHERE `calendar_accounts`.`status` = '1' AND `calendars`.`status` = '1' AND `calendars`.`is_conflict_calendar` = '1' AND `calendar_accounts`.`admin_user_id` = '".$admin_user_id."'";
	$conflict_calendar_ids = $wpdb->get_col($query);

	if( empty($conflict_calendar_ids) ){
		return [];
	}
	return $conflict_calendar_ids;
}

function wpcal_update_add_bookings_to_calendar_id_for_current_admin($add_bookings_to_calendar_id){
	global $wpdb;

	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('current_admin_id_missing_or_doesnt_have_enough_privilege');
	}

	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';

	$query = "SELECT `id` FROM `$table_calendar_accounts` WHERE `status` = '1' AND `admin_user_id` = '".$admin_user_id."'";

	$calendar_account_ids = $wpdb->get_col($query);
	if( empty($calendar_account_ids) ){
		return false;
	}

	$query2 = "UPDATE `$table_calendars` SET `is_add_events_calendar` = '0' WHERE `is_add_events_calendar` = '1' AND `calendar_account_id` IN(". implode(', ', $calendar_account_ids) .")";

	$set_zero_for_all = $wpdb->query($query2);
	if( $set_zero_for_all === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}

	if( $add_bookings_to_calendar_id == 'no' ){
		return true;
	}

	$updated_row_count = $wpdb->update($table_calendars, array('is_add_events_calendar' => '1'), array('id' => $add_bookings_to_calendar_id));
	if( $updated_row_count === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}

	return true;
}

function wpcal_update_conflict_calendar_ids_for_current_admin($conflict_calendar_ids, $conflict_calendar_ids_length){
	global $wpdb;

	if( $conflict_calendar_ids_length > 0 && is_array($conflict_calendar_ids) && count($conflict_calendar_ids) == $conflict_calendar_ids_length ){
		//this is good
	}
	elseif( $conflict_calendar_ids_length == 0 && ( !is_array($conflict_calendar_ids) || count($conflict_calendar_ids) == 0 ) ){
		//this is good
	}
	else{
		throw new WPCal_Exception('invalid_input');
	}

	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('current_admin_id_missing_or_doesnt_have_enough_privilege');
	}

	$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';

	$query = "SELECT `id` FROM `$table_calendar_accounts` WHERE `status` = '1' AND `admin_user_id` = '".$admin_user_id."'";

	$calendar_account_ids = $wpdb->get_col($query);
	if( empty($calendar_account_ids) ){
		return false;
	}

	$query2 = "UPDATE `$table_calendars` SET `is_conflict_calendar` = '0' WHERE `is_conflict_calendar` = '1' AND `calendar_account_id` IN(". implode(', ', $calendar_account_ids) .")";

	$set_zero_for_all = $wpdb->query($query2);
	if( $set_zero_for_all === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}

	if( empty($conflict_calendar_ids) || !is_array($conflict_calendar_ids) ){
		return true;
	}

	$query3 = "UPDATE `$table_calendars` SET `is_conflict_calendar` = '1' WHERE `id` IN(". implode(', ', $conflict_calendar_ids) .")";

	$updated_row_count = $wpdb->query($query3);
	if( $updated_row_count === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}

	wpcal_service_availability_slots_mark_refresh_cache_for_current_admin();
	return true;
}

function wpcal_get_primary_calendar_of_calendar_account($calendar_account_id){
	global $wpdb;

	$table_calendars = $wpdb->prefix . 'wpcal_calendars';

	$query = "SELECT `id`, `calendar_account_id`, `name`, `tp_cal_id`, `is_conflict_calendar`, `is_add_events_calendar`, `is_primary`, `is_writable` FROM `$table_calendars` WHERE `status` = '1' AND `calendar_account_id` = '".$calendar_account_id."' AND `is_primary` = '1' ORDER BY `id` LIMIT 1 ";

	return $wpdb->get_row($query);
}

function wpcal_check_and_add_default_calendars_for_current_admin($recently_added_calendar_account_id){
	//this will check and add default calendar for add_bookings and conflict calendar

	//check is this the only calendar for the this admin
	$calendar_accounts_details =  wpcal_get_calendar_accounts_details_of_current_admin();
	if( !is_array($calendar_accounts_details) || count($calendar_accounts_details) !== 1 ){
		return false;
	}

	$_calendar_account_details = array_shift($calendar_accounts_details);

	//verify new calendar for current admin
	if( $_calendar_account_details->id != $recently_added_calendar_account_id){
		return false;
	}

	//check any one has a calendar set for add_bookings and conflict calendar - if yes then no need to continue
	$add_bookings_to_calendar = wpcal_get_add_bookings_to_calendar_of_current_admin();
	if( !empty($add_bookings_to_calendar) ){
		return false;
	}

	$conflict_calendar_ids = wpcal_get_conflict_calendar_ids_of_current_admin();
	if( !empty($conflict_calendar_ids) ){
		return false;
	}

	// get primary calendar
	$calendar_details = wpcal_get_primary_calendar_of_calendar_account($recently_added_calendar_account_id);

	if( empty($calendar_details) ){
		return false;
	}

	$primary_calendar_id = $calendar_details->id;

	//all ok now add the primary calendar of calendar_account_id as add_bookins_calendar and conflict_calendar

	wpcal_update_add_bookings_to_calendar_id_for_current_admin($primary_calendar_id);

	wpcal_update_conflict_calendar_ids_for_current_admin($conflict_calendar_ids=[$primary_calendar_id], $conflict_calendar_ids_length=1);
}

function wpcal_reset_stuck_tp_calendar_sync_events_task(){
	global $wpdb;
	$table_calendars = $wpdb->prefix . 'wpcal_calendars';
	$n_mins_ago = time() - (10 * 60);
	$query = "UPDATE `$table_calendars` SET `list_events_sync_status` = NULL WHERE `list_events_sync_status` = 'running' AND `list_events_sync_last_update_ts` < '".$n_mins_ago."' ";
	$result = $wpdb->query($query);
	return $result;
}

function wpcal_sync_all_calendar_api_for_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('current_admin_id_missing_or_doesnt_have_enough_privilege');
	}

	WPCal_Cron::set_run_full_now(true);
	WPCal_Cron::sync_tp_calendars_and_events_by_admin($admin_user_id);
}

function wpcal_get_unique_calendar_account_ids_by_calendar_ids(array $calendar_ids){
	global $wpdb;

	$table_calendars = $wpdb->prefix . 'wpcal_calendars';
	$query = "SELECT DISTINCT `calendar_account_id` FROM `$table_calendars` WHERE  `id` IN(". implode(', ', $calendar_ids) .")";

	$calendar_account_ids = $wpdb->get_col($query);
	if( $calendar_account_ids === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return $calendar_account_ids;
}

function wpcal_get_wpcal_admin_users_details_for_admin_client(){
	//wpcal admin users only
	global $wpdb;

	$table_services = $wpdb->prefix . 'wpcal_services';
	$table_service_admins = $wpdb->prefix . 'wpcal_service_admins';

	$query = "SELECT `admin_user_id`, count(`id`) as `services_count`, 
	(
		SELECT count(`services`.`id`) FROM `$table_services` as `services` JOIN `$table_service_admins` as `service_admins`  ON `services`.id = `service_admins`.`service_id` WHERE `services`.`status` = 1 AND `service_admins`.`admin_user_id` = `$table_service_admins`.`admin_user_id`
	) as `services_active_count`
	 FROM `$table_service_admins` GROUP BY `admin_user_id`";

	 $result = $wpdb->get_results($query);	 

	 if(!empty($result)){
		 foreach($result as $key => &$admin_details){
			$user = get_user_by( 'id', $admin_details->admin_user_id );
			$name = trim($user->user_firstname.' '.$user->user_lastname);
			$name = empty($name) ? $user->display_name : $name;
			$admin_details->name = $name;
		 }
	 }
	 return $result;
}

function wpcal_listen_and_may_redirect(){
	if( empty($_GET['wpcal_action']) || empty($_GET['booking_id']) ){
		return;
	}
	$action = trim($_GET['wpcal_action']);
	$allowed_actions = [
		//booking related redirect to a page
		'booking_cancel',
		'booking_reschedule',
		'booking_view',

		//booking related redirect to add_link or download ics file
		'booking_tp_add_event',

		//booking related - tp meeting url(web conference) redirect
		'booking_meeting_redirect',
	];
	if( !in_array($action, $allowed_actions, true) ){
		return;
	}
	$booking_unqiue_link = sanitize_text_field($_GET['booking_id']);

	if( $action === 'booking_tp_add_event' ){
		$tp = sanitize_text_field($_GET['tp']);
		$booking_obj = wpcal_get_booking_by_unique_link($booking_unqiue_link);
		WPCal_TP_Calendars_Add_Event::redirect_to_add_link_or_download($tp, $booking_obj);
		return;
	}

	if( $action === 'booking_meeting_redirect' ){
		$booking_obj = wpcal_get_booking_by_unique_link($booking_unqiue_link);
		if( $booking_obj->is_location_needs_online_meeting() ){
			$redirect_url = $booking_obj->get_location_str();
			if( $redirect_url ){
				if( wp_redirect($redirect_url) ){
					exit;
				}
			}
			else{
				exit('Meeting URL not found please contact meeting host.');
			}
		}
		return;
	}

	try{
		$booking_obj = wpcal_get_booking_by_unique_link($booking_unqiue_link);
		$page_used_for_booking = $booking_obj->get_page_used_for_booking();
		
		if( !empty($page_used_for_booking['url']) ){
			$booking_page_url = $page_used_for_booking['url'];
		}
		else{//page_used_for_booking will not be available if the reschedule happens in admin end
			$post_details = $booking_obj->service_obj->get_post_details();
			$booking_page_url = !empty($post_details['link']) ? $post_details['link'] : '';
		}
		if( empty($booking_page_url) ){
			return;
		}

		$url_paths = [
			'booking_reschedule' => 'reschedule',
			'booking_cancel' => 'cancel',
			'booking_view' => 'view'
		];

		$redirect_url = $booking_page_url . '#/booking/' . $url_paths[$action] . '/' . $booking_unqiue_link;
		if($url_paths[$action] === 'cancel'){
			$redirect_url = $booking_page_url . '#/booking/view/' . $booking_unqiue_link . '/' . $url_paths[$action];
		}
		if( wp_redirect($redirect_url) ){
			exit;
		}
		return;
	}
	catch(WPCal_Exception $e){
		return;
	}
}

function wpcal_may_add_sample_services_on_plugin_activation(){
	global $wpdb;
	$table_service = $wpdb->prefix . 'wpcal_services';

	$query = "SELECT `id` FROM $table_service LIMIT 1";
	$any_service_exists = $wpdb->get_row($query);
	if(!empty($any_service_exists)){
		return false;
	}

	$working_hours = WPCal_General_Settings::get('working_hours');

	$common_sample_data = [
		'name' => '',
		'status' => '1',
		'locations' => [],
		'descr' => '',
		'color' => '',
		'relationship_type' => '1to1',
		'duration' => '',
		'display_start_time_every' => '',
		'max_booking_per_day' => NULL,
		'min_schedule_notice' => [
			'type'=> "units",
			'time_units'=> "4",
			'time_units_in'=> "hrs",
			'days_before_time'=> "23:59:59",
			'days_before'=> 1
		],
		'event_buffer_before' => 0,
		'event_buffer_after' => 0,
		'invitee_questions' => [
			'questions' => [
				[
					'is_enabled' => '1',
					'is_required' => '1',
					'question' => 'Please share anything that will help prepare for our meeting.',
					'answer_type' => 'textarea',
				],
			],
		],
		'default_availability_details' => [
			'date_range_type' => 'relative',
			'from_date' => NULL,
			'from_date' => NULL,
			'date_misc' => '+60D',
			'periods' => [
				$working_hours
			]
		],
	];

	$sample_1_data = $sample_2_data = $sample_3_data = $common_sample_data;

	$sample_1_data['name'] = '15 mins meeting';
	$sample_1_data['color'] = 'nephritis';
	$sample_1_data['duration'] = 15;
	$sample_1_data['display_start_time_every'] = 15;

	$sample_2_data['name'] = '30 mins meeting';
	$sample_2_data['color'] = 'belize';
	$sample_2_data['duration'] = 30;
	$sample_2_data['display_start_time_every'] = 15;

	$sample_3_data['name'] = '60 mins meeting';
	$sample_3_data['color'] = 'wisteria';
	$sample_3_data['duration'] = 60;
	$sample_3_data['display_start_time_every'] = 30;

	try{
		wpcal_add_service($sample_1_data);
		wpcal_add_service($sample_2_data);
		wpcal_add_service($sample_3_data);
	}
	catch(WPCal_Exception $e){

	}
}

function wpcal_include_and_get_tp_class($provider){
	$list_providers = [
		'zoom_meeting',
		'gotomeeting_meeting'
	];

	$provider_class = [
		'zoom_meeting' => 'WPCal_TP_Zoom_Meeting',
		'gotomeeting_meeting' => 'WPCal_TP_GoToMeeting_Meeting'
	];

	if( !in_array( $provider, $list_providers, true ) ){
		throw new WPCal_Exception('invalid_tp_provider');
	}

	$include_file = WPCAL_PATH . '/includes/tp/class_'.$provider.'.php';
	include_once($include_file);

	return $provider_class[$provider];
}

function wpcal_max_tp_account_limit_reached_error_msg($provider){
	$provider_name = [
		'zoom_meeting' => 'Zoom',
		'gotomeeting_meeting' => 'GoToMeeting',
	];
	$msg = 'Max '.$provider_name[$provider].' account limit reached. <a href="admin.php?page=wpcal_admin#/settings/integrations">Click here</a>.';
	return $msg;
}

function wpcal_add_tp_account_redirect($provider){

	if( wpcal_tp_accounts_is_limit_reached_for_current_admin($provider) ){
		echo wpcal_max_tp_account_limit_reached_error_msg($provider);
		exit;
	}

	//verify plan limits before going down
	$tp_class = wpcal_include_and_get_tp_class($provider);
	$tp_obj = new $tp_class(0);
	$url = $tp_obj->get_add_account_url();

	if(empty($url)){
		throw new WPCal_Exception('tp_auth_url_data_missing');
	}

	$redirect_site_url = [ 
		'zoom_meeting' => WPCAL_ZOOM_OAUTH_REDIRECT_SITE_URL,
		'gotomeeting_meeting' => WPCAL_GOTOMEETING_OAUTH_REDIRECT_SITE_URL
	];

	$final_url = $redirect_site_url[$provider].'cal-api/?tp_provider='.$provider.'&passed_data='.urlencode( base64_encode($url) );
	//$final_url = $url;
	
	wp_redirect($final_url);
	exit;
}

function wpcal_tp_account_receive_token_and_add_account($provider){
	//having temporarily saving add request and use it again when auth code comes  it will be useful

	if( wpcal_tp_accounts_is_limit_reached_for_current_admin($provider) ){
		echo wpcal_max_tp_account_limit_reached_error_msg($provider);
		exit;
	}

	//verify plan limits before going down
	$tp_class = wpcal_include_and_get_tp_class($provider);
	$tp_obj = new $tp_class(0);
	$tp_obj->add_account_after_auth();

	$tp_page_link = 'admin.php?page=wpcal_admin#/settings/integrations';
	wp_redirect($tp_page_link);
	exit;
}


function wpcal_tp_accounts_is_limit_reached_for_current_admin($provider){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('user_doesnt_have_admin_rights');
	}

	return wpcal_tp_accounts_is_limit_reached($provider, $admin_user_id);
}

function wpcal_tp_accounts_is_limit_reached($provider, $admin_user_id){
	$providers_setting = [
		'zoom_meeting' => [
			'provider_slug' => 'zoom',
			'provider_type' => 'meeting',
			'limit_per' => 'user',
			'limit' => 1
		],
		'gotomeeting_meeting' => [
			'provider_slug' => 'gotomeeting',
			'provider_type' => 'meeting',
			'limit_per' => 'user',
			'limit' => 1
		]
	];

	if( !isset($providers_setting[$provider]) ){
		throw new WPCal_Exception('invalid_tp_provider');
	}

	if( empty($admin_user_id) || !is_numeric($admin_user_id) ){
		throw new WPCal_Exception('invalid_admin_user_id');
	}

	$limit = $providers_setting[$provider]['limit'];

	global $wpdb;
	$table_tp_accounts = $wpdb->prefix . 'wpcal_tp_accounts';
	$query = "SELECT count(*) FROM `$table_tp_accounts` WHERE `admin_user_id` = '".$admin_user_id."' AND `provider` = '".$provider."'";
	$count = $wpdb->get_var($query);

	return $limit <= $count;
}

function wpcal_get_tp_accounts_of_current_admin(){
	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('user_doesnt_have_admin_rights');
	}

	$result = wpcal_get_tp_accounts_by_admin($admin_user_id);
	return $result;
}

function wpcal_get_tp_accounts_by_admin($admin_user_id){
	global $wpdb;
	$table_tp_accounts = $wpdb->prefix . 'wpcal_tp_accounts';

	$query = "SELECT `id`, `admin_user_id`, `provider`, `provider_type`, `status`, `tp_user_id`, `tp_account_email` FROM `$table_tp_accounts` WHERE `admin_user_id` = '".$admin_user_id."'";
	$result = $wpdb->get_results($query);
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return $result;
}

function wpcal_disconnect_tp_account_by_id($tp_account_id, $provider, $force=false){
		
	$tp_account_class = wpcal_include_and_get_tp_class($provider);
	$tp_account_obj = new $tp_account_class($tp_account_id);
	
	$result = $tp_account_obj->revoke_access_and_delete_its_data($force);
	return $result;
}

function wpcal_check_auth_if_fails_remove_tp_accounts_for_current_admin(){
	$tp_accounts = wpcal_get_tp_accounts_of_current_admin();
	$supported_auth_check = ['zoom_meeting'];
	$result_accounts = [];

	//one removal per call - that is better - also because of exception
	foreach($tp_accounts as $tp_account){
		if( in_array($tp_account->provider, $supported_auth_check, true) ){
			$tp_class = wpcal_include_and_get_tp_class($tp_account->provider);
			$tp_obj = new $tp_class($tp_account->id);
			if( method_exists($tp_obj, 'check_auth_if_fails_remove_account') ){
				$result = $tp_obj->check_auth_if_fails_remove_account();
				$result_accounts[] = ['id' => $tp_account->id, 'provider' => $tp_account->provider, 'auth_status' => $result];
			}
		}
	}
	return $result_accounts;
}

function wpcal_add_or_update_online_meeting_for_booking(WPCal_Booking $booking_obj){
	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$is_location_needs_tp_account_service = $booking_obj->is_location_needs_tp_account_service();
	if(!$is_location_needs_tp_account_service){
		throw new WPCal_Exception('booking_location_doesnt_need_online_meeting');
	}

	$provider = $booking_obj->get_location_type();
	$tp_account = wpcal_get_active_tp_account_by_admin_and_provider($admin_user_id , $provider);

	if( empty($tp_account) ){
		throw new WPCal_Exception('tp_account_missing');
	}

	$tp_class = wpcal_include_and_get_tp_class($tp_account->provider);
	$tp_obj = new $tp_class($tp_account->id);

	if( !empty($booking_obj->get_meeting_tp_resource_id()) ){
		$tp_obj->update_meeting($booking_obj);
	}
	else{
		$tp_obj->create_meeting($booking_obj);
	}
}

function wpcal_delete_online_meeting_for_booking(WPCal_Booking $booking_obj){

	$admin_user_id = $booking_obj->get_admin_user_id();
	if(empty($admin_user_id)){
		throw new WPCal_Exception('booking_admin_user_id_missing');
	}

	$meeting_tp_resource_id = $booking_obj->get_meeting_tp_resource_id();
	$tp_resource_obj = new WPCal_TP_Resource($meeting_tp_resource_id);
	$provider = $tp_resource_obj->get_provider();

	$tp_account = wpcal_get_active_tp_account_by_admin_and_provider($admin_user_id , $provider);

	if( empty($tp_account) ){
		throw new WPCal_Exception('tp_account_missing');
	}

	$tp_class = wpcal_include_and_get_tp_class($tp_account->provider);
	$tp_obj = new $tp_class($tp_account->id);

	$tp_obj->delete_meeting($booking_obj);
}

function wpcal_get_active_tp_account_by_admin_and_provider($admin_user_id , $provider){
	global $wpdb;
	$table_tp_accounts = $wpdb->prefix . 'wpcal_tp_accounts';
	$query = "SELECT * FROM `$table_tp_accounts` WHERE `admin_user_id` = '".$admin_user_id."' AND `provider` = '".$provider."' AND `status` = '1'";
	$result = $wpdb->get_row($query);
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return $result;
}

function wpcal_booking_update_online_meeting_details(WPCal_Booking $booking_obj, $location_type, $new_location_form_data, $meeting_tp_resource_id){
	global $wpdb;
	if($booking_obj->get_location_type() !== $location_type){
		throw new WPCal_Exception('booking_location_type_mismatch');
	}
	$location_details = $booking_obj->get_location();
	if( !isset($location_details['form']) ){
		$location_details['form'] = [];
	}
	$new_location_form_data = wpcal_get_allowed_fields($new_location_form_data, array('location', 'password_data'));
	if( empty($new_location_form_data['location']) ){
		throw new WPCal_Exception('location_details_not_available');
	}
	$location_details['form'] = array_merge($location_details['form'],$new_location_form_data);

	$location_details = json_encode($location_details);

	$table_bookings = $wpdb->prefix . 'wpcal_bookings'; 
	$update_data = [
		'location' => $location_details,
		'meeting_tp_resource_id' => $meeting_tp_resource_id,
		'updated_ts' => time()
	];
	$result = $wpdb->update($table_bookings, $update_data, array('id' => $booking_obj->get_id()));
	if( $result === false ){
		throw new WPCal_Exception('db_error', '', $wpdb->last_error);
	}
	return true;
}


function wpcal_get_tp_locations_for_current_admin(){

	$admin_user_id = get_current_user_id();

	if ( empty($admin_user_id) || !current_user_can( 'activate_plugins' ) ) {
		throw new WPCal_Exception('current_admin_id_missing_or_doesnt_have_enough_privilege');
	}

	return wpcal_get_tp_locations_by_admin($admin_user_id);
}

function wpcal_get_tp_locations_by_admin($admin_user_id){
	$tp_locations = [
		'zoom_meeting' => [
			'is_connected_and_active' => false,
			'is_connected' => false,
			'is_active' => false,
		],
		'gotomeeting_meeting' => [
			'is_connected_and_active' => false,
			'is_connected' => false,
			'is_active' => false,
		],
		'googlemeet_meeting' => [
			'is_connected_and_active' => false,
			'is_connected' => false,
			'is_active' => false,
		]
	];
	$tp_accounts = wpcal_get_tp_accounts_by_admin($admin_user_id);
	foreach($tp_accounts as $tp_account){
		foreach($tp_locations as $tp_location_type => $tp_location_details){
			if( $tp_location_type === 'googlemeet_meeting' ){
				continue;
			}
			if( $tp_account->provider === $tp_location_type ){
				$tp_locations[$tp_location_type]['is_connected'] = true;
				if( $tp_account->status ){
					$tp_locations[$tp_location_type]['is_active'] = true;
				}
				$tp_locations[$tp_location_type]['is_connected_and_active'] = $tp_locations[$tp_location_type]['is_connected'] && $tp_locations[$tp_location_type]['is_active'];
				break;
			}
		}
	}

	$add_bookings_to_calendar = wpcal_get_add_bookings_to_calendar_by_admin($admin_user_id);
	if( !empty($add_bookings_to_calendar) && $add_bookings_to_calendar->provider == 'google_calendar' && $add_bookings_to_calendar->calendar_id ){
		$tp_locations['googlemeet_meeting']['is_connected'] = true;
		if( $add_bookings_to_calendar->calendar_account_status ){
			$tp_locations['googlemeet_meeting']['is_active'] = true;
		}
		$tp_locations['googlemeet_meeting']['is_connected_and_active'] = $tp_locations['googlemeet_meeting']['is_connected'] && $tp_locations['googlemeet_meeting']['is_active'];		
	}

	return $tp_locations;
}

function wpcal_get_booking_location_content($booking, $for, $provider='', $whos_view, $options=[]){
	if( !in_array($for, ['calendar_event']) ){
		return '';
	}

	$phone_html = true;
	if($for === 'calendar_event'){
		$phone_html = false;//currently not working in google_calendar
	}

	$booking_obj = wpcal_get_booking($booking);
	$location = $booking_obj->get_location();

	if( empty($location) || empty($location['type']) ){
		return '';
	}

	$label_of_location_types = [
		'zoom_meeting' => 'Zoom',
		'gotomeeting_meeting' => 'GoToMeeting',
		'googlemeet_meeting' => 'Google Hangout / Meet',
	];

	$line_break = "\n";

	$location_html = '';

	if( empty($location['form']['location']) && $location['type'] === 'googlemeet_meeting' && !empty($options['overide_location_if_empty']) ){
		$location['form']['location'] = $options['overide_location_if_empty'];
	}
	  
	if( in_array($location['type'],['zoom_meeting', 'gotomeeting_meeting', 'googlemeet_meeting']) && !empty($location['form']['location']) ) {

		$location_html .= '<b>'.$label_of_location_types[$location['type']].' Web Conference</b>';
		  $location_html .= $line_break . '<a href="'.$location['form']['location'].'">'.$location['form']['location'].'</a>';
		  $location_html .= $line_break . 'You can join from any device.';

	  if( !empty($location['form']['password_data']['password']) ) {
		  $password_label = $location['form']['password_data']['label'] ? $location['form']['password_data']['label'] : 'Password';
		$location_html .= $line_break . $password_label.': '.$location['form']['password_data']['password'];
	  }
	}
	elseif( $location['type'] === 'phone' && !empty($location['form']['location'])  && !empty($location['form']['who_calls']) ) {
		//$location_html .= '<b>Phone call</b>';
		$location_str = $booking_obj->get_location_str($whos_view, $phone_html);
		$location_html .= $line_break . $location_str;
	}
	elseif( in_array($location['type'],['physical', 'custom', 'ask_invitee']) && !empty($location['form']['location'])  ){
		$location_html .= '<b>'.$location['form']['location'].'</b>';

		if( in_array($location['type'],['physical', 'custom']) && !empty($location['form']['location_extra'])  ){
			$location_html .= $line_break . $location['form']['location_extra'];
		}
	}
	
	if( !empty($location_html) ){
		$location_html = 'Location: ' . $location_html;
	}
	return $location_html;
}

function wpcal_is_current_admin_owns_resource($resource_type, $resource_id, $on_error_throw=true){
	$admin_user_id = get_current_user_id();
	$is_owns = wpcal_is_admin_owns_resource($resource_type, $resource_id, $admin_user_id);
	if(!$is_owns && $on_error_throw){
		throw new WPCal_Exception('access_denied');
	}
	return $is_owns;
}

function wpcal_is_admin_owns_resource($resource_type, $resource_id, $admin_user_id){
	global $wpdb;

	$allowed_resource_types = [
		'service',
		'booking',
		'calendar_account',
		'tp_account'//integration
	];
	if( !in_array($resource_type, $allowed_resource_types, true) ){
		throw new WPCal_Exception('invalid_resource_type');
	}
	if( empty($resource_id) || !is_numeric($resource_id) ){
		throw new WPCal_Exception('invalid_resource_id');
	}
	if( empty($admin_user_id) || !is_numeric($admin_user_id) ){
		throw new WPCal_Exception('invalid_admin_user_id');
	}

	$admin_user = get_user_by( 'id', $admin_user_id );

	if( empty($admin_user) ||  !($admin_user instanceof WP_User) ){
		throw new WPCal_Exception('invalid_admin_user_id');
	}

	if ( !$admin_user->has_cap('activate_plugins') ) {
		throw new WPCal_Exception('user_doesnt_have_admin_rights');
	}

	if( $resource_type === 'service' ){
		try{
			$service_obj = new WPCal_Service($resource_id);
			$service_admin_id = $service_obj->get_owner_admin_id();
			$is_owns = ($service_admin_id == $admin_user_id);
			return $is_owns;
		}
		catch(WPCal_Exception $e){
			$error = $e->getError();
			if($error === 'service_id_not_exists'){
				throw new WPCal_Exception('resource_id_not_exists');
			}
			throw $e;
		}
	}
	elseif( $resource_type === 'booking' ){
		try{
			$booking_obj = new WPCal_Booking($resource_id);
			$booking_admin_id = $booking_obj->get_admin_user_id();
			$is_owns = ($booking_admin_id == $admin_user_id);
			return $is_owns;
		}
		catch(WPCal_Exception $e){
			$error = $e->getError();
			if($error === 'booking_id_not_exists'){
				throw new WPCal_Exception('resource_id_not_exists');
			}
			throw $e;
		}
	}
	elseif( $resource_type === 'calendar_account' ){
		$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
		$query = "SELECT `admin_user_id` FROM `$table_calendar_accounts` WHERE `id` = '".$resource_id."'";
		$calendar_account_details = $wpdb->get_row($query);
		if( $calendar_account_details === false ){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
		elseif( empty($calendar_account_details) ){
			throw new WPCal_Exception('resource_id_not_exists');
		}
		$calendar_account_admin_id = $calendar_account_details->admin_user_id;
		$is_owns = ($calendar_account_admin_id == $admin_user_id);
		return $is_owns;
	}
	elseif( $resource_type === 'tp_account' ){
		$table_tp_accounts= $wpdb->prefix . 'wpcal_tp_accounts';
		$query = "SELECT `admin_user_id` FROM `$table_tp_accounts` WHERE `id` = '".$resource_id."'";
		$tp_account_details = $wpdb->get_row($query);
		if( $tp_account_details === false ){
			throw new WPCal_Exception('db_error', '', $wpdb->last_error);
		}
		elseif( empty($tp_account_details) ){
			throw new WPCal_Exception('resource_id_not_exists');
		}
		$tp_account_admin_id = $tp_account_details->admin_user_id;
		$is_owns = ($tp_account_admin_id == $admin_user_id);
		return $is_owns;
	}
	return false;
}

function wpcal_dev_preview_all_emails(){


	$locations = [
		
		[
		  'type' => 'physical',
		  'form' => 
		  [
			'location' => 'City Center',
			'location_extra' => 'Mainland China, 3rd floor',
		  ],
		],

		[
			'type' => 'physical',
			'form' => 
			[
			  'location' => 'Besant Nagar Beach',
			  'location_extra' => '',
			],
		],
		
		[
		  'type' => 'phone',
		  'form' => 
		  [
			'who_calls' => 'admin',
			'location' => '+1 555 555 7890',
		  ],
		],

		[
			'type' => 'phone',
			'form' => 
			[
			  'who_calls' => 'invitee',
			  'location' => '+1 666 666 7890',
			],
		  ],
		
		[
		  'type' => 'googlemeet_meeting',
		  'form' => 
			[
				'location' => 'https://meet.google.com/aaa-bbbb-ccc',
			],	
		],
		
		[
		  'type' => 'zoom_meeting',
		  'form' => 
			[
				'location' => 'https://us04web.zoom.us/j/11112222333?pwd=cU11ZmFoRlFIV0lMZkgga25uSGV4dz09',
				'password_data' => 
				[
					'label' => 'Password',
					'password' => '78hhjCp'
				],	

			],	
		],
		
		[
		  'type' => 'gotomeeting_meeting',
		  'form' => 
			[
				'location' => 'https://global.gotomeeting.com/join/1111222333',
			],	
		],
		
		[
		  'type' => 'custom',
		  'form' => 
		  [
			'location' => 'Skype',
			'location_extra' => 'Skype ID: eureka',
		  ],
		],

		[
			'type' => 'custom',
			'form' => 
			[
			  'location' => 'Yahoo Messenger',
			  'location_extra' => '',
			],
		  ],
		
		[
		  'type' => 'ask_invitee',
		  'form' => 
		  [
			'location' => '',
			'location_extra' => 'Anywhere within chennai.',
		  ],
		],
	];

	$services = wpcal_get_services_of_current_admin();
	if( empty($services) ){
		echo 'Current admin should have atleast one active Event Type to see the preview';
		return;
	}
	$service_id = $services[0]->id;

	$booking_from = new DateTime('now', wp_timezone() );
	$booking_from->add( new DateInterval('P1D') );
	$booking_from->setTime(10, 15, 0);
	$booking_to = clone $booking_from;
	$booking_to->setTime(10, 45, 0);

	$booking_obj = new WPCal_Booking(0);
	$booking_obj->set_service_id($service_id);
	$booking_obj->set_status(1);
	$booking_obj->set_unique_link('593cd87bcd498f41231de722f9425bd65a9929fd');
	$booking_obj->set_admin_user_id(wp_get_current_user()->ID);
	$booking_obj->set_invitee_wp_user_id('');
	$booking_obj->set_invitee_name('John Doe');
	$booking_obj->set_invitee_email('john@example.com');
	$booking_obj->set_invitee_tz('Asia/Kolkata');
	$booking_obj->set_invitee_question_answers([]);
	$booking_obj->set_booking_from_time($booking_from->format('U'));
	$booking_obj->set_booking_to_time($booking_to->format('U'));
	$booking_obj->set_booking_ip('127.0.0.1');
	$booking_obj->set_location([]);

	$mail_categories = [
		'new_booking' => [
			'send_admin_new_booking_info',
			'send_invitee_booking_confirmation',
			'send_invitee_booking_reminder'
		],
		'reschedule_booking' => [
			'send_admin_reschedule_booking_info',
			'send_invitee_reschedule_booking_confirmation',
		],
		'cancel_booking' => [
			'send_admin_booking_cancellation',
			'send_invitee_booking_cancellation',
		],
	];

	WPCal_Mail::$dev_preview = true;

	foreach( $locations as $location){
		var_dump($location);
	//foreach($mail_categories as $mail_category){
		$mail_category = 'new_booking';		
		$mail_category_data = $mail_categories[$mail_category];
		foreach($mail_category_data as $mail_type){
			echo $mail_category .'<br>--------------------------<br>';
			echo $mail_type .'<br>--------------------------<br>';
			$booking_obj->set_location(json_encode($location));
			call_user_func(array('WPCal_Mail', $mail_type), $booking_obj);
		}
	//}
	}

	// foreach( $locations as $location){
	// 	$booking_obj->set_location($location);
	// 	$mail_type = '';
	// 	call_user_func(array(WPCal_Mail, $mail_type), $booking_obj);
	// }

}