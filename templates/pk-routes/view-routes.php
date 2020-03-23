<div class="ui cards" style="">
   <?php
      global $wpdb;
      $tbl_pp_location = $wpdb->prefix.'pukpun_locations';
      $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
      $tbl_pp_hubs_data = $wpdb->prefix.'pukpun_hubs_data';
  
      $settings_tbl = $wpdb->prefix.'pukpun_settings';
      $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
      $apiKey = $result->key_value;

      function renderNotification($title,$message){
        echo "
          <h2 class='ui center aligned icon header' style='margin-top:25px;'>
            <i class='bullhorn icon'></i> $title
          </h2>
        ";
      }

      function renderTableData($locations){
        foreach($locations as $location){ ?>
        <tr>
          <td><?= $location->location_name; ?></td>
          <td><?= $location->location_created_at; ?></td>
          <td></td>
        </tr>
        <?php }
      }

      function renderTabData($locations){
        $mIndex = 0;
        echo "<script>
          var location_name=[];
          var location_data=[];
        </script>"; 
        
        foreach($locations as $location){ 
          $location_data_string = json_encode($location->location_data);
          $location_name_string = json_encode($location->location_name);
          echo "<script>
            location_name[$mIndex] = $location_name_string;
            location_data[$mIndex] = $location_data_string;
          </script>";
          $hubIdentifier = $location->hub_id == NULL ? 'border:1px solid #F44336' : '';
        ?>

        <div class="ui card" style="<?= $hubIdentifier; ?>">
            <div class="blurring dimmable image">
              <div class="ui dimmer">
                  <div class="content">
                    <div class="center">
                        <div onclick="showModal(location_name[<?php echo $mIndex ?>],location_data[<?php echo $mIndex ?>])"
                          class="ui inverted button" style="padding-top:7px;">View</div>
                    </div>
                  </div>
              </div>
              <img src="https://previews.123rf.com/images/dmstudio/dmstudio1003/dmstudio100300108/6703985-vector-city-map.jpg">
            </div>
            <div class="content">
              <a class="header"><?php echo $location->location_name;?></a>
              <div class="meta">
                  <span class="date">
                  <?php
                    $month = $location->location_created_at; 
                    $date = DateTime::createFromFormat('Y-m-d', $month);  
                    $shorthMonth = $date->format('M');
                    $shorthYear = $date->format('Y');
                    echo "Created in {$shorthMonth} {$shorthYear}";
                  ?>
                  </span>
              </div>
            </div>

            <div class="extra content">
              <div class="ui two buttons">
                <div onclick="updateLoc(<?php echo $location->location_id; ?>)" class="ui inverted green button" style="padding-top:7px !important">Edit</div>
                <div onclick="deleteLoc(<?php echo $location->location_id; ?>,'<?php echo $location->location_name; ?>')" class="ui inverted red button" style="padding-top:7px !important">Delete</div>
              </div>
            </div>

        </div>


        <?php 
        $mIndex++; }
      }

      function changeTitle($title){
        echo "<script>jQuery('#locationTitle').text('$title Locations');</script>";
      }

      $singleHub = '';

      $notfound = false;
      if(isset($_GET['hub_id'])){ // Single Hub
        $sqlQuery = "SELECT * FROM $tbl_pp_location 
          INNER JOIN $tbl_pp_hubs ON $tbl_pp_hubs.hub_id = $tbl_pp_location.hub_id
          WHERE $tbl_pp_location.hub_id = ".$_GET['hub_id']." AND isPrecarious = '0'";
        $locations = $wpdb->get_results($sqlQuery);
        if(count($locations) <= 0){
          renderNotification('Invalid Route','Something went wrong !');
        }else{
          $singleHub = $locations[0]->hub_name;
        }
      }else{
        $locations = $wpdb->get_results("SELECT * FROM $tbl_pp_location WHERE isPrecarious = '0'");
        if(count($locations) <= 0){
          renderNotification('Route Not Found',$msg);
          $notfound = true;
        }
      }

      if($notfound == false){
        if($singleHub != ''){
          changeTitle($singleHub);
        }
      }

  ?>

  <!-- <table class="ui celled table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Created At</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?//= renderTableData($locations); ?>
  </tbody>
</table> -->

  <?= renderTabData($locations); ?>

</div>

<div class="ui modal viewModal">
   <i class="close icon"></i>
   <div class="header modalTitle"></div>
   <div class="image content" style="padding:0px;">
      <div id="viewLocationMap" style="width:100%; height:400px;"></div>
   </div>
</div>

<div class="ui modal deleteModal">
  <i class="close icon"></i>
  <div class="header deleteTitle"></div>
  <div class="actions">
    <button class="ui button" onclick="deleteLoc(-1,-1)" style="padding-top:7px;">Cancel</button>
    <button class="ui red button confirmDelete" onclick="deleteLocNow()" style="padding-top:7px;">OK</button>
  </div>
</div>

<script type="module">

  import uberMapStyle from "<?= plugin_dir_url( __FILE__ ).'../../assets/js/mapStyle.js'; ?>";

  jQuery(document).ready(() => {

    jQuery('.menu .item').tab();

    jQuery('.top.menu .attachedTab').tab({
      'onVisible':function(){
        alert("Called");
      }
    });
    
    jQuery('.card .image').dimmer({
      on: 'hover'
    });

  });
 
  window.createLocation = function(createUrl){
    window.location.href = createUrl;
  }

  window.deleteLocNow = function(){
    let location_id = jQuery(".confirmDelete").attr("location_id");
    if(location_id == '' || location_id == null){
      alert('Invalid Location !');
    }else{
      jQuery.ajax({
        type: "POST",
        url: "<?php echo plugin_dir_url( __FILE__ ).'../../actions/delete_location.php'; ?>",
        data: {action: 'delete', location_id},
        success: function (data, status){
          jQuery('.deleteModal').modal('hide');
          let result = parseInt(data);
          console.log(result,typeof result,status);
          if(result == 1){
            location.reload();
          }
        }
      });
    }
  }

  window.deleteLoc = function(locID,locName){
    if(locID == -1 && locName == -1){
      jQuery('.deleteModal').modal('hide');
    }else{
      jQuery(".deleteTitle").html('Are you sure want to delete '+locName+' ?');
      jQuery('.deleteModal').modal('show');
      jQuery('.confirmDelete').attr('location_id', locID);
    }
  }

  window.updateLoc = function(locationID){
    let url = "<?php echo admin_url('admin.php?page=pukpun_routes'); ?>";
    url = url+"&editRoute="+locationID;
    window.location.href = url;
  }
   
  window.showModal = function(locName,locData){

    let data = '{"data":['+locData.replace(/(lat|lng)/g, '"$1"')+']}';
    let polygon = JSON.parse(data);
    
    jQuery('.viewModal').modal({
      centered: false,
    }).modal('show');
    
    jQuery('.modalTitle').text(locName);    

    var triangle = new google.maps.Polygon({
      paths: polygon.data,
      strokeColor: '#3F51B5',
      strokeOpacity: 1,
      strokeWeight: 2,
      fillColor: '#7986CB',
      fillOpacity: 0.5
    });

    var latlngbounds = new google.maps.LatLngBounds();
    for (var i = 0; i < polygon.data.length; i++) {
      latlngbounds.extend(polygon.data[i]);
    }
    var map = new google.maps.Map(document.getElementById('viewLocationMap'),{
      styles : uberMapStyle
    });
    map.fitBounds(latlngbounds);
    triangle.setMap(map);
  }
  
</script>