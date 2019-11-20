<?php

  require_once("../../../wp-load.php");

  global $wpdb;

  $hubs = $wpdb->prefix.'pukpun_hubs';
  $hubs_data = $wpdb->prefix.'pukpun_hubs_data';
  $locations = $wpdb->prefix.'pukpun_locations';

  $finalHubs = array();
  $hubsOnly = $wpdb->get_results("SELECT * FROM $hubs");

  foreach($hubsOnly as $eachHub){
    $ifHubHaveData = $wpdb->get_var("SELECT COUNT(*) FROM $hubs_data WHERE hub_id=$eachHub->hub_id");

    if($ifHubHaveData > 0){

      $sqlQuery = "SELECT * FROM $hubs_data 
        INNER JOIN $hubs ON $hubs.hub_id = $hubs_data.hub_id
        INNER JOIN $locations ON $locations.location_id = $hubs_data.location_id
        WHERE $hubs_data.hub_id = ".$eachHub->hub_id;

      $allLocations = array();
      $eachHubData = $wpdb->get_results($sqlQuery);
      foreach($eachHubData as $data){
        array_push($allLocations,$data->location_data);
      }

      $myHubs = new stdClass();  
      $myHubs->name = $eachHub->hub_name;
      $myHubs->coordinate = $eachHub->hub_coordinate;
      $myHubs->countLocation = $ifHubHaveData;
      $myHubs->data = $allLocations;
      $myHubs->created_at = $eachHub->hub_created_at;
      $myHubs->updated_at = $eachHub->hub_updated_at;
      array_push($finalHubs,$myHubs);

    }

  }

  header('Content-type: text/javascript');

  $finalTotalHubs = json_encode($finalHubs,JSON_PRETTY_PRINT);

  echo $finalTotalHubs;

?>