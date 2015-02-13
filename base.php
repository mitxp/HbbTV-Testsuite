<?php

function sendContentType() {
  header('Pragma: no-cache');
  header('Cache-Control: no-cache');
  header('Content-Style-Type: text/css');
  $uagent = strtolower($_SERVER['HTTP_USER_AGENT']);
  if (strstr($uagent, 'firefox') || strstr($uagent, 'chrome')) {
    header('Content-Type: application/xhtml+xml; charset=UTF-8');
  } else {
    header('Content-Type: application/vnd.hbbtv.xhtml+xml; charset=UTF-8');
  }
}

function videoObject($left=0, $top=0, $width=1280, $height=720) {
  if ($_REQUEST['demo']) {
    global $ROOTDIR;
    return '<img id="video" style="position: absolute; left: '.$left.'px; top: '.$top.'px; width: '.$width.'px; height: '.$height.'px;" src="'.$ROOTDIR.'/video.jpg" />';
  }
  return '<object id="video" type="video/broadcast" style="position: absolute; left: '.$left.'px; top: '.$top.'px; width: '.$width.'px; height: '.$height.'px;"></object>';
}
function appmgrObject() {
  if ($_REQUEST['demo']) return '';
  return '<object id="appmgr" type="application/oipfApplicationManager" style="position: absolute; left: 0px; top: 0px; width: 0px; height: 0px;"></object><object id="oipfcfg" type="application/oipfConfiguration" style="position: absolute; left: 0px; top: 0px; width: 0px; height: 0px;"></object>';
}

function openDocument($title='MIT-xperts HbbTV testsuite', $allscripts=1, $addheaders='') {
  global $ROOTDIR;
  echo '<?xml version="1.0" encoding="utf-8" ?>'."\n";
  echo '<!DOCTYPE html PUBLIC "-//HbbTV//1.1.1//EN" "http://www.hbbtv.org/dtd/HbbTV-1.1.1.dtd">'."\n";
  echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
  echo "<head>\n";
  echo "<title>$title</title>\n".$addheaders;
  echo "<meta http-equiv=\"content-type\" content=\"Content-Type: application/vnd.hbbtv.xhtml+xml; charset=UTF-8\" />\n";
  echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$ROOTDIR/base.css\" />\n";
  echo "<script type=\"text/javascript\" src=\"$ROOTDIR/settings.js\"></script>\n";
  echo "<script type=\"text/javascript\" src=\"$ROOTDIR/releaseinfo.js\"></script>\n";
  if ($allscripts) {
    echo "<script type=\"text/javascript\" src=\"$ROOTDIR/keycodes.js\"></script>\n";
    echo "<script type=\"text/javascript\" src=\"$ROOTDIR/base.js\"></script>\n";
  }
}

function getMediaURL($useHttps=0) {
  global $ROOTDIR;
  $path = $_SERVER['PHP_SELF'];
  $count = substr_count($ROOTDIR, '..');
  for ($i=0; $i<=$count; $i++) {
    $path = dirname($path);
    if (!$path) $path = '/';
  }
  if (substr($path, -1)!=='/') {
    $path .= '/';
  }
  $ret = $useHttps ? 'https' : 'http';
  $ret .= '://'.$_SERVER['SERVER_NAME'].$path.'media/';
  return $ret;
}

?>
