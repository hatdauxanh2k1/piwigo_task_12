<?php
$root_url = preg_replace('#/plugins/alphatab/inc$#', '', dirname($_SERVER['SCRIPT_NAME']));
$path  = dirname(__DIR__, 3) . '/';
$normalizedPath = str_replace('\\', '/', $path);
define('PHPWG_ROOT_PATH', $normalizedPath);

include_once(PHPWG_ROOT_PATH . 'include/common.inc.php');
include_once(PHPWG_ROOT_PATH . 'admin/include/functions.php');
function is_current_user_admin()
{
  global $user;
  return isset($user['status']) && $user['status'] === 'webmaster';
}
if (!is_current_user_admin()) {
  http_response_code(403);
  echo json_encode(['error' => 'Bạn không có quyền truy cập']);
  exit;
} else {
  // echo json_encode(['Success' => 'Bạn có quyền truy cập']);
  // exit;
}
$file_path = PHPWG_PLUGINS_PATH . 'alphatab/admin/data/dataTab_write.json';
$image_ids = file_get_contents($file_path) ?? [];
$root_url = preg_replace('#/plugins/alphatab/inc$#', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html>

<head>
  <script src="https://cdn.jsdelivr.net/npm/@coderline/alphatab@latest/dist/alphaTab.js"></script>
</head>

<body>
  <h3>Lấy thông tin file nhạc</h3>
  <div id="log">
    <div class="log-item">
      <h4>Logs DONE: <span id="log-count-done">0</span></h4>
      <ol id="status"></ol>
    </div>
    <div class="log-item">
      <h4>Logs count: <span id="log-count-file">0</span></h4>
      <ol id="statusFile"></ol>
    </div>
  </div>
  <div id="wrapper"></div>
  </div>
  <style>
    #log {
      display: flex;
    }

    #log .log-item {
      flex: 1;
      padding: 0 22px;
    }

    #status,
    #statusFile {
      border: 1px solid #ddd;
      height: 500px;
      width: 100%;
      overflow: scroll;
    }

    #wrapper {
      height: 500px;
      width: 100%;
      overflow: hidden;
      position: relative;
    }

    #statusFile li,
    #status li {
      background: #f9f9f9;
      border: 1px solid #ddd;
      padding: 10px;
      margin-bottom: 8px;
      border-radius: 4px;
      font-family: monospace;
    }
  </style>
  <script>
    const imageIds = <?= $image_ids ?>; // Dữ liệu được truyền từ PHP sang JS
    function log(msg) {
      const li = document.createElement('li');
      li.innerHTML = msg;
      document.getElementById('status').appendChild(li);
      // Cập nhật số lượng log
      const countStatus = document.getElementById('status').children.length;
      document.getElementById('log-count-done').textContent = countStatus;
    }

    function logFile(msg) {
      const liItem = document.createElement('li');
      liItem.innerHTML = msg;
      document.getElementById('statusFile').appendChild(liItem);
      // Cập nhật số lượng log
      const countFile = document.getElementById('statusFile').children.length;
      document.getElementById('log-count-file').textContent = countFile;
    }
    const jsonArrayString = imageIds;
    const dataAPI = []; // Mảng để lưu trữ dữ liệu từ API
    const wrapper = document.getElementById('wrapper'); // Lấy phần tử wrapper từ DOM
    jsonArrayString.forEach(item => {
      let settings = {
        file: '<?= $root_url  ?>' + '/' + item.info.path,
        player: {
          enablePlayer: true,
        },
        importer: {
          beatTextAsLyrics: true
        }
      };
      // Khởi tạo AlphaTab API với cấu hình
      let api = new alphaTab.AlphaTabApi(wrapper, settings);
      dataAPI.push({
        id: item.info.id,
        path: item.info.path,
        api: api
      });
      api.scoreLoaded.on(score => {
        const itemId = item.info.id;
        const pathFile = item.info.path;
        const subTitle = score.subtitle || 'Không có phụ đề';
        const tempo = score.tempo || 'Không có tempo';
        const numberOfTracks = score.tracks.length || 'Không có track nào';
        const numberOfBar = score.masterBars.length || 'Không có bar nào';
        const artist = score.artist || 'Không rõ nghệ sĩ';
        const album = score.album || 'Không có album';
        const copyright = score.copyright || 'Không có bản quyền';
        const title = score.title || 'Không tiêu đề';
        const descripion = `<div id="song-details"><h1 class="title">Title: ${title}</h1><h2 class="subtitle">Subtitle: ${subTitle}</h2><h2 class="artist">Artist: ${artist}</h2><h3 class="album">Album: ${album}</h3><p class="tempo-tab">Tempo: ${tempo}</p><p class="number-of-track">Number of track: ${numberOfTracks}</p><p class="number-of-bar">Number of bar: ${numberOfBar}</p><h5 class="copyright">Copyright: ${copyright}</h5></div>`;

        logFile(`ID: ${item.info.id}<br>
        File: ${item.info.path}<br>
        Title: ${score.title}<br>
        Subtitle: ${score.subtitle}<br>
        Artist: ${score.artist}<br>
        Album: ${score.album}<br>
        Tempo: ${score.tempo}<br>
        Number of tracks: ${score.tracks.length}<br>
        Number of bar: ${score.masterBars.length}`);

        fetch('<?= $root_url  ?>' + '/plugins/alphatab/inc/update-description.php?pwg_token=<?= get_pwg_token() ?>', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              id: item.info.id,
              pathFile: pathFile,
              title: title,
              subTitle: subTitle,
              tempo: tempo,
              artist: artist,
              album: album,
              numberOfTracks: numberOfTracks,
              numberOfBar: numberOfBar,
              comment: descripion,
              token: '<?= get_pwg_token() ?>'
            })
          })
          .then(res => res.json())
          .then(msg => {
            if (msg.success) {
              log(`✅ ID ${msg.id}: Success : ${msg.success}  : ${msg.path}`);
            } else {
              log(`❌ Lỗi: ${msg.error}`);
            }
          });

      });

    });
    // console.log(dataAPI);
  </script>
</body>

</html>