<div class="ui special cards" style="">
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
          <div class='ui icon info message' style='width:98%;'>
            <i class='exclamation icon'></i>
            <div class='content'>
              <div class='header'>$title</div>
              <div class='description' style='margin-top:5px;'>$message</div>
            </div>
            <i class='close icon'></i>
          </div> 
        ";
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

      $singleHub = '';

      if(isset($_GET['hub_id'])){ // Single Hub
        $sqlQuery = "SELECT * FROM $tbl_pp_location 
          INNER JOIN $tbl_pp_hubs ON $tbl_pp_hubs.hub_id = $tbl_pp_location.hub_id
          WHERE $tbl_pp_location.hub_id = ".$_GET['hub_id'];
        $locations = $wpdb->get_results($sqlQuery);
        if(count($locations) <= 0){
          $createLocationUrl = '"'.admin_url('/admin.php?page=pukpun_locations-new').'"';
          renderNotification('Invalid Location','Something went wrong !');
          echo "<script>var createLocationUrl = ".$createLocationUrl."</script>";
        }else{
          $singleHub = $locations[0]->hub_name;
        }
      }else{
        $locations = $wpdb->get_results("SELECT * FROM $tbl_pp_location");
        if(count($locations) <= 0){
          $createLocationUrl = '"'.admin_url('/admin.php?page=pukpun_locations-new').'"';
          echo "<script>var createLocationUrl = ".$createLocationUrl."</script>";
          $msg = "Click <a href='#' onclick='createLocation(".$createLocationUrl.")'>here</a> to create your first location";
          renderNotification('Location Not Found',$msg);
        }
      }

      if($singleHub != ''){
          echo "
            <div class='ui segment' style='width:99%; margin-top: 25px; margin-right: 20px;'>
            <h2 class='ui left floated header' style='padding-left:10px;'>$singleHub Locations</h2>
            <div class='ui clearing divider'></div>
            <div class='ui cards' style='padding-left: 10px; padding-right: 5px;'>
          ";
      }else{
          echo "
            <div class='ui segment' style='width:99%; margin-top: 25px; margin-right: 20px;'>
            <h2 class='ui left floated header' style='padding-left:10px;'>Locations</h2>
            <div class='ui clearing divider'></div>
            <div class='ui cards' style='padding-left: 10px; padding-right: 5px;'>
          ";
      }
  ?>

  <?= renderTabData($locations); ?>

</div>
</div>
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

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&libraries=drawing"></script>

<script type="module">

  import uberMapStyle from "<?php echo plugin_dir_url( __FILE__ ).'../assets/js/mapStyle.js'; ?>";

  jQuery(document).ready(() => {

    jQuery('.menu .item').tab();

    jQuery('.top.menu .attachedTab').tab({
      'onVisible':function(){
        alert("Called")
        filterSelection('unattachedHub');
      }
    });
    

    jQuery('.special.cards .image').dimmer({
      on: 'hover'
    });
    jQuery('.message .close').on('click', function() {
      jQuery(this).closest('.message').transition('fade');
      window.location.href = createLocationUrl;
    });
  });






  window.filterSelection = function(c) {
  var x, i;
  x = document.getElementsByClassName("carditem");
  if (c == "all") c = "";
  for (i = 0; i < x.length; i++) {
    w3RemoveClass(x[i], "show");
    if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], "show");
  }
}

window.w3AddClass = function(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    if (arr1.indexOf(arr2[i]) == -1) {
      // element.className += " " + arr2[i];
      element.style.display = 'block';
    }
  }
  console.log('Adding Class');
}

window.w3RemoveClass = function(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(" ");
  arr2 = name.split(" ");
  for (i = 0; i < arr2.length; i++) {
    while (arr1.indexOf(arr2[i]) > -1) {
      arr1.splice(arr1.indexOf(arr2[i]), 1);     
    }
  }
  element.className = arr1.join(" ");
  element.style.display = 'none';
  console.log('removed');
}

 
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
        url: "<?php echo plugin_dir_url( __FILE__ ).'../actions/delete_location.php'; ?>",
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
    let url = "<?php echo admin_url('admin.php?page=pukpun_locations'); ?>";
    url = url+"&editLocation="+locationID;
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