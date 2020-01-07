<div class='ui cards' style='padding-left: 10px; padding-right: 5px;'>
  <?php
    global $wpdb;
    $hubTbl = $wpdb->prefix.'pukpun_hubs';
    $precariousTbl = $wpdb->prefix.'pukpun_locations';
    $precariousHubs = $wpdb->get_results("
      SELECT * FROM $precariousTbl
      INNER JOIN $hubTbl on $hubTbl.hub_id = $precariousTbl.hub_id
      WHERE $precariousTbl.isPrecarious = '1'
    ");

    if(count($precariousHubs) <= 0){
      echo "
        <h2 class='ui center aligned icon header' style='margin-top:25px;'>
          <i class='bullhorn icon'></i> Precarious Not Found
        </h2>
      ";
    }

    $mIndex = 0;
    echo "<script>
      var location_name=[];
      var location_data=[];
    </script>";
    foreach($precariousHubs as $eachHub){
      $location_data_string = json_encode($eachHub->location_data);
      $location_name_string = json_encode($eachHub->hub_name);
      echo "<script>
        location_name[$mIndex] = $location_name_string;
        location_data[$mIndex] = $location_data_string;
      </script>";
  ?>
      
      <div class="ui card">
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
          <a class="header"><?= $eachHub->hub_name;?></a>
          <div class="meta">
          <span class="date">
              <?php
                $month = $eachHub->location_created_at; 
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
            <div onclick="updateLoc(<?= $eachHub->location_id; ?>)" class="ui inverted green button" style="padding-top:7px !important">Edit</div>
            <div onclick="deleteLoc(<?= $eachHub->location_id; ?>,'<?= $eachHub->hub_name; ?>')" class="ui inverted red button" style="padding-top:7px !important">Delete</div>
          </div>
        </div>
      </div>

  <?php $mIndex++; } ?>
 
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

  import uberMapStyle from "<?php echo plugin_dir_url( __FILE__ ).'../../assets/js/mapStyle.js'; ?>";

  jQuery(document).ready(() => {

    jQuery('.card .image').dimmer({
      on: 'hover'
    });

  });

  window.showModal = function(locName,locData){
    let data = '{"data":['+locData.replace(/(lat|lng)/g, '"$1"')+']}';
    let polygon = JSON.parse(data);

    jQuery('.viewModal').modal({
      centered: false,
    }).modal('show');

    jQuery('.modalTitle').text(locName);    

    var triangle = new google.maps.Polygon({
      paths: polygon.data,
      strokeColor: '#E91E63',
      strokeOpacity: 1,
      strokeWeight: 2,
      fillColor: '#E91E63',
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

  window.deleteLoc = function(locID,locName){
    if(locID == -1 && locName == -1){
      jQuery('.deleteModal').modal('hide');
    }else{
      jQuery(".deleteTitle").html('Are you sure want to delete precarious of '+locName+' ?');
      jQuery('.deleteModal').modal('show');
      jQuery('.confirmDelete').attr('location_id', locID);
    }
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

  window.updateLoc = function(locationID){
    let url = "<?php echo admin_url('admin.php?page=pukpun_precarious'); ?>";
    url = url+"&editPrecarious="+locationID;
    window.location.href = url;
  }
  
</script>