<?php
  global $wpdb;
  $settings_tbl = $wpdb->prefix.'pukpun_settings';
  $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
  $apiKey = $result->key_value;
?>
<div class="ui special cards" style="margin-top:10px;">
   <?php
      global $wpdb;
      $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
      $tbl_pp_hubs_data = $wpdb->prefix.'pukpun_hubs_data';
      $tbl_pp_locations = $wpdb->prefix.'pukpun_locations';
      $hubs = $wpdb->get_results("SELECT * FROM $tbl_pp_hubs");
      $mapStyle = "feature:administrative%7Celement:geometry.fill%7Ccolor:0xd6e2e6&style=feature:administrative%7Celement:geometry.stroke%7Ccolor:0xcfd4d5&style=feature:administrative%7Celement:labels.text.fill%7Ccolor:0x7492a8&style=feature:administrative.neighborhood%7Celement:labels.text.fill%7Clightness:25&style=feature:landscape.man_made%7Celement:geometry.fill%7Ccolor:0xdde2e3&style=feature:landscape.man_made%7Celement:geometry.stroke%7Ccolor:0xcfd4d5&style=feature:landscape.natural%7Celement:geometry.fill%7Ccolor:0xdde2e3&style=feature:landscape.natural%7Celement:labels.text.fill%7Ccolor:0x7492a8&style=feature:landscape.natural.terrain%7Cvisibility:off&style=feature:poi%7Celement:geometry.fill%7Ccolor:0xdde2e3&style=feature:poi%7Celement:labels.icon%7Csaturation:-100&style=feature:poi%7Celement:labels.text.fill%7Ccolor:0x588ca4&style=feature:poi.park%7Celement:geometry.fill%7Ccolor:0xa9de83&style=feature:poi.park%7Celement:geometry.stroke%7Ccolor:0xbae6a1&style=feature:poi.sports_complex%7Celement:geometry.fill%7Ccolor:0xc6e8b3&style=feature:poi.sports_complex%7Celement:geometry.stroke%7Ccolor:0xbae6a1&style=feature:road%7Celement:labels.icon%7Csaturation:-45%7Clightness:10%7Cvisibility:on&style=feature:road%7Celement:labels.text.fill%7Ccolor:0x41626b&style=feature:road.arterial%7Celement:geometry.fill%7Ccolor:0xffffff&style=feature:road.highway%7Celement:geometry.fill%7Ccolor:0xc1d1d6&style=feature:road.highway%7Celement:geometry.stroke%7Ccolor:0xa6b5bb&style=feature:road.highway%7Celement:labels.icon%7Cvisibility:on&style=feature:road.highway.controlled_access%7Celement:geometry.fill%7Ccolor:0x9fb6bd&style=feature:road.local%7Celement:geometry.fill%7Ccolor:0xffffff&style=feature:transit%7Celement:labels.icon%7Csaturation:-70&style=feature:transit.line%7Celement:geometry.fill%7Ccolor:0xb4cbd4&style=feature:transit.line%7Celement:labels.text.fill%7Ccolor:0x588ca4&style=feature:transit.station%7Cvisibility:off&style=feature:transit.station%7Celement:labels.text.fill%7Ccolor:0x008cb5%7Cvisibility:on&style=feature:transit.station.airport%7Celement:geometry.fill%7Csaturation:-100%7Clightness:-5&style=feature:water%7Celement:geometry.fill%7Ccolor:0xa6cbe3";
      
      foreach($hubs as $hub){
        $mapPreview = "https://maps.googleapis.com/maps/api/staticmap?key=$apiKey&center=$hub->hub_coordinate";
        $mapPreview .= "&zoom=16&format=png&maptype=roadmap&style=$mapStyle&size=400x400";
        $countLocation = $wpdb->get_var("SELECT COUNT(*) FROM $tbl_pp_hubs_data WHERE hub_id=$hub->hub_id");
      ?>

        <div class="ui card">
          <div class="blurring dimmable image">
            <div class="ui dimmer">
                <div class="content">
                  <div class="center">
                    <div id="viewMdodal" onclick="showModal(`<?= admin_url('/admin.php?page=pukpun_locations&hub_id='.$hub->hub_id); ?>`)" class="ui inverted button" style="padding-top:7px;">View</div>
                    <div id="viewMdodal" onclick="deleteConfirmation(<?php echo $hub->hub_id; ?>,'<?php echo $hub->hub_name; ?>')" class="ui inverted button" style="padding-top:7px;">Delete</div>
                  </div>
                </div>
            </div>
            <img src="<?= $mapPreview; ?>">
          </div>
          <div class="content">
            <a class="header" onclick="showModal(`<?= admin_url('/admin.php?page=pukpun_locations&hub_id='.$hub->hub_id); ?>`)"><?= $hub->hub_name;?></a>
            <div class="meta">
                <span class="date">Created in Nov 2019</span>
            </div>
          </div>
          <div class="extra content">
            <a onclick="showModal(`<?= admin_url('/admin.php?page=pukpun_locations&hub_id='.$hub->hub_id); ?>`)"><i class="users icon"></i><?= $countLocation; ?> Locations</a>
          </div>
        </div>
   <?php } ?>
</div>

<div class="ui modal deleteHub">
  <i class="close icon"></i>
  <div class="header deleteTitle"></div>
  <div class="image content">
    <h5>• It will also delete all location inside it.</h5>
  </div>
  <div class="actions">
    <button class="ui button" onclick="deleteConfirmation(-1,-1)" style="padding-top:7px;">Cancel</button>
    <button class="ui red button confirmDelete" onclick="deleteHub()" style="padding-top:7px;">OK</button>
  </div>
</div>

<script type="text/javascript"> 
   jQuery(document).ready(() => {
   
    jQuery('.special.cards .image').dimmer({
      on: 'hover'
    });
   
   });   
   
  const showModal = (viewUrl) => {
    window.location.href = viewUrl;
  }   

  const deleteConfirmation = (hubId,hubName) => {
    if(hubId == -1 && hubName == -1){
      jQuery('.deleteHub').modal('hide');
    }else{
      jQuery(".deleteTitle").html('Are you sure want to delete '+hubName+' ?');
      jQuery('.deleteHub').modal('show');
      jQuery('.confirmDelete').attr('hub_id', hubId);
    }
  }

  const deleteHub = () => {
    let hub_id = jQuery(".confirmDelete").attr("hub_id");
    if(hub_id == '' || hub_id == null){
      alert('Invalid Location !');
    }else{
      jQuery.ajax({
        type: "POST",
        url: "<?php echo plugin_dir_url( __FILE__ ).'../actions/delete_hub.php'; ?>",
        data: {action: 'delete', hub_id},
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
   
</script>


