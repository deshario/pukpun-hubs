<?php
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

    if(isset($_POST['locationName']) && isset($_POST['locationData'])){
      if($_POST['locationData'] == ''){
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

    if($isFormOk){
      $location_name   = $_POST['locationName'];
      $location_data   = $_POST['locationData'];
      $wpdb->insert($tbl_pp_location,
        array(
            'location_name' => $location_name,
            'location_data' => $location_data,
            'location_created_at' => date("Y-m-d"),
        ),
        array('%s','%s','%s')
      );
    wp_redirect(admin_url('/admin.php?page=pukpun_locations'));
  }else{
    echo "<script>console.log('No POST')</script>";
  }
}

?>

<div class="ui grid" style="margin-top:10px; padding-right:5px; margin-right:0;">
   <div class="wide column">

   <form method="post" action="">
      <div class="ui card fluid">
         <div class="content">
            <i class="map marker alternate icon"></i>Create Location
         </div>
         <div class="content">
            <div class="ui form">
               <div class="field">
                  <label>Location Name</label>
                  <input type="text" name="locationName" placeholder="RM1" required/>
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
      </div>
    </form>
  
   </div>
</div>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDhHg4UMW0HIy9ZdBJfTQRrbkxz91APPi0&libraries=drawing"></script>
      
<script type="module">

  import uberMapStyle from "<?php echo plugin_dir_url( __FILE__ ).'../assets/js/mapStyle.js'; ?>";

  const getJSONString = (jsonObject) => {
    let simplifiedData = JSON.stringify(jsonObject);
    return simplifiedData.replace(/"/g, "").replace('[','').replace(']','');
  }

  jQuery(document).ready(() => { 

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

    // google.maps.event.addListener(event.overlay.getPath(), 'set_at', function(index, obj) {

    //   let currentData = JSON.parse(JSON.stringify(drawingData));
    //   let newData = JSON.parse(JSON.stringify(obj));
    //   const updatedDrawingData = Object.assign([], currentData, {[index]: newData});

    //   let updatedLatLong = getJSONString(updatedDrawingData);
    //   jQuery("textarea#hub_data").val(updatedLatLong);

    // });


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
      // console.log(verifiedLatlng);
      jQuery("textarea#hub_data").val(verifiedLatlng);
    }

  });

</script>