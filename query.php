<?php

  require_once("../../../wp-load.php");

  global $wpdb;

  $hubs = $wpdb->prefix.'pukpun_hubs';
  $locations = $wpdb->prefix.'pukpun_locations';

  $finalHubs = array();
  $hubsOnly = $wpdb->get_results("SELECT * FROM $hubs");

  $allLocations = array();

  foreach($hubsOnly as $eachHub){
    
    $ifHubHaveData = $wpdb->get_var("SELECT COUNT(*) FROM $locations WHERE $locations.hub_id=$eachHub->hub_id");
    if($ifHubHaveData > 0){

      $sqlQuery = "SELECT * FROM $locations WHERE $locations.hub_id = ".$eachHub->hub_id;
      $eachHubData = $wpdb->get_results($sqlQuery);

      foreach($eachHubData as $data){
        $hubLocation = array();
        $hubLocation['location_id'] = $data->location_id;
        $hubLocation['location_name'] = $data->location_name;
        $hubLocation['location_data'] = $data->location_data;
        $hubLocation['isPrecarious'] = $data->isPrecarious;
        array_push($allLocations,$hubLocation);
      }

      $myHubs = new stdClass();  
      $myHubs->name = $eachHub->hub_name;
      $myHubs->coordinate = $eachHub->hub_coordinate;
      $myHubs->countLocation = $ifHubHaveData;
      $myHubs->data = $allLocations;
      $myHubs->created_at = $eachHub->hub_created_at;
      $myHubs->updated_at = $eachHub->hub_updated_at;
      array_push($finalHubs,$myHubs);
      $allLocations = [];
    }

  }

  header('Content-type: text/javascript');

  $finalTotalHubs = json_encode($finalHubs,JSON_PRETTY_PRINT);

  echo $finalTotalHubs;

?>