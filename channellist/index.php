<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
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
  showStatus(true, '');
  if (name=='list') {
    var vid = document.getElementById('video');
    var lst = null;
    try {
      lst = vid.getChannelConfig().channelList;
    } catch (e) {
      lst = null;
    }
    if (!lst) {
      showStatus(false, 'could not get channelList.');
      return;
    }
    try {
      var txt = 'Channel list items:';
      for (var i=0; i<lst.length && i<20; i++) {
        var ch = lst.item(i);
        txt += '<br />'+i+': '+ch.name;
      }
      showStatus(true, 'accessing channel list succeeded.');
      setInstr(txt);
    } catch (e) {
      showStatus(false, 'accessing channel list failed.');
      return;
    }
  } else if (name=='check') {
    var ccid;
    var vid = document.getElementById('video');
    try {
      ccid = vid.currentChannel.ccid;
    } catch (e) {
      ccid = null;
    }
    if (!ccid) {
      showStatus(false, 'cannot determine current channel');
      return;
    }
    var lst = null;
    try {
      lst = vid.getChannelConfig().channelList;
    } catch (e) {
      lst = null;
    }
    if (!lst) {
      showStatus(false, 'could not get channelList.');
      return;
    }
    var chobj = null;
    try {
      chobj = lst.getChannel(ccid);
    } catch (e) {
      showStatus(false, 'lst.getChannel(ccid) failed.');
      return;
    }
    if (chobj) {
      showStatus(true, 'channel for ccid='+ccid+' found.');
    } else {
      showStatus(false, 'channel for ccid='+ccid+' not found.');
    }
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
  <li name="list">Test 1: show channel list</li>
  <li name="check">Test 2: check getChannel(ccid)</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
