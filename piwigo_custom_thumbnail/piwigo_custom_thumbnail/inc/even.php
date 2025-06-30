<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

add_event_handler('init', 'add_admin_vars');

function get_custom_thumbnails_config()
{
  global $conf;

  if (!isset($conf['custom_thumbnails'])) {
    return array(); // Trả về mảng rỗng nếu không tồn tại
  }

  $data = @unserialize($conf['custom_thumbnails']);
  return (is_array($data)) ? $data : array();
}

function add_admin_vars()
{
  global $template;
  $thumbnails = get_custom_thumbnails_config();
  $template->assign('CUSTOM_THUMBNAILS', $thumbnails);
}
