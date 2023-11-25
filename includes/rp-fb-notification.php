<?php
/**
 * RP_fb_Notification
 *
 * @package RP_fb_Notification
 * @since 1.0
 */
defined( 'ABSPATH' ) || exit;
class RP_fb_Notification {
  protected $data;
  public function __construct() {
  }
  /**
   * Send test notification
   *
   * @return JSON Object
   * @since 1.0
   */
  public function rp_facebook_test_notification_service() {
    // Get the recipient user ID and test message from the AJAX request
    $recipient_user_id = sanitize_text_field($_POST['recipient_user_id']);
    $test_message = sanitize_text_field($_POST['test_message']);
    // Create an instance of your Facebook notification service
    $facebook_notification_service = new RP_Facebook_Notification_Service();
    // Send the test Facebook notification
    $response = $facebook_notification_service->send_text_notification( $test_message);
    if ($response) {
        // Notification sent successfully
        wp_send_json_success(array('message' => 'Test message sent successfully!'));
    } else {
        // Handle errors
        wp_send_json_error(array('message' => 'Failed to send the test message.'));
    }
    wp_die();
  }
}




