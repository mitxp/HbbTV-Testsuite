<?php
header('Pragma: no-cache');
header('Cache-Control: no-cache');
header('Content-Type: text/plain;charset=UTF-8');

$DOMAINNAME = $_SERVER['SERVER_NAME']??'itv.mit-xperts.com';
session_set_cookie_params(172800, '/', $DOMAINNAME, false);
session_start();
if ($_REQUEST['kill']??null) {
  $_SESSION['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
  $_SESSION['csslok']=0;
}
echo ($_SESSION['csslok']??0)==1 ? 'OK' : 'FAILED';
?>
