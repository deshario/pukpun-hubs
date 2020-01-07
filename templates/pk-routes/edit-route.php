  <?php
    global $wpdb;
    $settings_tbl = $wpdb->prefix.'pukpun_settings';
    $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
    $apiKey = $result->key_value;

    if(isset($_POST['update'])){
      updateLocation();
    }

    function showNoti($type='negative', $title = 'Invalid Form', $icon='attention', $message){
      echo "
      <div class='ui icon $type message' style='width:99%;'>
        <i class='$icon icon'></i>
        <div class='content'>
          <div class='header'>$title</div>
          <div class='description' style='margin-top:5px;'>$message</div>
        </div>
        <i class='close icon'></i>
      </div> 
      ";
    }

    function updateLocation(){
      global $wpdb;
      $isFormOk = false;
      $useDefaultData = false;
      $tbl_pp_location = $wpdb->prefix.'pukpun_locations';

      if(isset($_POST['polygonData']) && isset($_POST['locationID']) && isset($_POST['isInitial']) && isset($_POST['attachedHub'])){
        if($_POST['attachedHub'] == ''){
          showNoti("Please attach hub");
        }else{
          if($_POST['isInitial'] == 1 && $_POST['polygonData'] != ''){ // Same Polygon Data
            $useDefaultData = true;
          }
          $isFormOk = true;
        }
      }
  
      if($isFormOk){
        if($useDefaultData == true){
          $location_data   = $_POST['polygonData'];
        }else{
          $location_data   = $_POST['locationData'];
        }
        $location_id     = $_POST['locationID'];
        $location_name   = $_POST['locationName'];
        $attached_hub   = $_POST['attachedHub'];
        $result = $wpdb->update($tbl_pp_location,array(
          'location_name' => $location_name,
          'location_data' => $location_data,
          'hub_id' => $attached_hub,
          'location_updated_at' => date("Y-m-d"),
          ),array('location_id' => $location_id)
        );
        if($result == true){
          wp_redirect(admin_url('/admin.php?page=pukpun_routes'));
        }else if($result == 0){
          showNoti('warning','Update Error','bell outline','No rows affected');
        }else{
          showNoti("Something went wrong !");
        }
      }
    
    }

    $editRouteID = isset($_GET['editRoute']) ? $_GET['editRoute'] : null;
    if($editRouteID == null){
      echo "<script>alert('Invalid Access')</script>";
    }else{ 
      global $wpdb;
      $tbl_pp_location = $wpdb->prefix.'pukpun_locations';
      $foundLocation = $wpdb->get_row("SELECT * FROM $tbl_pp_location WHERE location_id = $editRouteID");
      if($foundLocation == null && $foundLocation == ''){
        echo "<script>
          alert('Invalid location id');
          window.history.back();
        </script>";
      }else{
        $id = $foundLocation->location_id;
        $name = $foundLocation->location_name;
        $hub_id = $foundLocation->hub_id;
        $polygonData = $foundLocation->location_data;
        $data = json_encode($polygonData);
        echo "<script>var location_data = $data;</script>";
   ?>

  <div class="ui grid" style="margin-top:10px; padding-right:5px; margin-right:0;">
    <div class="wide column">
        <form method="post" action="">
          <div class="ui card fluid">
              <div class="content">
                <i class="map marker alternate icon"></i>Edit Route
              </div>
              <div class="content">
                <div class="ui form">
                    <div class="field">
                      <label>Location Name</label>
                      <input type="text" name="locationName" value="<?= $name; ?>" required/>
                    </div>
                    <div class="field">
                      <label>Attached Hub</label>
                      <select name="attachedHub" class="label ui selection fluid dropdown">
                        <option value=''>Select Hub</option>
                        <?php
                          $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
                          $pukpunHubData = $wpdb->get_results("SELECT * FROM $tbl_pp_hubs");
                            foreach($pukpunHubData as $eachLocation){
                              if($hub_id == $eachLocation->hub_id){
                                echo "<option selected value='$eachLocation->hub_id'>".$eachLocation->hub_name."</option>";
                              }else{
                                echo "<option value='$eachLocation->hub_id'>".$eachLocation->hub_name."</option>";
                              }
                            }
                        ?>
                      </select>
                    </div>
                    <div class="field">
                      <label>Polygon</label>
                      <div style="border:1px solid rgba(34,36,38,.1); padding:5px; position:relative; z-index:0;">
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
                <input type="hidden" id="polygonData" name="polygonData" value="<?= $polygonData; ?>" />
                <input type="submit" class="ui right floated primary button" style="padding-top:7px;" name="update" value="Update"/>
                <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
              </div>
          </div>
        </form>
    </div>
  </div>

  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&libraries=drawing"></script>

  <script type="module">

    import BlitzMap from "<?php echo plugin_dir_url( __FILE__ ).'../../assets/js/blitzMap.js'; ?>";

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

      jQuery('.label.ui.dropdown').dropdown();
      jQuery('.no.label.ui.dropdown').dropdown({
          useLabels: false
      });

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