<?php
  global $wpdb;
  $settings_tbl = $wpdb->prefix.'pukpun_settings';
  $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
  $apiKey = $result->key_value;

  if(isset($_POST['create'])){
    submitPrecarious();
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

  function submitPrecarious($location_id = null){
    global $wpdb;
    $isFormOk = false;
    $tbl_pp_location = $wpdb->prefix.'pukpun_locations';

    if(isset($_POST['locationData']) && isset($_POST['attachedHub'])){
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
            handleError("The location name already exists.");
          }else{
            $isFormOk = true;
          }
        }
      }
    }

    if($isFormOk){
      $location_data   = $_POST['locationData'];
      $wpdb->insert($tbl_pp_location,
        array(
          'location_name' => '-',
          'location_data' => $location_data,
          'location_created_at' => date("Y-m-d"),
          'isPrecarious' => 1,
          'hub_id' => $attachedHub,
        )
      );
      wp_redirect(admin_url('/admin.php?page=pukpun_precarious'));
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
                <label>Attach Hub</label>
                <select name="attachedHub" class="label ui selection fluid dropdown" onchange="getHubRoutes(this);">
                  <option value="">Select Hub</option>
                  <?php
                    global $wpdb;
                    $tbl_hub = $wpdb->prefix.'pukpun_hubs';
                    $tbl_loc = $wpdb->prefix.'pukpun_locations';

                    $rawSql = "SELECT $tbl_hub.hub_id, $tbl_hub.hub_name FROM $tbl_loc AS loc
                      INNER JOIN $tbl_hub ON $tbl_hub.hub_id = loc.hub_id
                      WHERE loc.hub_id NOT IN (
                        SELECT loc2.hub_id from $tbl_loc AS loc2 WHERE loc2.hub_id IN 
                        (SELECT loc3.hub_id FROM $tbl_loc AS loc3 WHERE loc3.isPrecarious = '1')
                      )
                      GROUP BY loc.hub_id
                    ";

                    $hubs = $wpdb->get_results($rawSql);
                    foreach($hubs as $eachHub){
                      echo "<option value='$eachHub->hub_id'>".$eachHub->hub_name."</option>";
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
      <div class="extra content" style="padding-top:20px;">
          <input type="hidden" id="hub_id" name="hub_id" value="<?php echo $hub_id;?>" />
          <input type="submit" class="ui right floated primary button" style="padding-top:7px;" name="create"/>
          <button class="clearBtn ui right floated red button" style="padding-top:7px;">Clear</button>
      </div>
    </form>
</div>
 
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&libraries=drawing"></script>
      
<script type="module">

  import uberMapStyle from "<?php echo plugin_dir_url( __FILE__ ).'../../assets/js/mapStyle.js'; ?>";

  var pathPolygon;

  window.getHubRoutes = function(e){
    let hub_id = e.value;
    jQuery.ajax({
        url: "<?= plugin_dir_url(__FILE__).'../../actions/query_locations.php' ?>",
        type: "GET",
        async: false,
        data: {hub_id : hub_id},
        success: function (response){
          let data = JSON.parse(response);
          // console.log(data);
          let polygon = data.reduce((found, eachHub) => {
              let precariousData = JSON.parse('{"data":['+eachHub.data.replace(/(lat|lng)/g, '"$1"')+']}');
              found.push(precariousData.data);
              return found;
          }, []);
          setMap(polygon);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus, errorThrown);
        }
    });

  }

  const setMap = (polygon) => {
    console.log('setting map',polygon);
    if(pathPolygon != undefined){
      pathPolygon.setMap(null);    
    }
    pathPolygon = new google.maps.Polygon({
      paths: polygon,
      strokeColor: "#673AB7",
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: "#673AB7",
      fillOpacity: 0.35
    });
    pathPolygon.setMap(map);
    let bounds = new google.maps.LatLngBounds();
    pathPolygon.getPath().forEach(function (path, index) {
      bounds.extend(path);
    });
    map.fitBounds(bounds);
    map.setZoom(12);
  }

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