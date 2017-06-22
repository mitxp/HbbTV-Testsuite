<?php
header('Access-Control-Allow-Origin: *');

function clean($txt) {
  $txt = str_replace(';', ',', $txt);
  $ret = '';
  for ($i=0; $i<strlen($txt); $i++) {
    $c = substr($txt, $i, 1);
    if (ord($c)<32) {
      continue;
    }
    if (ord($c)>127) {
      $c = '_';
    }
    $ret .= $c;
  }
  return $ret;
}

function getHbbTV($uagent) {
  $idx = stripos($uagent, 'HbbTV/');
  if ($idx===false) {
    return null;
  }
  $uagent = substr($uagent, $idx);
  $idx = strpos($uagent, ')');
  if ($idx) {
    $uagent = substr($uagent, 0, $idx+1);
  }
  return $uagent;
}

$now = time();
$step = clean($_REQUEST['step']);
if (!$step) {
  exit;
}
$succss = (int)$_REQUEST['succss'];
$pin = max(0, min(10000, (int)$_REQUEST['pin']));
$run = max(0, min(1000000, (int)$_REQUEST['run']));
$note = clean($_REQUEST['note']);
$txt = clean($_REQUEST['txt']);
$line = "$now;$step;$succss;$note;$txt\n";

$uagent = $_SERVER['HTTP_USER_AGENT'];
$hbbtv = getHbbTV($uagent);
$logdir = 'log';
$logext = '.report';
$file = $logdir.'/'.$pin.'-'.$run.'-'.sha1($_SERVER['REMOTE_ADDR'].'#'.$uagent).$logext;

if (is_file($file)) {
  $fp = @fopen($file, 'a');
} else {
  $fp = @fopen($file, 'w');
  fputs($fp, ';'.$_SERVER['REMOTE_ADDR'].';'.$now.';'.$uagent."\n");
}
if (!$fp) {
  exit;
}
fputs($fp, $line);
fclose($fp);


if ($hbbtv) {
  $file = $logdir.'/bydev-'.sha1($hbbtv).$logext;
  if (is_file($file)) {
    $fp = @fopen($file, 'a');
  } else {
    $fp = @fopen($file, 'w');
    fputs($fp, ';'.$_SERVER['REMOTE_ADDR'].';'.$now.';'.$uagent."\n");
  }
  if ($fp) {
    fputs($fp, $line);
    fclose($fp);
  }
}


# expire old log files
$expired = time()-31*24*3600;
$delfiles = array();
$fp = @opendir($logdir);
while (false!==($file=@readdir($fp))) {
  if (strlen($file)<10 || substr($file, -strlen($logext))!==$logext) {
    continue;
  }
  $file = $logdir.'/'.$file;
  $tim = @filemtime($file);
  if ($tim && $tim<$expired && is_file($file)) {
    $delfiles[] = $file;
  }
}
@closedir($fp);
foreach ($delfiles as $file) {
  @unlink($file);
}

