<?php
if(!defined( 'ABSPATH' )){ exit;}

abstract class WPCal_Abstract_TP_Calendar{
	//abstract protected function get_list();
	abstract protected function api_refresh_calendars();
	//abstract protected function api_refresh_events();
    //abstract protected function get_events();
    
    abstract protected function api_add_event($cal_details, WPCal_Booking $booking_obj);
    abstract protected function api_update_event($cal_details, WPCal_Booking $booking_obj);
    abstract protected function api_delete_event($cal_details, WPCal_Booking $booking_obj);

	public function get_calendar_id_by_tp_cal_id($tp_cal_id){
		global $wpdb;

		$table_calendars = $wpdb->prefix . 'wpcal_calendars';
		$query = "SELECT `id` FROM `$table_calendars` WHERE `calendar_account_id` = '". $this->cal_account_id . "' AND `tp_cal_id` = '".$tp_cal_id."'";
		$result = $wpdb->get_var($query);
		if(!empty($result)){
			return $result;
		}
		return false;
	}

	protected function do_add_or_update_calendar($cal_data){
		global $wpdb;
		
		$table_calendars = $wpdb->prefix . 'wpcal_calendars';
		$cal_id = $this->get_calendar_id_by_tp_cal_id($cal_data['tp_cal_id']);
		
        $cal_data['updated_ts'] = time();
        if(!empty($cal_id)){
            $result = $wpdb->update($table_calendars, $cal_data, ['id' => $cal_id]);
            if($result === false){
                throw new WPCal_Exception('db_error', '', $wpdb->last_error);
            }
        }
        else{
            $cal_data['status'] = '1';
			$cal_data['added_ts'] = $cal_data['updated_ts'];
            $result = $wpdb->insert($table_calendars, $cal_data);
            if($result === false){
                throw new WPCal_Exception('db_error', '', $wpdb->last_error);
            }
        }
        return $result;
    }

    protected function update_calendar_sync_status($cal_id, $new_status, $old_status){
        global $wpdb;

        $cal_data = [
            'list_events_sync_status' => $new_status, 'list_events_sync_last_update_ts' => time()
        ];

        $where = ['id' => $cal_id, 'list_events_sync_status' => $old_status];

        $table_calendars = $wpdb->prefix . 'wpcal_calendars';
        
        $result = $wpdb->update($table_calendars, $cal_data, $where);
        if($result === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        return $result;
    }
    
    protected function do_add_or_update_calendar_event($cal_id, $event_data){
        global $wpdb;
        
        var_dump('====cal_id=event_data===', $cal_id, $event_data);
		
		$table_calendar_events = $wpdb->prefix . 'wpcal_calendar_events';
		$event_id = $this->get_event_id_by_tp_event_id($cal_id, $event_data['tp_event_id']);
		
        $event_data['updated_ts'] = time();
        if(!empty($event_id)){
            $result = $wpdb->update($table_calendar_events, $event_data, ['id' => $event_id]);
            if($result === false){
                throw new WPCal_Exception('db_error', '', $wpdb->last_error);
            }
        }
        else{
			$event_data['added_ts'] = $event_data['updated_ts'];
            $result = $wpdb->insert($table_calendar_events, $event_data);
            if($result === false){
                throw new WPCal_Exception('db_error', '', $wpdb->last_error);
            }
        }
        wpcal_service_availability_slots_mark_refresh_cache_by_admin($this->cal_account_details->admin_user_id);
        return $result;
    }

    protected function delete_calendar_event($cal_id, $tp_event_id){
        var_dump('====cal_id=tp_event_id===', $cal_id, $tp_event_id);

		global $wpdb;
		
		$table_calendar_events = $wpdb->prefix . 'wpcal_calendar_events';
        $result = $wpdb->delete($table_calendar_events, ['calendar_id' => $cal_id, 'tp_event_id' => $tp_event_id]);
        if($result === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        wpcal_service_availability_slots_mark_refresh_cache_by_admin($this->cal_account_details->admin_user_id);
        return $result;
    }
    
    protected function get_event_id_by_tp_event_id($cal_id, $tp_event_id){
        global $wpdb;

		$table_calendar_events = $wpdb->prefix . 'wpcal_calendar_events';
		$query = "SELECT `id` FROM `$table_calendar_events` WHERE `calendar_id` = '". $cal_id . "' AND `tp_event_id` = '".$tp_event_id."'";
		$result = $wpdb->get_var($query);
		if(!empty($result)){
			return $result;
		}
		return false;
    }
	
	protected function load_account_details(){
        global $wpdb;

        $table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
		$query = "SELECT * FROM `$table_calendar_accounts` WHERE id = '". $this->get_cal_account_id() . "' AND `provider` = '".$this->get_provider()."'";
        $result = $wpdb->get_row($query);
        if(empty($result)){
            throw new WPCal_Exception('calendar_account_id_not_exists');
        }

        $this->api_token = wpcal_decode_token($result->api_token);
        unset($result->api_token);
        $this->cal_account_details = $result;
    }

    protected function update_account_details($details){
        global $wpdb;

        $data = wpcal_get_allowed_fields($details, $this->get_cal_account_details_edit_allowed_keys());
        isset($data['api_token']) ? $data['api_token'] = wpcal_encode_token($data['api_token']) : '';

        $table = $wpdb->prefix . 'wpcal_calendar_accounts';
        $result = $wpdb->update( $table, $data, ['id' => $this->get_cal_account_id()]);
        if($result === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        $this->load_account_details();
    }

    protected function get_all_conflict_calendars(){
        global $wpdb;
		
        $table_calendars = $wpdb->prefix . 'wpcal_calendars';
        $query = "SELECT * FROM `$table_calendars` WHERE `calendar_account_id` = '". $this->get_cal_account_id() . "' AND `status` = '1' AND `is_conflict_calendar` = '1'";
		$result = $wpdb->get_results($query);
		if(!empty($result)){
			return $result;
		}
		return [];
    }

    protected function add_or_update_calendar_account($details){
        global $wpdb;

        if( !is_array($details) ){
            return false;
        }

        $data = wpcal_get_allowed_fields($details, ['account_email', 'api_token']);
        $table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
        $current_admin_user_id = get_current_user_id();

        //check if exists then update
        $query = "SELECT `id` FROM `$table_calendar_accounts` WHERE `admin_user_id` = '".$current_admin_user_id."' AND `account_email` = '".$data['account_email']."' ";
        $calendar_account_id = $wpdb->get_var($query);

        if( !empty($calendar_account_id) ){
            $this->cal_account_id = $calendar_account_id;
            $this->update_account_details(['api_token' => $data['api_token']]);
            return $calendar_account_id;
        }

        isset($data['api_token']) ? $data['api_token'] = wpcal_encode_token($data['api_token']) : '';

        //add it here
        $data['provider'] = $this->get_provider();
        $data['status'] = '1';
        $data['admin_user_id'] = $current_admin_user_id;

        $data['added_ts'] = $data['updated_ts'] = time();

        $result = $wpdb->insert( $table_calendar_accounts, $data );
        if($result === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        $calendar_account_id = $wpdb->insert_id;
        return $calendar_account_id;
    }

    protected function remove_calendar_account_and_its_data(){
        global $wpdb;

        $table_calendar_accounts = $wpdb->prefix . 'wpcal_calendar_accounts';
        $table_calendars = $wpdb->prefix . 'wpcal_calendars';
		$table_calendar_events = $wpdb->prefix . 'wpcal_calendar_events';
        
        $cal_account_id = $this->cal_account_id;

        if(empty($cal_account_id)){
            return;
        }

        $query1 = "SELECT `id` FROM `$table_calendars` WHERE `calendar_account_id` = '".$cal_account_id."'";
        $calendar_ids = $wpdb->get_col($query1);

        if(!empty($calendar_ids)){
            $query2 = "DELETE FROM `$table_calendar_events` WHERE `calendar_id` IN(". implode(', ', $calendar_ids) .")";
            $delete_calendar_events = $wpdb->query($query2);
            if( $delete_calendar_events === false){
                throw new WPCal_Exception('db_error', '', $wpdb->last_error);
            }
            
            $query3 = "DELETE FROM `$table_calendars` WHERE `id` IN(". implode(', ', $calendar_ids) .")";
            $delete_calendars = $wpdb->query($query3);
            if( $delete_calendars === false){
                throw new WPCal_Exception('db_error', '', $wpdb->last_error);
            }
        }

        $delete_calendar_account= $wpdb->delete($table_calendar_accounts, ['id' => $cal_account_id]);
        if( $delete_calendar_account === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }

        wpcal_service_availability_slots_mark_refresh_cache_by_admin($this->cal_account_details->admin_user_id);

        return true;
    }

}