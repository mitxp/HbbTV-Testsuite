<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var fullscreen = false;

window.onload = function() {
  menuInit();
  initVideo();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.');
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
  var e = document.getElementById('vidctrbg');
  e.style.display = 'none';
  if (name=='lright') {
    setvidsize(700, 480, 320, 180, 'on the lower left of the screen.');
  } else if (name=='lcenter') {
    e.style.display = 'block';
    setvidsize(100, 480, 1080, 180, 'on the lower center of the screen (video aspect ratio should be correct, and it should have a black border on the left/right).');
  } else if (name=='full') {
    setvidsize(0, 0, 1280, 720, 'fullscreen in the background.');
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
  }
}
function setvidsize(x, y, w, h, txt) {
  var vid = document.getElementById('video');
  vid.style.left = x+'px'; 
  vid.style.top = y+'px'; 
  vid.style.width = w+'px'; 
  vid.style.height = h+'px'; 
  showStatus(true, 'Please check visual result.');
  setInstr('Video position should now be '+txt);
}
function togglefullscreen() {
  fullscreen = !fullscreen;
  var oldsel = selected;
  try {
    document.getElementById('video').setFullScreen(fullscreen);
    showStatus(true, 'Setting fullsceen('+fullscreen+') mode succeeded');
  } catch (e) {
    showStatus(false, 'Setting fullsceen('+fullscreen+') mode failed');
  }
  menuSelect(oldsel);
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
  var ihtml = '<object id="video" type="'+mtype+'" style="position: absolute; left: 600; top: 250; width: 160px; height: 90px;"><'+'/object>';
  elem.innerHTML = ihtml;
  var succss = false;
  var phase = 1;
  try {
    var videlem = document.getElementById('video');
    if (videlem) {
      if (typ) {
	phase = 2;
        videlem.data = 'http://itv.ard.de/video/timecode.php/video.mp4';
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
  showStatus(succss, 'Setting the video object '+mtype+' '+(succss?'succeeded':'failed in phase '+phase));
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div id="vidctrbg" style="left: 480px; top: 460px; width: 320px; height: 220px; background-color: #f0f0f0; display: none;"></div>
<div id="vidcontainer" style="left: 0px; top: 0px; width: 1280px; height: 720px;"></div>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="vidbroadcast">Test 1: start broadcast video</li>
  <li name="lright">Test 2: lower right</li>
  <li name="lcenter">Test 3: lower center</li>
  <li name="full">Test 4: fullscreen (background)</li>
  <li name="vidstop">Test 5: stop video</li>
  <li name="vidstream">Test 6: start streaming video</li>
  <li name="lright">Test 7: lower right</li>
  <li name="togglefs">Test 8: toggle fullscreen mode</li>
  <li name="lcenter">Test 9: lower center</li>
  <li name="full">Test 10: fullscreen (background)</li>
  <li name="vidbroadcast">Test 11: start broadcast video</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
