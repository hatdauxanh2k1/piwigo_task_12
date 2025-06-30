<?php
/*
Plugin Name: Custom Thumbnail Handler
Version: 1.0.0
Description: Set default thumbnails for specific file types like MP3, PDF,....
Author: Your Name
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Define paths
define('CUSTOM_THUMBNAIL_PATH', PHPWG_PLUGINS_PATH . 'piwigo_custom_thumbnail/');
define('CUSTOM_THUMBNAIL_ADMIN', CUSTOM_THUMBNAIL_PATH . 'admin/');
define('CUSTOM_THUMBNAIL_IMAGES', CUSTOM_THUMBNAIL_PATH . 'assets/images/');
define('CUSTOM_THUMBNAIL_TEMPLATE', CUSTOM_THUMBNAIL_ADMIN . 'template/');

include_once(CUSTOM_THUMBNAIL_PATH . 'inc/even.php');
// Register event handlers
add_event_handler('get_admin_plugin_menu_links', 'custom_thumbnail_admin_menu');

// Add admin menu
function custom_thumbnail_admin_menu($menu)
{
  $menu[] = array(
    'NAME' => 'Custom Thumbnail',
    'URL' => get_admin_plugin_menu_link(dirname(__FILE__)) . '/admin/admin.php'
  );
  return $menu;
}

add_event_handler('loc_begin_page_header', 'customs_thumbnail_add_css');

function customs_thumbnail_add_css()
{
  $plugin_url = get_root_url() . 'plugins/piwigo_custom_thumbnail/assets/css/custom.css';

  echo '
    <link rel="stylesheet" type="text/css" href="' . $plugin_url . '">';
}
