<?php
defined( 'ABSPATH' ) || exit;
class RP_FB_Notification_Loader {  
  public $version = '1.0';
  /**
   * The single instance of the class.
   *
   * @var RP_Facebook_Notification
   * @since 1.0
   */
  protected static $_instance = null;
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  public function __construct() {
    $this->define_constants();
    $this->includes();
    $this->init_hooks();
  }
  private function define( $name, $value ) {
    if ( ! defined( $name ) ) {
      define( $name, $value );
    }
  }
  private function define_constants() {
    $this->define( 'RP_FACEBOOK_NOTIFICATION_VERSION', $this->version );
    $this->define( 'RP_FACEBOOK_NOTIFICATION_PLUGIN_DIR', plugin_dir_path( RP_FACEBOOK_NOTIFICATION_FILE ) );
    $this->define( 'RP_FACEBOOK_NOTIFICATION_PLUGIN_URL', plugin_dir_url( RP_FACEBOOK_NOTIFICATION_FILE ) );
    $this->define( 'RP_FACEBOOK_NOTIFICATION_BASE', plugin_basename( RP_FACEBOOK_NOTIFICATION_FILE ) );
  }
  private function init_hooks() {
    add_action( 'admin_notices', array( $this, 'rp_facebook_notification_required_plugins' ) );
    add_filter( 'plugin_action_links_'.RP_FACEBOOK_NOTIFICATION_BASE, array( $this, 'rp_facebook_notification_settings_link' ) ); 
  }
  private function includes() {
    require_once RP_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/admin/rp-fb-notification-settings.php';
    require_once RP_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/admin/rp-fb-notification-admin-fields.php';
    require_once RP_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/rp-fb-notification.php';
    require_once RP_FACEBOOK_NOTIFICATION_PLUGIN_DIR . '/vendor/autoload.php';
    require_once RP_FACEBOOK_NOTIFICATION_PLUGIN_DIR . 'includes/rp-fb-notification-services.php'; 
  }
  public function rp_facebook_notification_required_plugins() {
    if ( ! is_plugin_active( 'restropress/restro-press.php' ) ) {
      $plugin_link = 'https://wordpress.org/plugins/restropress/';
      echo '<div id="notice" class="error"><p>' . sprintf( __( 'facebook Notification For RestroPress requires <a href="%1$s" target="_blank"> RestroPress </a> plugin to be installed. Please install and activate it', 'rp-facebook-notification' ), esc_url( $plugin_link ) ).  '</p></div>';
      deactivate_plugins( '/restropress-facebook-notification/restropress-facebook-notification.php' );
    }
  }
  public function rp_facebook_notification_settings_link( $links ) {
    $link = admin_url( 'admin.php?page=rpress-settings&tab=facebook_notification' );
    $settings_link = sprintf( __( '<a href="%1$s">Settings</a>', 'rp-facebook-notification' ), esc_url( $link ) ); 
    array_unshift( $links, $settings_link ); 
    return $links;
  }
}