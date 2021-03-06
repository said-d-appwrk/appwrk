<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Admin_Data_Format{

	public static function format_service_availability_for_cal($data){
		$formated_data  = array();
		if(!empty($data['default_availability'])){
			$formated_data['default_availability'] = $data['default_availability'];
		}

		if(!empty($data['dates_availability'])){
			$formated_data['dates_availability'] = $data['dates_availability'];

			$cal_format_availability = array();
			foreach($data['dates_availability'] as $date => $date_availability){
				$_data['dates_availability'][] = self::_format_date_service_availability_for_cal($date_availability, $date, $cal_format_availability);
			}
			$formated_data['dates_availability_for_cal'] = $cal_format_availability;
		}

		if(!empty($data['availability_date_ranges'])){
			$formated_data['availability_date_ranges'] = $data['availability_date_ranges'];
		}

		return $formated_data;
	}

	private static function _format_date_service_availability_for_cal($date_availability, $date, &$cal_format_availability){

		foreach($date_availability['periods'] as $_key => $period_availability){
			$cal_format = array();
			$cal_format['start'] = $date.' '.$period_availability['from_time'];
			$cal_format['end'] = $date.' '.$period_availability['to_time'];

			$start_time_obj = WPCal_DateTime_Helper::DateTime_DB_to_DateTime_obj($cal_format['start']);

			$end_time_obj = WPCal_DateTime_Helper::DateTime_DB_to_DateTime_obj($cal_format['end']);

			$cal_format['title'] = WPCal_DateTime_Helper::DateTime_Obj_to_time_format($start_time_obj).' - '.WPCal_DateTime_Helper::DateTime_Obj_to_time_format($end_time_obj);
			$cal_format_availability[] = $cal_format;
		}
	}
}