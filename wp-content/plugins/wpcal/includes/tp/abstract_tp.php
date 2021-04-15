<?php
if(!defined( 'ABSPATH' )){ exit;}

abstract class WPCal_Abstract_TP{

    private $allowed_provider_details = [
        'zoom_meeting' => ['provider' => 'zoom_meeting', 'provider_type' => 'meeting'],
        'gotomeeting_meeting' => ['provider' => 'gotomeeting_meeting', 'provider_type' => 'meeting']
    ];

    private $allowed_tp_account_fields = [
        'tp_user_id',
        'tp_account_email',
        'api_token',
    ];

    protected function get_provider(){
        return $this->provider;
    }

    protected function get_provider_type(){
        return $this->provider_type;
    }

    protected function get_tp_account_id(){
        return $this->tp_account_id;
    }

    protected function is_valid_provider_details(){

        $current_provider = $this->get_provider();

        if( isset($this->allowed_provider_details[$current_provider]) ){
            return true;
        }
        throw new WPCal_Exception('invalid_tp_provider_details');
    }

    protected function add_or_update_account($details){
        global $wpdb;

        if( !is_array($details) ){
            return false;
        }
        
        $this->is_valid_provider_details();

        $data = wpcal_get_allowed_fields($details, $this->allowed_tp_account_fields);
        $table_tp_accounts = $wpdb->prefix . 'wpcal_tp_accounts';
        $current_admin_user_id = get_current_user_id();

        $tp_account_id = $this->tp_account_id;
        if( !$tp_account_id ){
            //check if exists then update
            $query = "SELECT `id` FROM `$table_tp_accounts` WHERE `provider` = '".$this->get_provider()."' AND `provider_type` = '".$this->get_provider_type()."' AND `admin_user_id` = '".$current_admin_user_id."'";
            $query_where_addl = " AND `tp_account_email` = '".$data['tp_account_email']."'";
            if( !empty($data['tp_user_id']) ){//this will help if email is different and same user id OR user id is different same email.
                $query_where_addl = " AND (`tp_user_id` = '".$data['tp_user_id']."' OR `tp_account_email` = '".$data['tp_account_email']."') ";
            }
            $query .= $query_where_addl;
            $tp_account_id = $wpdb->get_var($query);
        }

        if( !empty($tp_account_id) ){
            $this->tp_account_id = $tp_account_id;
            $this->update_account_details(['api_token' => $data['api_token']]);
            return $tp_account_id;
        }

        isset($data['api_token']) ? $data['api_token'] = wpcal_encode_token($data['api_token']) : '';

        //add it here
        $data['provider'] = $this->get_provider();
        $data['provider_type'] = $this->get_provider_type();
        $data['status'] = '1';
        $data['admin_user_id'] = $current_admin_user_id;

        $data['added_ts'] = $data['updated_ts'] = time();

        $result = $wpdb->insert( $table_tp_accounts, $data );
        if($result === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        $tp_account_id = $wpdb->insert_id;
        return $tp_account_id;
    }
    
    protected function update_account_details($details){
        global $wpdb;

        $data = wpcal_get_allowed_fields($details, $this->allowed_tp_account_fields);
        isset($data['api_token']) ? $data['api_token'] = wpcal_encode_token($data['api_token']) : '';

        $table = $wpdb->prefix . 'wpcal_tp_accounts';
        $result = $wpdb->update( $table, $data, ['id' => $this->get_tp_account_id()]);
        if($result === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        $this->load_account_details();
    }
    
    protected function load_account_details(){
        global $wpdb;

        $table_tp_accounts = $wpdb->prefix . 'wpcal_tp_accounts';
        $query = "SELECT * FROM `$table_tp_accounts` WHERE id = '". $this->get_tp_account_id() . "' AND `provider` = '".$this->get_provider()."' AND `provider_type` = '".$this->get_provider_type()."'";
        $result = $wpdb->get_row($query);
        if(empty($result)){
            throw new WPCal_Exception('tp_account_id_not_exists');
        }

        $this->api_token = wpcal_decode_token($result->api_token);
        unset($result->api_token);
        $this->tp_account_details = $result;
    }

    protected function remove_tp_account_and_its_data(){
        global $wpdb;

        $table_tp_accounts = $wpdb->prefix . 'wpcal_tp_accounts';
        $tp_account_id =$this->get_tp_account_id();

        if(empty($tp_account_id)){
            return;
        }

        $delete_tp_account= $wpdb->delete($table_tp_accounts, ['id' => $tp_account_id]);
        if( $delete_tp_account === false){
            throw new WPCal_Exception('db_error', '', $wpdb->last_error);
        }
        return true;
    }

}