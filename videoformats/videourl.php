<?php

header('Content-Type: text/plain;charset=UTF-8');
$id = $_REQUEST['id'];
if ($id=='ardepg') {
  echo 'video/mp4#http://itv.mit-xperts.com/hbbtvtest/media/trailer.php';
} else if ($id=='https') {
  echo 'video/mp4#https://itv.mit-xperts.com/video/dasgrossehansi.mp4';
} else if ($id=='mpegts') {
  echo 'video/mpeg#http://itv.mit-xperts.com/hbbtvtest/media/timecode.mpeg';
} else if ($id=='rtl') {
  echo 'video/mp4#http://bilder.rtl.de/tt_hd/trailer_hotelinspektor.mp4';
} else if ($id=='audiomp3') {
  $m3u = @file_get_contents('http://streams.br.de/br-klassik_2.m3u');
  $m3u = strtok(str_replace("\r", "\n", $m3u), "\n");
  while ($m3u && substr($m3u, 0, 4)!='http') {
    $m3u = strtok("\n");
  }
  echo 'audio/mpeg#'.$m3u;
} else if ($id=='audiomp4') {
  echo 'audio/mp4#http://itv.mit-xperts.com/hbbtvtest/media/audio.php';
} else if ($id=='irthd') {
  echo 'video/mp4#http://itv.mit-xperts.com/hbbtvtest/media/irthd.mp4';
} else if ($id=='tsstream') {
  echo 'video/mpeg#http://itv.mit-xperts.com/hbbtvtest/media/livestream.php';
} else if ($id=='daserste1') {
  echo 'application/dash+xml#http://itv.ard.de/ardstart/dyn/stream.php/offset-0/http/dasersteamddash.akamaized.net/dash/live/2033393/daserste/dvbt2/manifest.mpd';
} else if ($id=='daserste2') {
  echo 'application/dash+xml#http://itv.ard.de/ardstart/dyn/stream.php/offset-60/http/dasersteamddash.akamaized.net/dash/live/2033393/daserste/dvbt2/manifest.mpd';
} else {
  echo 'Unknown ID';
}
?>
