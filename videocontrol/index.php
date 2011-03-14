<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var playing = false;
var testTimeout = false;

window.onload = function() {
  menuInit();
  initVideo();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.');
  showVidState();
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
function showVidState() {
  try {
    var vid = document.getElementById('video');
    document.getElementById('vidstate').innerHTML = 'Video play state = '+vid.playState+'<br />play position = '+vid.playPosition+'<br />speed = '+vid.speed;
  } catch (e) {
    if (playing) {
      showStatus(false, 'Cannot query video state');
    }
  }
  setTimeout(function() {showVidState();}, 1000);
}
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='start') {
    try {
      var vid = document.getElementById('video');
      vid.stop();
      vid.data = 'http://itv.ard.de/video/timecode.php/video.mp4';
      vid.play(1);
      showStatus(true, 'Video should be playing now');
      playing = true;
    } catch (e) {
      showStatus(false, 'Cannot start video');
    }
  } else if (name=='pause') {
    setSpeed(0);
  } else if (name=='play') {
    setSpeed(1);
  } else if (name=='ffwd') {
    setSpeed(2);
  } else if (name=='slowm') {
    setSpeed(0.5);
  } else if (name=='gopos30') {
    gotoPos(30);
  } else if (name=='gopos150') {
    gotoPos(150);
  } else if (name=='rewind') {
    setSpeed(-1);
  }
}
function setSpeed(fact) {
  var addmsg = (fact==0||fact==1) ? '' : '. Note: test is OK even though this test failed, as feature is not mandatory.';
  try {
    var vid = document.getElementById('video');
    vid.play(fact);
    setInstr('Waiting to check reported playback speed...');
    setTimeout(function() {
      if (parseInt(fact)==parseInt(vid.speed)) {
        showStatus(true, 'Video playback speed should now be '+fact);
      } else {
        showStatus(addmsg, 'Setting speed succeeded, but reported speed is '+vid.speed+addmsg);
      }
    }, 1000);
  } catch (e) {
    showStatus(addmsg, 'Cannot change playback speed'+addmsg);
  }
}
function gotoPos(scnds) {
  try {
    var vid = document.getElementById('video');
    vid.seek(scnds*1000);
    setInstr('Waiting for playback to resume to check reported playback position...');
    testPos(scnds);
  } catch (e) {
    showStatus(false, 'Cannot change playback position');
  }
}
function testPos(scnds) {
  if (testTimeout) {
    clearTimeout(testTimeout);
  }
  var vid = document.getElementById('video');
  testTimeout = setTimeout(function() {
    testTimeout = false;
    if (vid.playState && (vid.playState==3 || vid.playState==4)) {
      testPos(scnds); // delay test, we are not playing yet.
      return;
    }
    var secs = isNaN(vid.playPosition) ? -1 : Math.floor(vid.playPosition/1000);
    if (secs>=0 && secs>=(scnds-2) && secs<=(scnds+10)) {
      showStatus(true, 'Video playback position is at '+secs+' seconds');
    } else {
      showStatus(false, 'Seek succeeded, but reported playbackposition is at '+secs+' seconds');
    }
  }, 2000);
}


//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="video" type="video/mp4" style="position: absolute; left: 700px; top: 220px; width: 320px; height: 180px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<div id="vidstate" class="txtdiv" style="left: 700px; top: 390px; width: 400px; height: 90px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="start">Test 1: start streaming video</li>
  <li name="pause">Test 2: pause video</li>
  <li name="play">Test 3: continue playing (1x)</li>
  <li name="ffwd">Test 4: fast forward (2x)</li>
  <li name="slowm">Test 5: slow motion (0.5x)</li>
  <li name="play">Test 6: continue playing (1x)</li>
  <li name="gopos30">Test 7: go to position 00:30</li>
  <li name="gopos150">Test 8: go to position 02:30</li>
  <li name="rewind">Test 9: rewind (-1x)</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
