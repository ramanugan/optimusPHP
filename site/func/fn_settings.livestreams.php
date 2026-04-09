<?
session_start();
include('../init.php');
include('fn_common.php');
checkUserSession();

loadLanguage($_SESSION["language"], $_SESSION["units"]);

// check privileges
if ($_SESSION["privileges"] == 'subuser') {
  $user_id = $_SESSION["manager_id"];
} else {
  $user_id = $_SESSION["user_id"];
}


if (@$_POST['cmd'] == 'load_data_livestreams') {
  $imei = $_POST['imei'];
  $q = "SELECT url_stream, total_time_allowed_seconds, total_time_remaining_seconds FROM gs_object_streams WHERE imei ='$imei'";
  $r = mysqli_query($ms, $q);

  $response = new stdClass();
  $response->url_stream = '';
  $response->total_time_allowed_seconds = 0;
  $response->total_time_remaining_seconds = 0;

  if ($r) {
    while ($row = mysqli_fetch_array($r)) {
      $url = $row['url_stream'];

      $re = '/(optimumondemand.es).*(\/(\d{1,2}))/m';
      preg_match_all($re, $url, $matches, PREG_SET_ORDER, 0);
      $response->url_stream = 'https://' . $matches[0][1] . '/live?stream=' . $matches[0][3];



      $response->total_time_allowed_seconds = $row['total_time_allowed_seconds'];
      $response->total_time_remaining_seconds = $row['total_time_remaining_seconds'];
    }
  }

  header('Content-type: application/json');
  echo json_encode($response);
  die;
}

if (@$_POST['cmd'] == 'play_time') {
  $imei = $_POST['imei'];
  $playtime = $_POST['playTime'];
  $playTime = isset($_POST['playTime']) ? intval($_POST['playTime']) : 0;

  if ($playTime > 0) {
    $q = "SELECT `total_time_remaining_seconds` FROM gs_object_streams WHERE `imei`='" . $imei . "'";
    $result = mysqli_query($ms, $q);

    if ($result && mysqli_num_rows($result) > 0) {
      $row = mysqli_fetch_assoc($result);
      $currentPlayTime = intval($row['total_time_remaining_seconds']);
      $newPlayTime = $currentPlayTime - $playTime;
      $response = ['newPlayTime' => $newPlayTime];

      $q = "UPDATE gs_object_streams SET `total_time_remaining_seconds`='" . $newPlayTime . "' WHERE `imei`='" . $imei . "'";
      $r = mysqli_query($ms, $q);
    }
  }
  header('Content-type: application/json');
  echo json_encode($response);
  die;
}

if (@$_POST['cmd'] == 'get_max_play_time') {
  $imei = $_POST['imei'];

  $q = "SELECT `total_time_remaining_seconds` FROM gs_object_streams WHERE `imei`='" . $imei . "'";
  $r = mysqli_query($ms, $q);
  $row = mysqli_fetch_array($r);

  $maxPlayTime = $row['total_time_remaining_seconds'];
  $response = ['maxPlayTime' => $maxPlayTime];


  header('Content-type: application/json');
  echo json_encode($response);
  die;
}

if (@$_POST['cmd'] == 'show_ondemand_events') {
  $imei = $_POST['imei'];

  $connftp = setupConnectionFTP();
  if ($connftp == "FTP connection failed.") {
    return null;
  }

  ftp_pasv($connftp, true);
  //traverse the directory recursively and get the files
  $files = array();
  traverseDirectory('queclink/cv200/' . $imei . '/video', $files, $connftp);
  $dataRows = array();

  foreach ($files as $file) {
    $re = '/(\d{8})_(\d{6})_(..)_(\d)\.mp4$/m';
    preg_match_all($re, basename($file), $matches, PREG_SET_ORDER, 0);

    $type_event = $matches[0][3];
    $type_camera_legend = $matches[0][4] == '1' ? 'Exterior' : 'Interior';

    switch ($type_event) {
      case '01':
        $type_event_legend = 'Ignición ON';
        break;
      case '02':
        $type_event_legend = 'Ignición OFF';
        break;
      case '03':
        $type_event_legend = 'Desconexión de Corriente';
        break;
      case '04':
        $type_event_legend = 'Detección de Choque';
        break;
      case '05':
        $type_event_legend = 'Aceleracion Brusca';
        break;
      case '06':
        $type_event_legend = 'Frenado Brusco';
        break;
      case '07':
        $type_event_legend = 'Giro Brusca';
        break;
      case '08':
        $type_event_legend = 'Exceso de Velocidad';
        break;
      case '09':
        $type_event_legend = 'Boton de Panico';
        break;
      case 'D1':
        $type_event_legend = 'Ojos Cerrados';
        break;
      case 'D2':
        $type_event_legend = 'Bostezo';
        break;
      case 'D3':
        $type_event_legend = 'Distracción';
        break;
      case 'D4':
        $type_event_legend = 'Fumando';
        break;
      case 'D5':
        $type_event_legend = 'Uso de Telefono';
        break;
      case 'D6':
        $type_event_legend = 'Conducción Peligrosa';
        break;
      case 'D7':
        $type_event_legend = 'Bloqueo de Camara/Ojos';
        break;
      case 'D8':
        $type_event_legend = 'Cinturón Desabrochado';
        break;
      default:
        $type_event_legend = 'Evento_Desconocido';
        break;
    }

    $pattern = '/(\d{4})(\d{2})(\d{2})/i';
    $replacement = '$1-$2-$3';
    $date = preg_replace($pattern, $replacement, $matches[0][1]);
    $pattern = '/(\d{2})(\d{2})(\d{2})/i';
    $replacement = '$1:$2:$3';
    $time = preg_replace($pattern, $replacement, $matches[0][2]);

    $url = "<a href='/func/fn_settings.livestreams.php?cmd=download_ondemand_event&nameVideo=" . $file . "'>Descargar</a>";

    $datetime = $date . ' ' . $time;

    $dateTimeObject = new DateTime($datetime);

    $dateTimeObject->modify('-6 hours');

    $newDate = $dateTimeObject->format('Y-m-d');
    $newTime = $dateTimeObject->format('H:i:s');

    $data = array(
      'date' => $newDate,
      'time' => $newTime,
      'name_file' => $type_event_legend . ' - ' . $type_camera_legend,
      'url' => $url,
    );
    //if 'type_event' start with 'D' then it is a camera event
    if (strpos($type_event, 'D') === 0) {
      $dataRows['DMS'][] = $data;
    } else {
      $dataRows['GPS'][] = $data;
    }
  }
  ftp_quit($connftp);
  header('Content-type: application/json');
  echo json_encode($dataRows);
  die;
}

if (@$_GET['cmd'] == 'download_ondemand_event') {
  $nameVideo = $_GET['nameVideo'];
  $connftp = setupConnectionFTP();
  //$fp = fopen('php://output', 'w+');
  //stream_set_write_buffer($fp, 0);
  $videoEvento = $_SERVER['DOCUMENT_ROOT'] . '/data/user/objftp/' . basename($nameVideo);
  $fp = fopen($videoEvento, "w");
  $file = ftp_fget($connftp, $fp, $nameVideo, FTP_BINARY, 0);

  ftp_quit($connftp);
  fclose($fp);
  // send reponse to client, so it can start download
  /*
  $path = $_SERVER['DOCUMENT_ROOT'] . '/data/user/objftp/' . $nameVideo;
  $fp = @fopen($path, 'rb');
  $size = filesize($path); // File size
  $length = $size;           // Content length
  $start = 0;               // Start byte
  $end = $size - 1;       // End byte
  header('Content-type: video/mp4');
  header("Accept-Ranges: bytes");
  if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end = $end;
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $start-$end/$size");
      exit;
    }
    if ($range == '-') {
      $c_start = $size - substr($range, 1);
    } else {
      $range = explode('-', $range);
      $c_start = $range[0];
      $c_end = (isset($range[1]) && is_numeric($range[1])) ?
        $range[1] : $size;
    }
    $c_end = ($c_end > $end) ? $end : $c_end;
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
      header('HTTP/1.1 416 Requested Range Not Satisfiable');
      header("Content-Range: bytes $start-$end/$size");
      exit;
    }
    $start = $c_start;
    $end = $c_end;
    $length = $end - $start + 1;
    fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
  }
  header("Content-Range: bytes $start-$end/$size");
  header("Content-Length: " . $length);
  $buffer = 1024 * 8;
  while (!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $buffer > $end) {
      $buffer = $end - $p + 1;
    }
    set_time_limit(0);
    echo fread($fp, $buffer);
    ob_flush();
    flush();
  }
  fclose($fp);
  exit();
  */


  //header('Content-Description: File Transfer');
  //header('Accept-Ranges: bytes');
  //header('Set-Cookie: fileDownload=true; path=/');
  //header('Content-type: application/octet-stream');
  //header('Content-Disposition: attachment; filename="' . basename($videoEvento) . '"');
  //header('Content-Transfer-Encoding: binary');
  //header('Expires: 0');
  //header('Cache-Control: must-revalidate');
  //header('Pragma: public');
  //header('Content-Length: ' . filesize($videoEvento));

  header('Content-Type: application/octet-stream');
  header("Content-Disposition: attachment; filename=" . basename($videoEvento));
  header('Content-Transfer-Encoding: Binary');
  header("Content-Length: " . filesize($videoEvento));
  header('Connection: Keep-Alive');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');

  ob_clean();
  flush();
  $fileDownload = readfile($videoEvento);
  unlink($videoEvento);
  die;
}


function setupConnectionFTP()
{
  $ftp_server = '104.236.8.90';
  $ftp_user_name = 'ftpuser';
  $ftp_user_pass = 'Eiphu+uwi=d2ahru';
  $ftp_port = 21;
  $conn_id = ftp_connect($ftp_server, $ftp_port);
  if (!$conn_id) {
    return ("FTP connection failed.");
  }
  $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
  ftp_set_option($conn_id, FTP_USEPASVADDRESS, false);
  $mode = ftp_pasv($conn_id, true);
  if ((!$conn_id) || (!$login_result) || (!$mode)) {
    return ("FTP connection failed.");
  }
  return $conn_id;
}

function traverseDirectory($directory, &$files, $connftp)
{
  $list = ftp_rawlist($connftp, $directory, true);
  foreach ($list as $item) {
    $parts = preg_split("/\s+/", $item);
    $name = end($parts);
    if ($name === '.' || $name === '..') {
      continue;
    }
    $path = $directory . '/' . $name;
    if ($parts[0][0] === 'd') {
      traverseDirectory($path, $files, $connftp);
    } else {
      $files[] = $path;
    }
  }
}
