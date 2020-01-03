<?php
  /**
   * Uninstall PukPun Hubs
   *
   * @package   PukPun Hubs
   * @author    Deshario Sunil
   */


  if ( ! defined( 'ABSPATH' ) ) {
    exit();
  }

  if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit; // Exit if accessed directly
  }

  global $wpdb;
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pukpun_hubs");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pukpun_locations");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pukpun_hubs_data");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pukpun_logs");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pukpun_settings");