<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;
window.onload = function() {
  menuInit();
  registerMenuListener(function(liid) {
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
  });
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. Displayed data must be verified manually, as configured preferences are unknown to this test.<br/><br/><b>Note: LocalSystem tests marked with &quot;Optional 7.3.3&quot; are optional, as this part of the specification is not mandatory for HbbTV devices.</b>');
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  var oipfcfg = document.getElementById('oipfcfg');
  if (!oipfcfg || !oipfcfg.configuration) {
    showStatus(false, 'Cannot find .configuration in application/oipfConfiguration');
    return;
  }
  if (name.length>8 && name.substring(0, 8)==='localSys' && !oipfcfg.localSystem) {
    showStatus(2, 'Cannot find .localSystem in application/oipfConfiguration. As this attribute is not mandatory in HbbTV, this might be expected.');
    return;
  }
  var config = oipfcfg.configuration;
  var result;
  var attrib;
  var valid = false;
  try {
    if (name=='audlang') {
      attrib = 'preferredAudioLanguage';
      result = config.preferredAudioLanguage;
      valid = validateLanguageList(result);
    } else if (name=='sublang') {
      attrib = 'preferredSubtitleLanguage';
      result = config.preferredSubtitleLanguage;
      valid = validateLanguageList(result);
    } else if (name=='uilang') {
      attrib = 'preferredUILanguage';
      result = config.preferredUILanguage;
      valid = validateLanguageList(result);
    } else if (name=='country') {
      attrib = 'countryId';
      result = config.countryId;
      valid = validateLanguageList(result) && result.length==3;
    } else if (name=="localSysDeviceID") {
      attrib = 'deviceID';
      result = oipfcfg.localSystem.deviceID;
      valid = (typeof result === "undefined") || result.length > 0 || "" === result;
    } else if (name=="localSysModelName") {
      attrib = 'modelName';
      result = oipfcfg.localSystem.modelName;
      valid = result.length > 0;
    } else if (name=="localSysVendorName") {
      attrib = 'vendorName';
      result = oipfcfg.localSystem.vendorName;
      valid = result.length > 0;
    } else if (name=="localSysSoftwareVersion") {
      attrib = 'softwareVersion';
      result = oipfcfg.localSystem.softwareVersion;
      valid = result.length > 0;
    } else if (name=="localSysHardwareVersion") {
      attrib = 'hardwareVersion';
      result = oipfcfg.localSystem.hardwareVersion;
      valid = result.length > 0;
    } else if (name=="localSysSerialNumber") {
      attrib = 'serialNumber';
      result = oipfcfg.localSystem.serialNumber;
      valid = (typeof result === "undefined") || result.length > 0 || "" === result;
    } else {
      showStatus(false, 'Unknown test name '+name);
      return;
    }
  } catch (e) {
    showStatus(false, 'Error while accessing '+attrib+' attribute');
  }
  if (valid) {
    showStatus(true, attrib+' = '+result);
  } else {
    showStatus(false, 'Configuration attribute '+attrib+' has invalid value: '+result);
  }
}
function validateLanguageList(txt) {
  txt = txt.toUpperCase();
  if (!txt) return false;
  while (txt) {
    if (txt.length<3) {
      return false;
    }
    for (var i=0; i<3; i++) {
      var c = txt.charCodeAt(i);
      if (c<0x41 || c>0x5a) return false;
    }
    txt = txt.substring(3);
    if (txt.length>0) {
      if (txt.substring(0, 1)!=',') return false;
      txt = txt.substring(1);
    }
  }
  return true;
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="audlang">Show preferred audio language</li>
  <li name="sublang">Show preferred subtitle language</li>
  <li name="country">Show country ID</li>
  <li name="uilang">Show preferred UI lang (HbbTV 1.2)</li>
  <li name="localSysDeviceID" class="Optional 7.3.3">Optional 7.3.3: localSys deviceID</li>
  <li name="localSysModelName" class="Optional 7.3.3">Optional 7.3.3: localSys modelName</li>
  <li name="localSysVendorName" class="Optional 7.3.3">Optional 7.3.3: localSys vendorName</li>
  <li name="localSysSoftwareVersion" class="Optional 7.3.3">Optional 7.3.3: localSys softwareVersion</li>
  <li name="localSysHardwareVersion" class="Optional 7.3.3">Optional 7.3.3: localSys hardwareVersion</li>
  <li name="localSysSerialNumber" class="Optional 7.3.3">Optional 7.3.3: localSys serialNumber</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 300px; width: 400px; height: 400px;"></div>

</body>
</html>
