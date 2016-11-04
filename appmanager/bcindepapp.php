<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var origSvc = null;
var testPrefix = <?php echo json_encode(getTestPrefix().'.bcaccess'); ?>;
window.onload = function() {
  menuInit();
  initVideo();
  registerMenuListener(function(liid) {
    if (liid=='exit') {
      if (origSvc) {
        try {
          vid.setChannel(origSvc, false);
        } catch (e) {
        }
      }
      document.location.href='index.php';
    } else {
      runStep(liid);
    }
  });
  initApp();
  setInstr('Please run all tests. The last test will return to the main testsuite application. Navigate to the test using up/down, then press OK to start the test.');
  origSvc = document.getElementById('video').currentChannel;
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  var vid = document.getElementById('video');
  if (name=='selnull') {
    try {
      vid.setChannel(null, false);
      showStatus(2, 'Test only passed if you can neither hear nor see the broadcast video!');
    } catch (e) {
      showStatus(false, 'Setting null channel failed.');
    }
  } else if (name=='selsvc') {
    if (!origSvc) {
      showStatus(false, 'No original service object found.');
      return;
    }
    try {
      vid.setChannel(origSvc, false);
      showStatus(2, 'Test only passed if you now can see and hear the broadcast video!');
    } catch (e) {
      showStatus(false, 'Returning back to original channel failed.');
    }
  } else if (name=='bcvideo') {
    vid.onChannelChangeSucceeded = function() {
      vid.onChannelChangeSucceeded = null;
      vid.onChannelChangeError = null;
      showStatus(false, 'No SecurityException received. Application had access to video/broadcast, but no access should have been granted (broadcast-independant app).');
    };
    vid.onChannelChangeError = function() {
      vid.onChannelChangeSucceeded = null;
      vid.onChannelChangeError = null;
      showStatus(false, 'No SecurityException received, but at least access to video/broadcast was denied via onChannelChangeError (broadcast-independant app).');
    };
    try {
      vid.bindToCurrentChannel();
      setInstr('Did not get the expected SecurityException (see A.2.4.2 in HbbTV spec), waiting for channel change event...');
    } catch (e) {
      vid.onChannelChangeSucceeded = null;
      vid.onChannelChangeError = null;
      showStatus(true, 'Access to video/broadcast denied via Exception (broadcast-independant app).');
    }
  } else if (name=='eitpf') {
    try {
      if (vid.programmes && vid.programmes.length>0 && vid.programmes[0]) {
        showStatus(false, 'Application had access to EIT programmes of video/broadcast, but no access should have been granted (broadcast-independant app).');
        return;
      }
    } catch (e) {
    }
    showStatus(true, 'Access to video/broadcast denied via Exception (broadcast-independant app).');
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
  <li name="selnull">Test 1: Go broadcast-independant</li>
  <li name="bcvideo">Test 2: Test access to broadcast video</li>
  <li name="eitpf">Test 3: Test access to EIT p/f data</li>
  <li name="selsvc">Test 4: Go broadcast-related</li>
  <li name="exit">Test 5: Return to HbbTV testsuite</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
