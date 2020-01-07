<?php
  global $wpdb;
  $settings_tbl = $wpdb->prefix.'pukpun_settings';
  $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
  $apiKey = $result->key_value;

  if(isset($_POST['create'])){
    submitLocation();
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

  function submitLocation($location_id = null){
    global $wpdb;
    $isFormOk = false;
    $tbl_pp_location = $wpdb->prefix.'pukpun_locations';

    if(isset($_POST['locationName']) && isset($_POST['locationData']) && isset($_POST['attachedHub'])){
      $attachedHub = $_POST['attachedHub'];
      $locationData = $_POST['locationData'];
      if($attachedHub == '' && $locationData == ''){
        handleError('All fields are required');
      }else{
        if($attachedHub == ''){
          handleError("Hub Not Attached");
        }else if($locationData == ''){
          handleError("Polygon data can't be empty !");
        }else{
          $countName = $wpdb->get_var("SELECT COUNT(*) FROM $tbl_pp_location WHERE location_name = '".$_POST['locationName']."'");
          if($countName > 0){
            handleError("The route name already exists.");
          }else{
            $isFormOk = true;
          }
        }
      }
    }

    if($isFormOk){
      $location_name   = $_POST['locationName'];
      $location_data   = $_POST['locationData'];
      $wpdb->insert($tbl_pp_location,
        array(
          'location_name' => $location_name,
          'location_data' => $location_data,
          'location_created_at' => date("Y-m-d"),
          'hub_id' => $attachedHub,
        ),
        array('%s','%s','%s','%s')
      );
      wp_redirect(admin_url('/admin.php?page=pukpun_routes'));
    }else{
      echo "<script>console.log('No POST')</script>";
    }
  }

?>

<div class="ui fluid" style="padding:10px 10px 30px 10px;">

   <form method="post" action="">
      <div class="content">
        <div class="ui form">
            <div class="field">
              <label>Route Name</label>
              <input type="text" name="locationName" placeholder="" required/>
            </div>
            <div class="field">
              <label>Attach Hub</label>
              <select name="attachedHub" class="label ui selection fluid dropdown">
                <option value="">Select Hub</option>
                <?php
                  $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
                  $pukpunHubData = $wpdb->get_results("SELECT * FROM $tbl_pp_hubs");
                    foreach($pukpunHubData as $eachLocation){
                      echo "<option value='$eachLocation->hub_id'>".$eachLocation->hub_name."</option>";
                    }
                ?>
              </select>
            </div>
            <div class="field">
              <label>Draw Polygon</label>
              <div style="border:1px solid rgba(34,36,38,.1); padding:5px;">
                  <div id="googleMapper" style="width:100%; height:500px;"></div>
              </div>
            </div>
            <div class="ui accordion field">
              <div class="title active">
                <i class="icon dropdown"></i> Polygon Data
              </div>
              <div class="content field">
                  <div class="field" style="margin-left: 20px; margin-top: -5px;">
                    <textarea name="locationData" rows="10" id="hub_data" style="resize:none;" readonly></textarea>
                  </div>
              </div>
            </div>
        </div>
      </div>
      <div class="extra content">
        <input type="hidden" id="hub_id" name="hub_id" value="<?php echo $hub_id;?>" />
        <input type="submit" class="ui right floated primary button" style="padding-top:7px;" name="create"/>
        <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
      </div>
    </form>
  
</div>

<script type="module">

import uberMapStyle from "<?= plugin_dir_url( __FILE__ ).'../../assets/js/mapStyle.js'; ?>";

  const getJSONString = (jsonObject) => {
    let simplifiedData = JSON.stringify(jsonObject);
    return simplifiedData.replace(/"/g, "").replace('[','').replace(']','');
  }

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

  var map = new google.maps.Map(document.getElementById('googleMapper'),{
    center: {
      lat: 13.7563,
      lng: 100.5018,
    },
    zoom: 8,
    styles : uberMapStyle,
  });

  var drawingManager = new google.maps.drawing.DrawingManager({
    drawingMode: google.maps.drawing.OverlayType.POLYGON,
    drawingControl: true,
    drawingControlOptions: {
      position: google.maps.ControlPosition.TOP_CENTER,
      drawingModes: ['polygon']
    },
    polygonOptions: {
      editable: true,
      draggable: true
    },
    markerOptions: {
      draggable: true
    },
  });
  drawingManager.setMap(map); 

  google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
    event.overlay.set('editable', true);
    drawingManager.setMap(null);
    
    let drawingData = event.overlay.getPath().getArray();
    let latLongData = getJSONString(drawingData);
    jQuery("textarea#hub_data").val(latLongData);

    let mPaths = event.overlay.getPath();

    google.maps.event.addListener(mPaths, 'set_at', processVertex);

    google.maps.event.addListener(mPaths, 'insert_at', processVertex);

    function processVertex(e) {
      let latlongArr = [];
      for (var i = 0; i < this.getLength(); i++) {
          let vertex = this.getAt(i).toUrlValue(6);
          let splited = vertex.split(",");
          let latlng = {
            lat:splited[0],
            lng:splited[1]
          }
          latlongArr.push(latlng);
      }
      let verifiedLatlng = getJSONString(latlongArr);
      jQuery("textarea#hub_data").val(verifiedLatlng);
    }

  });

</script>