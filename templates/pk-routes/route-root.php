<div class="ui grid" style="padding-right:5px; margin-right:0;">
   <div class="wide column">
    <div class="ui top attached tabular menu">
      <a class="active item" data-tab="first" id='locationTitle'>PukPun Routes</a>
      <a class="item" data-tab="second">Create Route</a>
    </div>
    <div class="ui bottom attached active tab segment" data-tab="first">
      <?php include(plugin_dir_path( __FILE__ ).'/view-routes.php'); ?>
    </div>
    <div class="ui bottom attached tab segment" data-tab="second">
      <?php include(plugin_dir_path( __FILE__ ).'/create-route.php'); ?>
    </div>
  </div>
</div>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?= $apiKey ?>&libraries=drawing"></script>

<script>

  jQuery(document).ready(() => {
    jQuery('.menu .item').tab();
  });

</script>