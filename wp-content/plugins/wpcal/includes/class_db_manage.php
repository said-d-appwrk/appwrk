<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_DB_Manage{
	private static $collation = '';
	private static $wpdb;
	private static $db_version = WPCAL_VERSION;
	private static $current_db_version;//update purpose

	public static function on_plugin_activate(){
		self::install();
	}

	private static function install(){
		self::db_init();
		self::create_tables();
	}

	private static function db_init(){
		global $wpdb;
		self::$collation = self::get_collation();
		self::$wpdb = $wpdb;
	}

	private static function create_tables(){
		self::create_table_availability_dates();
		self::create_table_availability_periods();
		self::create_table_background_tasks();
		self::create_table_bookings();
		self::create_table_calendars();
		self::create_table_calendar_accounts();
		self::create_table_calendar_events();
		self::create_table_services();
		self::create_table_service_admins();
		self::create_table_service_availability();
		self::create_table_service_availability_slots_cache();
		self::create_table_tp_accounts();
		self::create_table_tp_resources();

		add_option('wpcal_db_version', self::$db_version);//it will not update if there is exisiting one
	}

	private static function create_table_availability_dates(){
		$table_name = self::$wpdb->prefix ."wpcal_availability_dates";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`day_index_list` varchar(20) DEFAULT NULL,
			`date_range_type` enum('relative','from_to','infinite') NOT NULL,
			`from_date` date DEFAULT NULL,
			`to_date` date DEFAULT NULL,
			`date_misc` varchar(45) DEFAULT NULL,
			`type` enum('default','custom') NOT NULL,
			`is_available` tinyint(1) unsigned NOT NULL DEFAULT '1',
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `from_date` (`from_date`),
			KEY `to_date` (`to_date`),
			KEY `day_index_list` (`day_index_list`),
			KEY `date_range_type` (`date_range_type`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_availability_periods(){
		$table_name = self::$wpdb->prefix ."wpcal_availability_periods";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`availability_date_id` bigint(20) unsigned NOT NULL,
			`from_time` time NOT NULL,
			`to_time` time NOT NULL,
			PRIMARY KEY (`id`),
			KEY `availability_date_id` (`availability_date_id`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}
	
	private static function create_table_background_tasks(){
		$table_name = self::$wpdb->prefix ."wpcal_background_tasks";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`task_name` varchar(128) NOT NULL,
			`status` enum('pending','running','completed','error','retry','manual') NOT NULL DEFAULT 'pending',
			`scheduled_time_ts` int(10) unsigned NOT NULL DEFAULT '0',
			`expiry_ts` int(10) unsigned DEFAULT NULL,
			`main_arg_name` varchar(128) DEFAULT NULL,
			`main_arg_value` varchar(128) DEFAULT NULL,
			`task_args` text,
			`error_info` text,
			`dependant_id` bigint(20) DEFAULT NULL,
			`retry_attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
			`next_retry` int(10) unsigned DEFAULT NULL,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `task_name` (`task_name`),
			KEY `status` (`status`),
			KEY `main_arg_name` (`main_arg_name`),
			KEY `main_arg_value` (`main_arg_value`),
			KEY `scheduled_time_ts` (`scheduled_time_ts`),
			KEY `next_retry` (`next_retry`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_bookings(){
		$table_name = self::$wpdb->prefix ."wpcal_bookings";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`service_id` bigint(20) unsigned NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '1',
			`unique_link` varchar(64) NOT NULL DEFAULT '',
			`admin_user_id` bigint(20) unsigned NOT NULL,
			`invitee_wp_user_id` bigint(20) unsigned DEFAULT NULL,
			`invitee_name` varchar(256) NOT NULL,
			`invitee_email` varchar(256) NOT NULL,
			`invitee_question_answers` mediumtext,
			`invitee_tz` varchar(128) DEFAULT NULL,
			`location` text,
			`booking_from_time` int(10) unsigned NOT NULL,
			`booking_to_time` int(10) unsigned NOT NULL,
			`booking_ip` varchar(45) DEFAULT NULL,
			`page_used_for_booking` text,
			`event_added_calendar_id` bigint(20) unsigned DEFAULT NULL,
			`event_added_tp_cal_id` varchar(256) DEFAULT NULL,
			`event_added_tp_event_id` varchar(256) DEFAULT NULL,
			`meeting_tp_resource_id` bigint(20) unsigned DEFAULT NULL,
			`rescheduled_booking_id` bigint(20) unsigned DEFAULT NULL,
			`reschedule_cancel_reason` mediumtext,
			`reschedule_cancel_user_id` bigint(20) unsigned DEFAULT NULL,
			`reschedule_cancel_action_ts` int(10) unsigned DEFAULT NULL,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `service_id` (`service_id`),
			KEY `status` (`status`),
			KEY `unique_link` (`unique_link`),
			KEY `admin_user_id` (`admin_user_id`),
			KEY `booking_from_time` (`booking_from_time`),
			KEY `booking_to_time` (`booking_to_time`),
			KEY `rescheduled_booking_id` (`rescheduled_booking_id`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_calendars(){
		$table_name = self::$wpdb->prefix ."wpcal_calendars";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`calendar_account_id` bigint(20) unsigned NOT NULL,
			`name` varchar(256) NOT NULL,
			`status` tinyint(1) NOT NULL,
			`tp_cal_id` varchar(256) NOT NULL,
			`is_conflict_calendar` tinyint(1) NOT NULL DEFAULT '0',
			`is_add_events_calendar` tinyint(1) NOT NULL DEFAULT '0',
			`is_readable` tinyint(1) NOT NULL,
			`is_writable` tinyint(1) NOT NULL,
			`is_primary` tinyint(1) NOT NULL,
			`timezone` varchar(128) NOT NULL,
			`list_events_sync_token` varchar(128) DEFAULT NULL,
			`list_events_sync_status` enum('started','running','completed','error') DEFAULT NULL,
			`list_events_sync_last_update_ts` int(10) unsigned DEFAULT NULL,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `calendar_account_id` (`calendar_account_id`),
			KEY `tp_cal_id` (`tp_cal_id`),
			KEY `status` (`status`),
			KEY `is_conflict_calendar` (`is_conflict_calendar`),
			KEY `is_add_events_calendar` (`is_add_events_calendar`),
			KEY `list_events_sync_last_update_ts` (`list_events_sync_last_update_ts`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_calendar_accounts(){
		$table_name = self::$wpdb->prefix ."wpcal_calendar_accounts";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`admin_user_id` bigint(20) unsigned NOT NULL,
			`provider` enum('google_calendar') NOT NULL,
			`status` tinyint(1) NOT NULL,
			`tp_user_id` varchar(1000) DEFAULT NULL,
			`account_email` varchar(1000) NOT NULL,
			`api_token` text,
			`list_calendars_sync_token` varchar(256) DEFAULT NULL,
			`list_calendars_sync_last_update_ts` int(10) unsigned DEFAULT NULL,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `admin_user_id` (`admin_user_id`),
			KEY `provider` (`provider`),
			KEY `status` (`status`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_calendar_events(){
		$table_name = self::$wpdb->prefix ."wpcal_calendar_events";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`calendar_id` bigint(20) unsigned NOT NULL,
			`status` tinyint(1) NOT NULL,
			`tp_event_id` varchar(256) NOT NULL,
			`from_time` int(10) unsigned NOT NULL,
			`to_time` int(10) unsigned NOT NULL,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `calendar_id` (`calendar_id`),
			KEY `status` (`status`),
			KEY `tp_event_id` (`tp_event_id`),
			KEY `from_time` (`from_time`),
			KEY `to_time` (`to_time`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_services(){
		$table_name = self::$wpdb->prefix ."wpcal_services";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(191) NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '1',
			`locations` mediumtext NOT NULL,
			`descr` text,
			`post_id` bigint(20) unsigned DEFAULT NULL,
			`color` varchar(100) DEFAULT NULL,
			`relationship_type` enum('1to1','1ton') NOT NULL,
			`duration` smallint(5) unsigned NOT NULL COMMENT 'in mintues',
			`display_start_time_every` smallint(5) unsigned NOT NULL COMMENT 'in mintues',
			`max_booking_per_day` int(10) unsigned DEFAULT NULL,
			`min_schedule_notice` varchar(256) NOT NULL DEFAULT '0' COMMENT 'in json object',
			`event_buffer_before` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'in mintues',
			`event_buffer_after` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'in mintues',
			`invitee_questions` mediumtext,
			`last_cached_slots_generated` int(10) unsigned DEFAULT NULL,
			`refresh_cache` tinyint(1) unsigned NOT NULL DEFAULT '1',
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `name` (`name`),
			KEY `status` (`status`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_service_admins(){
		$table_name = self::$wpdb->prefix ."wpcal_service_admins";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`admin_user_id` bigint(20) NOT NULL,
			`service_id` bigint(20) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `admin_user_id` (`admin_user_id`),
			KEY `service_id` (`service_id`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_service_availability(){
		$table_name = self::$wpdb->prefix ."wpcal_service_availability";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`service_id` bigint(20) NOT NULL,
			`availability_dates_id` bigint(20) NOT NULL,
			PRIMARY KEY (`id`),
			KEY `service_id` (`service_id`),
			KEY `availability_dates_id` (`availability_dates_id`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_service_availability_slots_cache(){
		$table_name = self::$wpdb->prefix ."wpcal_service_availability_slots_cache";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`service_id` bigint(20) unsigned NOT NULL,
			`availability_date` date NOT NULL,
			`is_available` tinyint(1) unsigned NOT NULL,
			`is_all_booked` tinyint(1) unsigned NOT NULL,
			`cache_created_ts` int(10) unsigned NOT NULL,
			`slots` longtext NOT NULL,
			UNIQUE KEY `service_id_availability_date` (`service_id`,`availability_date`),
			KEY `availability_date` (`availability_date`),
			KEY `service_id` (`service_id`)
		  ) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_tp_accounts(){
		$table_name = self::$wpdb->prefix ."wpcal_tp_accounts";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`admin_user_id` bigint(20) unsigned NOT NULL,
			`provider` enum('zoom_meeting','gotomeeting_meeting') NOT NULL,
			`provider_type` enum('meeting') NOT NULL,
			`status` tinyint(1) NOT NULL,
			`tp_user_id` varchar(1000) DEFAULT NULL,
			`tp_account_email` varchar(1000) NOT NULL,
			`api_token` text,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `admin_user_id` (`admin_user_id`),
			KEY `provider` (`provider`),
			KEY `status` (`status`)
			) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	private static function create_table_tp_resources(){
		$table_name = self::$wpdb->prefix ."wpcal_tp_resources";

		$query = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`for_type` enum('booking') NOT NULL,
			`for_id` bigint(20) NOT NULL,
			`type` enum('meeting') NOT NULL,
			`status` enum('active','cancelled','deleted') NOT NULL,
			`provider` enum('zoom_meeting','gotomeeting_meeting') NOT NULL,
			`tp_account_id` bigint(20) unsigned NOT NULL COMMENT 'local data',
			`tp_user_id` varchar(1000) DEFAULT NULL,
			`tp_account_email` varchar(1000) DEFAULT NULL,
			`tp_id` varchar(1000) NOT NULL,
			`tp_data` mediumtext,
			`added_ts` int(10) unsigned NOT NULL,
			`updated_ts` int(10) unsigned NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB " . self::$collation;

		return self::do_create_table($table_name, $query);		  
	}

	public static function update(){
		self::db_init();
		
		$old_db_version = self::$current_db_version = get_option('wpcal_db_version', '0.0');

		if(version_compare(self::$current_db_version, self::$db_version, '>=')){
			return true;
		}

		self::update_v_0_1_0();
		self::update_v_0_9_1_0();

		if( $old_db_version !== self::$current_db_version){
			update_option('wpcal_db_version', self::$current_db_version);
		}
	}

	private static function update_v_0_1_0(){
		$v = '0.1.0';
		if(version_compare(self::$current_db_version, $v, '>=')){
			return;
		}

		self::$current_db_version = $v;
	}

	private static function update_v_0_9_1_0(){
		$v = '0.9.1.1';
		if(version_compare(self::$current_db_version, $v, '>=')){
			return;
		}

		self::create_table_tp_accounts();
		self::create_table_tp_resources();
		
		$table_services = self::$wpdb->prefix ."wpcal_services";
		if( self::is_table_exist($table_services) && self::is_column_exist($table_services, 'location') ){
			$alter_query = "ALTER TABLE `$table_services` 
			CHANGE `color` `color` varchar(100) NULL AFTER `post_id`,
			CHANGE `location` `locations` mediumtext  NOT NULL AFTER `status`";
			self::$wpdb->query($alter_query);
		}

		$table_bookings = self::$wpdb->prefix ."wpcal_bookings";
		if( self::is_table_exist($table_bookings) && !self::is_column_exist($table_bookings, 'meeting_tp_resource_id') ){
			$alter_query = "ALTER TABLE `$table_bookings` 
			ADD `meeting_tp_resource_id` bigint(20) unsigned NULL AFTER `event_added_tp_event_id`";
			self::$wpdb->query($alter_query);
		}

		$table_calendar_accounts = self::$wpdb->prefix ."wpcal_calendar_accounts";
		if( self::is_table_exist($table_calendar_accounts) && !self::is_column_exist($table_calendar_accounts, 'tp_user_id') ){
			$alter_query = "ALTER TABLE `$table_calendar_accounts` 
			ADD `tp_user_id` varchar(1000) NULL AFTER `status`,
			CHANGE `account_email` `account_email` varchar(1000) NOT NULL AFTER `tp_user_id`";
			self::$wpdb->query($alter_query);
		}

		$table_background_tasks = self::$wpdb->prefix ."wpcal_background_tasks";
		if( self::is_table_exist($table_background_tasks) && !self::is_column_exist($table_background_tasks, 'dependant_id') ){
			$alter_query = "ALTER TABLE `$table_background_tasks`
			CHANGE `status` `status` enum('pending','running','completed','error','retry','manual') NOT NULL DEFAULT 'pending' AFTER `task_name`,
			ADD `dependant_id` bigint(20) NULL AFTER `error_info`";
			self::$wpdb->query($alter_query);
		}

		self::update_v_0_9_1_0_change_service_locations_json();
		self::update_v_0_9_1_0_change_booking_location_json();
		self::update_v_0_9_1_0_encode_calendar_accounts_api_token();

		//need to check and do the alter. After query run check - Need to IMPROVE LATER

		self::$current_db_version = $v;
	}

	private static function update_v_0_9_1_0_change_service_locations_json(){
		$table_services = self::$wpdb->prefix ."wpcal_services";
		$query = "SELECT `id`, `locations` FROM `$table_services`";
		$rows = self::$wpdb->get_results($query);
		foreach($rows as $row){
			if($row->locations){
				$decoded = json_decode($row->locations, true);
				if( $decoded === null ){
					$location_array = ['type' => 'physical', 'form' => ['location' => '', 'location_extra' => '']];
					$location_array['form']['location'] = $row->locations;
					$locations = [$location_array];
					$encoded = json_encode($locations);
					self::$wpdb->update($table_services, ['locations' => $encoded], ['id' => $row->id]);
				}
			}
		}
	}

	private static function update_v_0_9_1_0_change_booking_location_json(){
		$table_bookings = self::$wpdb->prefix ."wpcal_bookings";
		$query = "SELECT `id`, `location` FROM `$table_bookings`";
		$rows = self::$wpdb->get_results($query);
		foreach($rows as $row){
			if($row->location){
				$decoded = json_decode($row->location, true);
				if( $decoded === null ){
					$location_array = ['type' => 'physical', 'form' => ['location' => '', 'location_extra' => '']];
					$location_array['form']['location'] = $row->location;
					$encoded = json_encode($location_array);
					self::$wpdb->update($table_bookings, ['location' => $encoded], ['id' => $row->id]);
				}
			}
		}
	}

	private static function update_v_0_9_1_0_encode_calendar_accounts_api_token(){
		$table_calendar_accounts = self::$wpdb->prefix ."wpcal_calendar_accounts";
		$query = "SELECT `id`, `api_token` FROM `$table_calendar_accounts`";
		$rows = self::$wpdb->get_results($query);
		foreach($rows as $row){
			$row->api_token = trim($row->api_token);
			if( !empty($row->api_token) && substr($row->api_token, 0, 1) == '{' ){
				$encoded_token = wpcal_encode_token($row->api_token);
				self::$wpdb->update($table_calendar_accounts, ['api_token' => $encoded_token], ['id' => $row->id]);
			}
		}
	}

	private static function do_create_table($table_name, $query){
		self::$wpdb->query($query);

		// $last_db_error = $GLOBALS['wpdb']->last_error;

		// if(!is_table_exist($table_name)){
		// 	$query_error = get_error_msg('create_table_error').' Table:('.$table_name.')';
		// 		if($last_db_error){
		// 			$query_error = get_error_msg('create_table_error').' Error:('.$last_db_error.') Table:('.$table_name.')';
		// 		}
		// 		throw new Exception('create_table_error', $query_error);
		// }
	}

	private static function do_query($query){
		return self::$wpdb->query($query);

		// $last_db_error = $GLOBALS['wpdb']->last_error;
	}

	private static function is_table_exist($table){
		$escaped_table_name = self::esc_table_name($table);
		if( self::$wpdb->get_var("SHOW TABLES LIKE '$escaped_table_name'") == $table ){
			return true;
		}
		return false;
	}

	private static function is_column_exist($table, $column){
		$db_name = DB_NAME;
		$column_exists = $GLOBALS['wpdb']->get_results("SELECT * 
			FROM information_schema.COLUMNS 
			WHERE 
				TABLE_SCHEMA = '$db_name' AND 
				TABLE_NAME = '$table' AND 
				COLUMN_NAME = '$column'"
		);

		return !empty($column_exists);
	}

	private static function  esc_table_name($table){
		$tmp_replacer = '||**^**||';
		$search = array('\\_', '_', $tmp_replacer);
		$replace = array($tmp_replacer, '\\_', '\\_');
		return str_replace($search, $replace, $table);//do left to right if already escapsed string comes in, i am trying to maintain that with left to right replacement of str_replace with $tmp_replacer //why escaping the (_) because it is single character whild card in mysql
	}

	private static function get_collation(){
		global $wpdb;
		if (method_exists( $wpdb, 'get_charset_collate')) {
			$charset_collate =  $wpdb->get_charset_collate();
		}
	
		return !empty($charset_collate) ?  $charset_collate : ' DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci ' ;
	}
}

