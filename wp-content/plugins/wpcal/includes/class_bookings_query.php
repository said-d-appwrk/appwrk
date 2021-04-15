<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Bookings_Query{
	private $service_obj;

	private static $total_bookings = '';

	public function __construct(){
		
	}

	public static function get_past_bookings_for_admin_client($options){
		$args = array();
		$args['order_by'] = 'booking_from_time';
		$args['order'] = 'DESC';

		$args['to_time'] = WPCal_DateTime_Helper::unix_to_DateTime_obj($options['view_base_timing']);

		$result =  self::get_upcoming_and_past_bookings_for_admin_client($options, $args);
		return $result;
	}

	public static function get_upcoming_bookings_for_admin_client($options){
		$args = array();
		$args['order_by'] = 'booking_from_time';
		$args['order'] = 'ASC';

		$args['to_time_greater_than'] = WPCal_DateTime_Helper::unix_to_DateTime_obj($options['view_base_timing']);

		$result =  self::get_upcoming_and_past_bookings_for_admin_client($options, $args);
		return $result;
	}

	private static function get_upcoming_and_past_bookings_for_admin_client($options, $_args){
		global $wpdb;

		if( !is_numeric($options['page']) ){
			$options['page'] = 0;
		}else{
			$options['page'] = (int) $options['page'];
		}

		if( !isset($options['view_base_timing']) || !is_numeric($options['view_base_timing']) ){
			$options['view_base_timing'] = time();
		}else{
			$options['view_base_timing'] = (int) $options['view_base_timing'];
		}

		$args = array();

		$args['admin_user_id'] = get_current_user_id();

		$args['select_cols'] = [
			'id',
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
			'updated_ts'
		];

		$args['get_service_details'] = [
			'name',
			'color'
		];

		$args['status'] = 1;
		$args['order_by'] = 'booking_from_time';
		$args['order'] = 'ASC';
		$args['page'] = $options['page'];
		$args['items_per_page'] = 10;
		$args['get_plain_data'] = true;

		$args = array_merge($args, $_args);

		$result = array();
		$result['bookings'] = self::_get_bookings($args);
		$result['page'] = $args['page'];

		$total_items = self::$total_bookings;

		$result['eol'] = !($total_items > ($args['items_per_page'] * ($result['page'] + 1) ) );


		return $result;
	}

	public static function get_bookings_for_day_by_service(WPCal_Service $service_obj, $date_obj, $exclude_booking_id=null){

		$args = array();
		$args['from_time'] = clone $date_obj;
		$args['calc_to_time_by_from_time'] = '+1 day';
		$args['service_id'] = $service_obj->get_id();
		$args['service_obj'] = $service_obj;
		$args['get_slot_obj_by'] = 'service_obj';
		$args['status'] = 1;

		if(!empty($exclude_booking_id) && is_numeric($exclude_booking_id)){
			$args['exclude_booking_id'] = $exclude_booking_id;
		}

		return self::_get_bookings($args);
	}

	public static function get_bookings_for_day_by_admin_and_exclude_service(WPCal_Service $exclude_service_obj, $date_obj, $admin_id){

		$args = array();
		$args['from_time'] = clone $date_obj;
		$args['calc_to_time_by_from_time'] = '+1 day';
		$args['exclude_service_id'] = $exclude_service_obj->get_id();
		$args['get_slot_obj_by'] = 'respective_service_obj';
		$args['admin_user_id'] = $admin_id;
		$args['status'] = 1;

		return self::_get_bookings($args);
	}

	private static function _get_bookings($args){
		/**
		 * 'from_time', 'to_time' date objects from_time always >= is used, to_time always < is used
		 * 'get_slot_obj_by' => 'service_obj' (get from $args['service_obj']) | 'respective_service_obj' get on the go
		 */
		$default_args = array(
			'status' => 1,
		);

		$args = array_merge($default_args, $args);

		global $wpdb;
		$table_wpcal_bookings = $wpdb->prefix . 'wpcal_bookings';

		$after_select = '';

		if(isset($args['items_per_page']) && is_int($args['items_per_page']) && $args['items_per_page'] > 0){
			$after_select = 'SQL_CALC_FOUND_ROWS';
		}

		$select_cols = array('id', 'service_id', 'booking_from_time', 'booking_to_time');

		if(!empty($args['select_cols']) ){
			$select_cols = $args['select_cols']; 
		}

		$query = "SELECT $after_select ". '`'. implode('`, `', $select_cols) . '`' ." FROM `$table_wpcal_bookings` ";
		$query_where = " WHERE 1=1 ";

		if(isset($args['from_time'])){
			$query_where.= " AND `booking_from_time` >=  '".WPCal_DateTime_Helper::DateTime_Obj_to_unix($args['from_time'])."'";
		}

		if(isset($args['calc_to_time_by_from_time']) && isset($args['from_time'])){
			$args['to_time'] = clone $args['from_time'];
			$args['to_time']->modify($args['calc_to_time_by_from_time']);
		}

		if(isset($args['to_time'])){
			$query_where.= " AND `booking_to_time` <  '". WPCal_DateTime_Helper::DateTime_Obj_to_unix($args['to_time'])."'";
		}

		if(isset($args['to_time_greater_than'])){
			$query_where.= " AND `booking_to_time` >=  '". WPCal_DateTime_Helper::DateTime_Obj_to_unix($args['to_time_greater_than'])."'";
		}

		if(isset($args['service_id'])){
			$query_where.= " AND `service_id` = '". $args['service_id'] . "'";
		}

		if(isset($args['exclude_service_id'])){
			$query_where.= " AND `service_id` != '". $args['exclude_service_id'] . "'";
		}

		if(isset($args['admin_user_id'])){
			$query_where.= " AND `admin_user_id` = '". $args['admin_user_id'] . "'";
		}

		if(isset($args['exclude_booking_id'])){
			$query_where.= " AND `id` != '". $args['exclude_booking_id'] . "'";
		}

		if(isset($args['status'])){
			$query_where.= " AND `status` = '". $args['status'] . "'";
		}

		$query_order = "";

		if(isset($args['order_by'])){
			$order = "ASC";
			$order_by = $args['order_by'];

			if( isset($args['order']) && in_array($args['order'], array('ASC', 'DESC'))){
				$order = $args['order'];
			}
			$query_order = " ORDER BY `".$order_by."` $order";
		}

		// $args['order_by'] = 'booking_from_time';
		// $args['order'] = 'ASC';

		$query_limit = "";
		$offset = 0;
		$page = 0;
		if(isset($args['items_per_page']) && is_int($args['items_per_page']) && $args['items_per_page'] > 0){
			$limit = $args['items_per_page'];		

			if(isset($args['page'])){
				$page = $args['page'];
			}

			$offset = $limit * $page;

			$query_limit = " LIMIT ".$limit." OFFSET ".$offset;
		}

		

		$query = $query . $query_where . $query_order . $query_limit;

		$results = $wpdb->get_results($query);
		self::$total_bookings = $wpdb->get_var("SELECT FOUND_ROWS()");
		if( empty($results) ){
			return array();
		}

		$cached_service_obj = array();

		if( !empty($args['get_service_details']) && is_array($args['get_service_details']) ){
			$allowed_service_details = ['name', 'color'];
		
			if(!wpcal_is_subset($allowed_service_details, $args['get_service_details'])){
				throw new WPCal_Exception('invalid_service_details');
			}
		}

		foreach($results as $key => $row){

			if( !empty($args['get_service_details']) && is_array($args['get_service_details']) ){
				if( !isset($cached_service_obj[$row->service_id])){
					$cached_service_obj[$row->service_id] = new WPCal_Service($row->service_id);
				}
				$service_obj = $cached_service_obj[$row->service_id];
				$booking_service_data = new stdClass();
				foreach($args['get_service_details'] as $_service_feild){
					$booking_service_data->{$_service_feild} = call_user_func([$service_obj, 'get_'.$_service_feild]);
				}
				$results[$key]->service_details = $booking_service_data;
			}

			if( !isset($args['get_plain_data']) || !$args['get_plain_data'] ){
				$results[$key]->booking_from_time = WPCal_DateTime_Helper::unix_to_DateTime_obj($row->booking_from_time);
				$results[$key]->booking_to_time = WPCal_DateTime_Helper::unix_to_DateTime_obj($row->booking_to_time);
			}
			
			if( isset( $args['get_slot_obj_by']) ){
				if( $args['get_slot_obj_by'] === 'service_obj'){
					$service_obj = $args['service_obj'];
				}
				elseif( $args['get_slot_obj_by'] === 'respective_service_obj'){
					if( !isset($cached_service_obj[$row->service_id])){
						$cached_service_obj[$row->service_id] = new WPCal_Service($row->service_id);
					}
					$service_obj = $cached_service_obj[$row->service_id];
				}
				$results[$key]->slot_obj = new WPCal_Slot($service_obj, $results[$key]->booking_from_time, $results[$key]->booking_to_time);
			}

			if( isset( $row->invitee_question_answers) ){
				if( !empty($row->invitee_question_answers) ){
					$results[$key]->invitee_question_answers = json_decode($row->invitee_question_answers, true);
				}
				if( !is_array($results[$key]->invitee_question_answers) ){
					$results[$key]->invitee_question_answers = [];
				}
			}
			if( isset( $row->location) ){
				if( !empty($row->location) ){
					$results[$key]->location = json_decode($row->location, true);
				}
				if( !is_array($results[$key]->location) ){
					$results[$key]->location = [];
				}
			}
		}

		return $results;
	}
}