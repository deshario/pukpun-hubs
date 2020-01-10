<?php

  $isEdit = false;
  $editHubID = isset($_GET['editHub']) ? $_GET['editHub'] : null;
  if($editHubID != null){
    global $wpdb;
    $tbl_hubs = $wpdb->prefix.'pukpun_hubs';
    $foundLocation = $wpdb->get_row("SELECT * FROM $tbl_hubs WHERE hub_id = $editHubID");
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