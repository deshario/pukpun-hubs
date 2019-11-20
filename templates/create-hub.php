<?php

   if(isset($_POST['createHub'])){
      if(isset($_POST['locations_ids']) && $_POST['locations_ids'] != ''){
         global $wpdb;
         $ids = $_POST['locations_ids'];
         $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
         $wpdb->insert($tbl_pp_hubs,
            array(
               'hub_name' => $_POST['hubName'],
               'hub_coordinate' => $_POST['hubCoordinate'],
               'hub_created_at' => date("Y-m-d"),
            ),
            array('%s','%s','%s')
         );
         $insertedHubID = $wpdb->insert_id;
         if($insertedHubID != 0){ // Save Success
            $allHubData = array();
            $allLocationID = explode(",",$ids);
            foreach($allLocationID as $eachLocationID){
               $eachHubData = array(
                  'hub_id'   => $insertedHubID, 
                  'location_id'  => $eachLocationID
               );
               array_push($allHubData, $eachHubData);
            }

            $values = $place_holders = array();
            if(count($allHubData) > 0) {
               foreach($allHubData as $data) {
                  array_push($values, $data['hub_id'], $data['location_id']);
                  $place_holders[] = "(%s, %s)";
               }
               do_insert($wpdb->prefix.'pukpun_hubs_data',$place_holders, $values);
               wp_redirect(admin_url('/admin.php?page=pukpun_hubs'));
            }
         }

      }else{
         handleError('Please select locations');
      }
   }

   function do_insert($tableName, $place_holders, $values){
      global $wpdb;
      $query    = "INSERT INTO $tableName (`hub_id`, `location_id`) VALUES ";
      $query   .= implode(', ',$place_holders);
      $sql      = $wpdb->prepare("$query", $values);
      if($wpdb->query($sql)){
         return true;
      }else{
         return false;
      }
   }

   function handleError($message){
      echo "
      <div class='ui icon negative message' style='width:99%;'>
         <i class='attention icon'></i>
         <div class='content'>
         <div class='header'>Invalid Form</div>
         <div class='description' style='margin-top:5px;'>$message</div>
         </div>
         <i class='close icon'></i>
      </div> 
      ";
   }

   global $wpdb;
   $tbl_pp_location = $wpdb->prefix.'pukpun_locations';
   $tbl_pp_hubData = $wpdb->prefix.'pukpun_hubs_data';

   $pukpunLocations = $wpdb->get_results("SELECT * FROM $tbl_pp_location");
   
   $allLocations = array();
   
   $GLOBALS['allTempLocations'] = array();
   
   foreach($pukpunLocations as $location){
      $tempLocation = new stdClass(); 
      $tempLocation->location_id = $location->location_id;
      $tempLocation->location_name = $location->location_name;
      array_push($allLocations,json_decode(json_encode($tempLocation),true));
      array_push($GLOBALS['allTempLocations'],$location->location_id);
   }

   $GLOBALS['newTempLocations'] = $allLocations;

   $pukpunHubData = $wpdb->get_results("SELECT * FROM $tbl_pp_hubData");
   $allHubData = array();
   foreach($pukpunHubData as $eachHubData){
      $temper = $wpdb->get_row("SELECT * FROM $tbl_pp_location WHERE location_id = $eachHubData->location_id");
      $tempHubData= new stdClass(); 
      $tempHubData->location_id = $eachHubData->location_id;
      $tempHubData->location_name = $temper->location_name;
      array_push($allHubData,$eachHubData->location_id);
   }
   
   function myreduce($start,$next){
      $tempLocation = $GLOBALS['allTempLocations'];
      $ffk = $GLOBALS['newTempLocations'];
      $result = $start;
      $isFound = array_search($next,$tempLocation);
      $tempObject = new stdClass(); 
      $tempObject->location_id = $ffk[$isFound]['location_id'];
      $tempObject->location_name = $ffk[$isFound]['location_name'];
      array_push($result,$tempObject);
      return $result;
   }

   $differenceLocations = array_diff($GLOBALS['allTempLocations'],$allHubData);
   $uniqueLocations = array_reduce($differenceLocations,'myreduce',array());

?>

<div class="ui grid" style="margin-top:10px; padding-right:5px; margin-right:0;">
   <div class="wide column">
      <form method="post" id="hubForm" action="#">
         <div class="ui card fluid">
            <div class="content">
               <i class="map marker alternate icon"></i>Create Hub
            </div>
            <div class="content">
               <div class="ui form">
                  <div class="field">
                     <label>Hub Name</label>
                     <input type="text" name="hubName" placeholder="Ramintra" required/>
                  </div>
                  <div class="field">
                     <label>Hub Coordinates</label>
                     <input type="text" name="hubCoordinate" placeholder="14.87623,19.324786" required/>
                  </div>
                  <div class="inline field">
                     <label style="margin-bottom:5px;">Locations</label>
                     <select id="locations" multiple="" class="label ui selection fluid dropdown" onchange="manageSelect()">
                        <option value="">Select Locations</option>
                        <?php
                           foreach($uniqueLocations as $eachLocation){
                              echo "<option value='$eachLocation->location_id'>".$eachLocation->location_name."</option>";
                           }
                        ?>
                     </select>
                  </div>
                  <input type="hidden" id="locations_ids" name="locations_ids"/>
                  <input type="submit" name="createHub" class="ui right floated primary button" style="padding-top:7px;" />
                  <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>

<script type="text/javascript"> 

   const getJSONString = (jsonObject) => {
      let simplifiedData = jsonObject.toString();
      // return simplifiedData.replace(/"/g, "").replace('[','').replace(']','');
      return simplifiedData.replace(/[\[\]"[\]]/g, '');
   }

   function manageSelect(){
      let selected_values = jQuery("#locations").dropdown("get value");
      selected_values = JSON.stringify(selected_values);
      let compiled_selected_values = getJSONString(selected_values);
      jQuery('#locations_ids').val(compiled_selected_values);
   }

   jQuery(document).ready(() => {
      jQuery('.label.ui.dropdown').dropdown();
      jQuery('.no.label.ui.dropdown').dropdown({
         useLabels: false
      });
      jQuery('.clearBtn').on('click',function(){
         jQuery('.ui.dropdown').dropdown('restore defaults')
      });

   }); 

</script>
     
