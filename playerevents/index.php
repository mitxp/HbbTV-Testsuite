<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var eventnames = ['stopped', 'playing', 'paused', 'connecting', 'buffering', 'finished', 'error'];
var foundevents = [];
var starttime = 0;
var eventtxt = '';
var speedchangereceived = false;
var poschangereceived = false;
var pausetimer = false;
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
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.<br /><br /><b>IMPORTANT: The test result is displayed when the video stops. If no result is displayed at the end of the video, a required finished/error event is not sent. Please check if the video starts playing as soon as the playing event is received.<'+'/b>');
  runNextAutoTest();
};
function runStep(name) {
  var vid = document.getElementById('video');
  vid.onPlayStateChange = null;
  vid.onPlaySpeedChanged = null;
  vid.onPlayPositionChanged = null;
  if (pausetimer) {
    clearTimeout(pausetimer);
  }
  try {
    vid.stop();
  } catch (e) {
    // ignore
  }
  setInstr('Starting video...');
  showStatus(true, '');
  eventtxt = '';
  for (var i=0; i<eventnames.length; i++) {
    foundevents[i] = 0;
  }
  speedchangereceived = false;
  poschangereceived = false;
  document.getElementById('vidstate').innerHTML = '';
  vid.onPlayStateChange = function(newstate) {
    var state = (newstate && typeof newstate === 'number') ? newstate : vid.playState;
    var ename = 'unknown event state '+state;
    if (state>=0 && state<eventnames.length) {
      foundevents[state]++;
      ename = eventnames[state]+'('+state+')';
    }
    eventtxt += '<br />@sec '+Math.floor((new Date().getTime()-starttime)/1000)+': '+ename;
    setInstr('Waiting while playing video (test result is displayed at end of video...'+eventtxt);
    if (state==5 || state==6) {
      var errorno = isNaN(vid.error) ? -1 : vid.error;
      showResult(name, errorno);
    }
  };
  vid.onPlaySpeedChanged = function() {
    speedchangereceived = true;
  };
  vid.onPlayPositionChanged = function() {
    poschangereceived = true;
    document.getElementById('vidstate').innerHTML = 'Play position = '+vid.playPosition+'<br />Play time = '+vid.playTime;
  };
  starttime = new Date().getTime();
  try {
    if (name=='valid') {
      vid.data = '<?php echo getMediaURL(); ?>trailer.php';
      vid.play(1);
      checkpausetimer(); // pause video and restart it in order to check if events are sent correctly
    } else if (name=='invalid0') {
      vid.data = 'http://<?php echo $_SERVER['SERVER_NAME'].str_replace('index.php','',$_SERVER['PHP_SELF']); ?>invalidformat.php';
      vid.play(1);
    } else if (name=='invalid1') {
      vid.data = 'http://10.240.255.128/cannotconnect.mp4';
      vid.play(1);
    } else if (name=='invalid2') {
      vid.data = 'http://<?php echo $_SERVER['SERVER_NAME'].str_replace('index.php','',$_SERVER['PHP_SELF']); ?>novideo.php/video.mp4';
      vid.play(1);
    } else if (name=='invalid3') {
      vid.data = 'http://<?php echo $_SERVER['SERVER_NAME'].str_replace('index.php','',$_SERVER['PHP_SELF']); ?>notfound.mp4';
      vid.play(1);
    }
  } catch (e) {
    showStatus(false, 'Cannot start video playback');
  }
}
function checkpausetimer() {
  var vid = document.getElementById('video');
  var state = (vid && vid.playState) ? vid.playState : 0;
  if (state==3 || state==4) { // still connecting or buffering
    pausetimer = setTimeout(function() { checkpausetimer(); }, 2000);
  } else {
    pausetimer = setTimeout(function() {
      vid.play(0);
      pausetimer = setTimeout(function() {
        vid.play(1);
        pausetimer = false;
      }, 5000);
    }, 12000);
  }
}
function showResult(name, errorno) {
  var errmsg = '';
  if (name=='valid') {
    if (foundevents[1]<2) {
      errmsg += '<br />not at least 2x PLAYING events received ('+foundevents[1]+'x instead)';
    }
    if (foundevents[2]<1) {
      errmsg += '<br />not at least 1x PAUSED event received ('+foundevents[2]+'x instead)';
    }
    if (foundevents[5]>1) {
      errmsg += '<br />multiple FINISHED events received ('+foundevents[5]+'x)';
    }
    if (!foundevents[5]) {
      errmsg += '<br />no FINISHED event received';
    }
    if (foundevents[6]) {
      errmsg += '<br />ERROR event received';
    }
  } else { // invalid video should cause a single error event
    if (foundevents[1]) {
      errmsg += '<br />PLAYING event received';
    }
    if (foundevents[5]) {
      errmsg += '<br />FINISHED event received';
    }
    if (foundevents[6]>1) {
      errmsg += '<br />multiple ERROR events received';
    }
    if (!foundevents[6]) {
      errmsg += '<br />no ERROR event received';
    }
    if (name=='invalid0' && errorno!=0 && errorno!=4) {
      errmsg += '<br />ERROR variable set to '+errorno+', should be 0 or 4';
    } else if (name=='invalid1' && errorno!=1) {
      errmsg += '<br />ERROR variable set to '+errorno+', should be 1';
    } else if (name=='invalid2' && errorno!=0 && errorno!=2 && errorno!=4) {
      errmsg += '<br />ERROR variable set to '+errorno+', should be 0, 2, or 4';
    } else if (name=='invalid3' && errorno!=1 && errorno!=2 && errorno!=5 && errorno!=6) {
      errmsg += '<br />ERROR variable set to '+errorno+', should be 1, 2, 5 or 6';
    }
  }
  if (errmsg) {
    showStatus(false, 'The received events were not correct (see above).');
    setInstr('The following problems were detected:'+errmsg);
  } else {
    showStatus(true, 'Test succeeded.');
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="video" type="video/mp4" style="position: absolute; left: 100px; top: 480px; width: 320px; height: 180px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<div id="vidstate" class="txtdiv" style="left: 700px; top: 420px; width: 400px; height: 60px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="valid">Test 1: play valid video</li>
  <li name="invalid0">Test 2: invalid video (A/V format)</li>
  <li name="invalid1">Test 3: invalid video (cannot connect)</li>
  <li name="invalid2">Test 4: invalid video (bad content)</li>
  <li name="invalid3">Test 5: invalid video (404 not found)</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
