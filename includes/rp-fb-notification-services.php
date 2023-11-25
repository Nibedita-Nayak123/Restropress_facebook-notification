<?php
/**
 * RP_facebook_Notification
 *
 * @package RP_facebook_Notification
 * @since 1.0
 */
defined( 'ABSPATH' ) || exit;
// Inside your plugin, create a new class for Facebook Messenger notifications
class RP_Facebook_Notification_Service {
    protected $data;
    protected $options;
    protected $admin_facebook_user_id;
    protected $facebook_app_id;
    protected $facebook_app_secret;
    protected $facebook_page_token;
    public function __construct() {
        $this->facebook_app_id = rpress_get_option( 'facebook_app_id' );
        $this->admin_facebook_user_id = rpress_get_option( 'admin_facebook_user_id' );
        $this->facebook_page_id =rpress_get_option( 'facebook_page_id' );
        $this->facebook_page_token =rpress_get_option( 'facebook_page_token' );
        $this->facebook_page_secret =rpress_get_option( 'facebook_page_secret' );
        $this->admin_facebook_user_id =rpress_get_option( 'admin_facebook_user_id'  );
}
    /**
     * Send Facebook Messenger Notification
     *
     * @param recipient user ID, message
     * @return mixed
     * @since 1.0
     */
    public function send_text_notification( $message) {
        $response = array();
         // Example code to send a message using Facebook Graph API
        $url = "https://graph.facebook.com/v18.0/".$this->facebook_page_id."/messages?access_token=" . $this->facebook_page_token;
        $data = json_encode(array(
            "recipient" => array("id" => $this->admin_facebook_user_id),
            "message" => array("text" => $message),
            "messaging_type"=>"MESSAGE_TAG",
            "tag"=> "POST_PURCHASE_UPDATE"
        ));
        $headers = array(
            'Content-Type: application/json',
        );
        // Use cURL or another method to make an HTTP POST request to the Facebook Graph API
        $response = $this->_curl($url, $data, $headers, true);
        // Process the API response and handle errors as needed
        return $response;
     }
    /**
     * cURL to get response from the API
     */
  public function _curl( $url = '', $data = array(), $headers = array(), $is_post = false, $curl_options = array() ) { 
    if (empty($url)) {
      return false;
    }
    $response = '';
    try {
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 300);
      
      if ( !empty( $data ) ) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      }
      
      if ( !empty( $headers ) ) {
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
      }
      curl_setopt( $ch, CURLOPT_POST, $is_post );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
      
      if ( !empty( $curl_options ) ) {
        foreach ( $curl_options as $option => $value ) {
          $curl_option = constant( $option );
          if ($curl_option) {
            curl_setopt( $ch, $curl_option, $value );
          }
        }
      }
      $response = curl_exec($ch);
    } catch (Exception $ex) {
        error_log($ex->getMessage());
    }
      return $response;
  }
}
