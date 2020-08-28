<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");

sendContentType();
openDocument();
?>
<script type="text/javascript">
//<![CDATA[
var legalshowing = false;
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
  showVid();
  setInstr('Please run all sub-tests. Navigate to the test using up/down, then press OK to start the test. Sub-test details will be displayed upon selection.<br />In case of questions regarding content, please contact hbbtv@dolby.com');
  <?php if (array_key_exists('select', $_REQUEST)) { ?>
    menuSelectByName(<?php echo json_encode($_REQUEST['select']); ?>);
  <?php } ?>
  runNextAutoTest();
};
function nameselect(snam) {
  if (!snam) return;
  for (var i=0; i<opts.length; i++) {
    var check = opts[i].getAttribute('name');
    if (check==snam) {
      menuSelect(i);
      break;
    }
  }
}
function showVid() {
  var vid = document.createElement("object");
  vid.type = "video/broadcast";
  vid.setAttribute("type", vid.type);
  vid.style.position = "absolute";
  vid.style.left = "0px";
  vid.style.top = "0px";
  vid.style.width = "320px";
  vid.style.height = "180px";
  vid.style.outline = "transparent";
  document.getElementById("vidcontainer").appendChild(vid);
  try {
    vid.bindToCurrentChannel();
  } catch (e) {
    showStatus(false, 'Starting of broadcast video failed.');
  }
}
function runStep(name) {
  if (name=='legal') {
    legalshowing = !legalshowing;
    document.getElementById("legal").style.display = legalshowing ? "block" : "none";
  } else {
    document.location.href = "detail.php?id="+name;
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div style="left: 700px; top: 36px; width: 295px; height: 60px; background-image: url(dolby.png);"></div>
<div id="vidcontainer" style="left: 700px; top: 300px; width: 320px; height: 180px;"></div>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 114px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="legal" automate="ignore">Info on Usage policy / Disclaimer</li>
  <li name="mp4_basic">E-AC3 in mp4</li>
  <li name="ts_basic">E-AC3 in MPEG-TS</li>
  <li name="dash_basic">E-AC3 in MPEG-DASH</li>
  <li name="dash_multi_rate">Multi-Rate for MPEG-DASH</li>
  <li name="mp4_multi_lang">Multi-Language in mp4</li>
  <li name="ts_multi_lang">Multi-Language in MPEG-TS</li>
  <li name="dash_multi_lang">Multi-Language in MPEG-DASH</li>
  <li name="mp4_multi_codec">Multi-Codec in mp4</li>
  <li name="ts_multi_codec">Multi-Codec in MPEG-TS</li>
  <li name="dash_multi_codec">Multi-Codec in MPEG-DASH</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 490px; width: 400px; height: 250px;"></div>
<div id="legal" class="txtdiv" style="display: none; left: 700px; top: 110px; width: 440px; height: 610px; background-color: #132d48;">The Materials provided are for internal user evaluation, testing, and deployment of Dolby Digital Plus in HbbTV. You may not sell, assign, license, disclose, distribute, or otherwise transfer or make available these Materials, in whole or in part, in any form to any third parties. These Materials may not be reproduced or used for any purpose other than deploying Dolby Digital Plus. To the greatest extent permitted under mandatory applicable law under no circumstances shall Dolby or MIT-xperts be liable for any injury or damage resulting from use of these Materials or any incidental, special, direct, indirect, or consequential damages or loss of use, loss of data, revenue or profit even if Dolby or its agents have been made aware of the possibility of such damages and no warranty is provided with respect to the Materials.</div>

</body>
</html>
