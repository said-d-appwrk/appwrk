<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

class WPCal_Mail{
	private static $site_token;
	private static $site_url;
	private static $license_email;
	public static $dev_preview = false;

	private static function load_site_details(){
		if( !empty(self::$site_token) ){
			return;
		}
		$license_info = WPCal_License::get_site_token_and_account_email();
		if( empty($license_info['site_token']) ){
			throw new WPCal_Exception('invalid_license_info');
		}
		self::$site_token = $license_info['site_token'];
		self::$license_email = $license_info['email'];
		self::$site_url = site_url();
	}

	private static function send_mail($from_email, $to_email, $subject, $body, $options=[]){
		if(self::$dev_preview){
			echo 'Subject: '.$subject .'<br>--------------------------<br>'.$body;
			return;
		}

		self::load_site_details();

		//var_dump($to_email, $subject, $options);

		$schedule_time = time() - 1;
		if( !empty($options['schedule_time']) ){
			$schedule_time = $options['schedule_time'];
		}

		if( empty($options['event_id']) ){
			throw new WPCal_Exception('event_id_missing');
		}

		$reply_to = '';
		if( !empty($options['reply_to']) ){
			$reply_to = $options['reply_to'];
		}

		$request_data = [
			"app_id" => self::$site_token,
			"site_url" => self::$site_url,
			"plugin_slug" => WPCAL_PLUGIN_SLUG,
			"plugin_verion" => WPCAL_VERSION,
			"event_id" => $options['event_id'],
			"account_email" => self::$license_email,
			"to_emails" => $to_email,
			"reply_to" => $reply_to,
			"email_subject" => $subject,
			"email_body" => $body,
			"schedule_time" => $schedule_time,
			//"ip_address" => "127.0.0.1",
		];

		$request_body = json_encode($request_data);

		$http_args = array(
			'method' => "POST",
			'headers'   => [ 'Content-Type' => 'application/json' ],
			'timeout' => 10,
			'body' => $request_body
		);

		$url = WPCAL_CRON_URL.'cron-event';

		try{
			//$__start_time = microtime(1);
			$response = wp_remote_request( $url, $http_args );
			//var_dump(['total_time' => round( microtime(1) - $__start_time, 5)]);
			$response_data = wpcal_check_and_get_data_from_rest_api_response_json($response);
			//var_dump($response_data);
			if( isset($response_data['status']) ){
				if( $response_data['status'] === 'success' ){
					return true;
				}
				elseif( $response_data['status'] === 'error' ){
					$error_desc = isset($response_data['res_desc']) ? $response_data['res_desc'] : '';
					throw new WPCal_Exception('cron_server_error', $error_desc);
				}
			}
			else{
				wpcal_check_http_error($response);
			}
			throw new WPCal_Exception('cron_server_invalid_response');
		}
		catch(WPCal_Exception $e){
			throw $e;
		}
		catch(Exception $e){
			throw new WPCal_Exception('unknown_error', $e->getMessage());
		}
	}

	private static function delete_scheduled_mail($event_id){
		self::load_site_details();

		$request_data = [
			"app_id" => self::$site_token,
			"plugin_slug" => WPCAL_PLUGIN_SLUG,
			"plugin_verion" => WPCAL_VERSION,
			"event_id" => $event_id,
		];

		$request_body = json_encode($request_data);

		$http_args = array(
			'method' => "POST",
			'headers'   => [ 'Content-Type' => 'application/json' ],
			'timeout' => 10,
			'body' => $request_body
		);

		$url = WPCAL_CRON_URL.'delete-event';

		try{
			//$__start_time = microtime(1);
			$response = wp_remote_request( $url, $http_args );
			//var_dump(['total_time' => round( microtime(1) - $__start_time, 5)]);
			$response_data = wpcal_check_and_get_data_from_rest_api_response_json($response);
			//var_dump($response_data);
			if( isset($response_data['status']) ){
				if( $response_data['status'] === 'success' ){
					return true;
				}
				elseif( $response_data['status'] === 'error' ){
					$error_desc = isset($response_data['res_desc']) ? $response_data['res_desc'] : '';
					throw new WPCal_Exception('cron_server_error', $error_desc);
				}
			}
			else{
				wpcal_check_http_error($response);
			}
			throw new WPCal_Exception('cron_server_invalid_response');
		}
		catch(WPCal_Exception $e){
			throw $e;
		}
		catch(Exception $e){
			throw new WPCal_Exception('unknown_error', $e->getMessage());
		}
	}

	public static function send_admin_new_booking_info(WPCal_Booking $booking_obj){

		if( $booking_obj->is_booking_mail_sent_by_type('send_admin_new_booking_info_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());

		$location_html = self::get_booking_location_html($booking_obj, 'admin');

		$mail_data = [
			'booking_admin_first_name' => $admin_details['first_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'invitee_name' => $booking_obj->get_invitee_name(),
			'invitee_email' => $booking_obj->get_invitee_email(),
			'invitee_tz' => $booking_obj->get_invitee_tz(),
			'location_html' => $location_html,
			'booking_from_to_time_str_with_tz' => WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_obj->get_booking_from_time(), $booking_obj->get_booking_to_time()),
		];
		
		include(WPCAL_PATH . '/templates/emails/admin_new_booking_info.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		$to_email = $admin_details['email'];
		$subject = $mail_subject;
		$body = $mail_body;
		$options = [ 'event_id' => $booking_obj->get_unique_link() .'-admin_new_booking_info' ];
		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}

	public static function send_admin_reschedule_booking_info(WPCal_Booking $booking_obj){

		if( $booking_obj->is_booking_mail_sent_by_type('send_admin_reschedule_booking_info_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());

		$old_booking_obj = wpcal_get_old_booking_if_rescheduled($booking_obj->get_id());
		$old_booking_from_to_time_str_with_tz = '';
		if( $old_booking_obj ){
			$old_booking_from_time = $old_booking_obj->get_booking_from_time();
			$old_booking_to_time = $old_booking_obj->get_booking_to_time();

			$old_booking_from_to_time_str_with_tz = WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($old_booking_from_time, $old_booking_to_time);
		}

		$location_html = self::get_booking_location_html($booking_obj, 'admin');

		$mail_data = [
			'booking_admin_first_name' => $admin_details['first_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'invitee_name' => $booking_obj->get_invitee_name(),
			'invitee_email' => $booking_obj->get_invitee_email(),
			'invitee_tz' => $booking_obj->get_invitee_tz(),
			'location_html' => $location_html,
			'booking_from_to_time_str_with_tz' => WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_obj->get_booking_from_time(), $booking_obj->get_booking_to_time()),
			'old_booking_from_to_time_str_with_tz' => $old_booking_from_to_time_str_with_tz,
		];
		
		include(WPCAL_PATH . '/templates/emails/admin_reschedule_booking_info.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		$to_email = $admin_details['email'];
		$subject = $mail_subject;
		$body = $mail_body;
		$options = [ 'event_id' => $booking_obj->get_unique_link() .'-admin_reschedule_booking_info' ];
		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}

	public static function send_admin_booking_cancellation(WPCal_Booking $booking_obj){

		if( $booking_obj->is_booking_mail_sent_by_type('send_admin_booking_cancellation_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());

		$location_html = self::get_booking_location_html($booking_obj, 'admin');

		$mail_data = [
			'booking_admin_first_name' => $admin_details['first_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'invitee_name' => $booking_obj->get_invitee_name(),
			'invitee_email' => $booking_obj->get_invitee_email(),
			'invitee_tz' => $booking_obj->get_invitee_tz(),
			'location_html' => $location_html,
			'booking_from_to_time_str_with_tz' => WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_obj->get_booking_from_time(), $booking_obj->get_booking_to_time()),
		];
		
		include(WPCAL_PATH . '/templates/emails/admin_booking_cancellation.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		$to_email = $admin_details['email'];
		$subject = $mail_subject;
		$body = $mail_body;
		$options = [ 'event_id' => $booking_obj->get_unique_link() .'-admin_booking_cancellation' ];
		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}

	public static function send_invitee_booking_confirmation(WPCal_Booking $booking_obj){

		if( $booking_obj->is_booking_mail_sent_by_type('send_invitee_booking_confirmation_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());
		$invitee_name = $booking_obj->get_invitee_name();
		$invitee_name_parts = explode(' ', $invitee_name, 2);
		$hi_invitee_name = $invitee_name_parts[0];

		$timezone_name = wp_timezone()->getName();
		$booking_from_time = $booking_obj->get_booking_from_time();
		$booking_to_time = $booking_obj->get_booking_to_time();

		if( $booking_obj->get_invitee_tz() ){
			$timezone_name = $booking_obj->get_invitee_tz();
			$booking_from_time->setTimezone( new DateTimeZone($timezone_name) );
			$booking_to_time->setTimezone( new DateTimeZone($timezone_name) );
		}
		$booking_from_to_time_str_with_tz = WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_from_time, $booking_to_time);

		$location_html = self::get_booking_location_html($booking_obj, 'user');

		$mail_data = [
			'booking_admin_display_name' => $admin_details['display_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'hi_invitee_name' => $hi_invitee_name,
			'location_html' => $location_html,
			'add_event_to_google_calendar_url' => $booking_obj->get_add_event_to_google_calendar_url(),
			'download_ics_url' => $booking_obj->get_download_ics_url(),
			'reschedule_url' => $booking_obj->get_redirect_reschedule_url(),
			'cancel_url' => $booking_obj->get_redirect_cancel_url(),
			'booking_from_to_time_str_with_tz' => $booking_from_to_time_str_with_tz,
		];

		include(WPCAL_PATH . '/templates/emails/invitee_booking_confirmation.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		//$to_email = $booking_obj->get_invitee_name().' <'.$booking_obj->get_invitee_email().'>';
		$to_email = $booking_obj->get_invitee_email();
		$subject = $mail_subject;
		$body = $mail_body;
		$options = [
			'event_id' => $booking_obj->get_unique_link() .'-invitee_booking_confirmation',
			'reply_to' => $admin_details['email']
			//'reply_to' => $admin_details['display_name'].' <'.$admin_details['email'].'>'
		];

		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}

	public static function send_invitee_reschedule_booking_confirmation(WPCal_Booking $booking_obj){

		if( $booking_obj->is_booking_mail_sent_by_type('send_invitee_reschedule_booking_confirmation_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());
		$invitee_name = $booking_obj->get_invitee_name();
		$invitee_name_parts = explode(' ', $invitee_name, 2);
		$hi_invitee_name = $invitee_name_parts[0];

		$timezone_name = wp_timezone()->getName();
		$booking_from_time = $booking_obj->get_booking_from_time();
		$booking_to_time = $booking_obj->get_booking_to_time();

		$old_booking_obj = wpcal_get_old_booking_if_rescheduled($booking_obj->get_id());
		if( $old_booking_obj ){
			$old_booking_from_time = $old_booking_obj->get_booking_from_time();
			$old_booking_to_time = $old_booking_obj->get_booking_to_time();
		}

		if( $booking_obj->get_invitee_tz() ){
			$timezone_name = $booking_obj->get_invitee_tz();			
			$booking_from_time->setTimezone( new DateTimeZone($timezone_name) );
			$booking_to_time->setTimezone( new DateTimeZone($timezone_name) );
			if( $old_booking_obj ){
				$old_booking_from_time->setTimezone( new DateTimeZone($timezone_name) );
				$old_booking_to_time->setTimezone( new DateTimeZone($timezone_name) );
			}
	
		}
		$booking_from_to_time_str_with_tz = WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_from_time, $booking_to_time);

		$reschedule_from_to_time_str_with_tz = '';
		if( $old_booking_obj ){
			$reschedule_from_to_time_str_with_tz = WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($old_booking_from_time, $old_booking_to_time);
		}

		$location_html = self::get_booking_location_html($booking_obj, 'user');

		$mail_data = [
			'booking_admin_display_name' => $admin_details['display_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'hi_invitee_name' => $hi_invitee_name,
			'location_html' => $location_html,
			'add_event_to_google_calendar_url' => $booking_obj->get_add_event_to_google_calendar_url(),
			'download_ics_url' => $booking_obj->get_download_ics_url(),
			'reschedule_url' => $booking_obj->get_redirect_reschedule_url(),
			'cancel_url' => $booking_obj->get_redirect_cancel_url(),
			'booking_from_to_time_str_with_tz' => $booking_from_to_time_str_with_tz,
			'reschedule_booking_from_to_time_str_with_tz' => $reschedule_from_to_time_str_with_tz,
		];

		include(WPCAL_PATH . '/templates/emails/invitee_booking_reschedule.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		//$to_email = $booking_obj->get_invitee_name().' <'.$booking_obj->get_invitee_email().'>';
		$to_email = $booking_obj->get_invitee_email();
		$subject = $mail_subject;
		$body = $mail_body;
		$options = [
			'event_id' => $booking_obj->get_unique_link() .'-invitee_reschedule_booking_confirmation',
			'reply_to' => $admin_details['email']
			//'reply_to' => $admin_details['display_name'].' <'.$admin_details['email'].'>'
		];

		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}

	public static function send_invitee_booking_cancellation(WPCal_Booking $booking_obj){

		if( $booking_obj->is_booking_mail_sent_by_type('send_invitee_booking_cancellation_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());
		$invitee_name = $booking_obj->get_invitee_name();
		$invitee_name_parts = explode(' ', $invitee_name, 2);
		$hi_invitee_name = $invitee_name_parts[0];

		$timezone_name = wp_timezone()->getName();
		$booking_from_time = $booking_obj->get_booking_from_time();
		$booking_to_time = $booking_obj->get_booking_to_time();

		if( $booking_obj->get_invitee_tz() ){
			$timezone_name = $booking_obj->get_invitee_tz();			
			$booking_from_time->setTimezone( new DateTimeZone($timezone_name) );
			$booking_to_time->setTimezone( new DateTimeZone($timezone_name) );
		}
		$booking_from_to_time_str_with_tz = WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_from_time, $booking_to_time);

		$location_html = self::get_booking_location_html($booking_obj, 'user');

		$mail_data = [
			'booking_admin_display_name' => $admin_details['display_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'hi_invitee_name' => $hi_invitee_name,
			'location_html' => $location_html,
			'add_event_to_google_calendar_url' => $booking_obj->get_add_event_to_google_calendar_url(),
			'download_ics_url' => $booking_obj->get_download_ics_url(),
			'reschedule_url' => $booking_obj->get_redirect_reschedule_url(),
			'cancel_url' => $booking_obj->get_redirect_cancel_url(),
			'booking_from_to_time_str_with_tz' => $booking_from_to_time_str_with_tz,
		];

		include(WPCAL_PATH . '/templates/emails/invitee_booking_cancellation.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		//$to_email = $booking_obj->get_invitee_name().' <'.$booking_obj->get_invitee_email().'>';
		$to_email = $booking_obj->get_invitee_email();
		$subject = $mail_subject;
		$body = $mail_body;
		$options = [
			'event_id' => $booking_obj->get_unique_link() .'-invitee_booking_cancellation',
			'reply_to' => $admin_details['email']
			//'reply_to' => $admin_details['display_name'].' <'.$admin_details['email'].'>'
		];

		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}


	public static function send_invitee_booking_reminder(WPCal_Booking $booking_obj){
		if( $booking_obj->is_booking_mail_sent_by_type('send_invitee_booking_reminder_mail') ){
			//already sent
			return;
		}

		//prepare mail data
		$admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());
		$invitee_name = $booking_obj->get_invitee_name();
		$invitee_name_parts = explode(' ', $invitee_name, 2);
		$hi_invitee_name = $invitee_name_parts[0];

		$timezone_name = wp_timezone()->getName();
		$booking_from_time = $booking_obj->get_booking_from_time();
		$booking_to_time = $booking_obj->get_booking_to_time();

		$schedule_time = WPCal_DateTime_Helper::DateTime_Obj_to_unix($booking_from_time) - (24 * 60 * 60);

		if( $schedule_time < time() ){
			//schedule_time already crossed lets not send the reminder
			return true;
		}

		if( $booking_obj->get_invitee_tz() ){
			$timezone_name = $booking_obj->get_invitee_tz();			
			$booking_from_time->setTimezone( new DateTimeZone($timezone_name) );
			$booking_to_time->setTimezone( new DateTimeZone($timezone_name) );
		}
		
		$booking_from_to_time_str_with_tz = WPCal_DateTime_Helper::DateTime_Obj_to_from_and_to_full_date_time_with_tz($booking_from_time, $booking_to_time);

		$location_html = self::get_booking_location_html($booking_obj, 'user');

		$mail_data = [
			'booking_admin_display_name' => $admin_details['display_name'],
			'service_name' => $booking_obj->service_obj->get_name(),
			'hi_invitee_name' => $hi_invitee_name,
			'location_html' => $location_html,
			'add_event_to_google_calendar_url' => $booking_obj->get_add_event_to_google_calendar_url(),
			'download_ics_url' => $booking_obj->get_download_ics_url(),
			'reschedule_url' => $booking_obj->get_redirect_reschedule_url(),
			'cancel_url' => $booking_obj->get_redirect_cancel_url(),
			'booking_from_to_time_str_with_tz' => $booking_from_to_time_str_with_tz,
		];

		include(WPCAL_PATH . '/templates/emails/invitee_booking_reminder.php');

		if( !isset($mail_subject) || !isset($mail_body) ){
			throw new WPCal_Exception('mail_template_output_error');
		}

		$from_email = '';
		//$to_email = $booking_obj->get_invitee_name().' <'.$booking_obj->get_invitee_email().'>';
		$to_email = $booking_obj->get_invitee_email();
		$subject = $mail_subject;
		$body = $mail_body;

		$options = [
			'event_id' => $booking_obj->get_unique_link().'-invitee_booking_reminder',
			'reply_to' => $admin_details['email'],
			//'reply_to' => $admin_details['display_name'].' <'.$admin_details['email'].'>',
			'schedule_time' => $schedule_time
		];

		return self::send_mail($from_email, $to_email, $subject, $body, $options);
	}

	public static function delete_invitee_booking_reminder(WPCal_Booking $booking_obj){
		
		$event_id = $booking_obj->get_unique_link().'-invitee_booking_reminder';
		return self::delete_scheduled_mail($event_id);
	}

	private static function get_booking_location_html($booking_obj, $whos_view){
		$location = $booking_obj->get_location();
		if( empty($location) || empty($location['type']) ){
			return '';
		}

		$label_of_location_types = [
			'zoom_meeting' => 'Zoom',
			'gotomeeting_meeting' => 'GoToMeeting',
			'googlemeet_meeting' => 'Google Hangout / Meet',
		];

		$location_html = '
		<tr>
		<td style="padding: 10px 0;">
		  <strong style="font-size: 11px; text-transform: uppercase;"
			>Location</strong
		  ><br />';
		  
		if( in_array($location['type'],['zoom_meeting', 'gotomeeting_meeting', 'googlemeet_meeting']) && !empty($location['form']['location']) ) {

			$location_html .= '
		  <span style="color: #7c7d9c;">'.$label_of_location_types[$location['type']].' Web Conference</span>';
		  $location_html .= '<br />
		  <span style="color: #7c7d9c;"><a href="'.$location['form']['location'].'">'.$location['form']['location'].'</a></span>';

		  if( !empty($location['form']['password_data']['password']) ) {
			  $password_label = $location['form']['password_data']['label'] ? $location['form']['password_data']['label'] : 'Password';
			$location_html .= '<br />
			<span style="color: #7c7d9c;">'.$password_label.': '.$location['form']['password_data']['password'].'</span>';
		  }
		 
		  $location_html .= '<br />
		  <span style="font-size: 11px;">You can join from any device.</span>';
		}

		if( $location['type'] == 'phone' && !empty($location['form']['location'])  && !empty($location['form']['who_calls']) ) {
			//$location_html .= '<span style="color: #7c7d9c;">Phone call</span>';
			$location_str = $booking_obj->get_location_str($whos_view, $html=true);
			$location_html .= '<br />
			<span style="color: #7c7d9c;">'.$location_str.'</span>';
		}

		if( in_array($location['type'],['physical', 'custom', 'ask_invitee']) && !empty($location['form']['location'])  ){
			$location_html .= '
		  <span style="color: #7c7d9c;">'.$location['form']['location'].'</span>';
		}

		if( in_array($location['type'],['physical', 'custom']) && !empty($location['form']['location_extra'])  ){
			$location_html .= '<br>
		  <span style="color: #7c7d9c;">'.$location['form']['location_extra'].'</span>';
		}

		$location_html .= '
			</td>
		</tr>
		';
		return $location_html;
	}
}
