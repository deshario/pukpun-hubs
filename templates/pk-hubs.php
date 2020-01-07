<?php

  $isEdit = false;
  $editRouteID = isset($_GET['editRoute']) ? $_GET['editRoute'] : null;
  if($editRouteID != null){
    global $wpdb;
    $tbl_pp_location = $wpdb->prefix.'pukpun_locations';
    $foundLocation = $wpdb->get_row("SELECT * FROM $tbl_pp_location WHERE location_id = $editRouteID");
    if($foundLocation != null && $foundLocation != ''){
      $isEdit = true;
    }
  }

  if($isEdit){
    include(plugin_dir_path( __FILE__ ).'/pk-hubs/edit-hub.php');
  }else{
    include(plugin_dir_path( __FILE__ ).'/pk-hubs/hub-root.php');
  }

?>