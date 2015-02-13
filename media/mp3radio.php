<?php
$byterate = (int)(133000/8);
$vidfile = 'mp3radio.mp3';
$contenttype = 'audio/mpeg';

$size = @filesize($vidfile);
$mtime = @filemtime($vidfile);

header('Last-Modified: '.gmdate("D, d M Y H:i:s", $mtime)." GMT");
header('Content-Type: '.$contenttype);
header('icy-br: 128, 128');
header('ice-audio-info: ice-samplerate=44100;ice-bitrate=128;ice-channels=2');
header('icy-private: 1');
header('icy-pub: 0');
header('Cache-Control: no-cache');
header('Content-Length: '.$size);
header('Connection: close');

# start streaming
$fp = @fopen($vidfile,'rb');
$start = time()-4;
$bcount = 0;
while (!feof($fp) && $bcount<$size && !connection_aborted()) {
  $b = @fread($fp, min(8192, $size-$bcount));
  $check = $start+((int)($bcount/$byterate))-time();
  if ($check>0) {
    flush();
    sleep($check);
  }
  echo $b;
  $bcount += strlen($b);
}
fclose($fp);

?>
