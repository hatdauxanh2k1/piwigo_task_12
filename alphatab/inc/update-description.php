<?php
$root_url = preg_replace('#/plugins/alphatab/inc$#', '', dirname($_SERVER['SCRIPT_NAME']));
$path  = dirname(__DIR__, 3) . '/';
$normalizedPath = str_replace('\\', '/', $path);
define('PHPWG_ROOT_PATH', $normalizedPath);

// Nạp các file chứa các hàm cần thiết
include_once(PHPWG_ROOT_PATH . 'include/common.inc.php');
include_once(PHPWG_ROOT_PATH . 'include/functions.inc.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions_upload.inc.php');
header('Content-Type: application/json');

// Nhận dữ liệu POST JSON
$data = json_decode(file_get_contents('php://input'), true);

function check_token()
{

  // Lấy token từ $_REQUEST
  if (!empty($_REQUEST['pwg_token'])) {
    $token = $_REQUEST['pwg_token'];
  } else {

    if (isset($data['token'])) {
      $token = $data['token'];
    } else {
      http_response_code(400); // Bad Request
      echo json_encode(['error' => 'Thiếu token']);
      exit;
    }
  }

  // Kiểm tra token
  if (get_pwg_token() != $token) {
    access_denied();
  }
}

check_token();

// Kiểm tra dữ liệu hợp lệ
if (!isset($data['id']) || !isset($data['comment'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Thiếu id hoặc comment']);
  exit;
}
$file_path = __DIR__ . '/upload_log.json';
$logs = json_decode(file_get_contents($file_path), true) ?? [];


$found = false;
foreach ($logs as &$item) {
  if ($item['info']['id'] == $data['id']) {
    $found = true;
    break;
  }
}

// Nếu không tìm thấy ID
if (!$found) {
  http_response_code(404);
  echo json_encode(['error' => 'Không tìm thấy ID']);
  exit;
}

// Ghi lại vào file
$file_path_new = __DIR__ . '/final_uploaded_log.json';
file_put_contents($file_path_new, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));


if ($found) {
  // Cập nhật mô tả cho ảnh
  mass_updates(
    IMAGES_TABLE,
    array(
      'primary' => array('id'),
      'update' => array('name', 'author', 'comment'),
    ),
    array(
      array(
        'id' => $data['id'],
        'name' => $data['title'],
        'author' => $data['artist'],
        'comment' => $data['comment'],
      )
    )
  );
}

// Trả lại dữ liệu đã cập nhật
echo json_encode([
  'success' => true,
  'id' => $data['id'],
  'comment' => $data['comment'],
  'path' => $data['pathFile'],
]);
