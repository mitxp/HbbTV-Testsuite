<?php
require("validate.php");
header('Content-Type: video/mp4');

$id = (int)$_REQUEST['id'];
$uagent = $_SERVER['HTTP_USER_AGENT'];
$uagentok = validateUserAgent($uagent, $uagentmsg);
if ($uagentok) {
  $msg = 'OK: '.htmlspecialchars($uagentmsg);
} else {
  $msg = 'User agent '.htmlspecialchars($uagent).' is invalid: '.htmlspecialchars($uagentmsg);
}
echo $msg;

$dir = '_results';
$fp = fopen($dir.'/'.$id, 'wt');
fputs($fp, $msg);
fclose($fp);

$fp = opendir($dir);
while (false !== ($fnam=@readdir($fp))) {
  if ($fnam!='.' && $fnam!='..') $files[] = $fnam;
}
@closedir($fp);
$deltime = time()-3600;
foreach ($files as $dummy=>$fnam) {
  $fpath = "$dir/$fnam";
  if (filemtime($fpath)<$deltime) {
    unlink("$dir/$fnam");
  }
}

?>
