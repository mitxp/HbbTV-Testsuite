<?php
header('Content-Type: application/vnd.dvb.streamevent+xml');

echo '<?xml version="1.0" encoding="UTF-8"?>
<dsmcc xmlns="urn:dvb:mis:dsmcc:2009">
  <dsmcc_object component_tag="'.((int)$_REQUEST['ctag']).'">
    <stream_event stream_event_id="1" stream_event_name="testevent" />
  </dsmcc_object>
</dsmcc>';
?>
