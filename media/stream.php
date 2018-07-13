<?php
$size = @filesize($vidfile);
$mtime = @filemtime($vidfile);

$fp = @fopen($vidfile,'rb');
if(!$fp || !$size || !$byterate || !$contenttype) {
  header('HTTP/1.0 404 Not Found');
  echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested video was not found on this server.</p>\n</body></html>";
  exit;
}


$range = array();
$range[0] = 0;
$range[1] = $size-1;
if (isset($_SERVER['HTTP_RANGE'])) {
  list($sizeunit, $rangeinfo) = explode('=', $_SERVER['HTTP_RANGE'], 2);
  if (strtolower($sizeunit)=='bytes') {
    list($rangetxt) = explode(',', $rangeinfo, 2);
    list($from,$to) = explode('-', $rangetxt, 2);
    $from = max(0, (int)trim($from));
    $to = min($size-1, (int)trim($to));
    if (!$to) $to = $size-1;
    if ($from<$to) {
      $range[0] = $from;
      $range[1] = $to;
    }
  }
}
if ($range[0]!=0 || $range[1]!=$size-1) {
  header('HTTP/1.1 206 Partial Content');
  if ($range[0]) fseek($fp, $range[0]);
}
$clength = ($range[1]-$range[0]+1);
header('Last-Modified: '.gmdate("D, d M Y H:i:s", $mtime)." GMT");
header('Accept-Ranges: bytes');
header('Content-Length: '.$clength);
header('Content-Range: bytes '.$range[0].'-'.$range[1].'/'.$size);
header('Content-Type: '.$contenttype);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  # start streaming
  $start = time()-4;
  $bcount = 0;
  while (!feof($fp) && $bcount<$clength && !connection_aborted()) {
    $b = @fread($fp, min(8192, $clength-$bcount));
    $check = $start+((int)($bcount/$byterate))-time();
    if ($check>0) {
      flush();
      sleep($check);
    }
    echo $b;
    $bcount += strlen($b);
  }
}
fclose($fp);

?>
