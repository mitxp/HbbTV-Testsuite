<?php
$byterate = 900000/8;
$vidfile = 'timecode.mpeg';

$fp = @fopen($vidfile, 'rb');
if(!$fp) {
  header('HTTP/1.0 404 Not Found');
  echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested video was not found on this server.</p>\n</body></html>";
  exit;
}

header('Last-Modified: '.gmdate("D, d M Y H:i:s", time())." GMT");
header('Accept-Ranges: none');
header('Content-Type: video/mpeg');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $first = true;
  while (true) {
    $seekpos = 0;
    if ($first) {
      $first = false;
      $seekpos = (int)((microtime(true)*100)%1000);
      $seekpos *= 7*188;
    }
    fseek($fp, $seekpos, SEEK_SET);
    $start = time();
    $bcount = 0;
    while (!feof($fp) && !connection_aborted()) {
      $b = @fread($fp, 7520);
      $check = (int)(($start+($bcount/$byterate)-microtime(true))*10)-1;
      if ($check>0) {
        usleep($check*100);
      }
      echo $b;
      $bcount += strlen($b);
    }
  }
}

fclose($fp);

?>
