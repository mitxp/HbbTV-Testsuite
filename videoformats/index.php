<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var req = false;

window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  setInstr('Please play all audios / videos. Navigate using up/down, then press OK to start the test.');
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
  setInstr('Retrieving URL...');
  showStatus(true, '');
  if (req) {
    req.abort();
  }
  stopVideo();
  if (name=='ardepg') {
    playVideo('video/mp4', '<?php echo getMediaURL(); ?>trailer.php', true);
    return;
  }
  if (name=='zdf') {
    playVideo('video/mp4', '<?php echo getMediaURL(); ?>zdfredirect.php/test.mp4', true);
    return;
  }
  if (name=='mpegts') {
    playVideo('video/mpeg', '<?php echo getMediaURL(); ?>timecode.mpeg', true);
    return;
  }
  if (name=='audiomp3') {
    playVideo('audio/mpeg', '<?php echo getMediaURL(); ?>mp3radio.php', true);
    return;
  }
  if (name=='audiomp4') {
    playVideo('audio/mp4', '<?php echo getMediaURL(); ?>audio.php', true);
    return;
  }
  if (name=='https') {
    playVideo('video/mp4', '<?php echo getMediaURL(true); ?>trailer.mp4', true);
    return;
  }
  if (name=='irthd') {
    playVideo('video/mp4', '<?php echo getMediaURL(); ?>irthd.mp4', true);
    return;
  }
  if (name=='tsstream') {
    playVideo('video/mp4', '<?php echo getMediaURL(); ?>livestream.php', true);
    return;
  }
  if (name=='relative') {
    playVideo('video/mp4', '../media/trailer.php', true);
    return;
  }
  if (name=='dash') {
    playVideo('application/dash+xml', 'http://itv.mit-xperts.com/video/dash/index.php/test.mpd', true);
    return;
  }
  if (name=='queue') {
    try {
      playVideo('video/mp4', '<?php echo getMediaURL(); ?>trailer.php', false);
      var videlem = document.getElementById('video');
      videlem.queue('<?php echo getMediaURL(); ?>trailer_hotelinspektor.mp4');
      videlem.queue(null); // remove previous element from queue
      videlem.queue('<?php echo getMediaURL(); ?>timecode.php');
      showStatus(true, 'First Das Erste trailer should be played, followed by a time code video clip. An Hotelinspektor trailer shall never be played.');
    } catch (e) {
      showStatus(false, 'Queuing video failed.');
    }
    return;
  }
  req = new XMLHttpRequest();
  req.onreadystatechange = function() {
    if (req.readyState!=4 || req.status!=200) return;
    var s = req.responseText.split('#');
    playVideo(s[0], s[1], true);
    req.onreadystatechange = null;
    req = null;
  };
  req.open('GET', 'videourl.php?id='+name);
  req.send(null);
}
function stopVideo() {
  var elem = document.getElementById('vidcontainer');
  var oldvid = document.getElementById('video');
  if (oldvid) {
    oldvid.onPlayStateChange = null;
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
  }
  elem.innerHTML = '';
}
function playVideo(mtype, murl, registerlistener) {
  setInstr('Playing '+murl+' ('+mtype+')...');
  var elem = document.getElementById('vidcontainer');
  var ihtml = '<object id="video" type="'+mtype+'" style="position: absolute; left: 700px; top: 300px; width: 320px; height: 180px;"><'+'/object>';
  elem.innerHTML = ihtml;
  try {
    var videlem = document.getElementById('video');
    if (registerlistener) {
      videlem.onPlayStateChange = function() {
        if (1==videlem.playState) {
          videlem.onPlayStateChange = null;
          showStatus(true, mtype+' should be playing now.');
        } else if (6==videlem.playState) {
          videlem.onPlayStateChange = null;
          showStatus(false, mtype+' playback failed (error event).');
        }
      };
    }
    videlem.data = murl;
    videlem.play(1);
  } catch (e) {
    showStatus(false, 'Setting the video object '+mtype+' failed.');
  }
  
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div id="vidcontainer" style="left: 0px; top: 0px; width: 1280px; height: 720px;"></div>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="ardepg">ARD EPG video (MP4)</li>
  <li name="zdf">ZDF video (MP4+redirect)</li>
  <li name="mpegts">Test video (TS)</li>
  <li name="audiomp3">Audio stream (MP3)</li>
  <li name="audiomp4">Test audio (MP4)</li>
  <li name="https">HTTPS video (MP4)</li>
  <li name="irthd">IRT test (HD, MP4)</li>
  <li name="tsstream">Live stream test (TS, no seeking!)</li>
  <li name="relative">Relative video URL</li>
  <li name="dash">MPEG DASH test</li>
  <li name="queue">Video queuing test</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
