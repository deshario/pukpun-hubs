<div class="ui grid" style="margin-top:10px; padding-right:5px; margin-right:0;">
   <div class="wide column">
      <div class="ui card fluid">
         <div class="content">
            <i class="file outline icon"></i>Request Logs
         </div>
         <div class="content" style="padding:0px;">
            <table class="ui celled table" style="border:0; cursor:pointer;">
            <thead>
               <tr>
                  <th style='width:5px;'>#</th>
                  <th>Location</th>
                  <th>LatLong</th>
                  <th>Request At</th>
                  <th style='text-align: center;'>Request By</th>
               </tr>
            </thead>
               <tbody>
                  <?php
                     global $wpdb;
                     $pukpun_logs = $wpdb->prefix.'pukpun_logs';        
                     $request_logs = $wpdb->get_results("SELECT * FROM $pukpun_logs");
                     $iterator = 1;
                     foreach($request_logs as $eachlog){
                        $user_info = get_userdata($eachlog->log_creator);
                        $user_name = $user_info->display_name;
                        $mapLink = "https://maps.google.com/?q=$eachlog->log_latlng";
                        echo "
                           <tr>
                              <td>$iterator</td>
                              <td>$eachlog->log_location</td>
                              <td><a href='$mapLink' target='_blank'>$eachlog->log_latlng</a></td>
                              <td>$eachlog->log_created</td>
                              <td style='text-align: center;'>$user_name</td>
                           </tr>
                        ";
                        $iterator++;
                     }
                  ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>