<?php
/*
Plugin Name: Custom Thumbnail Handler
Version: 1.0.0
Description: Set default thumbnails for specific file types like MP3, PDF
Author: Your Name
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Define paths
define('CUSTOM_THUMBNAIL_UPLOAD', 'data/upload/custom_thumbnails/');

// Register event handlers
add_event_handler('format_exif_data', 'custom_thumbnail_handler');

// Xử lý lưu dữ liệu
if (isset($_POST['submit'])) {
  $new_thumbnails = array();
  if (isset($_POST['extensions']) && is_array($_POST['extensions'])) {
    foreach ($_POST['extensions'] as $index => $extensions_str) {
      $thumbnail = trim($_POST['thumbnails'][$index]);

      if (empty($thumbnail)) continue;

      // Tách các extension bởi dấu phẩy và chuẩn hoá (chữ thường, loại bỏ khoảng trắng)
      $extensions = array_map('trim', explode(',', strtolower($extensions_str)));

      foreach ($extensions as $ext) {
        // Bỏ qua nếu rỗng hoặc không hợp lệ
        if (empty($ext) || !preg_match('/^[a-z0-9]+$/', $ext)) continue;

        // Kiểm tra xem đã có extension này chưa (tránh ghi đè)
        if (!isset($new_thumbnails[$ext])) {
          $new_thumbnails[$ext] = $thumbnail;
        }
      }
    }
  }

  conf_update_param('custom_thumbnails', serialize($new_thumbnails));
  load_conf_from_db(); // 🔥 Tải lại cấu hình ngay lập tức
  $page['infos'][] = 'Settings saved';
}

// Load dữ liệu để hiển thị (đảm bảo luôn dùng dữ liệu mới nhất)
$thumbnail_settings = unserialize($conf['custom_thumbnails']);
if ($thumbnail_settings === false) {
  $thumbnail_settings = array();
}

// Handle file upload
if (isset($_FILES['thumbnail_upload'])) {
  $upload_dir = CUSTOM_THUMBNAIL_PATH . CUSTOM_THUMBNAIL_UPLOAD;

  // Kiểm tra thư mục có tồn tại và có quyền ghi không
  if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
    die("Thư mục upload không tồn tại hoặc không có quyền ghi!");
  }

  $filename = uniqid() . '_' . $_FILES['thumbnail_upload']['name'];
  $destination = $upload_dir . $filename;

  if (move_uploaded_file($_FILES['thumbnail_upload']['tmp_name'], $destination)) {
    $page['infos'][] = 'Upload ảnh thành công!';
  } else {
    $page['errors'][] = 'Lỗi khi upload: ' . $_FILES['thumbnail_upload']['error'];
  }
}

// Get uploaded thumbnails
$uploaded_thumbnails = array();
if (file_exists(CUSTOM_THUMBNAIL_PATH . CUSTOM_THUMBNAIL_UPLOAD)) {
  foreach (scandir(CUSTOM_THUMBNAIL_PATH . CUSTOM_THUMBNAIL_UPLOAD) as $file) {
    if ($file != '.' && $file != '..') {
      $uploaded_thumbnails[] = CUSTOM_THUMBNAIL_UPLOAD . $file;
    }
  }
}

$template->set_filenames(array(
  'plugin_admin_content' => CUSTOM_THUMBNAIL_TEMPLATE . 'admin.tpl'
));

$template->assign(array(
  'thumbnail_settings' => $thumbnail_settings,
  'uploaded_thumbnails' => $uploaded_thumbnails,
  'CUSTOM_THUMBNAIL_PATH' => CUSTOM_THUMBNAIL_PATH,
  'UPLOAD_DIR' => CUSTOM_THUMBNAIL_UPLOAD
));

$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
