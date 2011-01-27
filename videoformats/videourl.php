<?php
require('JSON.php');
$json = new Services_JSON();

header('Content-Type: text/plain;charset=UTF-8');
$id = $_REQUEST['id'];
if ($id=='ardepg') {
  echo 'video/mp4#http://itv.ard.de/video/trailer.php';
} else if ($id=='mpegts') {
  echo 'video/mpeg#http://itv.ard.de/video/timecode.mpeg';
} else if ($id=='rtl') {
  echo 'video/mp4#http://bilder.rtl.de/tt_hd/trailer_hotelinspektor.mp4';
} else if ($id=='audiomp3') {
  echo 'audio/mpeg#http://swr.ic.llnwd.net/stream/swr_mp3_m_swr3a';
} else if ($id=='audiomp4') {
  echo 'audio/mp4#http://itv.ard.de/video/audio.php';
} else if ($id=='irthd') {
  echo 'video/mp4#http://itv.ard.de/video/irthd.mp4';
} else if ($id=='zdf') {
  $data = $json->decode(file_get_contents('http://itv.mit-xperts.com/zdfmediathek/dyn/list.php?type=11'));
  $id = (int)$data[1][0][0];
  $data = $json->decode(file_get_contents('http://itv.mit-xperts.com/zdfmediathek/dyn/detail.php?id='.$id));
  $url = $data[10];
  echo 'video/mp4#'.$url;
} else if ($id=='zdflonggop') {
  echo 'video/mp4#http://streamtv2.zdf.de/100908_idrtest_100_vh.mp4';
} else if ($id=='tsstream') {
  echo 'video/mpeg#http://itv.ard.de/video/livestream.php';
} else {
  echo 'Unknown ID';
}
?>
