<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Cron{
	private static $delete_old_booking_slots_cache_n_days_before = 2;
	private static $delete_old_tp_calendar_events_n_days_before = 2;
	private static $delete_old_service_custom_availability_n_days_before = 31;
	//private static $is_cron_running = false;
	private static $run_full_now = false;

	public static function init(){
		add_filter( 'cron_schedules', 'WPCal_Cron::add_wp_cron_intervals' );
		add_action( 'wpcal_api_task_cron', 'WPCal_Cron::run_api_tasks' );
		add_action( 'wpcal_local_task_cron', 'WPCal_Cron::run_local_tasks' );
		add_action( 'wpcal_misc_cron', 'WPCal_Cron::run_misc_tasks' );
		self::may_add_wp_cron_schedules();	
	}

	public static function on_plugin_deactivate(){
		self::remove_wp_cron_schedules();
	}

	public static function add_wp_cron_intervals($schedules){
		$schedules['wpcal_hourly'] = [
			'interval' => 60 * 60,
			'display'  => esc_html__( 'Every Hour' )
		];
		$schedules['wpcal_every_5_mins'] = [
			'interval' => 5 * 60,
			'display'  => esc_html__( 'Every Five Mintues' )
		];
		$schedules['wpcal_every_12_hours'] = [
			'interval' => 60 * 60 * 12,
			'display'  => esc_html__( 'Every Fifteen Mintues' )
		];
		return $schedules;
	}

	public static function may_add_wp_cron_schedules(){
		if ( ! wp_next_scheduled( 'wpcal_api_task_cron' ) ) {
			wp_schedule_event( time(), 'wpcal_every_5_mins', 'wpcal_api_task_cron' );
		}

		if ( ! wp_next_scheduled( 'wpcal_local_task_cron' ) ) {
			wp_schedule_event( time(), 'wpcal_hourly', 'wpcal_local_task_cron' );
		}

		if ( ! wp_next_scheduled( 'wpcal_misc_cron' ) ) {
			wp_schedule_event( time(), 'wpcal_every_12_hours', 'wpcal_misc_cron' );
		}
	}

	public static function remove_wp_cron_schedules(){
		$timestamp = wp_next_scheduled( 'wpcal_api_task_cron' );
		wp_unschedule_event( $timestamp, 'wpcal_api_task_cron' );

		$timestamp = wp_next_scheduled( 'wpcal_local_task_cron' );
		wp_unschedule_event( $timestamp, 'wpcal_local_task_cron' );

		$timestamp = wp_next_scheduled( 'wpcal_misc_cron' );
		wp_unschedule_event( $timestamp, 'wpcal_misc_cron' );
	}

	public static function run_api_tasks(){
		WPCal_Background_Tasks::run_tasks();
		wpcal_reset_stuck_tp_calendar_sync_events_task();
		self::sync_tp_calendars_and_events();
	}

	public static function run_local_tasks(){
		update_option('wpcal_last_local_task_started_time', time());//using wp option functions for transactional checking is not reliable because of cache, here only purpose is logging
		self::delete_old_tp_calendar_events();
		self::delete_old_booking_slots_cache();
		self::delete_old_service_custom_availability();
	}

	public static function run_misc_tasks(){
		WPCal_License::check_validity();
	}

	public static function set_run_full_now(Bool $v){
		self::$run_full_now = $v;
	}

	//--------------------calendar API related below--------------------

	public static function sync_tp_calendars_and_events(){
		$admin_user_ids = self::_get_admin_user_ids_of_conflict_calendars();
		foreach($admin_user_ids as $admin_user_id){
			self::sync_tp_calendars_and_events_by_admin($admin_user_id);
		}
	}

	private static function is_admin_having_active_service($admin_user_id){
		//any one the service should have service->status == 1 and service max date in future

		$options = [
			'admin_user_id' => $admin_user_id,
			'statuses' => 1
		];

		$services = wpcal_get_services($options);

		if( empty($services) ){
			return false;
		}

		$today = WPCal_DateTime_Helper::now_DateTime_obj();
		$today->setTime(0, 0, 0);

		foreach($services as $service){
			try{
				$service_obj = wpcal_get_service($service->id);

				$service_availability_details_obj = new WPCal_Service_Availability_Details($service_obj);

				$default_availability_details = $service_availability_details_obj->get_default_availability();

				$max_date = $default_availability_details->get_max_date();

				$max_date_buffer = clone $max_date;
				$max_date_buffer->add(new DateInterval('P2D'));//for safety reason

				if( $today <= $max_date_buffer ){
					return true;
				}
			}
			catch(WPCal_Exception $e){
				//benefit of doubt
				return true;
			}
		}
		return false;
	}

	private static function _get_admin_user_ids_of_conflict_calendars(){
		global $wpdb;
		$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
		$table_calendars = $wpdb->prefix . 'wpcal_calendars';

		//$query = "SELECT DISTINCT `calendar_accounts`.`admin_user_id` FROM `$table_calendars` as `calendars` JOIN `$table_calendar_accounts` as `calendar_accounts` ON `calendar_accounts`.`id` = `calendars`.`calendar_account_id` WHERE `calendar_accounts`.`status` = '1' AND `calendars`.`status` = '1' AND `calendars`.`is_conflict_calendar` = '1'";

		//GET ORDER BY list_events_sync_last_update_ts UNIQUE admin_user_id
		$query = "SELECT `calendar_accounts`.`admin_user_id`, min(`calendars`.`min_list_events_sync_last_update_ts`) as `admin_min_last_update_ts`  FROM `$table_calendar_accounts` as `calendar_accounts` INNER JOIN (
			SELECT `calendar_account_id`, min(`list_events_sync_last_update_ts`) as `min_list_events_sync_last_update_ts`
			FROM `$table_calendars`
			WHERE `status` = '1'
			AND `is_conflict_calendar` = 1
			AND (list_events_sync_status IS NULL OR list_events_sync_status = 'completed')
			GROUP BY `calendar_account_id`
		) `calendars`
		ON `calendar_accounts`.`id` = `calendars`.`calendar_account_id` 
		WHERE `calendar_accounts`.`status` = '1'
		GROUP BY `calendar_accounts`.`admin_user_id`
		ORDER BY `admin_min_last_update_ts` ASC";

		$admin_user_ids = $wpdb->get_col($query);
	
		if( empty($admin_user_ids) ){
			return [];
		}
		return $admin_user_ids;
	}

	private static function _get_calendar_account_details_of_conflict_calendars_by_admin($admin_user_id){
		global $wpdb;
		$table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
		$table_calendars = $wpdb->prefix . 'wpcal_calendars';

		//$query = "SELECT DISTINCT `calendar_accounts`.`id`, `calendar_accounts`.`provider` FROM `$table_calendars` as `calendars` JOIN `$table_calendar_accounts` as `calendar_accounts` ON `calendar_accounts`.`id` = `calendars`.`calendar_account_id` WHERE `calendar_accounts`.`status` = '1' AND `calendars`.`status` = '1' AND `calendars`.`is_conflict_calendar` = '1' AND `calendar_accounts`.`admin_user_id` = '".$admin_user_id."'";

		//GET ORDER BY list_events_sync_last_update_ts UNIQUE calendar_account_id
		$query = "SELECT `calendar_accounts`.`id`, `calendar_accounts`.`provider`, min(`calendars`.`min_list_events_sync_last_update_ts`) as `admin_min_last_update_ts`  FROM `$table_calendar_accounts` as `calendar_accounts` INNER JOIN (
			SELECT `calendar_account_id`, min(`list_events_sync_last_update_ts`) as `min_list_events_sync_last_update_ts`
			FROM `$table_calendars`
			WHERE `status` = '1'
			AND `is_conflict_calendar` = 1
			AND (list_events_sync_status IS NULL OR list_events_sync_status = 'completed')
			GROUP BY `calendar_account_id`
		) `calendars`
		ON `calendar_accounts`.`id` = `calendars`.`calendar_account_id` 
		WHERE `calendar_accounts`.`status` = '1' AND `calendar_accounts`.`admin_user_id` = '".$admin_user_id."'
		GROUP BY `calendar_accounts`.`id`
		ORDER BY `admin_min_last_update_ts` ASC";

		$calendar_accounts = $wpdb->get_results($query);
	
		if( empty($calendar_accounts) ){
			return [];
		}
		return $calendar_accounts;
	}

	public static function sync_tp_calendars_and_events_by_admin($admin_user_id){

		$is_admin_having_active_service = self::is_admin_having_active_service($admin_user_id);
		if(!$is_admin_having_active_service){
			return;
		}
		
		$calendar_accounts = self::_get_calendar_account_details_of_conflict_calendars_by_admin($admin_user_id);

		foreach($calendar_accounts as $calendar_account){
			self::sync_tp_calendars_and_events_by_calendar_account($calendar_account->id, $calendar_account->provider);
			wpcal_is_time_out() ? exit() : '';
		}
	}

	private static function sync_tp_calendars_and_events_by_calendar_account($calendar_account_id, $provider){
		try{
			$tp_calendar_class = wpcal_include_and_get_tp_calendar_class($provider);
			$tp_calendar_obj = new $tp_calendar_class($calendar_account_id);
			if(self::$run_full_now){
				$tp_calendar_obj->api_refresh_calendars();
				$tp_calendar_obj->refresh_events_for_all_conflict_calendars();
			}
			else{
				$tp_calendar_obj->may_api_refresh_calendars();
				$tp_calendar_obj->may_refresh_events_for_all_conflict_calendars();
			}
		}
		catch(Google_Service_Exception $e){
		}
		catch(WPCal_Exception $e){
		}
		catch(Exception $e){
		}
	}

	//--------------------calendar API related above--------------------

	public static function delete_old_tp_calendar_events(){
		global $wpdb;

		$limit_per_delete = 1000;

		$n_days_before = self::get_n_days_before(self::$delete_old_tp_calendar_events_n_days_before);
		
		$n_days_before_ts = WPCal_DateTime_Helper::DateTime_Obj_to_unix($n_days_before);

		$table_calendar_events = $wpdb->prefix . 'wpcal_calendar_events';

		$query = "DELETE FROM `$table_calendar_events` WHERE `to_time` < '".$n_days_before_ts."' LIMIT $limit_per_delete";

		$result = $wpdb->query($query);
		var_dump($result);
		if($result === false){
			//handle error
			return false;
		}
		return true;
	}

	public static function delete_old_booking_slots_cache(){
		global $wpdb;

		$limit_per_delete = 1000;

		$n_days_before = self::get_n_days_before(self::$delete_old_booking_slots_cache_n_days_before);
		
		$n_days_before_str = WPCal_DateTime_Helper::DateTime_Obj_to_Date_DB($n_days_before);

		$table_service_availability_slots_cache = $wpdb->prefix . 'wpcal_service_availability_slots_cache';

		$query = "DELETE FROM `$table_service_availability_slots_cache` WHERE `availability_date` < '".$n_days_before_str."' LIMIT $limit_per_delete";

		$result = $wpdb->query($query);
		var_dump($result);
		if($result === false){
			//handle error
			return false;
		}
		return true;
	}

	public static function delete_old_service_custom_availability(){
		global $wpdb;

		$limit_per_delete = 1000;

		$n_days_before = self::get_n_days_before(self::$delete_old_service_custom_availability_n_days_before);
		
		$n_days_before_str = WPCal_DateTime_Helper::DateTime_Obj_to_Date_DB($n_days_before);

		//delete rule currently only type = custom, date_range_type = from_to, to_date exists < then n_days_before_str

		$table_availability_dates = $wpdb->prefix . 'wpcal_availability_dates';

		$query1 = "SELECT `id` FROM `$table_availability_dates` WHERE `to_date` > '2000-01-01' AND `to_date` < '".$n_days_before_str."' AND `type` = 'custom' AND `date_range_type` = 'from_to' LIMIT $limit_per_delete";

		//var_dump($query1);
		$availability_dates_ids = $wpdb->get_col($query1);
		if( empty($availability_dates_ids) ){
			return true;
		}

		$table_availability_periods = $wpdb->prefix . 'wpcal_availability_periods';

		$query2 = "DELETE FROM `$table_availability_periods` WHERE `availability_date_id` IN(". implode(', ', $availability_dates_ids) .")";

		$result = $wpdb->query($query2);
		if($result === false){
			//handle error
			return false;
		}

		$query3 = "DELETE FROM `$table_availability_dates` WHERE `id` IN(". implode(', ', $availability_dates_ids) .")";

		$result = $wpdb->query($query3);
		var_dump($result);
		if($result === false){
			//handle error
			return false;
		}

		return true;
	}

	private static function get_n_days_before(int $days_before){
		$days_before = abs($days_before);
		$today = WPCal_DateTime_Helper::now_DateTime_obj();
		$today->setTime(0, 0, 0);
		$n_days_before = clone $today;
		$n_days_before->modify('-'.$days_before.' days');
		return $n_days_before;
	}
}
WPCal_Cron::init();