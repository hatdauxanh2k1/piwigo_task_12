{footer_script}
    var error_icon = "{$ROOT_URL}{$themeconf.icon_dir}/errors_small.png", max_requests = {$maxRequests};
{/footer_script}
{* this might sound ridiculous, but we want to fit the thumbnails to 90% of col-xs-12 without them being too blurry *}
{assign var=width value=520}
{assign var=height value=360}
{define_derivative name='derivative_params' width=$width height=$height crop=true}
{define_derivative name='derivative_params_square' type=IMG_SQUARE}
<div class="row">
{foreach from=$category_thumbnails item=cat name=cat_loop}
{if $theme_config->category_wells == 'never' || ($theme_config->category_wells == 'mobile_only' && get_device() == 'desktop')}
{assign var=derivative value=$pwg->derivative($derivative_params, $cat.representative.src_image)}
{if !$derivative->is_cached()}
    {combine_script id='jquery.ajaxmanager' path='themes/default/js/plugins/jquery.ajaxmanager.js' load='footer'}
    {combine_script id='thumbnails.loader' path='themes/default/js/thumbnails.loader.js' require='jquery.ajaxmanager' load='footer'}
{/if}
{* this needs a fixed size else it messes up the grid on tablets *}
{include file="grid_classes.tpl" width=260 height=180}
  <div class="category-card col-outer {if isset($smarty.cookies.view) and $smarty.cookies.view == 'list'}col-12{else}{$col_class}{/if}" data-grid-classes="{$col_class}">
  <div class="card card-thumbnail {if isset($cat.path_ext)}path-ext-{$cat.path_ext}{/if} {if isset($cat.file_ext)}file-ext-{$cat.file_ext}{/if}">
      <div class="h-100">
      {* Plugin_Custom_Thumbnail *}
        <a href="{$cat.URL}" class="ripple{if isset($smarty.cookies.view) and $smarty.cookies.view != 'list'} d-block{/if}">
          {assign var="thumbnail_categories_customs" value=false}  
            {if !empty($CUSTOM_THUMBNAILS)}
              {foreach from=$CUSTOM_THUMBNAILS key=ext item=path}
                {if $current.file_ext == $ext}
                <img class="{if isset($smarty.cookies.view) and $smarty.cookies.view == 'list'}card-img-left{else}card-img-top{/if} thumb-img" src="{$path}" alt="{$cat.TN_ALT}" title="{$cat.NAME|@replace:'"':' '|@strip_tags:false} - {'display this album'|@translate}">
                  {assign var="thumbnail_categories_customs" value=true}
                  {break}
                {/if}
              {/foreach}
            {/if}
            
            {if !$thumbnail_categories_customs}
              <img class="{if isset($smarty.cookies.view) and $smarty.cookies.view == 'list'}card-img-left{else}card-img-top{/if} thumb-img" {if $derivative->is_cached()}src="{$derivative->get_url()}"{else}src="{$ROOT_URL}themes/bootstrap_darkroom/img/transparent.png" data-src="{$derivative->get_url()}"{/if} alt="{$cat.TN_ALT}" title="{$cat.NAME|@replace:'"':' '|@strip_tags:false} - {'display this album'|@translate}">
            {/if}
        </a>
        {* Plugin_Custom_Thumbnail *}
        <div class="card-body">
          <h5 class="card-title ellipsis {if !empty($cat.icon_ts)} recent{/if}">
          <a href="{$cat.URL}">{$cat.NAME}</a>
          </h5>
          <div class="card-text">
{if not empty($cat.DESCRIPTION)}
          <div class="description {if $theme_config->cat_descriptions} d-block{/if}">{$cat.DESCRIPTION}</div>
{/if}
{if isset($cat.INFO_DATES) }
              <div class="info-dates">{$cat.INFO_DATES}</div>
{/if}
          </div>
        </div>
{if $theme_config->cat_nb_images}
        <div class="card-footer text-muted"><div class="d-inline-block ellipsis">{str_replace('<br>', ', ', $cat.CAPTION_NB_IMAGES)}</div></div>
{/if}
      </div>
    </div>
  </div>
{else}
{assign var=derivative_square value=$pwg->derivative($derivative_params_square, $cat.representative.src_image)}
{if !$derivative_square->is_cached()}
    {combine_script id='jquery.ajaxmanager' path='themes/default/js/plugins/jquery.ajaxmanager.js' load='footer'}
    {combine_script id='thumbnails.loader' path='themes/default/js/thumbnails.loader.js' require='jquery.ajaxmanager' load='footer'}
{/if}
  <div class="col-outer col-12">
    <div class="card">
      <div class="card-body p-0">
        <a href="{$cat.URL}">
          <div class="media h-100">
            <img class="d-flex mr-3" {if $derivative_square->is_cached()}src="{$derivative_square->get_url()}"{else}src="{$ROOT_URL}themes/bootstrap_darkroom/img/transparent.png" data-src="{$derivative_square->get_url()}"{/if} alt="{$cat.TN_ALT}">
            <div class="media-body pt-2">
              <h4 class="mt-0 mb-1">{$cat.NAME}</h4>
{if not empty($cat.DESCRIPTION)}
              <div class="description">{$cat.DESCRIPTION}</div>
{/if}
{if isset($cat.INFO_DATES) }
              <div>{$cat.INFO_DATES}</div>
{/if}
{if $theme_config->cat_nb_images}
              <div class="text-muted">{str_replace('<br>', ', ', $cat.CAPTION_NB_IMAGES)}</div>
{/if}
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
{/if}
{/foreach}
</div>

<script>
function getMaxChars() {
    let width = window.innerWidth;
    
    if (width > 1400) return 250;  // Màn hình rất lớn
    if (width > 1300) return 200;  // Màn hình rất lớn
    if (width > 1200) return 160;  // Màn hình rất lớn
    if (width > 992) return 100;   // Laptop, màn hình lớn
    if (width > 768) return 70;   // Tablet ngang
    if (width > 576) return 120;    // Tablet dọc, điện thoại lớn
    return 50;                     // Điện thoại nhỏ
}

function truncateTextForAll() {
    let maxChars = getMaxChars(); // Lấy số ký tự tối đa theo kích thước màn hình
    let textElements = document.querySelectorAll(".card-text .description"); // Chỉ chọn .description trong .card-text

    textElements.forEach(textElement => {
        // Lưu nội dung gốc nếu chưa có
        if (!textElement.getAttribute("data-fulltext")) {
            textElement.setAttribute("data-fulltext", textElement.innerText.trim());
        }

        let originalText = textElement.getAttribute("data-fulltext");

        // Kiểm tra nếu nội dung dài hơn maxChars
        if (originalText.length > maxChars) {
            textElement.innerText = originalText.substring(0, maxChars) + "...";
        } else {
            textElement.innerText = originalText;
        }
    });
}

// Gọi hàm khi tải trang (xử lý ngay lần đầu theo kích thước màn hình)
window.addEventListener("load", truncateTextForAll);

// Gọi lại khi resize màn hình (cập nhật số ký tự nếu cần)
window.addEventListener("resize", truncateTextForAll);


  document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('btn-grid').classList.contains('active')) {
      var descriptions = document.querySelectorAll('.description');
      descriptions.forEach(function(description) {
        description.classList.remove('d-block');
        description.style.display = 'none';
      });
    }
  document.getElementById('btn-grid').addEventListener('click', function() {
    var descriptions = document.querySelectorAll('.description');
    descriptions.forEach(function(description) {
    description.classList.remove('d-block');
    var cardTitle = description.closest('.card-body').querySelector('.card-body .card-title');
    cardTitle.classList.remove('text-left');
    description.style.display = 'none';
    });
  });
    document.getElementById('btn-list').addEventListener('click', function() {
      var descriptions = document.querySelectorAll('.description');
      descriptions.forEach(function(description) {
        description.classList.add('d-block');
        var cardTitle = description.closest('.card-body').querySelector('card-body .card-title');
        cardTitle.classList.add('text-left');
        description.style.display = 'block';
      });
    });
  });
  if (document.getElementById('btn-list').classList.contains('active')) {
    document.querySelectorAll('.card-body').forEach(function(cardBody) {
      var description = cardBody.querySelector('.description');
      var cardTitle = cardBody.querySelector('card-body .card-title');
      cardTitle.classList.add('text-left');
      description.classList.add('d-block');
      description.style.display = 'block';
    });
  }
</script>

{html_style}
/* Ẩn category cards ban đầu */
.category-card {
    margin: 0;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}
/* Class được thêm vào sau khi JS hoàn thành */
.category-card.loaded {
    opacity: 1;
}

{/html_style}

{footer_script require='jquery'}
jQuery(document).ready(function() {
  jQuery('#btn-grid').click();
  var $colOuter = jQuery('.category-card');
  var $cardBody = jQuery('.card-body');

  // Thêm class loaded để hiển thị smooth
  setTimeout(function() {
    $colOuter.addClass('loaded');
  }, 100);

  // Kiểm tra cookie view có giá trị là list
  {if isset($smarty.cookies.view) and $smarty.cookies.view == 'list'}
    jQuery('#btn-grid').click();
    jQuery('#btn-list').click();
  {/if}

});
{/footer_script}
{debug}