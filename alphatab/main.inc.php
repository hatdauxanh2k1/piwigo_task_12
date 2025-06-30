<?php
/*
Plugin Name: AlphaTab Integration
Version: 1.0
Description: Plugin to integrate AlphaTab for displaying .gp and .gpx files in Piwigo.
Plugin URI: http://yourwebsite.com
Author: Your Name
Author URI: http://yourwebsite.com
*/

if (!defined('PHPWG_ROOT_PATH')) {
  die('Hacking attempt!');
}

include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');
add_event_handler('init', 'auto_description_init');

function auto_description_init()
{
  include_once(dirname(__FILE__) . '/inc/auto_description.inc.php');
}

add_event_handler('init', 'alphatab_init');

function alphatab_init()
{
  global $template;
  $template->assign('PLUGIN_PATH', get_root_url() . 'plugins/alphatab/');
  $template->set_template_dir('plugins/alphatab/template/');
}
add_event_handler('loc_begin_page_header', 'alphatab_add_css');

function alphatab_add_css()
{
  $plugin_url = get_root_url() . 'plugins/alphatab/css/custom.css';

  echo '
    <link rel="stylesheet" type="text/css" href="' . $plugin_url . '">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" 
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer">';
}

add_event_handler('render_element_content', 'alphatab_override_content', EVENT_HANDLER_PRIORITY_NEUTRAL + 10, 2);

function alphatab_override_content($content, $element_info)
{
  global $template;

  $file_extension = pathinfo($element_info['path'], PATHINFO_EXTENSION);

  if (in_array($file_extension, array('gp3', 'gp4', 'gp5', 'mxl', 'xml', 'gp', 'gpx'))) {
    $template->set_filenames(array('alphatab' => 'alphatab.tpl'));
    $template->assign('FILE_PATH', $element_info['path']);
    $template->assign('PLUGIN_PATH', get_root_url() . 'plugins/alphatab/');

    return $template->parse('alphatab', true);
  }
  return $content; // Giữ nội dung gốc nếu không phải file ko được chỉ định
}

add_event_handler('get_admin_plugin_menu_links', 'alphatab_admin_menu');

function alphatab_admin_menu($menu)
{
  array_push($menu, array(
    'NAME' => 'AlphaTab Integration Settings',
    'URL' => get_admin_plugin_menu_link(dirname(__FILE__) . '/admin/admin.php'),
    'SECTION' => 'other'
  ));
  return $menu;
}
