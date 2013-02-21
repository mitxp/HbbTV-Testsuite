<?php
function validateUserAgent($uagent, &$msg) {
  $x = strstr($uagent, 'HbbTV/');
  if ($x) $uagent = $x;
  $x = strrpos($uagent, ')');
  if ($x) $uagent = substr($uagent, 0, $x+1);
  $uagent = trim($uagent);
  if (substr($uagent, 0, 6)!='HbbTV/') {
    $msg = 'does not start with HbbTV/';
    return false;
  }
  $uagent = substr($uagent, 6);
  $ver = substr($uagent, 0, 5);
  if ($ver!='1.1.1' && $ver!='1.2.1') {
    $msg = 'HbbTV version not equal to 1.1.1 / 1.2.1';
    return false;
  }
  $uagent = substr($uagent, 5);
  if (substr($uagent, 0, 2)!=' (') {
    $msg = 'version is not followed by a space and an opening bracket';
    return false;
  }
  $i = strpos($uagent, ')');
  if (!$i) {
    $msg = 'does not end with a closing bracket';
    return false;
  }
  $uagent = substr($uagent, 2, $i-2);
  if (substr_count($uagent, ';')<5) {
    $msg = 'not enough semicolons fount (need at least 5)';
    return false;
  }
  $data = explode(';', $uagent, 6);
  $capabilities = trim($data[0]);
  $capabilities = str_replace('+DL', '', $capabilities);
  $capabilities = str_replace('+PVR', '', $capabilities);
  $capabilities = str_replace('+RTSP', '', $capabilities);
  $capabilities = str_replace('+DRM', '', $capabilities);
  if ($capabilities) {
    $msg = 'invalid capabilities '.$capabilities;
    return false;
  }
  if (!$data[1]) {
    $msg = 'missing vendor name';
    return false;
  }
  if (!$data[2]) {
    $msg = 'missing model name';
    return false;
  }
  return true;
}

?>
