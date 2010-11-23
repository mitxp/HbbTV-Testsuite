<?php
header('Content-Type: text/plain');

$dom = new DOMDocument();
if (@$dom->loadXML($_REQUEST['data'])) {
  echo '1';
  // if (@$dom->schemaValidate('capabilities.xsd')) {
  //   echo '1';
  // } else {
  //   echo '0';
  // }
} else {
  echo '0';
}
echo htmlspecialchars($_REQUEST['data']);

?>
