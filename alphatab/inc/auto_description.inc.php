<?php
add_event_handler('loc_end_add_uploaded_file', 'my_after_upload_function');

function my_after_upload_function($info)
{
  if (!isset($info['id']) || !isset($info['path'])) return;
  $log_path = __DIR__ . '/upload_log.json';

  // Xử lý đường dẫn: bỏ dấu `./` nếu có
  $info['path'] = ltrim($info['path'], './');

  // Lấy tên ảnh từ database
  $file_name = '';
  $query = 'SELECT file FROM ' . IMAGES_TABLE . ' WHERE id = ' . $info['id'];
  $result = pwg_query($query);

  if ($row = pwg_db_fetch_assoc($result)) {
    $file_name = $row['file'];
  }
  // Thêm tên ảnh vào thông tin log
  $info['file_name'] = $file_name;
  // Ghi log
  $log_entry = [
    'time' => date("Y-m-d H:i:s"),
    'info' => $info
  ];

  // Đọc log hiện tại
  $log_data = [];
  if (file_exists($log_path)) {
    $content = file_get_contents($log_path);
    $log_data = json_decode($content, true);
    if (!is_array($log_data)) {
      $log_data = [];
    }
  }

  // Thêm log mới
  $log_data[] = $log_entry;

  // Ghi lại toàn bộ log
  file_put_contents(
    $log_path,
    json_encode($log_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
  );

  // Lấy đường dẫn file
  $file_path = $info['path'];

  // Lấy phần mở rộng của file (extension)
  $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

  // Danh sách các định dạng file hợp lệ
  $valid_extensions = array('gp3', 'gp4', 'gp5', 'mxl', 'xml', 'gp', 'gpx');

  // Kiểm tra xem phần mở rộng có hợp lệ không
  if (in_array(strtolower($file_extension), $valid_extensions)) {
    // Nếu hợp lệ, thêm mô tả mặc định
  }
}

if (isset($_GET['page']) && $_GET['page'] === 'photos_add') {
  $log_path = __DIR__ . '/upload_log.json';

  // Kiểm tra nếu file tồn tại thì xóa nội dung
  if (file_exists($log_path)) {
    file_put_contents($log_path, ''); // Ghi rỗng để xóa nội dung
  }
}
