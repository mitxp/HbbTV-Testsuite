<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
require("validate.php");
sendContentType();
openDocument();

$uagent = $_SERVER['HTTP_USER_AGENT'];
$uagentok = validateUserAgent($uagent, $uagentmsg);
if ($uagentok) {
  $uagentmsg = 'User agent '.htmlspecialchars($uagent).' is valid.';
} else {
  $uagentmsg = 'User agent '.htmlspecialchars($uagent).' is invalid: '.htmlspecialchars($uagentmsg);
}

$id = rand();
$videourl = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/checkvideo.php/test.mp4?id='.$id;
?>
<script type="text/javascript">
//<![CDATA[
var vidto = false;

window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test.');
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
  if (name=='browser') {
    var success = <?php echo $uagentok ? 'true' : 'false'; ?>;
    showStatus(success, '<?php echo $uagentmsg; ?>');
  } else if (name=='vidplayer') {
    if (vidto) {
      clearTimeout(vidto);
    }
    setInstr('Starting video playback (waiting for onPlayStateChange event)...');
    var vid = document.getElementById('video');
    vid.onPlayStateChange = null;
    try {
      vid.stop();
    } catch (e) {
      // ignore
    }
    vidto = setTimeout(function() {vidto = false; checkPlayer();}, 30000);
    vid.onPlayStateChange = function() {
      if (6!=vid.playState && 5!=vid.playState) return;
      if (vidto) {
        clearTimeout(vidto);
        vidto = false;
      }
      checkPlayer();
    };
    vid.data = '<?php echo $videourl; ?>';
    try {
      vid.play(1);
    } catch (e) {
      // ignore
    }
  }
}
function checkPlayer() {
  req = new XMLHttpRequest();
  req.onreadystatechange = function() {
    if (req.readyState!=4 || req.status!=200) return;
    var s = req.responseText;
    if (s.length>2 && s.substring(0, 2)=='OK') {
      showStatus(true, 'Video player user agent '+s);
    } else {
      showStatus(false, 'Video player user agent invalid: '+s);
    }
    req.onreadystatechange = null;
    req = null;
  }
  req.open('GET', 'getresult.php?id=<?php echo $id; ?>');
  req.send(null);
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="video" type="video/mp4" style="position: absolute; left: 100px; top: 480px; width: 320px; height: 180px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="browser">Test 1: Check browser user agent</li>
  <li name="vidplayer">Test 2: Check video player user agent</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
