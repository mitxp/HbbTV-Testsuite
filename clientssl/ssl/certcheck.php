<?php
header('Pragma: no-cache');
header('Cache-Control: no-cache');

$DOMAINNAME = $_SERVER['SERVER_NAME'];

$ALLOWEDCN = array();
$ALLOWEDCN['icordhdplus.humaxdigital.com'] = 1;
$ALLOWEDCN['icordhd-plus.humaxdigital.com'] = 1;
$ALLOWEDCN['600s.videoweb.de'] = 1;
$ALLOWEDCN['testbox.mit-xperts.com'] = 1;
$ALLOWEDCN['volksbox.inverto.tv'] = 1;

$msg = '';
if (!$_SERVER['SSL_CLIENT_VERIFY']=='SUCCESS') {
  $msg = 'Client SSL certificate signed by unknown CA';
} else {
  $sslcn = $_SERVER['SSL_CLIENT_S_DN_CN'];
  if (!$ALLOWEDCN[$sslcn]) {
    $msg = "Unknown CN=$sslcn";
  }
}
if ($msg) {
  $msg = "ERROR: $msg";
} else {
  if (!$_REQUEST['html']) {
    session_set_cookie_params(172800, '/', $DOMAINNAME, false);
    session_start();
    $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['csslok']=1;
    session_write_close();
  }
  $msg = 'OK';
}
#$fp = @fopen('/tmp/heide','at');
#@fputs($fp, $msg.' - '.date('Y-m-d H:i:s')."\n");
#@fclose($fp);
if ($_REQUEST['html']) {
  echo '<?xml version="1.0" encoding="utf-8" ?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n".'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de"><head><title>SSL Check</title>'."\n";
  echo '<style type="text/css">'."\nbody { width: 1280px; height: 720px; margin: 0; background-color: #000000; } p { color: #ffffff; padding: 100px; font-size: 24px; }\n</style>\n";
  echo "</head><body><p>\n";
  echo $msg;
  echo "\n</p></body>\n</html>";
} else {
  header('Content-Type: text/plain;charset=UTF-8');
  echo $msg;
}
?>
