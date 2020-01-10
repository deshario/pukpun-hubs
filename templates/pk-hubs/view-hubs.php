<div class="ui cards">
   <?php
      global $wpdb;
      $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
      $tbl_pp_hubs_data = $wpdb->prefix.'pukpun_hubs_data';
      $tbl_pp_locations = $wpdb->prefix.'pukpun_locations';
      $hubs = $wpdb->get_results("SELECT * FROM $tbl_pp_hubs");

      if(count($hubs) <= 0){
        echo "
          <h2 class='ui center aligned icon header' style='margin-top:25px;'>
            <i class='bullhorn icon'></i> Hub Not Found
          </h2>
        ";
      }

      echo "<script>var location_data = [];</script>";

      foreach($hubs as $hub){
        $countRoutes = $wpdb->get_var("SELECT COUNT(*) FROM $tbl_pp_locations WHERE hub_id=$hub->hub_id AND isPrecarious = '0'");
        $routeLabel = '';
        if($countRoutes <= 1){
          $routeLabel = $countRoutes.' Route';
        }else{
          $routeLabel = $countRoutes.' Routes';
        }

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
            <img src="<?= $cover[0]; ?>" style='height:225px'>
          </div>
          <div class="content">
            <a class="header" onclick="viewHubWithModal(<?= $hub->hub_id; ?>,<?= "'".$cover[0]."'"; ?>)"><?= $hub->hub_name;?></a>
          </div>
          <div class="extra content">
          <a><i class="map marker alternate icon"></i><?= $routeLabel; ?></a>
          <a style="float:right"><?= $hub->hub_opening;?></a>
          </div>
        </div>
        
   <?php } ?>
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
      <img id='viewImgSrc'/>
    </div>
    <div class="description">
        <p style='font-size:17px;'>Address : <a class='noAstyle' id='address_val'></a></p>
        <p style='font-size:17px;'>Coordinate : <a class='noAstyle' id='coordinate_val'></a></p>
        <p style='font-size:17px;'>Created : <a class='noAstyle' id='created_val'></a></p>
        <p style='font-size:17px;'>Open Time : <a class='noAstyle' id='opentime_val'></a></p>
        <p>
          <button class="button" id='viewAttachedLocation'>
            <i class="map marker alternate icon"></i>
            View attached routes
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
   
    jQuery('.card .image').dimmer({
      on: 'hover'
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
        url: "<?php echo plugin_dir_url( __FILE__ ).'../../actions/delete_hub.php'; ?>",
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

  const viewHubWithModal = (hub_id,logo) => {
    let location = location_data[hub_id];
    let viewLocationUrl = "<?= admin_url('/admin.php?page=pukpun_routes&hub_id')?>";
    jQuery("#viewHubHeader").text(location.hub_name);
    jQuery("#address_val").text(location.hub_address);
    jQuery("#coordinate_val").text(location.hub_coordinate);
    jQuery("#created_val").text(location.hub_created_at);
    jQuery("#opentime_val").text(location.hub_opening);
    jQuery("#viewImgSrc").attr("src",logo);
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