<?php
/**
* Plugin Name: RestroPress - Facebook Notifications
* Description: This plugin allows you to send Facebook notifications when there is a new order or order update.
* Plugin URI: https://restropress.com/?utm_source=wp-plugins&utm_campaign=plugin-uri&utm_medium=wp-dash
* Version: 1.0
* Author: nibedita
* Author URI: http://restropress.com/?utm_source=wp-plugins&utm_campaign=author-uri&utm_medium=wp-dash
* Text Domain: rp-fb-notification
* Domain Path: /languages/
*/

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'RP_FACEBOOK_NOTIFICATION_FILE' ) ) {
  define( 'RP_FACEBOOK_NOTIFICATION_FILE', __FILE__ );
}

// Include the main RP_Facebook_Notification class.
if ( ! class_exists( 'RP_FB_Notification_Loader', false ) ) {
  include_once dirname( __FILE__ ) . '/includes/class-rp-fb-notification-loader.php';
}

function RestroPress_Facebook_Notification() {
  return RP_FB_Notification_Loader::instance();
}

RestroPress_Facebook_Notification();
