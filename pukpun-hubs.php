<?php
/**
 * Plugin Name: PukPun Hubs
 * Plugin URI: https://github.com/deshario/pukpun-hubs
 * Description: Create pukpun hubs, delivery routes and manage shipping cost by distance of each routes.
 * Version:     1.0
 * Author:      Deshario Sunil
 * Author URI:  https://github.com/deshario
 * Text Domain: deshario
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

  if (!defined( 'ABSPATH')){
    exit; // Exit if accessed directly
  }

  function init_pukpun_hubs_database(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
     
    $tbl_pukpun_hub = $wpdb->prefix.'pukpun_hubs';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_pukpun_hub'") != $tbl_pukpun_hub){
      $queryHub = "CREATE TABLE $tbl_pukpun_hub (
        hub_id int(11) NOT NULL AUTO_INCREMENT,
        hub_name varchar(255) NOT NULL,
        hub_coordinate VARCHAR(255) NOT NULL,
        hub_created_at date DEFAULT NULL,
        hub_updated_at date DEFAULT NULL,
        hub_address text NOT NULL,
        hub_cover int(11) NOT NULL,
        hub_opening VARCHAR(255) NOT NULL,
        UNIQUE KEY (hub_id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($queryHub);
      $wpdb->query("ALTER TABLE $tbl_pukpun_hub CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    $tbl_pukpun_location = $wpdb->prefix.'pukpun_locations';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_pukpun_location'") != $tbl_pukpun_location){
      $hubTbl = $wpdb->prefix.'pukpun_hubs';
      $queryLocation = "CREATE TABLE $tbl_pukpun_location (
        location_id int(11) NOT NULL AUTO_INCREMENT,
        location_name VARCHAR(255) NOT NULL,
        location_data text NOT NULL,
        location_created_at date DEFAULT NULL,
        location_updated_at date DEFAULT NULL,
        isPrecarious ENUM('0','1') NOT NULL,
        hub_id int,
        FOREIGN KEY (hub_id) REFERENCES $hubTbl(hub_id),
        UNIQUE KEY (location_id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($queryLocation);
      $wpdb->query("ALTER TABLE $tbl_pukpun_location CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    $tbl_pukpun_log = $wpdb->prefix.'pukpun_logs';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_pukpun_log'") != $tbl_pukpun_log){
      $queryHubData = "CREATE TABLE $tbl_pukpun_log (
        log_id int(11) NOT NULL AUTO_INCREMENT,
        log_location VARCHAR(255) NOT NULL,
        log_latlng VARCHAR(255) NOT NULL,
        log_created DATETIME NOT NULL,
        log_creator int(11) NOT NULL,
        UNIQUE KEY (log_id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($queryHubData);
      $wpdb->query("ALTER TABLE $tbl_pukpun_log CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
    }

    $tbl_pukpun_settings = $wpdb->prefix.'pukpun_settings';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_pukpun_settings'") != $tbl_pukpun_settings){
      $querySetting = "CREATE TABLE $tbl_pukpun_settings (
        key_id int(11) NOT NULL AUTO_INCREMENT,
        key_name VARCHAR(255) NOT NULL,
        key_value VARCHAR(255) NOT NULL,
        UNIQUE KEY (key_id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($querySetting);
      $wpdb->query("ALTER TABLE $tbl_pukpun_settings CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
    }
    
  }
  register_activation_hook( __FILE__, 'init_pukpun_hubs_database' );

  register_activation_hook(__FILE__, 'my_plugin_activate');
  add_action('admin_init', 'my_plugin_redirect');

  function my_plugin_activate() {
    add_option('pukpun_activation_redirect', true);
  }

  function my_plugin_redirect() {
    if (get_option('pukpun_activation_redirect', false)) {
      delete_option('pukpun_activation_redirect');
      wp_redirect(admin_url('/admin.php?page=pukpun_settings'));
    }
  }

  add_action('admin_menu', 'init_hubs_menu');
  function init_hubs_menu(){
    add_menu_page(
      'All Hubs', 'PukPun Hubs', 'manage_options', 'pukpun_hubs', 'view_hub', 'dashicons-location' 
    );
    add_submenu_page(
      'pukpun_hubs', 'PukPun Routes', 'PukPun Routes', 'manage_options', 'pukpun_routes', 'viewLocation'
    );
    add_submenu_page(
      'pukpun_hubs', 'Precarious Area', 'Precarious Area', 'manage_options', 'pukpun_precarious', 'precariousArea'
    );
    add_submenu_page(
      'pukpun_hubs', 'Request Logs', 'Request Logs', 'manage_options', 'pukpun_logs', 'logs'
    );
    add_submenu_page(
      'pukpun_hubs', 'PukPun Settings', 'Settings', 'manage_options', 'pukpun_settings', 'settings'
    );
  }

  add_action('admin_enqueue_scripts','pukpun_hubs_style'); 
  function pukpun_hubs_style(){
    $page = isset($_GET['page']) ? $_GET['page'] : null;
    if($page != null){
      if($page == 'pukpun_hubs' || $page == 'pukpun_routes' || $page == 'pukpun_settings' || $page == 'pukpun_logs' || $page == 'pukpun_precarious'){
        wp_register_style('semantic_ui_css', 'https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css', false, '1.0.0' );
        wp_enqueue_style('semantic_ui_css'); 
        wp_register_script('semantic_ui_js', 'https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js', null, null, true );
        wp_enqueue_script('semantic_ui_js');
      }
    }
  }

  function view_hub(){
    include(plugin_dir_path( __FILE__ ).'/templates/pk-hubs.php');
  }

  function viewLocation(){
    include(plugin_dir_path( __FILE__ ).'/templates/pk-routes.php');
  }

  function precariousArea(){
    include(plugin_dir_path( __FILE__ ).'/templates/pk-precarious.php');
  }
      
  function logs(){
    include(plugin_dir_path( __FILE__ ).'/templates/pk-logs.php');
  }

  function settings(){
    include(plugin_dir_path( __FILE__ ).'/templates/pk-settings.php');
  }
  
  add_shortcode('pukpun_hubs', 'view_pukpun_hubs');
  function view_pukpun_hubs(){
    ob_start();
    include(plugin_dir_path( __FILE__ ).'/shortcode/pukpun_hubs.php');
    $layout = ob_get_clean();
    return $layout;
  }

  add_shortcode('pukpun_checkout', 'pukpun_checkout_map');
  function pukpun_checkout_map(){
    ob_start();
    include(plugin_dir_path( __FILE__ ).'/shortcode/pukpun_checkout.php');
    $layout = ob_get_clean();
    return $layout;
  }

?>