<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var vid;
var intType = -1;
var hbbtv12 = false;
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;
window.onload = function() {
  vid = document.getElementById('video');
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
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. Please note: even though subtitles are signalled in PMT, most of the time no subtitles are shown.');
  try {
    var cfg = document.getElementById("oipfcfg").configuration;
    var uagent = ""+navigator.userAgent;
    if (uagent.indexOf("HbbTV/")>=0 && uagent.indexOf("HbbTV/1.1")<0 && (cfg.preferredAudioLanguage || cfg.preferredSubtitleLanguage)) {
      hbbtv12 = true;
    }
  } catch (ignore) {
  }
  runNextAutoTest();
};
function getFakeIntType(type) {
  if (type=='vid') {
    return 0;
  } else if (type=='aud') {
    return 1;
  } else if (type=='sub') {
    return 2;
  }
}
function getFakeComponents(type) {
  var ret = [ ];
  var i, vc, key, expected = vbcomponents[type];
  for (i=expected.length-1; i>=0; i--) {
    vc = { };
    vc.type = getFakeIntType(type);
    for (key in expected[i]) {
      vc[key] = expected[i][key];
    }
    ret.push(vc);
  }
  return ret;
}
function getIntType(type) {
  try {
    if (type=='vid') {
      return vid.COMPONENT_TYPE_VIDEO;
    } else if (type=='aud') {
      return vid.COMPONENT_TYPE_AUDIO;
    } else if (type=='sub') {
      return vid.COMPONENT_TYPE_SUBTITLE;
    }
  } catch (e) {
    // handled below
  }
  return -1;
}
function getComponents(type) {
  var intType = getIntType(type);
  if (intType<0 || typeof(intType)=='undefined') {
    showStatus(false, 'COMPONENT_TYPE for '+type+' undefined');
    return false;
  }
  var vc = false;
  try {
    vc = vid.getComponents(intType);
  } catch (e) {
    showStatus(false, 'call getComponents failed.');
    return false;
  }
  if (!vc) {
    showStatus(false, 'call getComponents('+type+') returned null.');
    return false;
  }
  var expected = vbcomponents[type];
  if (vc.length!=expected.length) {
    showStatus(false, 'call getComponents('+type+') returned '+vc.length+' elements, expected are '+expected.length+' elements.');
    return false;
  }
  var i, j, key, foundStreams = [];
  for (i=0; i<expected.length; i++) {
    var expectStream = expected[i];
    foundStreams[i] = null;
    for (j=0; j<vc.length; j++) {
      var checkvc = vc[j];
      var found = true;
      try {
        if (intType!=checkvc.type) {
          showStatus(false, 'call getComponents('+type+') returned invalid component of type '+checkvc.type);
          return false;
        }
	for (key in expectStream) {
	  var vcvalue = 'undefined';
	  eval('vcvalue = checkvc.'+key+';');
	  if (key=='language') {
            vcvalue = vcvalue.toLowerCase();
          } else if (key=='aspectRatio') {
            vcvalue = Math.round(vcvalue*100.0)/100.0;
          }
	  if (vcvalue!=expectStream[key]) {
	    found = false;
	    break;
	  }
	}
      } catch (e) {
        showStatus(false, 'problem while accessing properties of getComponents('+type+')['+j+']');
        return false;
      }
      if (found) {
	foundStreams[i] = checkvc;
	break;
      }
    }
    if (foundStreams[i]==null) {
      var descrStr = 'type='+intType;
      for (key in expectStream) {
        descrStr += ', '+key+'='+expectStream[key];
      }
      showStatus(false, 'cannot find the following AVComponent: '+descrStr);
      return false;
    }
  }
  return foundStreams;
}
function selectComponents(type, index) {
  var activevc, i, vc = getComponents(type);
  var intType = getIntType(type);
  if (!vc) {
    return false;
  }
  for (i=0; i<vc.length; i++) {
    try {
      vid.unselectComponent(vc[i]);
    } catch (e) {
      showStatus(false, 'cannot unselect component '+vc[i]);
      return;
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
      return;
    }
  }
  setTimeout(function() {
    try {
      activevc = vid.getCurrentActiveComponents(intType);
    } catch (e) {
      showStatus(false, 'error while calling getCurrentActiveComponents('+intType+')');
      return;
    }
    if (!activevc) {
      showStatus(false, 'getCurrentActiveComponents did not return anything unselecting all components');
      return;
    }
    if (activevc.length!=0 && (!hbbtv12||index<0||type==='vid')) {
      showStatus(false, 'getCurrentActiveComponents returned a non-empty collection after unselecting all components');
      return;
    }
    if (index>=0 && index<vc.length) {
      var shouldBe = vc[index];
      try {
        vid.selectComponent(shouldBe);
      } catch (e) {
        showStatus(false, 'cannot select component '+type+index+' = '+vc[index]);
        return false;
      }
      setTimeout(function() {
        try {
          activevc = vid.getCurrentActiveComponents(intType);
        } catch (e) {
          showStatus(false, 'error while calling getCurrentActiveComponents('+intType+') after selecting component');
          return false;
        }
        if (!activevc || activevc.length!=1) {
          showStatus(false, 'getCurrentActiveComponents returned an invalid array after selecting a component');
          return false;
        }
        var key, selectedIs = activevc[0];
        for (key in vbcomponents[type][0]) {
          var shouldBeStr = 'undefined1';
          var selectedIsStr = 'undefined2';
          eval('shouldBeStr = shouldBe.'+key+';');
          eval('selectedIsStr = selectedIs.'+key+';');
          if (shouldBeStr != selectedIsStr) {
            showStatus(false, 'getCurrentActiveComponents returned invalid component: '+key+' is '+selectedIsStr+', but should be '+shouldBeStr);
            return;
          }
        }
        showStatus(true, 'component should now be selected.');
      }, 2000);
      setInstr('Waiting for component selection to finish...');
      return;
    }
    showStatus(true, 'component should now be selected.');
  }, 2000);
}

function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='getvid') {
    if (getComponents('vid')) {
      showStatus(true, 'All video components retrieved correctly');
    }
  } else if (name=='getaud') {
    if (getComponents('aud')) {
      showStatus(true, 'All audio components retrieved correctly');
    }
  } else if (name=='getsub') {
    if (getComponents('sub')) {
      showStatus(true, 'All subtitle components retrieved correctly');
    }
  } else if (name=='selvideo0') {
    selectComponents('vid', -1);
  } else if (name=='selvideo1') {
    selectComponents('vid', 0);
  } else if (name=='selaudio0') {
    selectComponents('aud', -1);
  } else if (name=='selaudio1') {
    selectComponents('aud', 0);
  } else if (name=='selaudio2') {
    selectComponents('aud', 1);
  } else if (name=='selsub0') {
    selectComponents('sub', -1);
  } else if (name=='selsub1') {
    selectComponents('sub', 0);
  } else if (name=='setfull') {
    try {
      document.getElementById('video').setFullScreen(true);
      showStatus(true, 'Setting fullScreen(true) mode succeeded');
    } catch (e) {
      showStatus(false, 'Setting fullScreen(true) mode failed');
    }
  }
}

//]]>
</script>

</head><body style="background: transparent;">

<div style="left: 0px; top: 0px; width: 1280px; height: 550px; background-color: #132d48;" />

<?php echo videoObject(700, 300, 320, 180);
echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="getvid">Test 1: get video AVComponents</li>
  <li name="getaud">Test 2: get audio AVComponents</li>
  <li name="getsub">Test 3: get subtitle AVComponents</li>
  <li name="selvideo0" automate="visual">Test 4: unselect video</li>
  <li name="selvideo1" automate="visual">Test 5: select video</li>
  <li name="selaudio0" automate="audio">Test 6: unselect audio</li>
  <li name="selaudio1" automate="audio">Test 7: select audio1</li>
  <li name="selaudio2" automate="audio">Test 8: select audio2</li>
  <li name="setfull">Test 9: make video fullscreen</li>
  <li name="selsub1" automate="visual">Test 10: select subtitles</li>
  <li name="selsub0" automate="visual">Test 11: unselect subtitles</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
