<?php
/**
 * Plugin Name: PukPun Hubs
 * Plugin URI: https://github.com/deshario/pukpun-hubs
 * Description: Create pukpun hubs, delivery locations and manage shipping cost by distance of each locations.
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
        UNIQUE KEY (hub_id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($queryHub);
    }

    $tbl_pukpun_location = $wpdb->prefix.'pukpun_locations';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_pukpun_location'") != $tbl_pukpun_location){
      $queryLocation = "CREATE TABLE $tbl_pukpun_location (
        location_id int(11) NOT NULL AUTO_INCREMENT,
        location_name VARCHAR(255) NOT NULL,
        location_data text NOT NULL,
        location_created_at date DEFAULT NULL,
        location_updated_at date DEFAULT NULL,
        UNIQUE KEY (location_id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($queryLocation);
    }

    $tbl_pukpun_hub_data = $wpdb->prefix.'pukpun_hubs_data';
    if($wpdb->get_var("SHOW TABLES LIKE '$tbl_pukpun_hub_data'") != $tbl_pukpun_hub_data){
      $queryHubData = "CREATE TABLE $tbl_pukpun_hub_data (
        id int(11) NOT NULL AUTO_INCREMENT,
        hub_id int(11) NOT NULL,
        location_id int(11) NOT NULL,
        UNIQUE KEY (id)
      );";
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($queryHubData);
    }
    
  }
  register_activation_hook( __FILE__, 'init_pukpun_hubs_database' );

  add_action('admin_menu', 'init_hubs_menu');
  function init_hubs_menu(){
    add_menu_page(
      'All Hubs', 'PukPun Hubs', 'manage_options', 'pukpun_hubs', 'view_hubs', 'dashicons-location' 
    );
    add_submenu_page(
      'pukpun_hubs', 'New Pukpun Hub', 'New Hub', 'manage_options', 'pukpun_hubs-new', 'manageHub'
    );
    add_submenu_page(
      'pukpun_hubs', 'PukPun Locations', 'Locations', 'manage_options', 'pukpun_locations', 'viewLocation'
    );
    add_submenu_page(
      'pukpun_hubs', 'New PukPun Location', 'New Location', 'manage_options', 'pukpun_locations-new', 'manageLocation'
    );
    add_submenu_page( 
      null,
      'My Custom Submenu Page',
      'My Custom Submenu Page',
      'manage_options',
      'pukpun_location',
      'editLocation'
    );
  }

  add_action('admin_enqueue_scripts','pukpun_hubs_style'); 
  function pukpun_hubs_style(){
    $page = isset($_GET['page']) ? $_GET['page'] : null;
    if($page != null){
      if($page == 'pukpun_hubs' || $page == 'pukpun_hubs-new' || $page == 'pukpun_locations' || $page == 'pukpun_locations-new' || $page == 'pukpun_location'){
        wp_register_style('semantic_ui_css', 'https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css', false, '1.0.0' );
        wp_enqueue_style('semantic_ui_css'); 
        wp_register_script('semantic_ui_js', 'https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js', null, null, true );
        wp_enqueue_script('semantic_ui_js');
      }
    }
  }

  function view_hubs(){
    include(plugin_dir_path( __FILE__ ).'/templates/view-hubs.php');
  }

  function viewLocation(){
    include(plugin_dir_path( __FILE__ ).'/templates/view-locations.php');
  }

  function manageHub(){
    include(plugin_dir_path( __FILE__ ).'/templates/create-hub.php');
  }

  function manageLocation(){
    include(plugin_dir_path( __FILE__ ).'/templates/create-location.php');
  }

  function editLocation(){
    include(plugin_dir_path( __FILE__ ).'/templates/edit-location.php');
  }

  function initializeUI($menuType){
    echo 'initializing UI : '.$menuType;
  }
  
   
?>