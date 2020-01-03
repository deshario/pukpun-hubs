<?php

  require_once("../../../../wp-load.php");
  
  global $wpdb;

  $action = isset($_POST['action']) ? $_POST['action'] : null;
  $hub_id = isset($_POST['hub_id']) ? $_POST['hub_id'] : null;
  $isDeleteAttachment = isset($_POST['isDeleteAttachment']) ? $_POST['isDeleteAttachment'] : null;

  if($action != null){

    if($action == 'delete' && $hub_id != null){
      $mHubs = $wpdb->prefix.'pukpun_hubs';
      $mLocations = $wpdb->prefix.'pukpun_locations';
      if($isDeleteAttachment == 1){ // Delete
        $wpdb->delete($mLocations, array('hub_id' => $hub_id));
      }else{
        $wpdb->update($mLocations, array('hub_id' => NULL), array('hub_id' => $hub_id) );
      }
      $wpdb->delete($mHubs, array('hub_id' => $hub_id));
      echo 1; // Force Reload
    }

  }

?>