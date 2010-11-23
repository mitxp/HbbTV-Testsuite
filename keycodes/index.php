<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var keynames = ['ENTER', 'LEFT', 'DOWN', 'PLAY', 'PAUSE', 'STOP', 'FAST_FWD', 'REWIND', 'BACK', '0', '5', '9', 'GREEN', 'YELLOW', 'RED'];
var keycodes = [];
var nextidx = 0;

window.onload = function() {
  registerKeyEventListener();
  initApp();
  for (var i=0; i<keynames.length; i++) {
    keycodes[i] = -1;
    try {
      eval('keycodes['+i+'] = KeyEvent.VK_'+keynames[i]);
      // do not use: eval('keycodes['+i+'] = VK_'+keynames[i]);
    } catch (e) {
      // ignore
    }
  }
  updateMsg();
  setKeyset(0x1+0x2+0x4+0x8+0x10+0x20+0x100);
};

function handleKeyCode(kc) {
  if (kc==VK_RED) {
    setInstr('Returning to test menu...');
    setTimeout(function() {
      document.location.href = '../index.php';
    }, 2000);
    return true;
  } else if (kc==VK_BLUE) {
    if (nextidx+1<keynames.length) {
      nextidx++;
      showStatus(true, '');
      updateMsg();
    }
    return true;
  } else if (kc==keycodes[nextidx]) {
    nextidx++;
    showStatus(true, 'Correct key event received, continuing with next key...');
    updateMsg();
    return true;
  }
  showStatus(false, 'Invalid key code '+kc+' recieved.');
  updateMsg();
  return false;
}
function updateMsg(success) {
  setInstr('Please press key <b>VK_'+keynames[nextidx]+'<'+'/b><br />on your remote control.<br /><br />Press VK_RED to cancel this test, or press VK_BLUE to skip this key code and advance to the next.');
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
