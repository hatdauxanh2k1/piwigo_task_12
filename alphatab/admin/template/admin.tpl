<div class="alphatab-settings-title">
  <h2>AlphaTab Settings</h2>
</div>

<h3 class="alphatab-section-title">Data File Contents (upload_log.json)</h3>
<div class="alphatab-actions">
  <button type="button" class="btn-active-sucess" id="read-scores-btn" disabled>
    {l10n('Go to Read Data')}
    <span class="alphatab-selected-count-badge">0</span>
  </button>
  <a href="{$RESET_DATA_URL}" class="btn-active-danger" 
     onclick="return confirm('{l10n('Are you sure you want to reset ALL synchronized data?')}');">
    {l10n('Reset Data')}
  </a>
</div>
{if isset($ITEMS) && !empty($ITEMS)}
  <p class="alphatab-total-count"><strong>Total entries:</strong> {$PAGINATION.total_items}</p>
<form id="items-form" method="post" action="{$BASE_ADMIN_URL}">
  <input type="hidden" name="submit_action" id="submit-action-input" value="">
  <a href="{$BASE_ADMIN_URL}&sort=asc" class="buttonLike {if $SORT_ORDER == 'asc'}active{/if}">Sort ASC</a>
  <a href="{$BASE_ADMIN_URL}&sort=desc" class="buttonLike {if $SORT_ORDER == 'desc'}active{/if}">Sort DESC</a>
  <div class="alphatab-item-list-container">
    <div class="table-wrapper">
      <table class="alphatab-item-table">
        <thead>
          <tr>
            <th width="30px"><input type="checkbox" id="select-all"></th>
            <th>ID {if $SORT_ORDER == 'desc'}&uarr;{else}&darr;{/if}</th>
            <th>File Name</th>
            <th>Time</th>
            <th>Path</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$ITEMS item=item name=myLoop}
          <tr class="alphatab-item">
            <td>
            <span class="item-number" style="font-size: 12px">{$smarty.foreach.myLoop.iteration}.</span>
            <input type="checkbox" name="selected_items[]" value="{$item.info.id}">
            </td>
            <td>{$item.info.id}</td>
            <td>
              {$item.info.file_name}
            </td>
            <td>{$item.time}</td>
            <td>{$item.info.path}</td>
          </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  </div>
  <div class="alphatab-pagination">
  {if $PAGINATION.total_pages > 1}
    <div class="pagination-info">
      Showing {$PAGINATION.items_per_page * ($PAGINATION.current_page - 1) + 1} - 
      {min($PAGINATION.items_per_page * $PAGINATION.current_page, $PAGINATION.total_items)} 
      of {$PAGINATION.total_items} entries
    </div>
    <div class="alphatab-pagination-controls">
      <div class="pagination-links">
        {if $PAGINATION.current_page > 1}
          <a href="{$BASE_ADMIN_URL}&sort={$SORT_ORDER}&page_num=1" class="page-link first-page">« First</a>
          <a href="{$BASE_ADMIN_URL}&sort={$SORT_ORDER}&page_num={$PAGINATION.current_page - 1}" class="page-link prev-page">‹ Prev</a>
        {/if}
        
        {for $page=1 to $PAGINATION.total_pages}
          {if $page == $PAGINATION.current_page}
            <span class="page-link current-page">{$page}</span>
          {else}
            <a href="{$BASE_ADMIN_URL}&sort={$SORT_ORDER}&page_num={$page}" class="page-link">{$page}</a>
          {/if}
        {/for}
        
        {if $PAGINATION.current_page < $PAGINATION.total_pages}
          <a href="{$BASE_ADMIN_URL}&sort={$SORT_ORDER}&page_num={$PAGINATION.current_page + 1}" class="page-link next-page">Next ›</a>
          <a href="{$BASE_ADMIN_URL}&sort={$SORT_ORDER}&page_num={$PAGINATION.total_pages}" class="page-link last-page">Last »</a>
        {/if}
      </div>
    </div>
  {/if}
</div>
</form>
{else}
  <p class="alphatab-empty"><i>Data Upload ảnh trống. Vui lòng upload ảnh !</i></p>
{/if}

{literal}
<script>
$(document).ready(function() {
  // Select all checkbox
  $('#select-all').change(function() {
    $('input[name="selected_items[]"]').prop('checked', $(this).prop('checked'));
  });

  // Sort buttons
  $('#sort-asc').click(function() {
    window.location.href = window.location.pathname + '?sort=asc';
  });
  
  $('#sort-desc').click(function() {
    window.location.href = window.location.pathname + '?sort=desc';
  });
  // Thêm shadow khi cuộn
  $('.alphatab-item-list-container').on('scroll', function() {
    if ($(this).scrollTop() > 0) {
      $(this).find('thead').css('box-shadow', '0 2px 5px rgba(0,0,0,0.1)');
    } else {
      $(this).find('thead').css('box-shadow', 'none');
    }
  });
  
  // Xử lý select all
  $('#select-all').change(function() {
    $('input[name="selected_items[]"]').prop('checked', $(this).prop('checked'));
  });

  // Select all checkbox (chỉ áp dụng cho trang hiện tại)
  $('#select-all').change(function() {
    $('input[name="selected_items[]"]').prop('checked', $(this).prop('checked'));
  });

  // Thêm shadow khi cuộn
  $('.alphatab-item-list-container').on('scroll', function() {
    if ($(this).scrollTop() > 0) {
      $(this).find('thead').css('box-shadow', '0 2px 5px rgba(0,0,0,0.1)');
    } else {
      $(this).find('thead').css('box-shadow', 'none');
    }
  });

  // Xử lý select all
  $('#select-all').change(function() {
    var isChecked = $(this).prop('checked');
    $('input[name="selected_items[]"]').prop('checked', isChecked);
    updateReadScoresButton();
  });

  // Xử lý khi các checkbox item thay đổi
  $('input[name="selected_items[]"]').change(function() {
    updateSelectAllState();
    updateReadScoresButton();
  });

  // Cập nhật trạng thái nút Read Scores
  function updateReadScoresButton() {
    var anyChecked = $('input[name="selected_items[]"]:checked').length > 0;
    $('#read-scores-btn').prop('disabled', !anyChecked);
  }

  // Cập nhật trạng thái checkbox "Select All"
  function updateSelectAllState() {
    var allChecked = $('input[name="selected_items[]"]').length === $('input[name="selected_items[]"]:checked').length;
    $('#select-all').prop('checked', allChecked);
  }

  // Khởi tạo trạng thái ban đầu
  updateReadScoresButton();
  updateSelectAllState();

  // Thêm shadow khi cuộn
  $('.alphatab-item-list-container').on('scroll', function() {
    if ($(this).scrollTop() > 0) {
      $(this).find('thead').css('box-shadow', '0 2px 5px rgba(0,0,0,0.1)');
    } else {
      $(this).find('thead').css('box-shadow', 'none');
    }
  });

  // Hàm cập nhật số lượng item được chọn
  function updateSelectedCount() {
    var count = $('input[name="selected_items[]"]:checked').length;
    $('.alphatab-selected-count-badge').text(count);
    $('#read-scores-btn').prop('disabled', count === 0);
  }
  // Xử lý select all
    $('#select-all').change(function() {
      $('input[name="selected_items[]"]').prop('checked', $(this).prop('checked'));
      updateSelectedCount();
    });

    // Xử lý khi các checkbox item thay đổi
    $('input[name="selected_items[]"]').change(function() {
      updateSelectAllState();
      updateSelectedCount();
    });

    // Các hàm khác giữ nguyên...
    updateSelectedCount(); // Khởi tạo lần đầu
 
});
</script>
{/literal}
{literal}
<script>
$(document).ready(function() {
  // Tối ưu: Cache các selector thường dùng
  const $itemsForm = $('#items-form');
  const $checkboxes = $('input[name="selected_items[]"]');
  
  // Hàm thu thập dữ liệu từ các item được chọn
  function collectSelectedData() {
    return $checkboxes.filter(':checked').map(function() {
      const $row = $(this).closest('tr');
      const $cells = $row.find('td');
      
      
      return {
        time:  $cells.eq(3).text().trim(),
        info: {
          id: $cells.eq(1).text().trim(),
          path: $cells.eq(4).text().trim(),
          file_name: $cells.eq(2).text().trim()
        }
      };
    }).get(); // .get() để chuyển jQuery object thành array
  }

  // Xử lý khi click nút Read Scores
  $('#read-scores-btn').on('click', function() {
    const checkedCount = $checkboxes.filter(':checked').length;
    
    if (checkedCount === 0) {
      alert('Vui lòng chọn ít nhất một item');
      return false;
    }
    
    try {
      const selectedData = collectSelectedData();
      const jsonData = JSON.stringify(selectedData);
      
      // Kiểm tra dữ liệu trước khi gửi
      if (!jsonData || jsonData === '[]') {
        throw new Error('Dữ liệu không hợp lệ');
      }
      
      // Đảm bảo input ẩn tồn tại
      let $dataInput = $('#selected-data-input');
      if (!$dataInput.length) {
        $dataInput = $('<input/>', {
          type: 'hidden',
          name: 'selected_data',
          id: 'selected-data-input'
        }).appendTo($itemsForm);
      }
      
      $dataInput.val(jsonData);
      $('<input/>', {type: 'hidden', name: 'submit_action', value: 'read_scores'}).appendTo($itemsForm);
      
      // Gửi form
      $itemsForm.trigger('submit');
      
    } catch (error) {
      console.error('Lỗi khi xử lý dữ liệu:', error);
      alert('Có lỗi xảy ra: ' + error.message);
    }
  });
});
</script>
{/literal}