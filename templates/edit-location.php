  <?php

    if(isset($_POST['update'])){
      updateLocation();
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

    function updateLocation(){
      global $wpdb;
      $isFormOk = false;
      $tbl_pp_location = $wpdb->prefix.'pukpun_locations';

      if(isset($_POST['locationID']) && isset($_POST['isInitial'])){
        if($_POST['isInitial'] == 1){
          handleError("Same Polygon Detected");
        }else{
          $isFormOk = true;
        }
      }
  
      if($isFormOk){
        $location_id     = $_POST['locationID'];
        $location_name   = $_POST['locationName'];
        $location_data   = $_POST['locationData'];
        $result = $wpdb->update($tbl_pp_location,array(
          'location_name' => $location_name,
          'location_data' => $location_data,
          'location_updated_at' => date("Y-m-d"),
          ),array('location_id' => $location_id)
        );
        if($result == true){
          wp_redirect(admin_url('/admin.php?page=pukpun_locations'));
        }else{
          handleError("Something went wrong !");
        }
    }
    
  }

    $editLocationID = isset($_GET['editLocation']) ? $_GET['editLocation'] : null;
    if($editLocationID == null){
      echo "<script>alert('Invalid Access')</script>";
    }else{ 
      global $wpdb;
      $tbl_pp_location = $wpdb->prefix.'pukpun_locations';
      $foundLocation = $wpdb->get_row("SELECT * FROM $tbl_pp_location WHERE location_id = $editLocationID");
      if($foundLocation == null && $foundLocation == ''){ // Found
        echo "<script>alert('Invalid location id')</script>";
      }else{
        $id = $foundLocation->location_id;
        $name = $foundLocation->location_name;
        $data = json_encode($foundLocation->location_data);
        echo "<script>var location_data = $data;</script>";
   ?>

  <div class="ui grid" style="margin-top:10px; padding-right:5px; margin-right:0;">
    <div class="wide column">
        <form method="post" action="">
          <div class="ui card fluid">
              <div class="content">
                <i class="map marker alternate icon"></i>Edit Location
              </div>
              <div class="content">
                <div class="ui form">
                    <div class="field">
                      <label>Location Name</label>
                      <input type="text" name="locationName" value="<?= $name; ?>" required readonly/>
                    </div>
                    <div class="field">
                      <label>Polygon</label>
                      <div style="border:1px solid rgba(34,36,38,.1); padding:5px;">
                          <div id="myMap" style="width:100%; height:500px;"></div>
                      </div>
                    </div>

                    <div class="ui accordion field">
                      <div class="title active">
                        <i class="icon dropdown"></i> Polygon Data
                      </div>
                      <div class="content field">
                        <div class="field" style="margin-left: 20px; margin-top: -5px;">
                          <textarea name="locationData" id="mapData" rows="10" style="resize:none;" readonly></textarea>
                        </div>
                      </div>
                    </div>

                </div>
              </div>
              <div class="extra content">
                <input type="hidden" id="isInitial" name="isInitial" value="1" />
                <input type="hidden" id="locationID" name="locationID" value="<?= $id; ?>" />
                <input type="submit" class="ui right floated primary button" style="padding-top:7px;" name="update" value="Update"/>
                <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
              </div>
          </div>
        </form>
    </div>
  </div>

  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDhHg4UMW0HIy9ZdBJfTQRrbkxz91APPi0&libraries=drawing"></script>

  <script type="module">

    import BlitzMap from "<?php echo plugin_dir_url( __FILE__ ).'../assets/js/blitzMap.js'; ?>";

    let data = '{"data":['+location_data.replace(/(lat|lng)/g, '"$1"')+']}';
    let polygon = JSON.parse(data);

    let bounds = new google.maps.LatLngBounds();
    for (var i=0; i< polygon.data.length; i++) {
      bounds.extend(polygon.data[i]);
    }
    let centerLatlng = bounds.getCenter();

    var mapObject = {
      "tilt": 0,
      "mapTypeId": "hybrid",
      "center" : centerLatlng,
      "overlays": [{
        "type": "polygon",
        "title": "",
        "content": "",
        "fillColor": "#000000",
        "fillOpacity": 0.3,
        "strokeColor": "#000000",
        "strokeOpacity": 0.9,
        "strokeWeight": 3,
        "paths": [
          polygon.data
        ]
      }]
    };

    jQuery("textarea#mapData").val(JSON.stringify(mapObject));

    BlitzMap.setMap('myMap', true, 'mapData');

    jQuery(document).ready(() => { 

      jQuery('.ui.accordion').accordion({
        collapsible: true,
        active: false
      });

      jQuery('.message .close').on('click', function() {
        jQuery(this).closest('.message').transition('fade');
      });

    }); 

  </script>
  
<?php } } ?>