<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Service_Availability_Details{
	private $service_obj;

	private $default_availability_cache = null;

	public function __construct(WPCal_Service $service_obj){
		$this->service_obj = $service_obj;
	}

	public function get_default_availability(){

		if($this->default_availability_cache !== null){
			return $this->default_availability_cache;
		}

		$default_availability_id = $this->get_default_availability_id();

		if( empty($default_availability_id) ){
			throw new WPCal_Exception('service_default_availability_data_missing');
		}
		else{
			$this->default_availability_cache = new WPCal_Availability_Date($default_availability_id);
			return $this->default_availability_cache;
		}
	}

	public function get_default_availability_id(){

		global $wpdb;
		$table_availability_dates = $wpdb->prefix . 'wpcal_availability_dates';
		$table_service_availability = $wpdb->prefix . 'wpcal_service_availability';

		$query = "SELECT `availability_dates`.`id` FROM `$table_service_availability` AS `service_availability` LEFT JOIN `$table_availability_dates` AS `availability_dates` ON `service_availability`.`availability_dates_id` = `availability_dates`.`id` WHERE `service_availability`.`service_id` = '".$this->service_obj->get_id()."' AND `availability_dates`.`type` = 'default'";

		$result = $wpdb->get_var($query);
		return $result;
	}

	public function get_availability_by_date_range( DateTime $from_date, DateTime $to_date ){

		$default_availability_obj = $this->get_default_availability();

		// $from_date = new DateTime(date('Y-m-d'));
		// $to_date = clone $from_date;
		// $to_date->modify( '+21 days' );

		$service_min_date = $default_availability_obj->get_min_date();
		$service_max_date = $default_availability_obj->get_max_date();

		list($available_from_date, $available_to_date) = WPCal_Service_Availability_Details::get_final_from_and_to_dates($from_date, $to_date, $service_min_date, $service_max_date);

		$availability_details = array();
		$availability_details['default_availability'] = $default_availability_obj;
		$availability_details['dates_availability'] = [];
		$availability_details['availability_date_ranges'] = [
			'service_min_date' => $service_min_date,
			'service_max_date' => $service_max_date,
			'available_from_date' => clone $available_from_date,
			'available_to_date' => clone $available_to_date,
			'requested_from_date' => $from_date,
			'requested_to_date' => $to_date
		];

		$full_to_date = clone $to_date;
		$full_to_date->add(new DateInterval('P1D'));//$to_date time is 00:00 to cover full day, 1 more day is added

		$full_service_max_date = clone $service_max_date;
		$full_service_max_date->add(new DateInterval('P1D'));//$service_max_date time is 00:00 to cover full day, 1 more day is added

		$is_there_common_period = WPCal_DateTime_Helper::is_two_slots_collide( $from_date->format('U'), $full_to_date->format('U'), $service_min_date->format('U'), $full_service_max_date->format('U') );

		if( !$is_there_common_period ){
			return $availability_details;
		}

		//var_dump($availability_details['availability_date_ranges']);

		//add one day to cover hours of the day say Jan 01 00:00:00 to Jan 02 00:00:00 to cover full Jan 01
		$available_to_date->add( new DateInterval('P1D') );

		// echo '<br>';
		// echo $available_from_date->format('c');
		// echo '<br>';
		// echo $__t1 = $available_to_date->format('c');
		// $t1_obj = new DateTime($__t1);
		// echo '<br>';
		// echo '||-> '.$t1_obj->format('c');


		$num_of_days_obj = $available_from_date->diff($available_to_date);
		//var_dump($num_of_days_obj);

		// echo '<br>';
		$num_of_days = $num_of_days_obj->format( '%a' );

		// var_dump($num_of_days);

		$_from_date = clone $available_from_date;

		$one_day_interval = new DateInterval('P1D');

		$i=0;
		while( $i < $num_of_days ){
			// echo '<br>';
			// echo $_from_date->format( 'c' );

			$formatted_date = WPCal_DateTime_Helper::DateTime_Obj_to_Date_DB($_from_date);

			$day_availability = $this->get_availability_by_date($_from_date);

			$availability_details['dates_availability'][$formatted_date] = $day_availability;

			$_from_date->add( $one_day_interval );
			$i++;
		}

		return $availability_details;
	}

	public function get_availability_by_date_range_for_admin_client( DateTime $from_date, DateTime $to_date ){
		$_data = array();

		$_availability_details = $this->get_availability_by_date_range($from_date, $to_date );

		if(!empty($_availability_details['default_availability'])){
			$_data['default_availability'] = $_availability_details['default_availability']->get_data_for_admin_client();
		}


		if(!empty($_availability_details['dates_availability'])){
			foreach($_availability_details['dates_availability'] as $formatted_date => $dates_availability_obj){
				$date_availability = $dates_availability_obj->get_data_for_admin_client();
				if( !$dates_availability_obj->is_available_by_date(WPCal_DateTime_Helper::DateTime_DB_to_DateTime_obj($formatted_date)) ){
					$date_availability['periods'] = array();
					$date_availability['is_available'] = 0;

				}
				$_data['dates_availability'][$formatted_date] = $date_availability;
			}
		}

		if(!empty($_availability_details['availability_date_ranges'])){
			foreach($_availability_details['availability_date_ranges'] as $_key => $date_obj){
				$_data['availability_date_ranges'][$_key] = WPCal_DateTime_Helper::DateTime_Obj_to_Date_DB($date_obj);
				//var_dump($date_obj);
			}
		}

		return $_data;
	}

	public function get_availability_by_date(DateTime $date, $options=array()){
		$required_date = $date->format('Y-m-d');
		$required_day_index = $date->format('N');

		global $wpdb;
		$table_availability_dates = $wpdb->prefix . 'wpcal_availability_dates';
		$table_service_availability = $wpdb->prefix . 'wpcal_service_availability';

		$query = "SELECT `availability_dates`.*, DATEDIFF(`availability_dates`.`to_date`,`availability_dates`.`from_date`) as `date_diff` FROM `$table_service_availability` AS `service_availability` LEFT JOIN `$table_availability_dates` AS `availability_dates` ON `service_availability`.`availability_dates_id` = `availability_dates`.`id` WHERE `service_availability`.`service_id` = '".$this->service_obj->get_id()."' AND `availability_dates`.`type` = 'custom'";
		if(isset($options['is_available'])){
			$query .= " AND `is_available` = '".$options['is_available']."'";
		}

		$query .= " AND (
			( `date_range_type` = 'from_to' AND (`from_date` <= '".$required_date."' AND `to_date` >= '".$required_date."') ) OR ( `date_range_type` = 'infinite' AND `day_index_list` LIKE '%$required_day_index%' AND (`from_date` IS NULL OR `from_date` <= '".$required_date."') ) 
			)";

		//$query .= " ORDER BY FIELD(`date_range_type`, 'from_to', 'infinite'), `date_diff` ASC";
		$query .= " ORDER BY  `id` DESC";

		//print($query);
		$result = $wpdb->get_row($query);
		if( empty($result) ){
			return $this->get_default_availability();
		}

		if(!empty($result->id) ){
			return new WPCal_Availability_Date($result->id);
		}
	}

	public function get_availability_by_date_except_not_available(DateTime $date){
		return $this->get_availability_by_date($date, array('is_available' => '1'));
	}

	public function get_availability_by_date_except_not_available_for_admin_client(DateTime $date){
		$date_availability_obj = $this->get_availability_by_date_except_not_available($date);
		$date_availability = $date_availability_obj->get_data_for_admin_client();
		return $date_availability;
	}

	//static methods
	public static function get_final_from_and_to_dates($required_from_date, $required_to_date, $service_min_date, $service_max_date){

		$available_from_date = clone $required_from_date;
		$available_to_date = clone $required_to_date;
		
		if( $required_from_date < $service_min_date ){
			$available_from_date = clone $service_min_date;
		}
		if( $required_to_date > $service_max_date || $required_to_date < $available_from_date  ){
			$available_to_date = clone $service_max_date;
		}
		return array($available_from_date, $available_to_date);
	}
}