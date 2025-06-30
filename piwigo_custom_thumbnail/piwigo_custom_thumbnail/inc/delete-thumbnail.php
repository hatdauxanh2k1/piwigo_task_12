<?php
// ĐẶT ĐẦU FILE
header('Content-Type: application/json');

// Tắt hiển thị lỗi (chỉ log)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/delete_thumbnail_errors.log');

// Kiểm tra Piwigo environment
define('PHPWG_ROOT_PATH', realpath(dirname(__FILE__) . '/../../..'));
include_once(PHPWG_ROOT_PATH . '/include/common.inc.php');

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  die(json_encode(['success' => false, 'error' => 'Method Not Allowed']));
}

// Chỉ admin được phép
if (!is_admin()) {
  http_response_code(403);
  die(json_encode(['success' => false, 'error' => 'Forbidden']));
}

try {
  $input = json_decode(file_get_contents('php://input'), true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('Invalid JSON input');
  }

  $filename = basename($input['filename'] ?? '');
  $filepath = PHPWG_ROOT_PATH . '/plugins/piwigo_custom_thumbnail/data/upload/custom_thumbnails/' . $filename;

  if (!file_exists($filepath)) {
    throw new Exception('File not found');
  }

  if (!unlink($filepath)) {
    throw new Exception('Delete failed');
  }

  die(json_encode(['success' => true]));
} catch (Exception $e) {
  error_log('[' . date('Y-m-d H:i:s') . '] Error: ' . $e->getMessage());
  http_response_code(400);
  die(json_encode(['success' => false, 'error' => $e->getMessage()]));
}
