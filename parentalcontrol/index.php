<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var scheme = null;
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
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. Displayed data must be verified manually, as configured preferences are unknown to this test.');
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='scheme1') {
    var pcmgr = document.getElementById('pcmgr');
    if (!pcmgr.parentalRatingSchemes || !pcmgr.parentalRatingSchemes.length) {
      showStatus(false, 'Cannot access pcmgr.parentalRatingSchemes');
      return;
    }
    try {
      for (var i=0; i<pcmgr.parentalRatingSchemes.length; i++) {
	var check = pcmgr.parentalRatingSchemes.item(i);
	if (check.name != 'dvb-si') continue;
	if (check.length!=0) {
	  showStatus(false, 'dvb-si scheme has length !=0: '+check.length);
	  return;
	}
	scheme = check;
	break;
      }
    } catch (e) {
      showStatus(false, 'Exception while searching for dvb-si scheme');
      return;
    }
    if (!scheme) {
      showStatus(false, 'Chould not find dvb-si scheme');
      return;
    }
    showStatus(true, 'dvb-si rating scheme was retrieved successfully');
  } else if (name=='scheme2') {
    var pcmgr = document.getElementById('pcmgr');
    if (!pcmgr.parentalRatingSchemes) {
      showStatus(false, 'Cannot access pcmgr.parentalRatingSchemes');
      return;
    }
    try {
      var check = pcmgr.parentalRatingSchemes.getParentalRatingScheme('dvb-si');
      if (check.name != 'dvb-si' || check.length != 0) {
	showStatus(false, 'dvb-si scheme returned by pcmgr.getParentalRatingScheme is invalid');
	return;
      }
      scheme = check;
    } catch (e) {
      showStatus(false, 'Exception while searching for dvb-si scheme'+e);
      return;
    }
    showStatus(true, 'dvb-si rating scheme was retrieved successfully');
  } else if (name=='threshold') {
    if (!scheme) {
      showStatus(false, 'Previous step(s) did not complete normally');
      return;
    }
    var rating = scheme.threshold;
    if (!rating) {
      showStatus(false, 'dvb-si scheme has no threshold');
      return;
    }
    if ('dvb-si'!=rating.scheme) {
      showStatus(false, 'Current threshold has invalid scheme');
      return;
    }
    if (!('name' in rating) || !('value' in rating)) {
      showStatus(false, 'The returned rating scheme threshold does not have both the name and value properties');
      return;
    }
    if (typeof rating.name === 'undefined' && typeof rating.value === 'undefined') {
      showStatus(false, 'Currently, no threshold is configured. Please configure a parental rating threshold on your device.');
      return;
    }
    if (parseInt(rating.name)!=rating.value) {
      showStatus(false, 'Current threshold rating name is not string representation of value attribute');
      return;
    }
    showStatus(true, 'Current rating threshold is '+rating.value+' (age '+(rating.value?(rating.value+3):rating.value)+' years), labels='+rating.labels+', region='+rating.region);
  }
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="pcmgr" type="application/oipfParentalControlManager" style="position: absolute; left: 0px; top: 0px; width: 0px; height: 0px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="scheme1">Traverse parentalRatingSchemes items</li>
  <li name="scheme2">Use getParentalRatingScheme</li>
  <li name="threshold">Show current threshold</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 300px; width: 400px; height: 400px;"></div>

</body>
</html>
