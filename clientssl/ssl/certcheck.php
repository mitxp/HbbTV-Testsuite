<?php
$DOMAINNAME = $_SERVER['SERVER_NAME'];

header('Pragma: no-cache');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: http://'.$DOMAINNAME);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: *');

$ALLOWEDCN = array();
$ALLOWEDCN['icordhdplus.humaxdigital.com'] = 1;
$ALLOWEDCN['icordhd-plus.humaxdigital.com'] = 1;
$ALLOWEDCN['hdfoxplus.humaxdigital.com'] = 1;
$ALLOWEDCN['HDNANO.HUMAXdigital.com'] = 1;
$ALLOWEDCN['600s.videoweb.de'] = 1;
$ALLOWEDCN['linux.vantage-digital.com'] = 1;
$ALLOWEDCN['TTmicroS855HbbTV.TechnoTrendGoerler.com'] = 1;
$ALLOWEDCN['iCordMini.HUMAXdigital.com'] = 1;
$ALLOWEDCN['SL90HDplusInterAktiv.comag.de'] = 1;
$ALLOWEDCN['testbox.mit-xperts.com'] = 1;
$ALLOWEDCN['viera2011.panasonic.com'] = 1;
$ALLOWEDCN['viera2012.panasonic.com'] = 1;
$ALLOWEDCN['viera2013.panasonic.com'] = 1;
$ALLOWEDCN['viera2014.panasonic.com'] = 1;
$ALLOWEDCN['viera2015.panasonic.com'] = 1;
$ALLOWEDCN['viera2016.panasonic.com'] = 1;
$ALLOWEDCN['viera2017.panasonic.com'] = 1;
$ALLOWEDCN['viera2018.panasonic.com'] = 1;
$ALLOWEDCN['viera2019low.panasonic.com'] = 1;
$ALLOWEDCN['com.panasonic.SmartTV2020mid'] = 1;
$ALLOWEDCN['com.panasonic.SmartTV2021mid'] = 1;
$ALLOWEDCN['com.panasonic.SmartTV2022mid'] = 1;
$ALLOWEDCN['SmartTV2019mid'] = 1;
$ALLOWEDCN['volksbox.inverto.tv'] = 1;
$ALLOWEDCN['ZBOX.INVERTO.tv'] = 1;
$ALLOWEDCN['volksboxWebEdition.inverto.tv'] = 1;
$ALLOWEDCN['GLOBAL_PLAT3.lge.com'] = 1;
$ALLOWEDCN['webosM.lge.com'] = 1;
$ALLOWEDCN['WebOS3K2L.lge.com'] = 1;
$ALLOWEDCN['NetCast4Hid.lge.com'] = 1;
$ALLOWEDCN['UFS925.KATHREIN.com'] = 1;
$ALLOWEDCN['videoengine.bang-olufsen.com'] = 1;
$ALLOWEDCN['s7100.vestel.com'] = 1;
$ALLOWEDCN['S7715.Vestel.com'] = 1;
$ALLOWEDCN['test.mstarsemi.com'] = 1;
$ALLOWEDCN['Philips Net TV TV 2k14 Marvell'] = 1;
$ALLOWEDCN['Philips Net TV TV 2k15'] = 1;
$ALLOWEDCN['Philips Net TV TV 2k16'] = 1;

$msg = '';
$sslcn = $_SERVER['SSL_CLIENT_S_DN_CN'] ?? '';
$i = strpos($sslcn, 'CN=');
if ($i>=0) {
  $sslcn = substr($sslcn, $i+3);
}
$i = strpos($sslcn, ',');
if ($i) {
  $sslcn = substr($sslcn, 0, $i);
}
if (($_SERVER['SSL_CLIENT_VERIFY']??'')!=='SUCCESS') {
  $msg = 'Client SSL certificate signed by unknown CA or no CA certificate provided.';
} else {
  if (!array_key_exists($sslcn, $ALLOWEDCN)) {
    $msg = "Unknown CN=$sslcn (please send us an email with your CN!)";
  }
}
$logfile = 'cn/'.md5($sslcn).'.txt';
@file_put_contents($logfile, "CN=$sslcn\nRESULT=$msg\n");
@chmod($logfile, 0666);

if ($msg) {
  $msg = "ERROR: $msg";
} else {
  if (!($_REQUEST['html']??'')) {
    session_set_cookie_params(172800, '/', $DOMAINNAME, false);
    session_start();
    $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['csslok']=1;
    session_write_close();
  }
  $msg = 'OK';
}

# uncomment this for logging
$fp = @fopen('/tmp/clientssltest','at');
@fputs($fp, date('Y-m-d H:i:s').':'.$_SERVER['REMOTE_ADDR'].':'.$msg."\n");
@fclose($fp);

if (array_key_exists('html', $_REQUEST) && $_REQUEST['html']) {
  echo '<?xml version="1.0" encoding="utf-8" ?>'."\n".'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n".'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de"><head><title>SSL Check</title>'."\n";
  echo '<style type="text/css">'."\nbody { width: 1280px; height: 720px; margin: 0; background-color: #000000; } p { color: #ffffff; padding: 100px; font-size: 24px; }\n</style>\n";
  echo "</head><body><p>\n";
  echo $msg;
  echo "\n</p></body>\n</html>";
} else {
  header('Content-Type: text/plain;charset=UTF-8');
  echo $msg;
}

