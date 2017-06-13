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
var eventNames = ["ended", "error", "loadeddata", "loadedmetadata", "loadstart", "pause", "play", "playing", "ratechange", "seeked", "seeking"];
var capturedEvents = {};
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
    govid(false, null);
  } else if (name=='vidstream') {
    govid(true, null);
  } else if (name=='vidpause' && isvidtyp) {
    vidpause(true);
  } else if (name=='vidplay' && isvidtyp) {
    vidpause(false);
  } else if (name=='vidseek' && isvidtyp) {
    vidseek(30000);
  } else if (name=='vidduration' && isvidtyp) {
    vidduration(254080);
  } else if (name=='events') {
    testEvents();
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
function moveVideoAway(alwaysCleanup) {
  var vid = document.getElementById('video');
  var ce, cntnr = document.getElementById('oldvidcontainer');
  if (!vid) {
    return; // no video to be removed
  }
  if (isvidtyp) {
    // HTML5 video is simple:
    vid.innerHTML = ""; // remove sources
    try {
      vid.load(); // This will release resources for the HTML5 video
    } catch (ignore) {
    }
    if (!alwaysCleanup) {
      return;
    }
  }
  while (ce = cntnr.firstChild) {
    try {
      ce.release();
    } catch (ignore) {
    }
    cntnr.removeChild(ce);
  }
  if (isvidtyp) {
    return;
  }
  if (!vid) {
    return;
  }
  // vid is video/broadcast object:
  // first, move it out of the way, to make room for the new video object
  vid.parentElement.removeChild(vid);
  vid.removeAttribute('id');
  if (!alwaysCleanup) {
    cntnr.appendChild(vid);
  }
  // now, stop the video to release resources for HTML5 video (do NOT release it)
  try {
    vid.stop();
  } catch (ignore) {
    // ignore
  }
  if (alwaysCleanup) {
    try {
      vid.release();
    } catch (ignore) {
      // ignore
    }
  }
}
function govid(typ, beforePlay) {
  var elem = document.getElementById('vidcontainer');
  moveVideoAway(!typ);
  isvidtyp = typ;
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
        if (beforePlay) {
          beforePlay(videlem);
        }
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
  if (!beforePlay || !succss) {
    showStatus(succss, 'Setting the video object '+(succss?'succeeded':'failed in phase '+phase));
  }
  markVideoPosition(600, 250, 160, 90);
  showVideoPosition(true);
  return succss;
}
function clearEvents() {
  var i;
  for (i=0; i<eventNames.length; i++) {
    capturedEvents[eventNames[i]] = 0;
  }
}
function testEvents() {
  var i, stages, checkStage, checkEvents, elistener, beforePlay, videoElement = null;
  elistener = function(event) {
    capturedEvents[event.type]++;
  };
  beforePlay = function(videlem) {
    videoElement = videlem;
    clearEvents();
    for (i=0; i<eventNames.length; i++) {
      videlem.addEventListener(eventNames[i], elistener, false);
    }
  };
  if (!govid(true, beforePlay)) {
    return; // setting up video failed
  }
  checkEvents = function(check) {
    var ename, expct, actl, typ;
    for (ename in check) {
      if (!check.hasOwnProperty(ename)) {
        continue;
      }
      expct = check[ename];
      if (expct==="?") {
        continue; // ignore this value
      }
      typ = expct.substring(0, 1);
      expct = parseInt(expct.substring(1), 10);
      actl = capturedEvents[ename]||0;
      if ((typ==='='&&actl===expct) || (typ==='<'&&actl<expct) || (typ==='>'&&actl>expct)) {
        continue; // value is OK
      }
      expct = check[ename].replace(/</g, "&lt;").replace(/>/g, "&gt;");
      return "Expected event count for "+ename+" to be "+expct+", actual value is "+actl;
    }
    return "OK";
  };
  var endCount = 0;
  stages = [
    {"descr":"Waiting for video to start...", "check":function() {
      if (!capturedEvents.playing) {
        return "WAIT";
      }
      return checkEvents({"loadeddata":">0", "loadedmetadata":">0", "loadstart":"=1", "pause":"=0", "play":"=1", "playing":"=1", "ratechange":"=0", "seeked":"=0", "seeking":"=0"});
    } },
    {"descr":"Pausing video...", "pause":3000, "check":function() { clearEvents(); videoElement.pause(); return "OK"; } },
    {"descr":"Waiting for video to pause...", "check":function() {
      if (!videoElement.paused) {
        return "WAIT";
      }
      return checkEvents({"loadeddata":"=0", "loadedmetadata":"=0", "loadstart":"=0", "pause":"=1", "play":"=0", "playing":"=0", "ratechange":"=0", "seeked":"=0", "seeking":"=0"});
    } },
    {"descr":"Resuming video...", "pause":3000, "check":function() { clearEvents(); videoElement.play(); return "OK"; } },
    {"descr":"Waiting for video to resume...", "check":function() {
      if (!capturedEvents.playing) {
        return "WAIT";
      }
      return checkEvents({"loadeddata":"=0", "loadedmetadata":"=0", "loadstart":"=0", "pause":"=0", "play":"=1", "playing":"=1", "ratechange":"=0", "seeked":"=0", "seeking":"=0"});
    } },
    {"descr":"Seeking video...", "pause":3000, "check":function() { clearEvents(); videoElement.currentTime = 240; return "OK"; } },
    {"descr":"Waiting for seek to complete...", "check":function() {
      if (videoElement.seeking) {
        return "WAIT";
      }
      return checkEvents({"loadeddata":"?", "loadedmetadata":"?", "loadstart":"?", "pause":"=0", "play":"=0", "playing":"?", "ratechange":"=0", "seeked":"=1", "seeking":"=1"});
    } },
    {"descr":"Check video position/duration...", "check":function() {
      clearEvents();
      if (videoElement.currentTime<235 || videoElement.currentTime>245) {
        return "Expected currentTime to be 240 seconds, but got value "+videoElement.currentTime;
      }
      if (videoElement.duration<250 || videoElement.duration>260) {
        return "Expected duration to be 254 seconds, but got value "+videoElement.duration;
      }
      return "OK";
    } },
    {"descr":"Waiting for end of video...", "check":function() {
      if (!videoElement.ended) {
        return "WAIT";
      }
      if (endCount===0) {
        endCount++;
        return "WAIT";
      }
      return checkEvents({"loadeddata":"=0", "loadedmetadata":"=0", "loadstart":"=0", "pause":"=1", "play":"=0", "playing":"?", "ratechange":"=0", "seeked":"=0", "seeking":"=0", "ended":"=1"});
    } }
  ];
  checkStage = function() {
    var stage = null, stageIdx = 0, reslt, pause;
    for (i=0; i<stages.length&&!stage; i++) {
      stage = stages[i];
      stageIdx = i;
    }
    if (!stage) {
      showStatus(true, 'Events test succeeded.');
      return;
    }
    pause = stage.pause||0;
    if (pause>0) {
      setInstr("Waiting...");
      stages[stageIdx].pause = 0;
      timr = setTimeout(function() {timr=null; checkStage();}, pause);
      return;
    }
    setInstr(stage.descr);
    try {
      reslt = stage.check();
    } catch (ex) {
      reslt = "exception = "+ex;
    }
    if (capturedEvents.error) {
      reslt = "Video playback error";
    } else if (capturedEvents.ended && stageIdx<stages.length-1) {
      reslt = "Premature end of video";
    }
    if (reslt==="OK") {
      stages[stageIdx] = null;
    } else if (reslt!=="WAIT") {
      showStatus(false, 'Events test failed: '+reslt+' (step '+stage.descr+')');
      return;
    }
    timr = setTimeout(function() {timr=null; checkStage();}, 1000);
  };
  timr = setTimeout(function() {timr=null; checkStage();}, 1000);
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div id="oldvidcontainer" style="left: 0px; top: 0px; width: 1280px; height: 720px;"></div>
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
  <li name="vidbroadcast#2">Test 9: start broadcast video</li>
  <li name="events">Test 10: test events</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
