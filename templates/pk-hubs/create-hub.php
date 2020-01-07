<?php
   if(isset($_POST['createHub'])){
      global $wpdb;
      $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
      $pdf = $_FILES['hubCover'];

      $hubName = $_POST['hubName'];
      $hubCoordinate = $_POST['hubCoordinate'];
      $countHubName = $wpdb->get_var(" SELECT COUNT(*) FROM $tbl_pp_hubs WHERE hub_name = '$hubName'");
      $countHubCoordinate = $wpdb->get_var(" SELECT COUNT(*) FROM $tbl_pp_hubs WHERE hub_coordinate = '$hubCoordinate'");
      
      $readyToInsert = false;
      if($countHubName > 0 && $countHubCoordinate > 0){
         handleError('Name and Coordinates are already taken !');
      }else{
         if($countHubName > 0){
            handleError('Name already taken !');
         }else if($countHubCoordinate > 0){
            handleError('Coordinates already taken !');
         }else{
            $readyToInsert = true;
         }
      }

      if($readyToInsert){
         $uploadedMediaId = media_handle_upload('hubCover',0); // return int |  ID
         if(is_wp_error($uploadedMediaId)){
            echo "Error uploading file: " . $uploadedMediaId->get_error_message().'<br/>';
         }
         $wpdb->insert($tbl_pp_hubs,
            array(
               'hub_name' => $_POST['hubName'],
               'hub_coordinate' => $_POST['hubCoordinate'],
               'hub_created_at' => date("Y-m-d"),
               'hub_address' => $_POST['hubAddress'],
               'hub_opening' => $_POST['hubOpening'],
               'hub_cover' => $uploadedMediaId
            )
         );
         wp_redirect(admin_url('/admin.php?page=pukpun_hubs'));
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

?>

<div class="ui fluid" style="padding:10px 10px 30px 10px;">
   <div class="wide column">
      <form method="post" id="hubForm" action="#" enctype="multipart/form-data">
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
               <div class="field">
                  <label>Hub Address</label>
                  <textarea rows="2" name="hubAddress" style="resize:none" required></textarea>
               </div>
               <div class="field">
                  <label>Hub Opening Time</label>
                  <input type="text" name="hubOpening" placeholder="06.00 - 18.00" required/>
               </div>
               <div class="field">
                  <label>Hub Cover</label>
                  <input type="file" name="hubCover" accept="image/*" required/>
               </div>
               <input type="hidden" id="locations_ids" name="locations_ids"/>
               <input type="submit" name="createHub" class="ui right floated primary button" style="padding-top:7px;" />
               <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
            </div>
         </div>
      </form>
   </div>
</div>