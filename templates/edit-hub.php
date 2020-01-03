<?php
  global $wpdb;
  $isEditgranted = false;
  $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
  $hubId = isset($_GET['editHub']) ? $_GET['editHub'] : null;
  if($hubId == null){
    echo "<script>alert('Invalid Access')</script>";
  }else{
    $pukpunHub = $wpdb->get_row("SELECT * FROM $tbl_pp_hubs WHERE hub_id = $hubId");
    if($pukpunHub == null && $pukpunHub == ''){
      echo "<script>
        alert('Invalid hub id');
        window.history.back();
      </script>";
    }else{
      $isEditgranted = true;
    }
  }

  if(isset($_POST['updateHub'])){
    updateHub();
  }

  function updateHub(){
    global $wpdb;
    $hubName = $_POST['hubName'];
    $hubCoordinate = $_POST['hubCoordinate'];
    $readyToUpdate = true;

    if($pukpunHub->hub_name != $hubName){
      $rawQuery = "SELECT * FROM $tbl_pp_hubs WHERE hub_name != '$pukpunHub->hub_name' AND hub_name = '$hubName'";
      $result = $wpdb->get_row($rawQuery);
      if(count((array)$result) > 0){
        handleError('Hub Name was already taken !');
        $readyToUpdate = false;
      }
    }

    if($pukpunHub->hub_coordinate != $hubCoordinate){
      $rawQuery = "SELECT * FROM $tbl_pp_hubs WHERE hub_coordinate != '$pukpunHub->hub_coordinate' AND hub_coordinate = '$hubCoordinate'";
      $result = $wpdb->get_row($rawQuery);
      if(count((array)$result) > 0){
        handleError('Hub Coordinate was already taken !');
        $readyToUpdate = false;
      }
    }

    if($readyToUpdate){
      global $wpdb;
      $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
      $result = $wpdb->update($tbl_pp_hubs,
        array(
          'hub_name' => $hubName,
          'hub_coordinate' => $hubCoordinate,
          'hub_address' => $_POST['hubAddress'],
          'hub_opening' => $_POST['hubOpening'],
          'hub_updated_at' => date("Y-m-d"),
        ),array('hub_id' => $_GET['editHub'])
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

  if($isEditgranted){

?>

<div class="ui grid" style="margin-top:10px; padding-right:5px; margin-right:0;">
   <div class="wide column">
      <form method="post" id="hubForm" action="#" enctype="multipart/form-data">
         <div class="ui card fluid">
            <div class="content">
               <i class="map marker alternate icon"></i>Edit Hub
            </div>
            <div class="content">
               <div class="ui form">
                  <div class="field">
                     <label>Hub Name</label>
                     <input type="text" name="hubName" value="<?= $pukpunHub->hub_name; ?>" required/>
                  </div>
                  <div class="field">
                     <label>Hub Coordinates</label>
                     <input type="text" name="hubCoordinate" value="<?= $pukpunHub->hub_coordinate; ?>" required/>
                  </div>
                  <div class="field">
                     <label>Hub Address</label>
                     <textarea rows="2" name="hubAddress" style="resize:none" required><?= $pukpunHub->hub_address; ?></textarea>
                  </div>
                  <div class="field">
                     <label>Hub Opening Time</label>
                     <input type="text" name="hubOpening" value="<?= $pukpunHub->hub_opening; ?>" required/>
                  </div>
                  <input type="submit" name="updateHub" class="ui right floated primary button" style="padding-top:7px;" />
                  <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>

  <?php } ?>