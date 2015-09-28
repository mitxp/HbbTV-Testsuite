<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");

$id = strtolower($_REQUEST['id']);
$id = addcslashes($id, "\0..\37'\\");
$i = strpos($id, '_');
if ($i>0) {
  $vidtype = substr($id, 0, $i);
} else {
  $vidtype = '';
}
if ($vidtype!='mp4' && $vidtype!='ts') {
  $vidtype = 'dash';
}
$dirname = $vidtype;
if ($id=='dash_playready') {
  $dirname .= '_enc';
}

$channels = array();
if ($id=='dash_multi_rate') {
  $hint = 'DASH rate switching. The DUT SHALL correctly decode and playback the video content, and at least one audio track. The DUT should switch to the best quality audio content (music) if the bandwidth is available.';
} else if ($id=='mp4_multi_lang' || $id=='ts_multi_lang') {
  $hint = 'Language selection. The DUT SHALL correctly recognise the presence of two audio tracks with languages deu and eng respectively which queried using getComponents, it SHALL correctly switch audio track using selectComponent, it MAY provide proprietary means to enable the user switching between audio tracks.';
  $channels['DEU/Channel check'] = '"encrypted":false, "language":"deu"';
  $channels['ENG/Music'] = '"encrypted":false, "language":"eng"';
} else if ($id=='dash_multi_lang') {
  $hint = 'Language selection. The DUT SHALL correctly recognise the presence of two audio tracks with languages fra and eng respectively which queried using getComponents, it SHALL correctly switch audio track using selectComponent, it MAY provide proprietary means to enable the user switching between audio tracks.';
  $channels['ENG/Channel check'] = '"encrypted":false, "language":"eng"';
  $channels['FRA/Music'] = '"encrypted":false, "language":"fra"';
} else if ($id=='mp4_multi_codec' || $id=='ts_multi_codec' || $id=='dash_multi_codec') {
  $hint = 'Codec selection. The DUT SHALL correctly recognise the presence of two audio tracks with codecs ec-3 and aac respectively which queried using getComponents, it SHALL correctly switch audio track using selectComponent';
  $channels['EAC3, 5.1ch'] = '"audioChannels":5';
  $channels['HE-AAC, 2.0ch'] = '"audioChannels":2';
} else if (strstr($dirname, '_enc')) {
  $hint = 'DRM Playback. The DUT SHALL correctly decode and playback the audio and video content IF DRM system is supported.';
} else {
  $hint = 'Basic Playback. The DUT SHALL correctly decode and playback the audio and video content.';
}
$channelkeys = array_keys($channels);


sendContentType();
openDocument();
?>
<script type="text/javascript">
//<![CDATA[
var id = '<?php echo $id; ?>';
var type = '<?php echo $vidtype; ?>';
var mtype = '<?php echo $vidtype=='dash' ? 'application/dash+xml' : ($vidtype=='ts'?'video/mpeg':'video/'.$vidtype); ?>';
var vidurl = 'http://streaming.dolby.com/ftproot/mitXperts/<?php echo $dirname.'/'.$id.'.'.($vidtype=='dash'?'mpd':$vidtype); ?>';
var hbbtv12 = false;
var vid = null;
var vidtimer = null;
var testTimeout = null;
var expected = [ <?php
  $firstch = true;
  foreach ($channels as $key=>$value) {
    if ($firstch) {
      $firstch = false;
    } else {
      echo ', ';
    }
    echo '{"displayname":"'.$key.'", '.$value.'}';
  }
?> ];

window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  showVid();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test.');
  try {
    var cfg = document.getElementById("oipfcfg").configuration;
    var uagent = ""+navigator.userAgent;
    if (uagent.indexOf("HbbTV/")>=0 && uagent.indexOf("HbbTV/1.1")<0 && (cfg.preferredAudioLanguage || cfg.preferredSubtitleLanguage)) {
      hbbtv12 = true;
    }
  } catch (ignore) {
  }
};
function showVid() {
  vid = document.createElement("object");
  vid.type = mtype;
  vid.setAttribute("type", vid.type);
  vid.setAttribute("style", "position: absolute; left: 0px; top: 0px; width: 416px; height: 234px; outline: transparent;");
  vid.style.position = "absolute";
  vid.style.left = "0px";
  vid.style.top = "0px";
  vid.style.width = "416px";
  vid.style.height = "234px";
  vid.style.outline = "transparent";
  document.getElementById("vidcontainer").appendChild(vid);
}
function showVidData() {
  if (vidtimer) {
    clearTimeout(vidtimer);
    vidtimer = null;
  }
  document.getElementById("vidstatus").innerHTML = 'Video play state = '+vid.playState+'<br />play position = '+vid.playPosition;
  vidtimer = setTimeout(function() { vidtimer=null; showVidData(); }, 1000);
}
function gotoPos(scnds) {
  try {
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
  testTimeout = setTimeout(function() {
    testTimeout = false;
    if (vid.playState && (vid.playState==2 || vid.playState==3 || vid.playState==4)) {
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
function compareComponent(checkvc, expectedIdx, intType) {
  var key, vcvalue, expectStream = expected[expectedIdx];
  try {
    if (intType!==checkvc.type) {
      showStatus(false, 'call getComponents('+intType+') returned invalid component of type '+checkvc.type);
      return -1;
    }
    for (key in expectStream) {
      if (key==='displayname') {
        continue; // skip this
      }
      vcvalue = 'undefined';
      eval('vcvalue = checkvc.'+key+';');
      if (key==='language') {
        vcvalue = vcvalue.toLowerCase();
      }
      if (key==='audioChannels') {
        vcvalue = Math.floor(vcvalue);
      }
      if (vcvalue!==expectStream[key]) {
        return 0;
      }
    }
  } catch (e) {
    showStatus(false, 'problem while accessing properties of getComponents('+intType+')');
    return -1;
  }
  return 1;
}
function getActiveComponentIdx() {
  var i, activevc, found, intType = vid.COMPONENT_TYPE_AUDIO;
  try {
    activevc = vid.getCurrentActiveComponents(intType);
  } catch (e) {
    showStatus(false, 'error while calling getCurrentActiveComponents('+intType+') after selecting component');
    return false;
  }
  if (!activevc || activevc.length!==1) {
    return -2;
  }
  for (i=0; i<expected.length; i++) {
    found = compareComponent(activevc[0], i, intType);
    if (found>0) {
      return i;
    }
  }
  return -1;
}
function showActiveComponent() {
  var i;
  getComponents();
  i = getActiveComponentIdx();
  if (i<-1) {
    showStatus(true, "No component is currently active.");
  } else if (i===-1) {
    showStatus(false, "Unable to determine active component.");
  } else if (i===false) {
    // error already displayed
  } else {
    showStatus(true, "Active component: "+expected[i].displayname);
  }
}
function getComponents() {
  var intType = vid.COMPONENT_TYPE_AUDIO, vc = false, i, j;
  var expectStream, checkvc, found, key, descrStr;
  if (intType<0 || (typeof intType)==='undefined') {
    showStatus(false, 'COMPONENT_TYPE_AUDIO undefined');
    return false;
  }
  try {
    vc = vid.getComponents(intType);
  } catch (e) {
    showStatus(false, 'call getComponents failed.');
    return false;
  }
  if (!vc) {
    showStatus(false, 'call getComponents('+intType+') returned null.');
    return false;
  }
  if (vc.length!==expected.length) {
    showStatus(false, 'call getComponents('+intType+') returned '+vc.length+' elements, expected are '+expected.length+' elements.');
    return false;
  }
  var foundStreams = [];
  for (i=0; i<expected.length; i++) {
    expectStream = expected[i];
    foundStreams[i] = null;
    for (j=0; j<vc.length; j++) {
      checkvc = vc[j];
      found = compareComponent(checkvc, i, intType);
      if (found<0) {
        return false; // compare failed
      }
      if (found) {
        foundStreams[i] = checkvc;
        break;
      }
    }
    if (foundStreams[i]===null) {
      descrStr = 'type='+intType;
      for (key in expectStream) {
        if (key==='displayname') {
          continue; // skip this
        }
        descrStr += ', '+key+'='+expectStream[key];
      }
      showStatus(false, 'cannot find the following AVComponent: '+descrStr);
      return false;
    }
  }
  return foundStreams;
}
function selectComponents(index) {
  var vc = getComponents();
  var i, shouldBe, activevc, intType = vid.COMPONENT_TYPE_AUDIO;
  if (!vc) {
    showStatus(false, 'no components');
    return false;
  }
  for (i=0; i<vc.length; i++) {
    try {
      vid.unselectComponent(vc[i]);
    } catch (e) {
      showStatus(false, 'cannot unselect component '+vc[i]);
      return false;
    }
  }
  if (hbbtv12 && index<0 && type!=='vid') {
    // We need to use unselectComponent(componentType) in HbbTV 1.2 due to
    // OIPF DAE Vol. 5, section 7.16.5.1.3, unselectComponent(AVComponent):
    // "If property preferredAudioLanguage in the Configuration object
    // (see section 7.3.1.1) is set then unselecting a specific component
    // returns to the default preferred audio language."
    // Only this call ensures that no component of this type is selected.
    try {
      vid.unselectComponent(intType);
    } catch (e) {
      showStatus(false, 'cannot unselect component by type '+vc[i]);
      return false;
    }
  }
  setTimeout(function() {selectComponentsStage2(index, vc);}, 1000);
}
function selectComponentsStage2(index, vc) {
  var i, shouldBe, activevc, intType = vid.COMPONENT_TYPE_AUDIO;
  try {
    activevc = vid.getCurrentActiveComponents(intType);
  } catch (e) {
    showStatus(false, 'error while calling getCurrentActiveComponents('+intType+')');
    return false;
  }
  if (activevc && activevc.length!==0 && (!hbbtv12 || index<0)) {
    showStatus(false, 'getCurrentActiveComponents returned a non-empty array after unselecting all components');
    return false;
  }
  if (index>=0 && index<vc.length) {
    shouldBe = vc[index];
    try {
      vid.selectComponent(shouldBe);
    } catch (e) {
      showStatus(false, 'cannot select component '+index+' = '+vc[index]);
      return false;
    }
    setTimeout(function() {
      var i = getActiveComponentIdx();
      if (i===-2) {
        showStatus(false, 'error while calling getCurrentActiveComponents('+intType+') after selecting component');
      } else if (i===-1) {
        showStatus(false, 'getCurrentActiveComponents returned invalid component after selecting desired component');
      } else if (i===false) {
        // error already displayed
      } else if (i===index) {
        showStatus(true, 'component should now be selected.');
      } else {
        showStatus(false, "Active component: "+expected[i].displayname+", expected: "+expected[index].displayname);
      }
    }, 2000);
    setInstr('Waiting for component selection to finish...');
    return;
  }
  showStatus(true, 'component should now be selected.');
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
      document.location.href = 'index.php';
    } else {
      runStep(liid);
    }
    return true;
  }
  return false;
}
function runStep(name) {
  if (name==="playvid") {
    try {
      if (vidtimer) {
        clearTimeout(vidtimer);
        vidtimer = null;
      }
      vid.stop();
    } catch (e) {
      // ignore
    }
    vid.data = vidurl;
    try {
      vid.play(1);
      showVidData();
      showStatus(true, 'Video should be playing now');
    } catch (e) {
      showStatus(false, 'Video playback failed: '+e);
    }
  } else if (name==="gotopos30") {
    gotoPos(30);
  } else if (name==="goch0") {
    selectComponents(-1);
  } else if (name==="goch1") {
    selectComponents(0);
  } else if (name==="goch2") {
    selectComponents(1);
  } else if (name==="showch") {
    showActiveComponent();
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<div style="left: 700px; top: 36px; width: 295px; height: 60px; background-image: url(dolby.png);"></div>
<div id="vidcontainer" style="left: 700px; top: 220px; width: 416px; height: 234px; background-color: #000000;"></div>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 114px; width: 400px; height: 360px;"></div>
<div id="vidstatus" class="txtdiv" style="left: 700px; top: 460px; width: 416px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="playvid">Start video</li>
<?php if (count($channels)) { ?>
  <li name="goch0">Unselect all audio channels</li>
  <li name="goch1">Select audio: <?php echo $channelkeys[0]; ?></li>
  <li name="goch2">Select audio: <?php echo $channelkeys[1]; ?></li>
  <li name="showch">Query active audio channel</li>
<?php } ?>
  <li name="gotopos30">Seek to pos. 00:00:30</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 530px; width: 400px; height: 160px;"></div>
<div id="hint" class="txtdiv" style="left: 100px; top: 420px; width: 400px; height: 260px;"><?php echo $hint; ?></div>

</body>
</html>
