<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
$upload_log_file = PHPWG_PLUGINS_PATH . 'alphatab/inc/upload_log.json';
$all_items = file_exists($upload_log_file) ? json_decode(file_get_contents($upload_log_file), true) : [];
$all_items = is_array($all_items) ? $all_items : [];
// Cấu hình phân trang
$items_per_page = 50; // Số item mỗi trang
$current_page = isset($_GET['page_num']) ? max(1, (int)$_GET['page_num']) : 1;

if (isset($_POST['submit_action']) && $_POST['submit_action'] === 'read_scores') {
  // Kiểm tra và xử lý dữ liệu đầu vào
  if (empty($_POST['selected_data'])) {
    http_response_code(400);
    die(json_encode([
      'success' => false,
      'message' => 'Không có dữ liệu được chọn!'
    ]));
  }

  // Xử lý JSON
  try {
    // Loại bỏ các ký tự escape không cần thiết
    $jsonString = stripslashes($_POST['selected_data']);

    // Decode JSON với chế độ bắt lỗi
    $selectedData = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);

    // Kiểm tra dữ liệu sau khi decode
    if (!is_array($selectedData)) {
      throw new Exception('Dữ liệu không phải là mảng hợp lệ');
    }

    // Chuẩn hóa dữ liệu
    $normalizedData = array_map(function ($item) {
      return [
        'time' => $item['time'] ?? date('Y-m-d H:i:s'),
        'info' => [
          'id' => $item['info']['id'] ?? '',
          'path' => $item['info']['path'] ?? '',
          'file_name' => $item['info']['file_name'] ?? null
        ]
      ];
    }, $selectedData);

    // Tạo tên file an toàn
    $filename = 'dataTab_write' . '.json';
    $filepath = __DIR__ . '/data/' . $filename;

    // Đảm bảo thư mục tồn tại
    if (!file_exists(__DIR__ . '/data')) {
      mkdir(__DIR__ . '/data', 0755, true);
    }

    // Lưu file với các tùy chọn format
    $jsonData = json_encode(
      $normalizedData,
      JSON_PRETTY_PRINT |
        JSON_UNESCAPED_SLASHES |
        JSON_UNESCAPED_UNICODE |
        JSON_NUMERIC_CHECK
    );

    if (file_put_contents($filepath, $jsonData) === false) {
      throw new Exception('Không thể ghi file');
    }

    // Ghi log thành công
    error_log("Đã lưu " . count($normalizedData) . " items vào $filename");

    // Trả về kết quả
    header('Content-Type: application/json');
    echo json_encode([
      'success' => true,
      'message' => 'Đã lưu thành công ' . count($normalizedData) . ' mục',
      'file' => $filename,
      'count' => count($normalizedData)
    ]);
  } catch (JsonException $e) {
    http_response_code(400);
    echo json_encode([
      'success' => false,
      'message' => 'Lỗi dữ liệu JSON: ' . $e->getMessage()
    ]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'success' => false,
      'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
  }

  redirect(get_absolute_root_url() . 'plugins/alphatab/inc/read-scores.php');
}




// Xử lý yêu cầu RESET
if (isset($_GET['reset']) && $_GET['reset'] == 1) {
  check_pwg_token();

  if (file_exists($upload_log_file)) {
    copy($upload_log_file, $upload_log_file . '.bak');
    file_put_contents($upload_log_file, json_encode([]));
    $page['infos'][] = l10n('All synchronization data has been reset');
  }

  redirect(get_admin_plugin_menu_link(__FILE__));
}

// Đọc và sắp xếp dữ liệu
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'desc';
if (!empty($all_items)) {
  usort($all_items, function ($a, $b) use ($sort_order) {
    return ($sort_order == 'asc')
      ? $a['info']['id'] - $b['info']['id']
      : $b['info']['id'] - $a['info']['id'];
  });
}
// Phân trang
$total_items = count($all_items);
$total_pages = ceil($total_items / $items_per_page);
$current_page = min($current_page, $total_pages);
$offset = ($current_page - 1) * $items_per_page;
$items = array_slice($all_items, $offset, $items_per_page);

// Gán biến template
$template->assign(array(
  'ITEMS' => $items,
  'READ_SCORES_URL' => get_absolute_root_url() . 'plugins/alphatab/inc/read-scores.php',
  'RESET_DATA_URL' => get_admin_plugin_menu_link(__FILE__) . '&section=alphatab%2Fadmin%2Fadmin.php&reset=1&pwg_token=' . get_pwg_token(),
  'FORM_ACTION_URL' => get_absolute_root_url() . 'plugins/alphatab/admin/admin.php',
  'SORT_ORDER' => $sort_order,
  'BASE_ADMIN_URL' => get_admin_plugin_menu_link(__FILE__),
  'PAGINATION' => array(
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'items_per_page' => $items_per_page,
    'total_items' => $total_items
  )
));

$template->set_filename('alphatab_admin', dirname(__FILE__) . '/template/admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'alphatab_admin');
