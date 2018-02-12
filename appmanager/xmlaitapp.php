<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var testPrefix = <?php echo json_encode(getTestPrefix().'.startxml'); ?>;
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
  setInstr('Please run both tests. The last test will return to the main testsuite application. Navigate to the test using up/down, then press OK to start the test.');
  <?php if (array_key_exists('dvb', $_REQUEST) && $_REQUEST['dvb']==='t') {
    echo 'service1[3] = "5a0b02df79401f8312ffffffff";'."\n";
  } ?>
  runNextAutoTest();
};
var occsTimer = null;
function runStep(name) {
  var ch = null, vid;
  setInstr('Executing step...');
  showStatus(true, '');
  if (occsTimer) {
    clearTimeout(occsTimer);
    occsTimer = null;
  }
  if (name=='accvid') {
    vid = document.getElementById('video');
    try {
      if (vid.programmes && vid.programmes.length>0 && vid.programmes[0]) {
        showStatus(false, 'Application had access to EIT programmes of video/broadcast, but no access should have been granted (broadcast-independant app).');
        return;
      }
      if (vid.programmes) {
        showStatus(false, 'Did not get Exception while accessing video, but no access should have been granted (broadcast-independant app).');
        return;
      }
    } catch (e) {
    }
    showStatus(true, 'Access to video/broadcast denied via Exception (broadcast-independant app).');
  } else if (name=='switchch') {
    vid = document.getElementById('video');
    try {
      // OIPF 7.13.1.3 says that dsd is binary encoded, so decode hex string to latin-1 dsd
      var dsd = '';
      var hex = service1[3];
      for (var i=0; i+1<hex.length; i+=2) {
	dsd += String.fromCharCode(parseInt(hex.substring(i, i+2), 16));
      }
      ch = vid.createChannelObject(13, dsd, service1[2]);
    } catch (e) {
      showStatus(false, 'createChannelObject failed for service ID '+service1[2]);
      return;
    }
    if (!ch) {
      showStatus(false, 'createChannelObject did not return anything.');
      return;
    }
    try {
      vid.onChannelChangeSucceeded = function() {
        if (occsTimer) {
          clearTimeout(occsTimer);
          occsTimer = null;
        }
        vid.onChannelChangeSucceeded = null;
        setInstr('onChannelChangeSucceeded called, waiting for 10 sec for AIT retrieval...');
        occsTimer = setTimeout(function() {
          occsTimer = null;
          if (vid.currentChannel) {
            showStatus(true, 'setChannel succeeded.');
          } else {
            showStatus(false, 'setChannel succeeded but currentChannel is not available.');
          }
        }, 10000);
      };
      setInstr('Setting channel, waiting for onChannelChangeSucceeded...');
      occsTimer = setTimeout(function() {
        occsTimer = null;
        vid.onChannelChangeSucceeded = null;
        showStatus(false, 'did not retrieve onChannelChangeSucceeded event');
      }, 15000);
      vid.setChannel(ch, false);
    } catch (e) {
      showStatus(false, 'setChannel('+ch+') failed.');
      return;
    }
  } else if (name=='runapp') {
    var app;
    try {
      var mgr = document.getElementById('appmgr');
      app = mgr.getOwnerApplication(document);
    } catch (e) {
      showStatus(false, 'Getting owner application failed');
      return;
    }
    try {
      if (app.createApplication(myappurl, false)) {
        showStatus(true, 'Starting of Testsuite application, please stand by...');
        app.destroyApplication();
      } else {
        showStatus(false, 'Starting of Testsuite application failed.');
      }
    } catch (e) {
      showStatus(false, 'Exception while calling createApplication');
      return;
    }
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo videoObject(100, 480, 320, 180); ?>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="accvid">Test 1: check video access</li>
  <li name="switchch">Test 2: switch back to broadcast</li>
  <li name="runapp">Test 3: start testsuite app again</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
