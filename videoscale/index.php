<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var fullscreen = false;
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;

window.onload = function() {
  menuInit();
  initVideo();
  registerMenuListener(function(liid) {
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
  });
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.');
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='lright') {
    setvidsize(700, 480, 320, 180, 'on the lower right of the screen.');
  } else if (name=='lcenter') {
    setvidsize(170, 480, 1010, 180, 'on the lower center of the screen (video aspect ratio should be correct, and it should have big black bars on the left/right).');
    markVideoPosition(515, 480, 320, 180);
  } else if (name=='full') {
    setvidsize(0, 0, 1280, 720, 'fullscreen in the background.');
  } else if (name=='vidclip') {
    var e = document.getElementById('vidcontainer');
    e.style.left = '700px';
    e.style.top = '260px';
    e.style.width = '320px';
    e.style.height = '180px';
    setvidsize(-64, -36, 448, 252, 'on the right of the screen (vertically centered, only partially visible).');
    markVideoPosition(700, 260, 320, 180);
  } else if (name=='togglefs') {
    togglefullscreen();
  } else if (name=='vidbroadcast') {
    govid(false);
  } else if (name=='vidstream') {
    govid(true);
  } else if (name=='vidstop') {
    try {
      document.getElementById('video').stop();
      showStatus(true, 'Video should now be stopped.');
    } catch (e) {
      showStatus(false, 'Stopping video failed.');
    }
    showVideoPosition(false);
  }
}
function setvidsize(x, y, w, h, txt) {
  var vid = document.getElementById('video');
  if (vid) {
    vid.style.left = x+'px'; 
    vid.style.top = y+'px'; 
    vid.style.width = w+'px'; 
    vid.style.height = h+'px'; 
  }
  showStatus(true, 'Please check visual result.');
  setInstr('Video position should now be '+txt);
  markVideoPosition(x, y, w, h);
}
function markVideoPosition(x, y, w, h) {
  var e = document.getElementById('vidpostxt');
  e.style.left = x+'px';
  e.style.top = (y-30)+'px';
  e = document.getElementById('vidposborder');
  e.style.left = (x-4)+'px'; 
  e.style.top = (y-4)+'px'; 
  e.style.width = w+'px'; 
  e.style.height = h+'px'; 
}
function showVideoPosition(isshowing) {
  var e = document.getElementById('vidpostxt');
  e.style.display = isshowing ? 'block' : 'none';
  e = document.getElementById('vidposborder');
  e.style.display = isshowing ? 'block' : 'none';
}
function togglefullscreen() {
  fullscreen = !fullscreen;
  var oldsel = selected;
  try {
    document.getElementById('video').setFullScreen(fullscreen);
    showStatus(true, 'Setting fullScreen('+fullscreen+') mode succeeded');
  } catch (e) {
    showStatus(false, 'Setting fullScreen('+fullscreen+') mode failed');
  }
  menuSelect(oldsel);
  showVideoPosition(!fullscreen);
}
function govid(typ) {
  var elem = document.getElementById('vidcontainer');
  var oldvid = document.getElementById('video');
  try {
    oldvid.stop();
  } catch (e) {
    // ignore
  }
  try {
    oldvid.release();
  } catch (e) {
    // ignore
  }
  var mtype = typ ? 'video/mp4' : 'video/broadcast';
  var ihtml = '<object id="video" type="'+mtype+'" style="position: absolute; left: 600px; top: 250px; width: 160px; height: 90px;"><'+'/object>';
  elem.style.left = '0px';
  elem.style.top = '0px';
  elem.style.width = '1280px';
  elem.style.height = '720px';
  elem.innerHTML = ihtml;
  var succss = false;
  var phase = 1;
  try {
    var videlem = document.getElementById('video');
    if (videlem) {
      if (typ) {
	phase = 2;
        videlem.data = '<?php echo getMediaURL(); ?>timecode.php/video.mp4';
	phase = 3;
        videlem.play(1);
        succss = true;
      } else {
	phase = 4;
        videlem.bindToCurrentChannel();
        succss = true;
      }
    }
  } catch (e) {
    // failed
  }
  fullscreen = false;
  try {
    videlem.setFullscreen(false);
  } catch (e) {
    // ignore
  }
  markVideoPosition(600, 250, 160, 90);
  showVideoPosition(true);
  if (!succss) {
    showStatus(false, 'Setting the video object '+mtype+' failed in phase '+phase);
    return;
  }
  if (!typ) {
    showStatus(true, 'Broadcast video should be playing now');
    return;
  }
  setInstr('Video object created, waiting for playback to start...');
  checkVideoPlaying(30, videlem, mtype);
}
function checkVideoPlaying(remainSecs, vid, mtype) {
  if (!vid.playState  || vid.playState==2 || vid.playState==3 || vid.playState==4) {
    // not playing yet
    if (remainSecs>0) {
      setTimeout(function() { checkVideoPlaying(remainSecs-1, vid, mtype); }, 1000);
      return;
    }
    showStatus(false, 'Video playback '+mtype+' failed.');
  } else {
    showStatus(true, 'Video '+mtype+' should be playing now');
  }
}


//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div id="vidcontainer" style="left: 0px; top: 0px; width: 1280px; height: 720px;"></div>
<?php echo appmgrObject(); ?>
<div style="left: 0px; top: 0px; width: 1280px; height: 720px;">
  <div id="vidpostxt" class="txtdiv" style="left: 480px; top: 430px; width: 320px; height: 30px; color: #ffffff; display: none;">Expected video position:</div>
  <div id="vidposborder" style="left: 480px; top: 460px; width: 320px; height: 220px; border: 4px solid #ffffff; display: none;"></div>
</div>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="vidbroadcast#1">Test 1: start broadcast video</li>
  <li name="lright#1" automate="visual">Test 2: lower right</li>
  <li name="lcenter#1" automate="visual">Test 3: lower center</li>
  <li name="full#1" automate="visual">Test 4: fullscreen (background)</li>
  <li name="vidclip#1" automate="visual">Test 5: video clipping test</li>
  <li name="vidstop#1">Test 6: stop video</li>
  <li name="vidstream#2">Test 7: start streaming video</li>
  <li name="lright#2" automate="visual">Test 8: lower right</li>
  <li name="togglefs#2" automate="visual">Test 9: toggle fullscreen mode</li>
  <li name="lcenter#2" automate="visual">Test 10: lower center</li>
  <li name="full#2" automate="visual">Test 11: fullscreen (background)</li>
  <li name="vidbroadcast#2">Test 12: start broadcast video</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
