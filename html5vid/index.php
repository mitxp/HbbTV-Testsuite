<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var isvidtyp = false;
var timr = null;
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
  if (timr) {
    clearTimeout(timr);
    timr = null;
  }
  showStatus(true, '');
  if (name=='lright') {
    setvidsize(700, 480, 320, 180, 'on the lower right of the screen.');
  } else if (name=='lcenter') {
    setvidsize(170, 480, 1010, 180, 'on the lower center of the screen (video aspect ratio should be correct, and it should have big black bars on the left/right).');
    markVideoPosition(515, 480, 320, 180);
  } else if (name=='vidbroadcast') {
    govid(false);
  } else if (name=='vidstream') {
    govid(true);
  } else if (name=='vidpause' && isvidtyp) {
    vidpause(true);
  } else if (name=='vidplay' && isvidtyp) {
    vidpause(false);
  } else if (name=='vidseek' && isvidtyp) {
    vidseek(30000);
  } else if (name=='vidduration' && isvidtyp) {
    vidduration(254080);
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
function vidpause(ispause) {
  var vid = document.getElementById('video');
  try {
    if (ispause) {
      vid.pause();
    } else {
      vid.play();
    }
    if (vid.paused===ispause) {
      showStatus(true, 'pause/play call succeeded.');
    } else {
      showStatus(false, 'pause/play call succeeded, but incorrect paused state reported.');
    }
  } catch (e) {
    showStatus(false, 'pause/play call failed.');
  }
}
function vidseek(millis) {
  var vid = document.getElementById('video');
  var secs = millis/1000;
  try {
    vid.currentTime = secs;
    validateSeek(vid, secs);
  } catch (e) {
    showStatus(false, 'seek call failed.');
  }
}
function validateSeek(vid, secs) {
  if (vid.seeking) {
    setInstr('Waiting for video seek to finish...');
    timr = setTimeout(function() {validateSeek(vid, secs);}, 1000);
    return;
  }
  var reportsec = vid.currentTime;
  if (reportsec>secs-1 && reportsec<secs+2) {
    showStatus(true, 'seek call succeeded.');
  } else {
    showStatus(false, 'seek call succeeded, but incorrect currentTime reported: '+reportsec+", should be "+secs);
  }
}
function vidduration(millis) {
  var vid = document.getElementById('video');
  var secs = millis/1000;
  try {
    console.log(vid.duration);
    if (vid.duration>secs-1 && vid.duration<secs+1) {
      showStatus(true, 'correct video duration reported.');
    } else {
      showStatus(true, 'invalid video duration reported: '+vid.duration+", should be "+secs);
    }
  } catch (e) {
    showStatus(false, 'duration check failed.');
  }
}
function govid(typ) {
  var elem = document.getElementById('vidcontainer');
  var oldvid = document.getElementById('video');
  isvidtyp = typ;
  try {
    oldvid.stop(); // This will stop the broadcast video, but will throw an (ignored) exception for the streaming video
  } catch (e) {
    // ignore
  }
  try {
    oldvid.release(); // This will release the broadcast video, but will throw an (ignored) exception for the streaming video
  } catch (e) {
    // ignore
  }
  var ihtml;
  if (typ) {
    ihtml = '<video id="video" style="position: absolute; left: 600px; top: 250px; width: 160px; height: 90px;"><'+'/video>';
  } else {
    ihtml = '<object id="video" type="video/broadcast" style="position: absolute; left: 600px; top: 250px; width: 160px; height: 90px;"><'+'/object>';
  }
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
        videlem.innerHTML = '<source src="<?php echo getMediaURL(); ?>timecode.php/video.mp4"><'+'/source>';
	phase = 3;
        videlem.play();
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
  showStatus(succss, 'Setting the video object '+(succss?'succeeded':'failed in phase '+phase));
  markVideoPosition(600, 250, 160, 90);
  showVideoPosition(true);
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
  <li name="vidbroadcast">Test 1: start broadcast video</li>
  <li name="vidstream">Test 2: start streaming video</li>
  <li name="lright">Test 3: lower right</li>
  <li name="lcenter">Test 4: lower center</li>
  <li name="vidpause">Test 5: pause video</li>
  <li name="vidplay">Test 6: resume video</li>
  <li name="vidseek">Test 7: seek to 30s</li>
  <li name="vidduration">Test 8: check video duration</li>
  <li name="vidbroadcast">Test 9: start broadcast video</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
