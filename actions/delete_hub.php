<?php

  require_once("../../../../wp-load.php");
  
  global $wpdb;

  $action = isset($_POST['action']) ? $_POST['action'] : null;
  $hub_id = isset($_POST['hub_id']) ? $_POST['hub_id'] : null;

  if($action != null){

    if($action == 'delete' && $hub_id != null){
      $mData = $wpdb->prefix.'pukpun_hubs_data';
      $mHubs = $wpdb->prefix.'pukpun_hubs';
      $wpdb->delete($mData, array('hub_id' => $hub_id) );
      $wpdb->delete($mHubs, array('hub_id' => $hub_id) );
      echo 1; // Force Reload
    }

  }

?>