<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var keynames = ['GREEN', 'LEFT', 'BACK', '9', 'STOP', 'FAST_FWD'];
var keymaskbits = ['GREEN', 'NAVIGATION', 'NAVIGATION', 'NUMERIC', 'VCR', 'VCR'];
var keycodes = [];
var keypressed = [];
var currentMask = 0;
var ksobj = false;
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;

window.onload = function() {
  menuInit();
  try {
    var app = document.getElementById('appmgr').getOwnerApplication(document);
    ksobj = app.privateData.keyset;
  } catch (e) {
    // ignore
  }
  for (var i=0; i<keynames.length; i++) {
    keypressed[i] = 0;
    keycodes[i] = -1;
    try {
      eval('keycodes['+i+'] = VK_'+keynames[i]);
    } catch (e) {
      // ignore
    }
    var maskname = keymaskbits[i];
    try {
      eval('keymaskbits['+i+'] = ksobj.'+maskname);
    } catch (e) {
      // ignore
    }
    if (isNaN(keymaskbits[i])) {
      showStatus(false, 'Cannot determine value for keyset.'+maskname+' - you will not be able to run this test.');
      keymaskbits[i] = 0x1000;
    }
  }
  registerKeyEventListener();
  initApp();
  setInstr('Please run all tests. For each test, press all keys. In each test, different keys should be passed to the application.<br />Note: If a key is not passed to the application, it might interact with the device (e.g. change channel).');
};
function isKeyNeedset(i) {
  if (currentMask===1 && keynames[i]==='LEFT') {
    return true;
  }
  return (currentMask&keymaskbits[i])>0;
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
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
    return true;
  } else if (kc==VK_RED && currentMask) {
    setKeyset(0x1+0x2+0x4+0x8+0x10);
    var success = true;
    for (var i=0; i<keynames.length; i++) {
      var needset = isKeyNeedset(i);
      success &= keypressed[i]==needset;
    }
    currentMask = 0;
    showStatus(success, success ? 'All keys received correctly.' : 'Not all keys were received correctly.');
    return true;
  }
  for (var i=0; i<keynames.length; i++) {
    if (kc==keycodes[i] && !keypressed[i]) {
      keypressed[i] = true;
      updateView();
    }
  }
  return true;
}
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  for (var i=0; i<keynames.length; i++) {
    keypressed[i] = 0;
  }
  if (name==="x") {
    currentMask = 1;
    try {
      ksobj.setValue(currentMask, [VK_LEFT]);
      updateView();
    } catch (e) {
      showStatus(false, 'Cannot call setValue on keyset object.');
    }
  } else {
    currentMask = parseInt(name);
    try {
      ksobj.setValue(currentMask);
      updateView();
    } catch (e) {
      showStatus(false, 'Cannot call setValue on keyset object.');
    }
  }
}
function updateView() {
  var txtyes = '';
  var txtno = '';
  for (var i=0; i<keynames.length; i++) {
    var txtkey = 'VK_'+keynames[i];
    var needset = isKeyNeedset(i);
    if (keypressed[i]) {
      txtkey = '<span style="color: #'+(needset?'10ff40':'ff6030')+'">'+txtkey+'<'+'/span>';
    }
    if (needset) {
      txtyes += (txtyes?', ':'')+txtkey;
    } else {
      txtno += (txtno?', ':'')+txtkey;
    }
  }
  if (!txtno) {
    txtno = '(none)';
  }
  var txt = 'The following keys should get passed to the application. As soon as they are pressed, they are marked green:<br /><b>'+txtyes+'<'+'/b><br /><br />The following keys should not get passed to the application (please press them, too):<br /><b>'+txtno+'<'+'/b><br /><br />When done press the red color key.';
  setInstr(txt);
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>

<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="319">Test 1: all keys</li>
  <li name="31">Test 2: navigation/color keys</li>
  <li name="3">Test 3: only red/green color keys</li>
  <li name="x">Test 4: only red color + left key</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
