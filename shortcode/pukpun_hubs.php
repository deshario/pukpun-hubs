<?php

  wp_register_style('semantic_ui_css', 'https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css', false, '1.0.0' );
  wp_enqueue_style('semantic_ui_css');

  wp_register_style('pukpun_css', plugin_dir_url( __FILE__ ).'../assets/css/pukpun_hubs.css', false, '1.0.0' );
  wp_enqueue_style('pukpun_css');

  global $wpdb;
  $tbl_pp_hubs = $wpdb->prefix.'pukpun_hubs';
  $hubs = $wpdb->get_results("SELECT * FROM $tbl_pp_hubs");

  $settings_tbl = $wpdb->prefix.'pukpun_settings';
  $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
  $apiKey = $result->key_value; ?>
  
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>"></script>

<?php
  $layout = "";
  $iterator = 0;
  $hubsData = json_encode($hubs);
  
  $aa = array('red','green');
  foreach($hubs as $hub){
      $img_atts = wp_get_attachment_image_src($hub->hub_cover,'adv-pos-a-large');
      $layout .= "
          <div class='ui card fluid pukpun-card'>
              <div class='ui grid'>
                  <div class='five wide column'>
                      <div class='content'>
                          <p align='center' class='pukpun-title'>$hub->hub_name</p>
                          <div class='ui clearing divider pukpun-divider'></div>
                          <p class='ml15'>ที่อยู่<br/>
                              <label class='ml15'>$hub->hub_address</label>
                          </p>
                          <p class='ml15'>สามารถรับน้ำได้<br/>
                              <label class='ml15'>จันทร์ – ศุกร์ เวลา $hub->hub_opening</label>
                          </p>
                          <img class='mlb13' src='$img_atts[0]'/>
                      </div>
                  </div>
                  <div class='ten wide column'>
                      <div id='map_$iterator' class='pukpun-map'></div>
                  </div>
              </div>
          </div>
      ";
      $iterator++;
  }
  echo '<script>var hubsData = '.$hubsData.'</script>';
  ?>

  <script>
  setTimeout(function(){
      for(let i=0; i<hubsData.length; i++){
          let eachHub = hubsData[i];
          let coordinate = eachHub.hub_coordinate.split(',');
          const element = document.getElementById('map_'+i);
          let gMap = new google.maps.Map(element); 
          gMap.setCenter(new google.maps.LatLng(coordinate[0],coordinate[1]));
          gMap.setMapTypeId(google.maps.MapTypeId.ROADMAP);
          gMap.setZoom(13);
          let markerCenter = new google.maps.LatLng(coordinate[0],coordinate[1]);
          let marker = new google.maps.Marker({position:markerCenter, map: gMap});
      }
  }, 500);

  </script>

<?= $layout; ?>