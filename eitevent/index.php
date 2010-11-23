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
  initVideo();
  registerKeyEventListener();
  initApp();
  try {
    var txt = 'Now running: '+getEitEvent(0)+'<br />Followed by: '+getEitEvent(1);
    showStatus(true, 'Test passed (if displayed data is correct)');
    setInstr(txt);
  } catch (e) {
    showStatus(false, 'Test failed');
  }
};

function getEitEvent(idx) {
  var vid = document.getElementById('video');
  var evt = vid.programmes[idx];
  var dstart = new Date(evt.startTime * 1000);
  var dend = new Date((evt.startTime+evt.duration) * 1000);
  var fromhrs = dstart.getHours();
  var frommin = dstart.getMinutes();
  var tohrs = dend.getHours();
  var tomin = dend.getMinutes();
  return (fromhrs<10?'0':'')+fromhrs+(frommin<10?':0':':')+frommin+' - '+(tohrs<10?'0':'')+tohrs+(tomin<10?':0':':')+tomin+'<br />'+evt.name;
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
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo videoObject(100, 480, 320, 180); ?>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
