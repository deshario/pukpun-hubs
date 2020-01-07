<div class="ui grid" style="padding-right:5px; margin-right:0;">
   <div class="wide column">
    <div class="ui top attached tabular menu">
      <a class="active item" data-tab="first" id='locationTitle'>PukPun Hubs</a>
      <a class="item" data-tab="second">Create Hub</a>
    </div>
    <div class="ui bottom attached active tab segment" data-tab="first">
      <?php include(plugin_dir_path( __FILE__ ).'/view-hubs.php'); ?>
    </div>
    <div class="ui bottom attached tab segment" data-tab="second">
      <?php include(plugin_dir_path( __FILE__ ).'/create-hub.php'); ?>
    </div>
  </div>
</div>

<script>

  jQuery(document).ready(() => {
    jQuery('.menu .item').tab();
  });

</script>