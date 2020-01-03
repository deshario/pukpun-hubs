<div class="ui special cards" style="margin-top:10px;">
   <?php
      global $wpdb;
      $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
      $tbl_pp_hubs_data = $wpdb->prefix.'pukpun_hubs_data';
      $tbl_pp_locations = $wpdb->prefix.'pukpun_locations';
      $hubs = $wpdb->get_results("SELECT * FROM $tbl_pp_hubs");

      $createHubUrl = '"'.admin_url('/admin.php?page=pukpun_hubs-new').'"';

      if(count($hubs) <= 0){
        echo "<script>var createHubUrl = ".$createHubUrl."</script>";
        echo "
          <div class='ui icon info message' style='width:98%;'>
            <i class='exclamation icon'></i>
            <div class='content'>
              <div class='header'>Hub Not Found</div>
              <div class='description' style='margin-top:5px;'>Click <a href='#' onclick='createHub(".$createHubUrl.")'>here</a> to create your first hub</div>
            </div>
            <i class='close icon'></i>
          </div> 
        ";
      }
      echo "<script>var location_data = [];</script>";

      echo "
        <div class='ui segment' style='width:99%; margin-top: 1px; margin-right: 20px;'>
        <h2 class='ui left floated header' style='padding-left:10px;'>PukPun Hubs</h2>
        <div class='ui clearing divider'></div>
        <div class='ui cards' style='padding-left: 10px; padding-right: 5px;'>
      ";

      foreach($hubs as $hub){
        $countLocation = $wpdb->get_var("SELECT COUNT(*) FROM $tbl_pp_locations WHERE hub_id=$hub->hub_id");
        $cover = wp_get_attachment_image_src($hub->hub_cover, 'adv-pos-a-large');
        $data = json_encode($hub);
        echo "<script>location_data[$hub->hub_id] = $data;</script>";
      ?>

        <div class="ui card" style="max-height:330px;">
          <div class="blurring dimmable image">
            <div class="ui dimmer">
                <div class="content">
                  <div class="center">
                    <div onclick="updateHub(<?= $hub->hub_id; ?>)" class="ui inverted button" style="padding-top:7px;">Edit</div>
                    <div onclick="deleteConfirmation(<?= $hub->hub_id; ?>,'<?= $hub->hub_name; ?>')" class="ui inverted button" style="padding-top:7px;">Delete</div>
                  </div>
                </div>
            </div>
            <img src="<?= $cover[0]; ?>">
          </div>
          <div class="content">
            <a class="header" onclick="viewHubWithModal(<?= $hub->hub_id; ?>)"><?= $hub->hub_name;?></a>
          </div>
          <div class="extra content">
          <a><i class="map marker alternate icon"></i><?= $countLocation; ?> Locations</a>
          <a style="float:right"><?= $hub->hub_opening;?></a>
          </div>
        </div>
   <?php } ?>
</div>
</div>
</div>

<div class="ui modal deleteHub">
  <i class="close icon"></i>
  <div class="header deleteTitle"></div>
  <div class="image content">
  <div class="ui checkbox">
    <input type="checkbox" id="delete_attachment">
    <label>Delete all attached locations.</label>
  </div>
  </div>
  <div class="actions">
    <button class="ui button" onclick="deleteConfirmation(-1,-1)" style="padding-top:7px;">Cancel</button>
    <button class="ui red button confirmDelete" onclick="deleteHub()" style="padding-top:7px;">OK</button>
  </div>
</div>

<div class="ui modal viewHub">
  <i class="close icon"></i>
  <div class="header" id="viewHubHeader"></div>
  <div class="image content" style='padding:15px;'>
    <div class="ui medium image">
      <img src="http://localhost:8080/wordpress/wp-content/uploads/2019/12/GCk9kqTURBXy9kNDEzZThiNDM4MzMyYzcyN2QxNTJmODJiOTM4NWQ4MC5qcGVnkpUDAADNB4DNBDiTBc0HgM0EOIGhMAE-2.png">
    </div>
    <div class="description">
        <p style='font-size:17px;'>Address : <a class='noAstyle' id='address_val'></a></p>
        <p style='font-size:17px;'>Coordinate : <a class='noAstyle' id='coordinate_val'></a></p>
        <p style='font-size:17px;'>Created : <a class='noAstyle' id='created_val'></a></p>
        <p style='font-size:17px;'>Open Time : <a class='noAstyle' id='opentime_val'></a></p>
        <p>
          <button class="button" id='viewAttachedLocation'>
            <i class="map marker alternate icon"></i>
            View attached locations
          </button>
        </p>
    </div>
  </div>
</div>

<script type="text/javascript"> 
   jQuery(document).ready(() => {

    jQuery('#blurThing').click(function(event){    
      var clickedId = event.target.id;
      if(clickedId == 'blurThing'){
        console.log('blur');
      }
  });
   
    jQuery('.special.cards .image').dimmer({
      on: 'hover'
    });
    jQuery('.message .close').on('click', function() {
      jQuery(this).closest('.message').transition('fade');
      window.location.href = createHubUrl;
    });
   
   });

  const updateHub = (hubID) => {
    let url = "<?php echo admin_url('admin.php?page=pukpun_hubs'); ?>";
    url = url+"&editHub="+hubID;
    window.location.href = url;
  }
   
  const redirectTo = (viewUrl) => {
    window.location.href = viewUrl;
  }

  const createHub = (createUrl) => {
    window.location.href = createUrl;
  }

  const deleteConfirmation = (hubId,hubName) => {
    if(hubId == -1 && hubName == -1){
      jQuery('.deleteHub').modal('hide');
    }else{
      jQuery(".deleteTitle").html('Are you sure want to delete '+hubName+' ?');
      jQuery('.deleteHub').modal('show');
      jQuery("#delete_attachment").prop("checked", false);
      jQuery('.confirmDelete').attr('hub_id', hubId);
    }
  }

  const deleteHub = () => {
    let isDeleteAttachment = jQuery("#delete_attachment").attr("checked") ? 1 : 0;
    let hub_id = jQuery(".confirmDelete").attr("hub_id");
    if(hub_id == '' || hub_id == null){
      alert('Invalid Location !');
    }else{
      jQuery.ajax({
        type: "POST",
        url: "<?php echo plugin_dir_url( __FILE__ ).'../actions/delete_hub.php'; ?>",
        data: {action: 'delete', hub_id, isDeleteAttachment},
        success: function (data, status){
          jQuery('.deleteHub').modal('hide');
          let result = parseInt(data);
          console.log(result,typeof result,status);
          if(result == 1){ // Force Reload
            location.reload();
          }
        }
      });
    }
  }

  const viewHubWithModal = (hub_id) => {
    let location = location_data[hub_id];
    let viewLocationUrl = "<?= admin_url('/admin.php?page=pukpun_locations&hub_id')?>";
    jQuery("#viewHubHeader").text(location.hub_name);
    jQuery("#address_val").text(location.hub_address);
    jQuery("#coordinate_val").text(location.hub_coordinate);
    jQuery("#created_val").text(location.hub_created_at);
    jQuery("#opentime_val").text(location.hub_opening);
    jQuery('.noAstyle').css(
      {'color': 'black'}
    );
    jQuery('.viewHub').modal('show');

    jQuery('#viewAttachedLocation').click(function(e){
      jQuery('.viewHub').modal('hide');
      let url = viewLocationUrl+'='+hub_id;
      window.location.href = url;
    });

  }

</script>


