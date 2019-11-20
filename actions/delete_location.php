<?php

  require_once("../../../../wp-load.php");
  
  global $wpdb;

  $action = isset($_POST['action']) ? $_POST['action'] : null;
  $location_id = isset($_POST['location_id']) ? $_POST['location_id'] : null;

  if($action != null){

    if($action == 'delete' && $location_id != null){
      $tbl_locations = $wpdb->prefix.'pukpun_locations';
      $result = $wpdb->delete($tbl_locations, array('location_id' => $location_id) );
      echo $result;
    }

  }

?>