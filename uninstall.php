<?php
  /**
   * Uninstall PukPun Hubs
   *
   * @package   PukPun Hubs
   * @author    Deshario Sunil
   * @license   MIT
   * @link      [Your URL]
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