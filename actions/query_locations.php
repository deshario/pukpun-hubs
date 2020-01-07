<?php

  require_once("../../../../wp-load.php");
  
  global $wpdb;

  $hubId = isset($_GET['hub_id']) ? $_GET['hub_id'] : null;

  if($hubId != null){

    $settings_tbl = $wpdb->prefix.'pukpun_settings';
    $location_tbl = $wpdb->prefix.'pukpun_locations';

    $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'query_api'");
    $queryUrl = $result->key_value;

    $locationsData = [];

    $locations = $wpdb->get_results("SELECT * FROM $location_tbl WHERE hub_id = $hubId");

    foreach($locations as $eachLocation){
        $tempLocation = new stdClass();
        $tempLocation->id = $eachLocation->location_id;
        $tempLocation->name = $eachLocation->location_name;
        $tempLocation->data = $eachLocation->location_data;
        $tempLocation->isPrecarious = $eachLocation->isPrecarious;
        array_push($locationsData,$tempLocation);
    }

    $finalTotalHubs = json_encode($locationsData,JSON_PRETTY_PRINT);

    echo $finalTotalHubs;

  }

?>