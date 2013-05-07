<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");

$referer = $_SERVER['HTTP_REFERER'];
$i = strrpos($referer, '?id=');
if ($i>0) {
  $referer = substr($referer, $i+4);
  $referer = addcslashes($referer, "\0..\37'\\");
} else {
  $referer = 'legal';
}

sendContentType();
openDocument();
?>
<script type="text/javascript">
//<![CDATA[
var legalshowing = false;
window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  showVid();
  nameselect('<?php echo $referer; ?>');
  setInstr('Please run all sub-tests. Navigate to the test using up/down, then press OK to start the test. Sub-test details will be displayed upon selection.');
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
    } else if (liid=='legal') {
      legalshowing = !legalshowing;
      document.getElementById("legal").style.display = legalshowing ? "block" : "none";
    } else {
      runStep(liid);
    }
    return true;
  }
  return false;
}
function runStep(name) {
  document.location.href = "detail.php?id="+name;
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div style="left: 700px; top: 36px; width: 320px; height: 65px; background-image: url(dolby.png);"></div>
<div id="vidcontainer" style="left: 700px; top: 240px; width: 320px; height: 180px;"></div>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="legal">Info on Usage policy / Disclaimer</li>
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
  <li name="dash_playready">MPEG-DASH with PlayReady DRM</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 430px; width: 400px; height: 400px;"></div>
<div id="legal" class="txtdiv" style="display: none; left: 700px; top: 110px; width: 440px; height: 610px; background-color: #132d48;">The Materials provided are for internal user evaluation, testing, and deployment of Dolby Digital Plus in HbbTV. You may not sell, assign, license, disclose, distribute, or otherwise transfer or make available these Materials, in whole or in part, in any form to any third parties. These Materials may not be reproduced or used for any purpose other than deploying Dolby Digital Plus. To the greatest extent permitted under mandatory applicable law under no circumstances shall Dolby or MIT-xperts be liable for any injury or damage resulting from use of these Materials or any incidental, special, direct, indirect, or consequential damages or loss of use, loss of data, revenue or profit even if Dolby or its agents have been made aware of the possibility of such damages and no warranty is provided with respect to the Materials.</div>

</body>
</html>
