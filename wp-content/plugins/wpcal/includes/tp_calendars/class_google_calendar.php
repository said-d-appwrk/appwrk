<?php
if(!defined( 'ABSPATH' )){ exit;}

include_once( WPCAL_PATH . '/includes/tp_calendars/abstract_tp_calendar.php');
include_once( WPCAL_PATH . '/lib/google-api-php-client/vendor/autoload.php');

class WPCal_TP_Google_Calendar extends WPCal_Abstract_TP_Calendar{

    private $api = null;
    protected $cal_account_id;
    protected $cal_account_details;
    protected $cal_account_details_edit_allowed_keys = ['status', 'api_token', 'list_calendars_sync_token', 'list_calendars_sync_last_update_ts'];
    protected $api_token = '';

    private $provider = 'google_calendar';

    private $api_client_keys='{"web":{"client_id": "828579377188-ctuoaec27lnbf8schfgfldca9fp3ch3t.apps.googleusercontent.com","project_id": "wpcal-plugin-user-sync","auth_uri": "https://accounts.google.com/o/oauth2/auth","token_uri": "https://oauth2.googleapis.com/token","auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs","client_secret": "eyT4GKrC6WEXqGx8HXg1Gito"}}';

    private $api_send_updates = 'all';

    public function __construct($cal_account_id){
        $this->cal_account_id = $cal_account_id;

        if($this->cal_account_id > 0){
            $this->load_account_details();
        }
    }

    public function get_provider(){
        return $this->provider;
    }

    public function get_cal_account_id(){
        return $this->cal_account_id;
    }

    public function get_cal_account_details_edit_allowed_keys(){
        return $this->cal_account_details_edit_allowed_keys;
    }

    public function set_api(){
        if($this->api === null){
            $client = $this->get_api_client();
            $service = new Google_Service_Calendar($client);
            $this->api = $service;
        }
    }

    public function may_api_refresh_calendars($attempt=0){
        if( $this->cal_account_details->list_calendars_sync_token  > (time() - (12 * 60 * 60)) ){
            //task last ran less than given time
            return false;
        }
        $this->api_refresh_calendars();
    }

    public function api_refresh_calendars($attempt=0){
        if($attempt > 1){
            return false;
        }

        $this->set_api();
        $calendar_list = $this->api->calendarList;
        $opt_params = array();
        $inital_sync_token = $this->cal_account_details->list_calendars_sync_token;
        if($inital_sync_token){
            $opt_params['syncToken'] = $inital_sync_token;
        }
        try{
        $list = $calendar_list->listCalendarList($opt_params);
        }
        catch(Google_Service_Exception $e){
            if( $e->getCode() == 410 ){
                $this->update_account_details(array('list_calendars_sync_token' => null));
                $attempt++;
                return $this->api_refresh_calendars($attempt);
            }
            throw $e;
        }
        
        while(true) {
            //var_dump($list);
            foreach ($list->getItems() as $list_item) {
                $this->add_or_update_calendar($list_item);
            }
            $page_token = $list->getNextPageToken();
            if ($page_token) {
              $opt_params = array('pageToken' => $page_token);
              $list = $calendar_list->listCalendarList($opt_params);
            } else {
                $next_sync_token = $list->getNextSyncToken( );
                if( $inital_sync_token != $next_sync_token ){
                    $this->update_account_details(array('list_calendars_sync_token' => $next_sync_token, 'list_calendars_sync_last_update_ts' => time()));
                }
              break;
            }
        }
    }

    private function add_or_update_calendar($cal_item){
        if( $cal_item->getDeleted() ){
            return true;
        }
        global $wpdb;
        $cal_data = [];
        $cal_data['calendar_account_id'] = $this->cal_account_id;
        $cal_data['name'] = $cal_item->getSummary();
        $cal_data['tp_cal_id'] = $cal_item->getId();
        $cal_data['is_readable'] = (int) $this->is_readable($cal_item->getAccessRole());
        $cal_data['is_writable'] = (int) $this->is_writable($cal_item->getAccessRole());
        $cal_data['is_primary'] = (int) ($cal_item->getPrimary() === true);
        $cal_data['timezone'] = $cal_item->getTimeZone();

        $result = $this->do_add_or_update_calendar($cal_data);
        return $result;
    }

    private function is_readable($access_role){
        if( in_array($access_role, array('owner', 'writer', 'reader'), true) ){
            return true;
        }
        return false;
    }

    private function is_writable($access_role){
        if( in_array($access_role, array('owner', 'writer'), true) ){
            return true;
        }
        return false;
    }

    public function refresh_events_for_all_conflict_calendars(){
        $this->may_refresh_events_for_all_conflict_calendars($check_and_do=false);
    }

    public function may_refresh_events_for_all_conflict_calendars($check_and_do=true){
        $conflict_calendars = $this->get_all_conflict_calendars();
        //get_min_max_dates_based_on_services();
        $min_date = WPCal_DateTime_Helper::DateTime_DB_to_DateTime_obj('now');
        $max_date = clone $min_date;
        $max_date->add(new DateInterval('P11Y'));
        foreach($conflict_calendars as $calendar){
            if($check_and_do){
                $this->may_api_refresh_calendar_events($calendar, $min_date, $max_date);
            }
            else{
                $this->api_refresh_calendar_events($calendar, $min_date, $max_date);
            }
        }
    }

    private function may_api_refresh_calendar_events($calendar, $min_date, $max_date){
        if( !( $calendar->list_events_sync_status === 'completed' || $calendar->list_events_sync_status == NULL ) ){
            //task running else where
            return false;
        }
        if( $calendar->list_events_sync_last_update_ts > (time() - (5 * 60)) ){
            //task last ran less than given time
            return false;
        }
        $status_update = $this->update_calendar_sync_status($calendar->id, 'running', $calendar->list_events_sync_status);
        if( $status_update != '1' ){
            //status of sync may changed by other instance
            return false;
        }
        $this->api_refresh_calendar_events($calendar, $min_date, $max_date);
        $status_update = $this->update_calendar_sync_status($calendar->id, 'completed', 'running');
    }

    public function api_refresh_calendar_events($calendar, $min_date, $max_date, $attempt=0){
        $cal_id = $calendar->id;
        if($attempt > 1){
            return false;
        }

        $this->set_api();

        $tp_cal_id = $calendar->tp_cal_id;
        $opt_params = array(
            //'orderBy' => 'startTime',
            'singleEvents' => true,
            //'showDeleted' => true,
            'timeMin' => $min_date->format('c'),//won't work with sync token
            'timeMax' => $max_date->format('c'),//won't work with sync token
            'timeZone' => wp_timezone()->getName()
        );

        $inital_sync_token = $calendar->list_events_sync_token;
        if($inital_sync_token){
            // the following params won't work if request have sync token iCalUID, orderBy, privateExtendedProperty, q, sharedExtendedProperty, timeMin, timeMax, updatedMin
            //Another thing to note is even inital request have timeMin and timeMax, events coming via sync token will be any new events and change in old events without any time limitations etc
            $opt_params = [
                'singleEvents' => true,
                'syncToken' => $inital_sync_token,
                'timeZone' => wp_timezone()->getName()
            ];
        }

        try{
            $events = $this->api->events->listEvents($tp_cal_id, $opt_params);
        }
        catch(Google_Service_Exception $e){
            if( $e->getCode() == 410 ){
                $this->do_add_or_update_calendar(array('list_events_sync_token' => null, 'tp_cal_id' => $tp_cal_id));
                $attempt++;
                return $this->api_refresh_calendar_events($calendar, $min_date, $max_date, $attempt);
            }
            throw $e;
        }
        
        while(true) {
           //var_dump($events);
            foreach ($events->getItems() as $event) {
                $this->handle_calendar_event($cal_id, $tp_cal_id, $event);
            }
            $page_token = $events->getNextPageToken();
            if ($page_token) {
              //$opt_params = array('pageToken' => $page_token);
              $opt_params['pageToken'] = $page_token;
              echo '<br>=============================================><br>';
              $events = $this->api->events->listEvents($tp_cal_id, $opt_params);
            } else {
                $next_sync_token = $events->getNextSyncToken();
                //var_dump(' $next_sync_token,',  $next_sync_token);
                if( $inital_sync_token != $next_sync_token ){
                    $this->do_add_or_update_calendar(array('list_events_sync_token' => $next_sync_token, 'tp_cal_id' => $tp_cal_id));
                }
              break;
            }
        }
    }

    protected function get_booking_id_from_tp_event_id($cal_id, $tp_cal_id,$tp_event_id){

        $booking_id = $this->get_booking_id_from_tp_event_id_and_cal_id($cal_id, $tp_event_id);
        if(!empty($booking_id)){
            return $booking_id;
        }

        //say if TP Calendar account is disconnected and re-connected now try to find with tp_cal_id
        $booking_id = $this->get_booking_id_from_tp_event_id_and_tp_cal_id($cal_id, $tp_cal_id, $tp_event_id);
        return $booking_id;
    }

    protected function get_booking_id_from_tp_event_id_and_cal_id($cal_id, $tp_event_id){
        global $wpdb;

        $table_bookings = $wpdb->prefix . 'wpcal_bookings';
        $query = "SELECT `id` FROM `$table_bookings` WHERE `event_added_calendar_id` = '".$cal_id."' AND `event_added_tp_event_id` = '".$tp_event_id."'";
        $result = $wpdb->get_var($query);
        if(!empty($result)){
            return $result;
        }
        return false;
    }

    protected function get_booking_id_from_tp_event_id_and_tp_cal_id($cal_id, $tp_cal_id, $tp_event_id){
        global $wpdb;

        $table_bookings = $wpdb->prefix . 'wpcal_bookings';
        $query = "SELECT `id`, `admin_user_id` FROM `$table_bookings` WHERE `event_added_tp_cal_id` = '".$tp_cal_id."' AND `event_added_tp_event_id` = '".$tp_event_id."'";
        $result = $wpdb->get_row($query);
        if(empty($result)){
            return false;
        }

        //verify booking's admin_id and cal_id(tp_cal_id is belongs to cal_id) admin are same, tp_cal_id should unique in the whole world atleast for google calendar, in same tp_cal_id connected via another account is also fine
        if( empty($result->admin_user_id) || empty($this->cal_account_details->admin_user_id) ){
            return false;
        }

        if( $result->admin_user_id == $this->cal_account_details->admin_user_id ){
            $booking_id = $result->id;
            return $booking_id;
        }
        return false;
    }

    private function handle_calendar_event($cal_id, $tp_cal_id, $event){
        $status = $event->getStatus();
        $tp_event_id = $event->getId();
        //var_dump('====event_status====', $event, $status);

        $_booking_id = $this->get_booking_id_from_tp_event_id($cal_id, $tp_cal_id, $tp_event_id);
        if($_booking_id){
            // The following commentted, "sync cancellation" will be bringing later
            // $attendees = $event->getAttendees();
            // if(!empty($attendees)){
            //     foreach($attendees as $attendee){
            //         if(!$attendee->getSelf() &&  $attendee->getResponseStatus() == 'declined'){
            //             var_dump('====cal_id=invitee_cancel_via_tp_cal===', $cal_id, $tp_event_id);

            //             wpcal_cancel_booking($_booking_id, $cancel_reason='invitee_cancel_via_tp_cal');
            //             return;
            //         }
            //     }
            // }
            return;
        }

        //The following are events not related to WPCal

        //check for group event declined
        $attendees = $event->getAttendees();
        if(!empty($attendees)){
            foreach($attendees as $attendee){
                if($attendee->getSelf() && $attendee->getResponseStatus() == 'declined'){
                    $this->delete_calendar_event($cal_id, $tp_event_id);
                    return;
                }
            }
        }

        if( $status === 'confirmed' || $status === 'tentative'){
            //add or update event

            $from_time = $this->event_time_to_unix($event->start);
            $to_time = $this->event_time_to_unix($event->end);

            $event_data = [
                'calendar_id' => $cal_id,
                'status' => '1',
                'tp_event_id' => $tp_event_id,
                'from_time' => $from_time,
                'to_time' => $to_time,
            ];
            $this->do_add_or_update_calendar_event($cal_id, $event_data);
        }
        elseif( $status === 'cancelled'){
            //may be delete event
            $this->delete_calendar_event($cal_id, $tp_event_id);
        }
    }

    private function event_time_to_unix($event_time){
        $t = $event_time->dateTime;
        if (empty($t)) {
            $t = $event_time->date;
        }
        $time_obj = WPCal_DateTime_Helper::DateTime_DB_to_DateTime_obj($t);
        $unix_time = WPCal_DateTime_Helper::DateTime_Obj_to_unix($time_obj);
        return $unix_time;
    }

    private function _prepare_booking_event_object_for_api(WPCal_Booking $booking_obj, $add_to_cal_details){

        $service_obj = $booking_obj->service_obj;

        $admin_details = wpcal_get_admin_details($booking_obj->get_admin_user_id());
        $invitee_name = $booking_obj->get_invitee_name();

        if( !empty($admin_details['display_name']) && !empty($invitee_name) ){
            $summary = $admin_details['display_name'] .' and '. $invitee_name;
        }
        else{
            $summary = $service_obj->get_name();
        }

        $location_content_options = [];
       
        $location = $booking_obj->get_location_str();
        if( empty($location) && $booking_obj->get_location_type() === 'googlemeet_meeting' ){
            $location = $booking_obj->get_redirect_meeting_url();
            $location_content_options['overide_location_if_empty'] = $location;
        }

        $form_time = WPCal_DateTime_Helper::DateTime_Obj_to_ISO($booking_obj->get_booking_from_time());
        $to_time = WPCal_DateTime_Helper::DateTime_Obj_to_ISO($booking_obj->get_booking_to_time());

        $location_descr = wpcal_get_booking_location_content($booking_obj, $for='calendar_event', $this->get_provider(), $whos_view='neutral', $location_content_options);

        $admin_attendee = [
            'email' => $add_to_cal_details->tp_cal_id,
            'displayName' => $admin_details['display_name'],
            'responseStatus' => 'accepted'
        ];

        $attendee = [
            'email' => $booking_obj->get_invitee_email(),
            'displayName' => $booking_obj->get_invitee_name(),
            'responseStatus' => 'accepted'
        ];

        $description = 'Event: <b>'.$service_obj->get_name().'</b>
';
        if($location_descr){
            $description .= "\n".$location_descr."\n";
        }

        $description .= '
Need to make changes to this event?
<a href="'.$booking_obj->get_redirect_cancel_url().'">Cancel this event click here</a>
<a href="'.$booking_obj->get_redirect_reschedule_url().'">Reschedule this event click here</a>

Powered by <a href="'.WPCAL_SITE_URL.'?utm_source=gcal&utm_medium=event">WPCal.io</a>';

        $event_data = array(
            'summary' => $summary,
            'location' => $location,
            'description' => $description,
            'start' => array(
              'dateTime' => $form_time,
            ),
            'end' => array(
              'dateTime' => $to_time,
            ),
            'attendees' => array(
                $admin_attendee,
                $attendee,
            ),
            'conferenceData' => null
        );

        if( $booking_obj->get_location_type() === 'googlemeet_meeting'){
            $event_data['conferenceData'] = [];
            $event_data['conferenceData']['createRequest'] = [
                'requestId' => sha1($booking_obj->get_id() .'|'. uniqid('', true)),
                //'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
            ];
        }

        $event = new Google_Service_Calendar_Event($event_data);

        return $event;
    }

    public function api_add_event($cal_details, WPCal_Booking $booking_obj){
        $this->set_api();

        $event = $this->_prepare_booking_event_object_for_api($booking_obj, $cal_details);
          
        $response_event = $this->api->events->insert($cal_details->tp_cal_id, $event, ['sendUpdates' => $this->api_send_updates, 'conferenceDataVersion' => 1]);//improve code NEED try catch

        $booking_id = $booking_obj->get_id();
        $_calendar_id = $cal_details->calendar_id;
        $_tp_cal_id = $cal_details->tp_cal_id;
        $_tp_event_id = $response_event->getId();

        wpcal_booking_update_tp_calendar_event_details($booking_id, $_calendar_id, $_tp_cal_id, $_tp_event_id);
        
        $this->may_handle_conference_data_for_meeting($response_event, $cal_details, $booking_obj);
    }

    public function api_update_event($cal_details, WPCal_Booking $booking_obj){
        $this->set_api();

        $event = $this->_prepare_booking_event_object_for_api($booking_obj, $cal_details);
          
        $response_event = $this->api->events->update($cal_details->tp_cal_id, $booking_obj->get_event_added_tp_event_id(), $event, ['sendUpdates' => $this->api_send_updates, 'conferenceDataVersion' => 1]);//improve code NEED try catch //setting 'conferenceDataVersion' => 1 here may generate new meeting url if all ready exists
        // var_dump($response_event);
        // printf('Event updated: %s\n', $response_event->htmlLink);

        $booking_id = $booking_obj->get_id();
        $_calendar_id = $cal_details->calendar_id;
        $_tp_cal_id = $cal_details->tp_cal_id;
        $_tp_event_id = $response_event->getId();

        wpcal_booking_update_tp_calendar_event_details($booking_id, $_calendar_id, $_tp_cal_id, $_tp_event_id);
        
        $this->may_handle_conference_data_for_meeting($response_event, $cal_details, $booking_obj);
    }

    public function api_update_event_location($cal_details, WPCal_Booking $booking_obj){//purpose of this method is when google meet meeting is required, first it will send temporary url as location. Now it will be updated with original url.
        $this->set_api();

        $event = $this->_prepare_booking_event_object_for_api($booking_obj, $cal_details);
          
        $response_event = $this->api->events->update($cal_details->tp_cal_id, $booking_obj->get_event_added_tp_event_id(), $event, ['sendUpdates' => 'none']);//improve code NEED try catch

    }

    public function api_delete_event($cal_details, WPCal_Booking $booking_obj){
        $this->set_api();
        $response_event = $this->api->events->delete($cal_details->tp_cal_id, $booking_obj->get_event_added_tp_event_id(), ['sendUpdates' => $this->api_send_updates]);//improve code NEED try catch
        //var_dump($response_event);
    }

    public function api_get_event($cal_details, WPCal_Booking $booking_obj){
        $this->set_api();
         
        $response_event = $this->api->events->get($cal_details->tp_cal_id, $booking_obj->get_event_added_tp_event_id());//improve code NEED try catch

        return $response_event;
    }

    public function get_and_set_meeting_url_from_event($cal_details, WPCal_Booking $booking_obj){
        $response_event = $this->api_get_event($cal_details, $booking_obj);
        $this->may_handle_conference_data_for_meeting($response_event, $cal_details, $booking_obj);
    }

    public function may_handle_conference_data_for_meeting($response_event, $cal_details, WPCal_Booking $booking_obj){

        if( $booking_obj->get_location_type() !== 'googlemeet_meeting'){
            return;
        }

        $meeting_link = '';
        $conference_data = $response_event->getConferenceData();
        if($conference_data){
            $conference_create_request = $conference_data->getCreateRequest();
            if($conference_create_request){
                $conference_create_request_status = $conference_create_request->getStatus();
                $conference_create_request_status_code = $conference_create_request_status->getStatusCode();
                if( $conference_create_request_status_code === 'success' ){
                    $entry_points = $conference_data->getEntryPoints();
                    foreach($entry_points as $entry_point){
                        if( $entry_point->getEntryPointType() === 'video' ){
                            $meeting_link = $entry_point->getUri();
                            break;
                        }
                    }
                }
            }
        }
        //what if status_code other than 'success' error handling need to be done. Improve Later

        if(!empty($meeting_link)){
            $location_type = 'googlemeet_meeting';
            $location_form_data = ['location' => $meeting_link ];
            wpcal_booking_update_online_meeting_details($booking_obj, $location_type, $location_form_data, null);

            $booking_obj = wpcal_get_booking($booking_obj->get_id());//reload the data from DB. Because it is updated just above.
            $this->api_update_event_location($cal_details, $booking_obj);
        }
    }

    private function init_api_base_client(){
        $client = new Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $auth_config = json_decode($this->api_client_keys, true);
        $client->setAuthConfig( $auth_config );
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        //$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        //$redirect_uri='https://wpcal-01.com/wp-admin/admin.php?page=wpcal_admin&wpcal_action=google_calendar_receive_token';
        $redirect_uri = WPCAL_GOOGLE_OAUTH_REDIRECT_SITE_URL.'cal-api-receive-it/';        
        $client->setRedirectUri($redirect_uri);
        return $client;
    }

    private function get_api_client(){
        $client = $this->init_api_base_client();

        // // Load previously authorized token from a file, if it exists.
        // // The file token.json stores the user's access and refresh tokens, and is
        // // created automatically when the authorization flow completes for the first
        // // time.
        // $tokenPath = WPCAL_PATH . '/lib/token.json';
        // //var_dump($tokenPath, file_exists($tokenPath));
        // if (file_exists($tokenPath)) {
        //     $accessToken = json_decode(file_get_contents($tokenPath), true);
        //     $client->setAccessToken($accessToken);
        // }

        $accessToken = json_decode($this->api_token, true);
        $client->setAccessToken($accessToken);

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {

                if( isset($_GET['code']) ){

                    $authCode = trim($_GET['code']);

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }
                else{
                    // Request authorization from the user.
                    $authUrl = $client->createAuthUrl();
                    echo '<h3><a  href="'.$authUrl.'">OAuth 2.0 Google here</a></h3>';
                    
                }

                // printf("Open the following link in your browser:\n%s\n", $authUrl);
                // print 'Enter verification code: ';
                // $authCode = trim(fgets(STDIN));

            }
            // Save the token to a file.
            // if (!file_exists(dirname($tokenPath))) {
            //     mkdir(dirname($tokenPath), 0700, true);
            // }
            // file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            $this->api_token = json_encode($client->getAccessToken());
            $this->update_account_details(array('api_token' => $this->api_token));
        }
        return $client;
    }

    public function get_add_account_url(){
        $client = $this->init_api_base_client();

        $site_redirect_url = trailingslashit(admin_url()) . 'admin.php?page=wpcal_admin&wpcal_action=google_calendar_receive_token';
        $state_array = ['site_redirect_url' => $site_redirect_url, 'state_token' => 'dshkjfhksdhfkjhsdkjfhskhfskjdhfkjhsdfkjhsdfkjh'];
        $state = wpcal_base64_url_encode(json_encode($state_array));

        $client->setState($state);

        $authUrl = $client->createAuthUrl();
        return $authUrl;
    }

    public function add_account_after_auth(){
        if( !isset($_GET['code']) || empty($_GET['code']) ){
            return false;            
        }

        $client = $this->init_api_base_client();
        $authCode = trim($_GET['code']);

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        //var_dump($accessToken);
        $client->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }

        $this->api_token = json_encode($client->getAccessToken());

        $this->set_api();

        $calendar_list = $this->api->calendarList;
        //if all goes well
        $primary_calendar_details = $calendar_list->get('primary');
        $new_calendar_account = [];
        $new_calendar_account['account_email'] = $primary_calendar_details->getId();
        $new_calendar_account['api_token'] = $this->api_token;

        $calendar_account_id = $this->add_or_update_calendar_account($new_calendar_account);

        if($calendar_account_id){
            $tp_calendar_obj = new WPCal_TP_Google_Calendar($calendar_account_id);
            $tp_calendar_obj->api_refresh_calendars();
            wpcal_check_and_add_default_calendars_for_current_admin($calendar_account_id);
        }
    }

    public function revoke_access_and_delete_its_data(){
        global $wpdb;
        $client = $this->get_api_client();

        $is_revoked = $client->revokeToken();
        if($is_revoked){
            $this->remove_calendar_account_and_its_data();
        }
        //need to improve as data removing is not confirmed
        return $is_revoked;
    }
}

// /**
//  * Returns an authorized API client.
//  * @return Google_Client the authorized client object
//  */
// function getClient()
// {
//     $client = new Google_Client();
//     $client->setApplicationName('Google Calendar API PHP Quickstart');
//     $client->setScopes(Google_Service_Calendar::CALENDAR);
//     $client->setAuthConfig( WPCAL_PATH . '/lib/Google_Cal_Api_client_id.json');
//     $client->setAccessType('offline');
// 	$client->setPrompt('select_account consent');
// 	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
// 	$client->setRedirectUri($redirect_uri='https://wpcal-01.com/wp-admin/admin.php?page=wpcal_admin_test');

//     // Load previously authorized token from a file, if it exists.
//     // The file token.json stores the user's access and refresh tokens, and is
//     // created automatically when the authorization flow completes for the first
//     // time.
//     $tokenPath = WPCAL_PATH . '/lib/token.json';
//     //var_dump($tokenPath, file_exists($tokenPath));
//     if (file_exists($tokenPath)) {
//         $accessToken = json_decode(file_get_contents($tokenPath), true);
//         $client->setAccessToken($accessToken);
//     }

//     // If there is no previous token or it's expired.
//     if ($client->isAccessTokenExpired()) {
//         // Refresh the token if possible, else fetch a new one.
//         if ($client->getRefreshToken()) {
//             $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//         } else {

//             if( isset($_GET['code'])){

//                 $authCode = trim($_GET['code']);

//                 // Exchange authorization code for an access token.
//                 $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
//                 var_dump($accessToken);
//                 $client->setAccessToken($accessToken);

//                 // Check to see if there was an error.
//                 if (array_key_exists('error', $accessToken)) {
//                     throw new Exception(join(', ', $accessToken));
//                 }
//             }
//             else{
//                 // Request authorization from the user.
//                 $authUrl = $client->createAuthUrl();
//                 echo '<h3><a  href="'.$authUrl.'">OAuth 2.0 Google here</a></h3>';
                
//             }
            

//             // printf("Open the following link in your browser:\n%s\n", $authUrl);
//             // print 'Enter verification code: ';
//             // $authCode = trim(fgets(STDIN));

//         }
//         // Save the token to a file.
//         if (!file_exists(dirname($tokenPath))) {
//             mkdir(dirname($tokenPath), 0700, true);
//         }
//         file_put_contents($tokenPath, json_encode($client->getAccessToken()));
//     }
//     return $client;
// }

// function sample_google_cal_call(){


//     // Get the API client and construct the service object.
//     $client = getClient();
//     $service = new Google_Service_Calendar($client);

//     // Print the next 10 events on the user's calendar.
//     $calendarId = 'primary';
//     $optParams = array(
//     'maxResults' => 10,
//     'orderBy' => 'startTime',
//     'singleEvents' => true,
//     'timeMin' => date('c'),
//     );
//     $results = $service->events->listEvents($calendarId, $optParams);
//     $events = $results->getItems();

//     if (empty($events)) {
//         print "No upcoming events found.\n";
//     } else {
//         print "Upcoming events:\n";
//         foreach ($events as $event) {
//             $start = $event->start->dateTime;
//             if (empty($start)) {
//                 $start = $event->start->date;
//             }
//             printf("%s (%s)\n", $event->getSummary(), $start);
//         }
//     }

// }