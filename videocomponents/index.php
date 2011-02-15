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
window.onload = function() {
  vid = document.getElementById('video');
  menuInit();
  initVideo();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. Please note: this test is still incomplete, we will have a new broadcast stream with 2 audio components and a subtitle component in January 2011. Until then, subtitle support cannot be tested.');
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
function getComponents(type) {
  var vc;
  intType = -1;
  try {
    if (type=='vid') {
      intType = vid.COMPONENT_TYPE_VIDEO;
    } else if (type=='aud') {
      intType = vid.COMPONENT_TYPE_AUDIO;
    } else if (type=='sub') {
      intType = vid.COMPONENT_TYPE_SUBTITLE;
    }
  } catch (e) {
    // handled below
  }
  if (intType<0 || typeof(intType)=='undefined') {
    showStatus(false, 'COMPONENT_TYPE for '+type+' undefined');
    return false;
  }
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
  var foundStreams = [];
  for (var i=0; i<expected.length; i++) {
    var expectStream = expected[i];
    foundStreams[i] = null;
    for (var j=0; j<vc.length; j++) {
      var checkvc = vc[j];
      var found = true;
      try {
        if (intType!=checkvc.type) {
          showStatus(false, 'call getComponents('+type+') returned invalid component of type '+checkvc.type);
          return false;
        }
	for (var key in expectStream) {
	  var vcvalue = 'undefined';
	  eval('vcvalue = checkvc.'+key+';');
	  if (key=='language') vcvalue = vcvalue.toLowerCase();
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
      for (var key in expectStream) {
        descrStr += ', '+key+'='+expectStream[key];
      }
      showStatus(false, 'cannot find the following AVComponent: '+descrStr);
      return false;
    }
  }
  return foundStreams;
}
function selectComponents(type, index) {
  var vc = getComponents(type);
  if (!vc) return false;
  for (var i=0; i<vc.length; i++) {
    try {
      vid.unselectComponent(vc[i]);
    } catch (e) {
      showStatus(false, 'cannot unselect component '+vc[i]);
      return false;
    }
  }
  var activevc;
  try {
    activevc = vid.getCurrentActiveComponents(intType);
  } catch (e) {
    showStatus(false, 'error while calling getCurrentActiveComponents('+intType+')');
    return false;
  }
  if (activevc && activevc.length!=0) {
    showStatus(false, 'getCurrentActiveComponents returned a non-empty array after unselecting all components');
    return false;
  }
  if (index>=0 && index<vc.length) {
    var shouldBe = vc[index];
    try {
      vid.selectComponent(shouldBe);
    } catch (e) {
      showStatus(false, 'cannot select component '+vc[index]);
      return false;
    }
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
    var selectedIs = activevc[0];
    for (var key in vbcomponents[type][0]) {
      var shouldBeStr = 'undefined1';
      var selectedIsStr = 'undefined2';
      eval('shouldBeStr = shouldBe.'+key+';');
      eval('selectedIsStr = selectedIs.'+key+';');
      if (shouldBeStr != selectedIsStr) {
        showStatus(false, 'getCurrentActiveComponents returned invalid component: '+key+' is '+selectedIsStr+', but should be '+shouldBeStr);
        return false;
      }
    }
  }
  showStatus(true, 'component should now be selected.');
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
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo videoObject(100, 480, 320, 180);
echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="getvid">Test 1: get video AVComponents</li>
  <li name="getaud">Test 2: get audio AVComponents</li>
  <li name="getsub">Test 3: get subtitle AVComponents</li>
  <li name="selvideo0">Test 4: unselect video</li>
  <li name="selvideo1">Test 5: select video</li>
  <li name="selaudio0">Test 6: unselect audio</li>
  <li name="selaudio1">Test 7: select audio1</li>
  <li name="selaudio2">Test 8: select audio2</li>
  <li name="selaudio2">Test 8: select audio2</li>
  <li name="selsub1">Test 9: select subtitles</li>
  <li name="selsub0">Test 10: unselect subtitles</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
