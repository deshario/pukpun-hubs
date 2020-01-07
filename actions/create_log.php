<?php
  require_once("../../../../wp-load.php");
  
  global $wpdb;
  
  date_default_timezone_set("Asia/Bangkok");

  if(isset($_POST['location']) && isset($_POST['latlng']) && isset($_POST['userID'])){
    $location = $_POST['location'];
    $latlng = $_POST['latlng'];
    $userID = $_POST['userID'];
    $dateNow = date("Y-m-d H:i:s");
    $logsTable = $wpdb->prefix.'pukpun_logs';
    $isInsert = $wpdb->insert($logsTable,
      array(
          'log_location' => $location,
          'log_latlng' => $latlng,
          'log_created' => $dateNow,
          'log_creator' => $userID
      ),
      array('%s','%s','%s','%s')
    );
    if($isInsert > 0){
      echo 'Add Success';
    }else{
      echo $wpdb->last_error;
    }
  }

?>