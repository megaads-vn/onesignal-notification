<?php

namespace MegaAds;

use Exception;
use GuzzleHttp\Client as HttpClient;

class OneSignalNotification {

    private $appId;
    private $apiKey;
    private $authKey;
    private $httpClient;

    const MESSAGE_AUTH_KEY = "Missing params USER AUTH KEY on init OneSignalNotification Service. Please try again...";
    const NOTIFICATION_ID = "Missing params NOTIFICATION_ID. Please try again...";
    const PLAYER_ID = "Missing params PLAYER_ID. Please try again...";
    const EMPTY_OPTIONS = "Missing params EMPTY_OPTIONS. Please try again...";

    public function __construct() {
        $args = func_get_args();
        if(sizeof($args) < 2) throw new Exception("Missing params APP_ID, API_KEY. Please try again...");

        $this->appId = $args[0];
        $this->apiKey = $args[1];

        if (isset($args[2])) $this->authKey = $args[2];

        $this->httpClient = new HttpClient(['base_uri' => 'https://onesignal.com/api/v1/']);
    }

    /**
     * Create Notification
     * @param:
     *   $options - array - build custom param
     *   $param - array
     *       Example PUSH: array(
     *           'title' => ['en' => 'Title'],
     *           'content' => ['en' => 'Message'],
     *           'url' => 'https://tuananhzippy.info',
     *       )
     *       Example EMAIL: array(
     *           'subject' => 'Title',
     *           'body' => "<a href='https://tuananhzippy.info'>Tuan Anh Zippy</a>",
     *           'to' => ['kieutuananh1995@gmail.com']
     *       )

     *   $type: string - default is PUSH
     *@return: object
    */
    public function create($options = array(), $params = array(), $type = 'push' ) {

        if(!empty($options)) {
            $fields = $options;
        } else {
            switch($type) {
                case "email": {

                    if(!isset($params['subject']) || !isset($params['body']) || !isset($params['to']))
                        throw new Exception("Missing params SUBJECT, BODY, TO. Please try again...");

                    $fields = array(
                        'app_id' => $this->appId,
                        'email_subject' => $params['subject'],
                        'email_body' => $params['body'],
                        'include_email_tokens' => $params['to'] //limit 2000
                    );

                    if(!empty($params['email_from_name'])) {
                        $fields['email_from_name'] = $params['email_from_name'];
                    }

                    if(!empty($params['email_from_address'])) {
                        $fields['email_from_address'] = $params['email_from_address'];
                    }
                } break;

                case "push": {

                    if(!isset($params['title']) || !isset($params['content']))
                        throw new Exception("Missing params TITLE, CONTENT. Please try again...");

                    if (!isset($params['url']) || !filter_var($params['url'], FILTER_VALIDATE_URL))
                        throw new Exception("URL not valid. Please try again...");

                    $fields = array(
                        'app_id' => $this->appId,
                        "url" => $params['url'],
                        'contents' => $params['content'],
                        'headings' => $params['title'],
                        'subtitle' => $params['title'],
                    );

                    if(!empty($params['to'])) {
                        $fields['include_player_ids'] = $params['to'];
                    } else {
                        $fields['included_segments'] = array('All');
                    }

                    if(!empty($params['filters'])) {
                        $fields['filters'] = $params['filters'];
                    }
                } break;

                default: break;
            }
        }

        if(empty($fields)) throw new Exception("Can't Notification. Please try again...");

        $request = $this->httpClient->request('POST', "notifications", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->apiKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $fields
        ));
        return $request->getBody();
    }

    /**
     * Deleting the message is in the scheduled
     * @param $notificationId - string - Example: 9fffae76-e2f4-4ce1-b8c3-38bede7819a5
     * @return array
    */
    public function delete($notificationId = null) {
        if(empty($notificationId)) throw new Exception(self::NOTIFICATION_ID);

        $request = $this->httpClient->request('DELETE', "notifications/$notificationId?app_id=".$this->appId, array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->apiKey
            )
        ));
        return $request->getBody();
    }

    /**
     * View the details of all of your current OneSignal apps
     * @param
     * @return array
    */
    public function viewApps() {
        if(empty($this->authKey))
            throw new Exception(self::MESSAGE_AUTH_KEY);

        $request = $this->httpClient->request('GET', "apps", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->authKey
            )
        ));
        return $request->getBody();
    }

    /**
     * View the details of a single OneSignal app
     * @param $appId - Example: 763c4975-0401-43e8-8e13-45ff4a75f63f
     * @return array
    */
    public function viewAnApp($appId = null) {
        if(empty($this->authKey))
            throw new Exception(self::MESSAGE_AUTH_KEY);

        if(empty($appId)) $appId = $this->appId;

        $request = $this->httpClient->request('GET', "apps/$appId", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->authKey
            )
        ));
        return $request->getBody();
    }

    /**
     * Creates a new OneSignal app
     * @param $params - array - Documentation: https://documentation.onesignal.com/reference#create-an-app
     * @return array
    */
    public function createApp($params = array()) {
        if(empty($this->authKey))
            throw new Exception(self::MESSAGE_AUTH_KEY);

        if(!isset($params['name']) || !isset($params['chrome_web_origin']))
            throw new Exception("Missing params NAME, WEB ORIGIN. Please try again...");

        if(empty($params)) throw new Exception("Can't Create APP. Please try again...");

        $request = $this->httpClient->request('POST', "apps", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->authKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $params
        ));
        return $request->getBody();
    }

    /**
     * Updates the name or configuration settings of an existing OneSignal app
     * @param $appId - Example: 763c4975-0401-43e8-8e13-45ff4a75f63f
     * @param $params - array - Documentation: https://documentation.onesignal.com/reference#update-an-app
     * @return object
    */
    public function updateApp($appId = null, $params = array()) {
        if(empty($appId)) $appId = $this->appId;

        if(empty($params)) throw new Exception("Can't Updated APP. Please try again...");

        $request = $this->httpClient->request('PUT', "apps/$appId", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->authKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $params
        ));
        return $request->getBody();
    }

    /**
     * View the details of multiple devices in one of your OneSignal apps
     * @param $pageSize - int - How many devices to return. Max is 300. Default is 300
     * @param $pageId - int - Result offset. Default is 0. Results are sorted by id;
     * @return array
    */
    public function viewDevices($appId = null, $pageSize = 300, $pageId = 0) {
        if(empty($appId)) $appId = $this->appId;

        $request = $this->httpClient->request('GET', "players?app_id=$appId&limit=$pageSize&offset=$pageId", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->apiKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $params
        ));
        return $request->getBody();
    }

    /**
     * View the details of an existing device in one of your OneSignal apps
     * @param $playerId - string
     * @return object
    */
    public function viewDevice($playerId = null) {
        if(empty($playerId)) throw new Exception(self::PLAYER_ID);

        $request = $this->httpClient->request('GET', "players/$playerId/?app_id=".$this->appId);
        return $request->getBody();
    }

    /**
     * Register a new device to one of your OneSignal apps
     * @param $options - array - Documentation: https://documentation.onesignal.com/reference#add-a-device
     * @return object
    */
    public function addDevice($options = array()) {
        if(empty($options)) throw new Exception(self::EMPTY_OPTIONS);

        if(!isset($options['app_id'])) $options['app_id'] = $this->appId;

        $request = $this->httpClient->request('POST', "players", array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $options
        ));
        return $request->getBody();
    }

    /**
     * Update an existing device in one of your OneSignal apps
     * @param $options - array - Documentation: https://documentation.onesignal.com/reference#edit-device
     * @return object
    */
    public function editDevice($playerId = null, $options = array()) {
        if(empty($playerId)) throw new Exception(self::PLAYER_ID);

        if(empty($options)) throw new Exception(self::EMPTY_OPTIONS);

        if(!isset($options['app_id'])) $options['app_id'] = $this->appId;

        $request = $this->httpClient->request('PUT', "players/$playerId", array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $options
        ));
        return $request->getBody();
    }

    /**
     * Update a device's session information
     * @param $playerId - string
     * @param $options - array - Documentation: https://documentation.onesignal.com/reference#new-session
     * @return object
    */
    public function newSession($playerId = null, $options = array()) {
        if(empty($playerId)) throw new Exception(self::PLAYER_ID);

        if(empty($options)) throw new Exception(self::EMPTY_OPTIONS);

        $request = $this->httpClient->request('POST', "players/$playerId/on_session", array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $options
        ));
        return $request->getBody();
    }

    /**
     * Generate a compressed CSV export of all of your current user data
     * @param $appId - string
     * @param $options - array - Documentation: https://documentation.onesignal.com/reference#csv-export
     * @return object
    */
    public function export($appId = null, $options = array()) {
        if(empty($appId)) $appId = $this->appId;

        $request = $this->httpClient->request('GET', "players/csv_export?app_id=".$this->appId, array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->apiKey,
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $options
        ));
        return $request->getBody();
    }

    /**
     * View the details of a single notification
     * @param $notificationId - string
     * @return object
    */
    public function viewNotification($notificationId = null) {
        if(empty($notificationId)) throw new Exception(self::NOTIFICATION_ID);

        $request = $this->httpClient->request('GET', "notifications/$notificationId?app_id=".$this->appId, array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->apiKey
            )
        ));
        return $request->getBody();
    }

    /**
     * View the details of multiple notifications
     * @param $pageSize - int - How many devices to notifications. Max is 50. Default is 50
     * @param $pageId - int - Result offset. Default is 0. Results are sorted by id;
     * @param $kind - int - Kind of notifications returned. Default (not set) is all notification types. Dashboard only is 0. API only is 1. Automated only is 3.
     * @return array
    */
    public function viewsNotification($appId = null, $pageSize = 50, $pageId = 0, $kind = 3) {
        if(empty($appId)) $appId = $this->appId;

        $request = $this->httpClient->request('GET', "notifications?app_id=".$this->appId."&limit=$pageSize&offset=$pageId&kind=$kind", array(
            'headers' => array(
                'Authorization' => 'Basic '.$this->apiKey
            )
        ));
        return $request->getBody();
    }

    /**
     * Track when users open a notification
     * @param $notificationId - string
     * @return object
    */
    public function trackOpen($notificationId = null) {
        if(empty($notificationId)) throw new Exception(self::NOTIFICATION_ID);

        $params = array(
            'app_id' => $this->appId,
            'opened' => true
        );

        $request = $this->httpClient->request('PUT', "notifications/$notificationId", array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8'
            ),
            'json' => $params
        ));
        return $request->getBody();
    }
}

?>