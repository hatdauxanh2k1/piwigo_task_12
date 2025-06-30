<link rel="stylesheet" href="{$CUSTOM_THUMBNAIL_PATH}admin/assets/css/admin.css">

<div class="titrePage">
    <h2>Custom Thumbnail Settings</h2>
</div>

{if isset($page.infos)}<div class="infos">{$page.infos}</div>{/if}

<div class="thumbnail-config">
     <!-- Upload Section -->
    <div class="upload-section">
        <p>Upload New Thumbnail</p>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="thumbnail_upload" accept="image/*" required>
            <button type="submit" name="upload_submit" class="button-icon">Upload</button>
        </form>
    </div>
    <!-- Main Configuration Form -->
    <form method="post" action="" id="thumbnail-form">
        <fieldset>
            <legend>Thumbnail Configuration</legend>
            <div class="actionButtons">
                <button type="button" id="add-entry" class="buttonLike">+ New Entry</button>
            </div>
            <table class="table2" id="thumbnail-table">
                <thead>
                    <tr>
                        <th>File Extension</th>
                        <th>Thumbnail Path/URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="thumbnail-entries">
                    {foreach from=$thumbnail_settings item=thumbnail key=extension}
                    <tr class="thumbnail-entry">
                        <td>
                            <input type="text" name="extensions[]" value="{$extension}"required>
                        </td>
                        <td>
                            <input type="text" name="thumbnails[]" value="{$thumbnail}" required>
                        </td>
                        <td>
                            <button type="button" class="button-icon delete-entry">Delete</button>
                        </td>
                    </tr>
                    {/foreach}
                    <tr class="thumbnail-entry">
                        <td>
                            <input type="text" name="extensions[]" value=""  placeholder="Ví dụ: jpg, png" >
                        </td>
                        <td>
                            <input type="text" name="thumbnails[]" value=""  placeholder="Điền link hoặc đường dẫn đến ảnh">
                        </td>
                        <td>
                            <button type="button" class="button-icon delete-entry">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div>
              <input type="submit" name="submit" value="Save Settings" class="btn-save-thumbnail-actions">
            </div>           
        </fieldset>
        
        
    </form>
    <!-- Thumbnail Selection -->
    <div class="thumbnail-selection-section">
    <h3>Select from Uploaded Thumbnails</h3>
    <div class="thumbnail-grid">
        {foreach from=$uploaded_thumbnails item=thumbnail}
        <div class="thumbnail-item" 
             data-fullpath="{$CUSTOM_THUMBNAIL_PATH}{$thumbnail}"
             data-filename="{$thumbnail}"
             onclick="handleThumbnailSelection(this, '{$CUSTOM_THUMBNAIL_PATH}{$thumbnail}')">
            <img src="{$CUSTOM_THUMBNAIL_PATH}{$thumbnail}" alt="Uploaded Thumbnail" loading="lazy">
            <div class="thumbnail-name">{$thumbnail|regex_replace:"/^.*\\//":""}</div>
            <button class="delete-thumbnail-btn" onclick="deleteThumbnail(event, '{$thumbnail}')">
                <i class="icon-trash"></i>
            </button>
        </div>
        {/foreach}
    </div>
</div>
</div>
{literal}
<script>
$(document).ready(function() {
    // Add new entry
    $('#add-entry').click(function() {
        var newRow = $('.thumbnail-entry').first().clone();
        newRow.find('input').val('');
        $('#thumbnail-entries').append(newRow);
    });
    
    // Delete entry
    $(document).on('click', '.delete-entry', function() {
        if ($('.thumbnail-entry').length > 1) {
            $(this).closest('.thumbnail-entry').remove();
        }
    });
    
    // Select thumbnail
    window.selectThumbnail = function(element, path) {
        $('.thumbnail-item').removeClass('selected');
        $(element).addClass('selected');
        
        let emptyInput = $('input[name="thumbnails[]"][value=""]').first();
        if (emptyInput.length === 0) {
            $('#add-entry').click();
            emptyInput = $('input[name="thumbnails[]"][value=""]').first();
        }
        emptyInput.val(path);
    };
    
    // Form submission handling
    $('#thumbnail-form').on('submit', function(e) {
        $('.thumbnail-entry').each(function() {
            if ($(this).find('input[name="extensions[]"]').val() === '' && 
                $(this).find('input[name="thumbnails[]"]').val() === '') {
                $(this).remove();
            }
        });
    });
});
</script>    
{/literal}
{literal}
<script>
// Track last selected thumbnail
let lastSelectedThumbnail = null;

function handleThumbnailSelection(element, fullPath) {
    // Toggle selection
    if (element.classList.contains('active')) {
        // Double click behavior: copy path
        copyThumbnailPath(fullPath, element);
        return;
    }

    // Update selection UI
    if (lastSelectedThumbnail) {
        lastSelectedThumbnail.classList.remove('active');
    }
    element.classList.add('active');
    lastSelectedThumbnail = element;

    // Auto-fill the path
    autoFillThumbnailPath(fullPath);
}

function copyThumbnailPath(path, parentElement) {
    // Modern Clipboard API
    navigator.clipboard.writeText(path).then(() => {
        showCopyFeedback(parentElement);
    }).catch(err => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = path;
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopyFeedback(parentElement);
        } catch (err) {
            console.error('Failed to copy:', err);
        }
        document.body.removeChild(textarea);
    });
}

function showCopyFeedback(element) {
    // Remove existing feedback if any
    const oldFeedback = element.querySelector('.copy-feedback');
    if (oldFeedback) oldFeedback.remove();

    // Create new feedback
    const feedback = document.createElement('div');
    feedback.className = 'copy-feedback';
    feedback.textContent = '✓ Copied!';
    element.appendChild(feedback);

    // Auto-remove after animation
    setTimeout(() => {
        feedback.remove();
    }, 2000);
}

function autoFillThumbnailPath(path) {
    const pathInputs = document.querySelectorAll('#thumbnail-table td:nth-child(2) input');
    
    // Find first empty input
    for (const input of pathInputs) {
        if (!input.value.trim()) {
            input.value = path;
            input.focus();
            return;
        }
    }
    
    // If no empty input, add new row
    addNewTableRow();
    document.querySelector('#thumbnail-table td:nth-child(2) input:last-of-type').value = path;
}

function addNewTableRow() {
    const tbody = document.querySelector('#thumbnail-table tbody');
    const newRow = document.createElement('tr');
    newRow.className = 'thumbnail-entry';
    newRow.innerHTML = `
        <td><input type="text" name="extensions[]" placeholder="Ví dụ: jpg, png"></td>
        <td><input type="text" name="thumbnails[]" placeholder="Điền link hoặc đường dẫn"></td>
        <td><button type="button" class="button-icon delete-entry">Delete</button></td>
    `;
    tbody.appendChild(newRow);
}
</script>
{/literal}
{literal}
<script>
async function deleteThumbnail(event, filename) {
    event.stopPropagation();
    
    if (!confirm('Bạn có chắc muốn xóa thumbnail này?')) {
        return;
    }

    const thumbnailItem = event.currentTarget.closest('.thumbnail-item');
    thumbnailItem.classList.add('deleting');
    try {
        const response = await fetch('./admin.php?page=plugin&section=piwigo_custom_thumbnail/inc/delete-thumbnail.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ filename: filename })
        });

       // Debug:
        const responseText = await response.text();
        // console.log("Response raw:", responseText);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${responseText}`);
        }

        const result = JSON.parse(responseText);
        
        if (!result.success) {
            throw new Error(result.error || 'Xóa thất bại');
        }

        thumbnailItem.remove();
        showToast('Đã xóa thành công!', 'success');

    } catch (error) {
       thumbnailItem.classList.remove('deleting');
        console.error('Full error:', error);
        showToast('Lỗi: ' + error.message, 'error');
    }
}
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = 'pwg-toast ' + type;
    toast.innerHTML = 
        '<span class="icon-' + type + '"></span>' +
        '<span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 3000);
}
</script>
{/literal}