<?php
require("validate.php");
header('Content-Type: text/plain');

$id = (int)($_REQUEST['id']??0);
$msg = @file_get_contents('_results/'.$id);
if (!$msg) {
  $msg = 'Video URL was never requested.';
}
echo $msg;

?>
