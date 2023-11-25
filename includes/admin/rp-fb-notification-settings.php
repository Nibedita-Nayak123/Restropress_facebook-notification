<?php
defined( 'ABSPATH' ) || exit;
class RP_Facebook_Notification_Settings {
    public function __construct() {
        // Add your initialization code here.
        add_action( 'init', array( $this, 'init' ) );
        // Add the settings page and fields.
        add_filter( 'rpress_settings_tabs', array( $this, 'rp_facebook_notification_settings_tab' ), 10 );
        add_filter( 'rpress_registered_settings', array( $this, 'rp_facebook_notification_settings_fields' ), 10 );
        add_action( 'admin_enqueue_scripts', array( $this, 'facebook_notification_admin_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'facebook_notification_admin_styles' ) );
        add_filter( 'rpress_settings_facebook_notification_sanitize', array( $this, 'rpress_settings_sanitize_facebook_notification' ), 10, 1);
        add_action( 'wp_ajax_rp_facebook_test_notification_service', array( $this, 'rp_facebook_test_notification_service' ), 10 );
        add_action( 'rpress_before_payment_receipt', array( $this, 'rpress_api_new_order_notification' ), 10, 2 );

      }


      public function rpress_api_new_order_notification($payment, $rpress_receipt_args) {
        $enable_admin_notification = rpress_get_option('enable_admin_facebook_notification');
        // Check if admin Facebook notification is enabled
        if ($enable_admin_notification) {
        $payment_id = $payment->ID;
        $payment = new RPRESS_Payment($payment_id);
        $branch_name = get_post_meta($payment_id, 'branch_name', true);
        $customer = $payment->first_name . ' ' . $payment->last_name;
        $fnameBilling = $payment->first_name;
        $phone = isset($payment->payment_meta['user_info']['phone']) ? $payment->payment_meta['user_info']['phone'] : (isset($payment->payment_meta['phone']) ? $payment->payment_meta['phone'] : '');
        $order_total = number_format((float)$payment->total, 2, '.', '');
        $currency = $payment->currency;
        $price = $currency . $order_total;
        $status = rpress_get_order_status($payment_id);
        $service_type = get_post_meta($payment_id, '_rpress_delivery_type', true);
        $service_type = rpress_service_label($service_type);
        $service_date = get_post_meta($payment->ID, '_rpress_delivery_date', true);
        $service_date = !empty($service_date) ? date('F j, Y', strtotime($service_date)) : '';
        $service_time = get_post_meta($payment->ID, '_rpress_delivery_time', true);
        $service_time = $service_time == 'ASAP' ? $service_time : date('h:i A', strtotime($service_time));
        // Get the site title
        $site_name = get_bloginfo('name');
       // Construct the message with placeholders
       $order_details = array(
        'ORDER_NUMBER' => $payment_id,
        'ORDER_STATUS' => $status,
        'SERVICE_DATE' => $service_date,
        'SHOP_NAME' => $site_name,
        'SERVICE_TIME' => $service_time,
        'SERVICE_TYPE' => $service_type,
        'BILLING_FNAME' => $fnameBilling,
        'FULLNAME' => $customer,
        'PHONE' => $phone,
        'PRICE' => $price,
    );
    $admin_facebook_text = rpress_get_option('admin_facebook_text');

    // Loop through the keys in $order_details and replace placeholders in $admin_facebook_text
    foreach ($order_details as $key => $value) {
        $placeholder = '{' . $key . '}';
        $admin_facebook_text = str_replace($placeholder, $value, $admin_facebook_text);
    }
    // Create an instance of your Facebook notification service
      $facebook_notification_service = new RP_Facebook_Notification_Service();

    // Send the Facebook notification
     $response = $facebook_notification_service->send_text_notification($admin_facebook_text);
      }
  
    }




    public function rp_facebook_test_notification_service() {
      // Get the recipient user ID and test message from the AJAX request
      $recipient_user_id = sanitize_text_field($_POST[ 'recipient_user_id' ]);
      $test_message = sanitize_text_field($_POST[ 'test_message' ]);
      $facebook_page_id = rpress_get_option( 'facebook_page_id' );
      $facebook_app_id = rpress_get_option( 'facebook_app_id' );
      $facebook_app_secret = rpress_get_option( 'facebook_app_secret' );
      $facebook_page_token = rpress_get_option( 'facebook_page_token' );
      $admin_facebook_text = rpress_get_option( 'admin_facebook_text' );
      // Create an instance of your Facebook notification service
      $facebook_notification_service = new RP_Facebook_Notification_Service();
      // Send the test Facebook notification
      $response = $facebook_notification_service->send_text_notification($test_message);
      if ($response) {
          // Notification sent successfully
          wp_send_json_success(
            array(
              'message' => 'Test message sent successfully!',
              'response' => $response
            )
          );
      } else {
          // Handle errors
          wp_send_json_error(array('message' => 'Failed to send the test message.'));
      }
      wp_die();
  }
    public function init() {
        // Add your Facebook notification logic here.
    }
    public function rp_facebook_notification_settings_tab( $tabs ) {
        $tabs['facebook_notification']  = __( 'Facebook Notification', 'rp-fb-notification' );
        return $tabs;
    }
    public function facebook_notification_admin_scripts() {
        wp_register_script( 'rp-facebook-sweetalert2', RP_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/js/fb.all.min.js', array( 'jquery' ), RP_FACEBOOK_NOTIFICATION_VERSION );
        wp_register_script( 'rp-facebook-admin-script', RP_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/js/rp-fb-admin.js', array( 'jquery', 'rp-facebook-sweetalert2' ), RP_FACEBOOK_NOTIFICATION_VERSION );
        $params = array(
          'ajax_url'               => rpress_get_ajax_url(),
          'message_sent'          => __( 'Message sent', 'rp_facebook_notification' ),
          'message_sent_number'   => __( 'Message has been sent to', 'rp_facebook_notification' ),
          'message_error'         => __( 'Error', 'rp_facebook_notification' ),
        );
        wp_localize_script( 'rp-facebook-admin-script', 'rpfacebookAdmin', $params );
        if ( isset( $_GET['tab'] ) 
          && $_GET['tab'] == 'facebook_notification'  ) {
          wp_enqueue_script( 'jquery-ui-accordion' );
          wp_enqueue_script( 'rp-facebook-sweetalert2' );
          wp_enqueue_script( 'rp-facebook-admin-script' );
        }
      }
    public function facebook_notification_admin_styles() {
        wp_register_style( 'rp-fb-admin-style' , RP_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/css/rp-fb-admin-style.css', array(), RP_FACEBOOK_NOTIFICATION_VERSION );
        wp_register_style( 'rp-fb-sweetalert2-style' , RP_FACEBOOK_NOTIFICATION_PLUGIN_URL . 'assets/css/fb.min.css', array(), RP_FACEBOOK_NOTIFICATION_VERSION );
        if ( isset( $_GET['tab'] ) 
          && $_GET['tab'] == 'facebook_notification'  ) {
          wp_enqueue_style( 'rp-fb-admin-style' );
          wp_enqueue_style( 'rp-fb-sweetalert2-style' );
        }
      }
    public function rp_facebook_notification_settings_fields( $fields ) {
        $settings = array(
            'facebook_admin_settings' => array(
                'id'            => 'facebook_admin_settings',
                'name'          => '<h3>' . __( 'Admin Facebook Settings', 'rp-fb-notification' ) . '</h3>',
                'desc'          => '',
                'type'          => 'header',
                'tooltip_title' => __( 'Admin Facebook Settings', 'rp-fb-notification' ),
                'tooltip_desc'  => __( 'These are the settings for admin notifications.' ),
            ),
            'enable_admin_facebook_notification' => array(
                'id'   => 'enable_admin_facebook_notification',
                'name' => __( 'Enable Admin Facebook Notification', 'rp-fb-notification' ),
                'desc' => __( 'Enable this option to get admin Facebook notifications.', 'rp-fb-notification' ),
                'type' => 'checkbox',
            ),
        'facebook_app_id' => array(
          'id'   => 'facebook_app_id',
          'name' => __( 'Facebook App ID', 'rp-fb-notification' ),
          'std'  => '',
          'type' => 'text',
          'desc' => __( 'Enter your Facebook App ID. You can obtain this ID by creating a Facebook App in the Facebook Developer Portal.', 'rp-fb-notification' ),
      ),
      'facebook_app_secret' => array(
          'id'   => 'facebook_app_secret',
          'name' => __( 'Facebook App Secret', 'rp-fb-notification' ),
          'std'  => '',
          'type' => 'text',
          'desc' => __( 'Enter your Facebook App Secret. You can find this in your Facebook App settings in the Facebook Developer Portal.', 'rp-fb-notification' ),
      ),
      'facebook_page_token' => array(
          'id'   => 'facebook_page_token',
          'name' => __( 'Facebook Page Token', 'rp-fb-notification' ),
          'std'  => '',
          'type' => 'text',
          'desc' => __( 'Enter your Facebook Page Token. This token is used to access your Facebook Page for sending notifications. You can generate this token in the Facebook Developer Portal.', 'rp-fb-notification' ),
      ),
      'facebook_page_id' => array(
        'id'   => 'facebook_page_id',
        'name' => __( 'Facebook Page Id', 'rp-fb-notification' ),
        'std'  => '',
        'type' => 'text',
        'desc' => __( 'Enter your Facebook Page id. This id is used to access your Facebook Page.', 'rp-fb-notification' ),
    ),
      'admin_facebook_user_id' => array(
        'id'   => 'admin_facebook_user_id',
        'name' => __( 'Admin Facebook User ID', 'rp-fb-notification' ),
        'std'  => 'enter user id.',
        'type' => 'text',
        'desc' =>__( 'Enter the User ID of the admin\'s Facebook account. You can find this ID in your Facebook profile settings.', 'rp-fb-notification' )
    ),
            'admin_facebook_text' => array(
                'id'   => 'admin_facebook_text',
                'name' => __( 'Admin Facebook Text', 'rp-fb-notification' ),
                'desc' => sprintf(
                    __( 'Available placeholders are {ORDER_NUMBER}, {ORDER_STATUS}, {SERVICE_DATE}, {SHOP_NAME}, {SERVICE_TIME}, {SERVICE_TYPE}, {BILLING_FNAME}, {FULLNAME}, {PHONE}, {PRICE}', 'rp_facebook_notification' )
                  ),
              'std'  => '#{ORDER_NUMBER} is updated with status {ORDER_STATUS} on {SERVICE_DATE} at {SHOP_NAME} ',
              'type' => 'textarea',
            ),
              'test_settings' => array(
                'id'            => 'test_settings',
                'name'          => '<h3>' . __( 'Test Settings', 'rp_facebook_notification' ) . '</h3>',
                'desc'          => '',
                'type'          => 'header',
                'tooltip_title' => __( 'Test facebook Settings', 'rp_facebook_notification' ),
              ),
              
              'test_facebook_text' => array(
                'id'   => 'test_facebook_text',
                'class'   => 'test_facebook_text',
                'name' => __( 'Test facebook text', 'rp_facebook_notification' ),
                'desc' => sprintf(
                      __( 'Enter the text which you want to send as the test message', 'rp_facebook_notification' )
                    ),
                'type' => 'textarea',
              ),
              'test_facebook_notifications' => array(
                'id'   => 'test_facebook_notifications',
                'type' => 'test_facebook_notifications',
              ),
              
            ); 
        $fields['facebook_notification'] = $settings;
        return $fields;
    }
    public function rpress_settings_sanitize_facebook_notification( $input ) {
        if ( isset( $_POST['facebook_notification'] ) ) {
          update_option( '_rpress_customer_facebook_notification', $_POST['facebook_notification'] );
        }
        return $input;
      }
}
new RP_Facebook_Notification_Settings(); 

