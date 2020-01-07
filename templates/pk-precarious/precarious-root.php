<div class="ui grid" style="padding-right:5px; margin-right:0;">
   <div class="wide column">
    <div class="ui top attached tabular menu">
      <a class="active item" data-tab="first">Precarious Area</a>
      <a class="item" data-tab="second">Create Precarious</a>
    </div>
    <div class="ui bottom attached active tab segment" data-tab="first">
      <?php include(plugin_dir_path( __FILE__ ).'/view-precarious.php'); ?>
    </div>
    <div class="ui bottom attached tab segment" data-tab="second">
      <?php include(plugin_dir_path( __FILE__ ).'/create-precarious.php'); ?>
    </div>
  </div>
</div>

<script>
  jQuery(document).ready(() => {
    jQuery('.menu .item').tab();
  });
</script>