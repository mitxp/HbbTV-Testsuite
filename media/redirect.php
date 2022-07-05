<?php
$ROOTDIR = '..';
require("$ROOTDIR/base.php");

$valid = strstr($_SERVER['HTTP_USER_AGENT'], 'HbbTV');
if (!$valid) {
  header('HTTP/1.0 403 Forbidden');
  header('Pragma: no-cache');
  header('Expires: Tue, 12 Feb 2000 12:00:00 GMT');
  header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
  echo '<html><head><title>Apache Tomcat/7.0.26 - Error report</title><style><!--H1 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:22px;} H2 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:16px;} H3 {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;font-size:14px;} BODY {font-family:Tahoma,Arial,sans-serif;color:black;background-color:white;} B {font-family:Tahoma,Arial,sans-serif;color:white;background-color:#525D76;} P {font-family:Tahoma,Arial,sans-serif;background:white;color:black;font-size:12px;}A {color : black;}A.name {color : black;}HR {color : #525D76;}--></style> </head><body><h1>HTTP Status 403 - </h1><HR size="1" noshade="noshade"><p><b>type</b> Status report</p><p><b>message</b> </p><p><b>description</b> <u>Access to the specified resource () has been forbidden.</u></p><HR size="1" noshade="noshade"><h3>Apache Tomcat/7.0.26</h3></body></html>';
  exit;
}

$url = getMediaURL().'zdf.mp4';

header('Pragma: no-cache');
header('Expires: Tue, 12 Feb 2000 12:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Last-Modified: Tue, 12 Feb 2000 12:00:00 GMT');
header('Location: '.$url);
header('Content-Length: 0');
header('Date: '.gmdate('D, d M Y H:i:s').' GMT');
header('Connection: close');

?>
