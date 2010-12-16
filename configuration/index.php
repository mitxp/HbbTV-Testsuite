<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. Displayed data must be verified manually, as configured preferences are unknown to this test.');
};
function handleKeyCode(kc) {
  if (kc==VK_UP) {
    menuSelect(selected-1);
    return true;
  } else if (kc==VK_DOWN) {
    menuSelect(selected+1);
    return true;
  } else if (kc==VK_ENTER) {
    var liid = opts[selected].getAttribute('name');
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
    return true;
  }
  return false;
}
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  var oipfcfg = document.getElementById('oipfcfg');
  if (!oipfcfg || !oipfcfg.configuration) {
    showStatus(false, 'Cannot find Configuration in application/oipfConfiguration');
    return;
  }
  var config = oipfcfg.configuration;
  var result;
  var attrib;
  try {
    if (name=='audlang') {
      attrib = 'preferredAudioLanguage';
      result = config.preferredAudioLanguage;
    } else if (name=='sublang') {
      attrib = 'preferredSubtitleLanguage';
      result = config.preferredSubtitleLanguage;
    } else if (name=='country') {
      attrib = 'countryId';
      result = config.countryId;
    } else {
      showStatus(false, 'Unknown test name '+name);
      return;
    }
  } catch (e) {
    showStatus(false, 'Error while accessing '+attrib+' attribute');
  }
  if (result && result.length==3) {
    showStatus(true, attrib+' = '+result);
  } else {
    showStatus(false, 'Configuration attribute '+attrib+' has invalid value: '+result);
  }
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
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 300px; width: 400px; height: 400px;"></div>

</body>
</html>
