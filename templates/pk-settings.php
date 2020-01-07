<?php
  global $wpdb;
  $settings_tbl = $wpdb->prefix.'pukpun_settings';
  $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'map_api_key'");
  $oldKey = $result->key_value;
  $btnText = $oldKey == '' ? 'Save' : 'Update';
  $btnClass = $oldKey == '' ? 'primary' : 'green';

  $result = $wpdb->get_row("SELECT * FROM $settings_tbl WHERE key_name = 'query_api'");
  $query_api = $result->key_value;
  $query_api_value = plugin_dir_url(__DIR__).'query.php';
  if($query_api == ''){
    $wpdb->insert($settings_tbl,array(
      'key_name' => 'query_api',
      'key_value' => $query_api_value,
    ),array('%s','%s'));
  }else{
    $wpdb->update($settings_tbl, array('key_value' => $query_api_value), array('key_name' => 'query_api') );
  }

  if($oldKey == ''){
    $apiKeyUrl = "https://developers.google.com/maps/documentation/javascript/get-api-key";
    showNotification('API KEY REQUIRED','Register your <a href="'.$apiKeyUrl.'" target="_blank"><u>key</u></a> to make hubs and routes working properly.');
  }

  function showNotification($title,$message){ 
    echo "
    <div class='ui icon info message' style='width:99%;'>
      <i class='cog icon'></i>
      <div class='content'>
        <div class='header'>$title</div>
        <div class='description' style='margin-top:5px;'>$message</div>
      </div>
      <i class='close icon'></i>
    </div> 
    ";
  }

  if(isset($_POST['save_btn'])){
    $apikey = isset($_POST['api_key']) ? $_POST['api_key'] : null;
    if($apikey != null){
      if($oldKey == ''){ // NEW
        $wpdb->insert($settings_tbl,array(
          'key_name' => 'map_api_key',
          'key_value' => $apikey,
        ),
          array('%s','%s')
        );
      }else{ // UPDATE
        $wpdb->update($settings_tbl, array('key_value' => $apikey), array('key_name' => 'map_api_key') );
      }
      wp_redirect(admin_url('/admin.php?page=pukpun_settings'));
    }
  }
?>

<div class="ui grid" style="padding-right:5px; margin-right:0;">
   <div class="wide column">
      <div class="ui top attached tabular menu">
         <a class="item active" data-tab="settings">Settings</a>
         <a class="item" data-tab="second">Query API</a>
      </div>

      <div class="ui bottom attached tab segment active" data-tab="settings">
         <form method="post" action="">
            <div class="ui form">
               <div class="field">
                  <label>API KEY</label>
                  <div class="ui action input">
                    <input type="text" name="api_key" value="<?= $oldKey; ?>" required/>
                    <button name="save_btn" class="ui <?= $btnClass; ?> button" style="height:38px"><?= $btnText; ?></button>
                </div>
               </div>
            </div>
         </form>
      </div>

      <div class="ui bottom attached tab segment" data-tab="second">
         <div class="ui form">
            <div class="field">
               <label>Data Source</label>
               <div class="ui action input">
                  <input type="text" value="<?= plugin_dir_url(__DIR__).'query.php';?>" readonly/>
                  <div class="ui primary button" style="height:38px" onclick="fetchData()">Execute</div>
               </div>
            </div>
         </div>
      </div>

   </div>
</div>

<script type="module">

  import * as pukpunRoot from "<?php echo get_stylesheet_directory_uri() . '/assets/js/pukpun.js' ?>";

  jQuery(document).ready(() => {
    jQuery('.menu .item').tab();
    jQuery('.message .close').on('click', function() {
      jQuery(this).closest('.message').transition('fade');
    });
  });


  window.fetchData = function(){
    jQuery.ajax({
      url: "<?php echo plugin_dir_url(__DIR__).'query.php'; ?>",
      type: "GET",
      success: function (response){
        let data = pukpunRoot.getParsedPrecariousData(response,true);
        let Pdata = pukpunRoot.getParsedPrecariousData(response,false);
        console.log('data',data);
        console.log('Pdata',Pdata);
        alert('Please check console');
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

</script>