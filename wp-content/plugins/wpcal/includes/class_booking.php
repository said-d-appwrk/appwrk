<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Booking{

	private $id = null;

	private $default_data = array(
		'name' => ''
	);

	private $data = array(
		'service_id' => '',
		'status' => '',
		'unique_link' => '',
		'admin_user_id' => '',
		'invitee_wp_user_id' => '',
		'invitee_name' => '',
		'invitee_email' => '',
		'invitee_tz' => '',
		'invitee_question_answers' => '',
		'booking_from_time' => '',
		'booking_to_time' => '',
		'booking_ip' => '',
		'location' => '',
		'page_used_for_booking' => '',
		'event_added_calendar_id' => '',
		'event_added_tp_cal_id' => '',
		'event_added_tp_event_id' => '',
		'meeting_tp_resource_id' => '',
		'rescheduled_booking_id' => '',
		'reschedule_cancel_reason' => '',
		'reschedule_cancel_user_id' => '',
		'reschedule_cancel_action_ts' => '',
		'added_ts' => '',
		'updated_ts' => '',
	);

	private $_service_obj;

	public function __construct($id=0){
		if ( is_numeric( $id ) && $id > 0 ) {
			$this->set_id( $id );
		}

		if ( $this->get_id() > 0 ) {
			$this->load();
		}
	}

	public function set_id($id){
		$this->id = $id;
	}

	public function get_id(){
		return $this->id;
	}

	public function load(){
		global $wpdb;
		$table = $wpdb->prefix . 'wpcal_bookings';
		$query = "SELECT * FROM `$table` WHERE id = '". $this->get_id() . "'";
		$result = $wpdb->get_row($query);
		if( empty($result) ){
			throw new WPCal_Exception('booking_id_not_exists');
		}

		foreach( $result as $prop => $value ){
			if( is_string($prop) && isset( $this->data[$prop] ) && ( method_exists($this, 'set_'.$prop) || $this->can_call('set_'.$prop) ) ){
				$this->{'set_'.$prop}($value);
			}
		}
	}

	public function can_call(string $method_name){
		static $get_allowed_keys = [
			'service_id',
			'status',
			'unique_link',
			'admin_user_id',
			'invitee_wp_user_id',
			'invitee_name',
			'invitee_email',
			'invitee_tz',
			'invitee_question_answers',
			'booking_from_time',
			'booking_to_time',
			'booking_ip',
			'location',
			'page_used_for_booking',
			'event_added_calendar_id',
			'event_added_tp_cal_id',
			'event_added_tp_event_id',
			'meeting_tp_resource_id',
			'rescheduled_booking_id',
			'reschedule_cancel_reason',
			'reschedule_cancel_user_id',
			'reschedule_cancel_action_ts',
		];
		static $set_allowed_keys = [
			'service_id',
			'status',
			'unique_link',
			'admin_user_id',
			'invitee_wp_user_id',
			'invitee_name',
			'invitee_email',
			'invitee_tz',
			//'invitee_question_answers',
			// 'booking_from_time',
			// 'booking_to_time',
			'booking_ip',
			'location',
			'page_used_for_booking',
			'event_added_calendar_id',
			'event_added_tp_cal_id',
			'event_added_tp_event_id',
			'meeting_tp_resource_id',
			'rescheduled_booking_id',
			'reschedule_cancel_reason',
			'reschedule_cancel_user_id',
			'reschedule_cancel_action_ts',
		];


		$method_name_parts = explode('_', $method_name, 2);
		if(
			count($method_name_parts) !== 2 || 
			!in_array($method_name_parts[0], array('get', 'set'))
		){
			return false;
		}
		if( $method_name_parts[0] === 'get' && !in_array($method_name_parts[1], $get_allowed_keys)){
			return false;			
		}
		if( $method_name_parts[0] === 'set' && !in_array($method_name_parts[1], $set_allowed_keys)){
			return false;
		}

		return true;
	}

	public function __call(string $method_name, $args){
		$trace = debug_backtrace();

		if(!$this->can_call($method_name)){
			try{
				throw new BadMethodCallException();
			}
			catch(BadMethodCallException $e){
				trigger_error('Undefined method  '. get_class($this) .'::' . $method_name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], E_USER_ERROR);
				//trigger_error('Undefined method  ' . $method_name . ' in ' . $e->getFile() . ' on line ' . $e->getLine(), E_USER_ERROR);
			}
		}

		$method_name_parts = explode('_', $method_name, 2);//this should be sync with can_call check
		

		if( $method_name_parts[0] === 'get' ){
			return $this->get_prop($method_name_parts[1]);
		}
		elseif( $method_name_parts[0] === 'set' ){
			return $this->set_prop($method_name_parts[1], $args[0]);
		}
	}

	public function __get($name){

		if($name === 'service_obj'){
			if($this->_service_obj){
				return $this->_service_obj;
			}
			elseif($this->get_service_id()){
				$this->_service_obj = new WPCal_Service($this->get_service_id());
				return $this->_service_obj;
			}
			else{
				throw new WPCal_Exception('invalid_service_id');
			}
		}
	}

	private function set_prop($prop, $value){
		if( isset( $this->data[$prop] ) ){
			$this->data[$prop] = $value;
		}
	}

	public function set_booking_from_time($value){
		$value = WPCal_DateTime_Helper::unix_to_DateTime_obj($value);
		$this->set_prop('booking_from_time', $value);
	}

	public function set_booking_to_time($value){
		$value = WPCal_DateTime_Helper::unix_to_DateTime_obj($value);
		$this->set_prop('booking_to_time', $value);
	}

	public function set_invitee_question_answers($value){
		if(!empty($value)){			
			$value = json_decode($value, true);
		}
		if( !is_array($value) ){	
			$value = [];
		}
		return $this->set_prop('invitee_question_answers', $value);
	}

	public function set_page_used_for_booking($value){
		if(!empty($value)){
			$value = json_decode($value, true);
		}
		if( !is_array($value) ){
			$value = [];
		}
		return $this->set_prop('page_used_for_booking', $value);
	}

	public function set_location($value){
		if(!empty($value)){
			$value = json_decode($value, true);
		}
		if( !is_array($value) ){
			$value = [];
		}
		return $this->set_prop('location', $value);
	}

	public function get_duration(){
		$from_ts = WPCal_DateTime_Helper::DateTime_Obj_to_unix($this->get_booking_from_time());
		$to_ts = WPCal_DateTime_Helper::DateTime_Obj_to_unix($this->get_booking_to_time());
		$duration_sec = $to_ts - $from_ts;
		$duration = round($duration_sec / 60);//should always be 0, round not required just additional safety
		return $duration;
	}

	public function get_plain_booking_from_time(){
		$value = $this->get_prop('booking_from_time');
		return WPCal_DateTime_Helper::DateTime_Obj_to_unix($value);
	}

	public function get_plain_booking_to_time(){
		$value = $this->get_prop('booking_to_time');
		return WPCal_DateTime_Helper::DateTime_Obj_to_unix($value);
	}

	
	// public function set_event_buffer_after($value){
	// 	$value = WPCal_DateTime_Helper::get_DateInterval_obj($value);
	// 	return $this->set_prop('event_buffer_after', $value);
	// }

	private function get_prop($prop){
		if( isset( $this->data[$prop] ) ){
			return $this->data[$prop];
		}
	}	

	private function get_plain_prop($prop){
		if( isset( $this->data[$prop] ) ){
			if(method_exists($this, 'get_plain_'.$prop)){
				return call_user_func(array($this, 'get_plain_'.$prop));
			}			
			return $this->data[$prop];
		}
	}

	public function get_plain_data(){
		$_data = [];
		foreach($this->data as $prop => $value){
			$_data[$prop] = $this->get_plain_prop($prop);
		}
		return $_data;
	}

	public function get_data_for_admin_client(){
		$_data = $this->get_plain_data();
		$allowed_keys = array(
			'service_id',
			'status',
			'unique_link',
			'admin_user_id',
			'invitee_wp_user_id',
			'invitee_name',
			'invitee_email',
			'invitee_tz',
			'invitee_question_answers',
			'booking_from_time',
			'booking_to_time',
			'booking_ip',
			'location',
			'rescheduled_booking_id',
			'reschedule_cancel_reason',
			'reschedule_cancel_user_id',
			'reschedule_cancel_action_ts',
		);

		$data_for_admin_client =  wpcal_get_allowed_fields($_data, $allowed_keys);
		return $data_for_admin_client;
	}

	public function get_data_for_user_client(){
		$_data = $this->get_plain_data();
		$allowed_keys = array(
			'service_id',
			'status',
			'unique_link',
			'admin_user_id',
			'invitee_wp_user_id',
			'invitee_name',
			'invitee_email',
			'invitee_tz',
			'invitee_question_answers',
			'booking_from_time',
			'booking_to_time',
			'location',
			'rescheduled_booking_id',
			'reschedule_cancel_action_ts',
		);

		$data_for_user_client =  wpcal_get_allowed_fields($_data, $allowed_keys);
		$data_for_user_client['rescheduled_booking_unique_link'] = null;
		if(!empty($data_for_user_client['rescheduled_booking_id'])){
			$data_for_user_client['rescheduled_booking_unique_link'] = wpcal_get_booking_unique_link_by_id($data_for_user_client['rescheduled_booking_id']);
		}

		$is_invitee_alert_by_calendar = WPCal_Background_Tasks::is_task_exists_by_main_args('add_or_update_booking_to_tp_calendar', 'booking_id', $this->get_id());
		$data_for_user_client['is_invitee_alert_by_calendar'] = $is_invitee_alert_by_calendar;

		$data_for_user_client['add_event_to_google_calendar_url'] = $this->get_add_event_to_google_calendar_url();	
		$data_for_user_client['download_ics_url'] = $this->get_download_ics_url();

		return $data_for_user_client;
	}

	public function is_active(){
		$status = $this->get_status();
		if( $status == '1' ){
			return true;
		}
		return false;
	}

	public function is_cancelled(){
		$status = $this->get_status();
		if( $status == '-1' || $status == '-2' ){
			return true;
		}
		return false;
	}

	public function is_rescheduled(){
		$status = $this->get_status();
		if( $status == '-5' ){
			return true;
		}
		return false;
	}

	private function _get_redirect_link($action){
		$site_url = trailingslashit(site_url());
		$unique_link = $this->get_unique_link();
		$url = $site_url.'?wpcal_action='.$action.'&booking_id='.$unique_link;
		return $url;
	}

	public function get_redirect_view_url(){
		$action = 'booking_view';
		return $this->_get_redirect_link($action);
	}

	public function get_redirect_reschedule_url(){
		$action = 'booking_reschedule';
		return $this->_get_redirect_link($action);
	}

	public function get_redirect_cancel_url(){
		$action = 'booking_cancel';
		return $this->_get_redirect_link($action);
	}

	public function get_add_event_to_google_calendar_url(){
		$action = 'booking_tp_add_event';
		$url = $this->_get_redirect_link($action).'&tp=google_calendar';
		return $url;
	}

	public function get_download_ics_url(){
		$action = 'booking_tp_add_event';
		$url = $this->_get_redirect_link($action).'&tp=ics';
		return $url;
	}

	public function get_redirect_meeting_url(){
		if( !$this->is_location_needs_online_meeting() ){
			return false;
		}
		$action = 'booking_meeting_redirect';
		$url = $this->_get_redirect_link($action);
		return $url;
	}

	public function is_booking_mail_sent_by_type($type){
		$result = WPCal_Background_Tasks::is_task_completed_by_main_args($type, 'booking_id', $this->get_id());
		return $result;
	}

	public function is_location_needs_tp_account_service(){
		$tp_accounts_used_for_location = [
			'zoom_meeting',
			'gotomeeting_meeting',
		];//no 'googlemeet_meeting' because it uses google_calendar api (additional info: also we need that calendar set as "add booking to" calendar).
		$location_type = $this->get_location_type();
		if( in_array($location_type, $tp_accounts_used_for_location,  true)){
			return true;
		}
		return false;
	}

	public function is_location_needs_online_meeting(){
		$locations_needs_online_meeting = [
			'zoom_meeting',
			'googlemeet_meeting',
			'gotomeeting_meeting',
		];
		$location_type = $this->get_location_type();
		if( in_array($location_type, $locations_needs_online_meeting,  true)){
			return true;
		}
		return false;
	}

	public function get_location_type(){
		$location = $this->get_location();
		if( empty($location) || !isset($location['type']) ){
			return '';
		}
		return $location['type'];
	}

	public function get_location_str($whos_view='neutral', $html=false){
		$location = $this->get_location();
		if( empty($location) || !isset($location['type']) || empty($location['form']['location']) ){
			return '';
		}
		
		if( $location['type'] == 'phone' ){
			$location_str = '';
			$admin_details = wpcal_get_admin_details($this->get_admin_user_id());
			$admin_name = $admin_details['display_name'];
			$invitee_name = $this->get_invitee_name();

			if ( $whos_view == 'admin') {
				if ($location['form']['who_calls'] == "invitee") {
					$location_str = $invitee_name . " will call you on ";
					$location_str .= $location['form']['location'];
				} else if ($location['form']['who_calls'] == "admin") {
					$location_str = "You will call " . $invitee_name . " on ";
					$location_str .= $html ? wpcal_get_phone_link($location['form']['location']) : $location['form']['location'];
				}
			}
			else if( $whos_view == 'user'){
				if ($location['form']['who_calls'] == "invitee") {
					$location_str = "You will call " . $admin_name . " on ";
					$location_str .= $html ? wpcal_get_phone_link($location['form']['location']) : $location['form']['location'];
				} else if ($location['form']['who_calls'] == "admin") {
					$location_str = $admin_name . " will call you on ";
					$location_str .= $location['form']['location'];
				}
			}
			else{// $whos_view == 'neutral'
				if ($location['form']['who_calls'] == "invitee") {
					$location_str = $invitee_name ." will call " . $admin_name . " on ";
				} else if ($location['form']['who_calls'] == "admin") {
					$location_str = $admin_name . " will call ". $invitee_name ." on ";
				}
				$location_str .= $html ? wpcal_get_phone_link($location['form']['location']) : $location['form']['location'];
			}
			return $location_str;
		}

		return $location['form']['location'];
	}

}
