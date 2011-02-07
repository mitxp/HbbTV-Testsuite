<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
$url = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/squeeze.aac';

sendContentType();
openDocument('MIT-xperts HBBTV testsuite', 1, '<link rel="prefetch" type="audio/mp4" href="'.$url.'" />'."\n")

?>
<script type="text/javascript">
//<![CDATA[
var req = false;

window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  setInstr('Try to run play multiple times to play back memory cached audio clip. At least after the first playback, later playbacks should (maybe) occur instantly.');
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
  if (name=='play') {
    try {
      document.getElementById('aud').stop();
    } catch (e) {
      // ignore
    }
    try {
      document.getElementById('aud').play(1);
      showStatus(true, 'Playback started, check audio (squeeeze sound should be played 2 times).');
    } catch (e) {
      showStatus(false, 'Playback failed.');
    }
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object type="audio/mp4" id="aud" data="<?php echo $url; ?>">
<param name="loop" value="2" />
</object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="play">Play squeeeze sound</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
