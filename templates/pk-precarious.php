<?php

  $isEdit = false;
  $editPrecariousID = isset($_GET['editPrecarious']) ? $_GET['editPrecarious'] : null;
  if($editPrecariousID != null){
    global $wpdb;
    $tbl_pp_location = $wpdb->prefix.'pukpun_locations';
    $foundLocation = $wpdb->get_row("SELECT * FROM $tbl_pp_location WHERE location_id = $editPrecariousID");
    if($foundLocation != null && $foundLocation != ''){
      $isEdit = true;
    }
  }

  if($isEdit){
    include(plugin_dir_path( __FILE__ ).'/pk-precarious/edit-precarious.php');
  }else{
    include(plugin_dir_path( __FILE__ ).'/pk-precarious/precarious-root.php');
  }

?>