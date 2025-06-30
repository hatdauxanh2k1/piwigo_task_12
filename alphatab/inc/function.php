<?php
function get_image_url_by_id($image_id)
{
  $query = '
SELECT filepath
FROM ' . IMAGES_TABLE . '
WHERE id = ' . (int)$image_id . '
LIMIT 1
';
  $result = pwg_db_fetch_assoc(pwg_query($query));
  if (!$result) return false;

  return get_absolute_root_url() . $result['filepath'];
}
