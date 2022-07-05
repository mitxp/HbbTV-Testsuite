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
  testPrefix += '.start'+('#foo2'==window.location.hash?'2':'1');
  menuInit();
  initVideo();
  registerMenuListener(function(liid) {
    runStep(liid);
  });
  initApp();
  setInstr('Please run all tests. The last test will return to the main testsuite application. Navigate to the test using up/down, then press OK to start the test.');
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='params') {
    var param1 = "<?php echo htmlspecialchars($_REQUEST['param1']??''); ?>"; // set from URL query string
    var param2 = "<?php echo htmlspecialchars($_REQUEST['param2']??''); ?>"; // also set from URL query string
    var shouldbe2 = '#foo2'==window.location.hash ? '' : 'value2';
    if (param1=='value1' && param2==shouldbe2) {
      showStatus(true, 'All parmeters were passed correctly.');
    } else {
      showStatus(false, 'Not all parameters passed to this application are correct: param1 is '+param1+', should be value1 / param2 is '+param2+', should be '+shouldbe2);
    }
  } else if (name=='video') {
    try {
      var ch = document.getElementById('video').currentChannel;
      var succ = ((ch.onid==service1[0]&&ch.tsid==service1[1]&&ch.sid==service1[2])
               || (ch.onid==service2[0]&&ch.tsid==service2[1]&&ch.sid==service2[2]));
      if (!succ) {
        showStatus(false, 'Detected channel DVB triple does not match actual channel information');
        return;
      }
    } catch (e) {
      showStatus(false, 'cannot determine current channel');
      return;
    }
    try {
      var vid = document.getElementById('video');
      vid.style.left = '700px';
      vid.style.top = '200px';
      vid.style.width = '320px';
      vid.style.height = '160px';
      showStatus(true, 'Please check visual result: did the video move from the left part of the screen to the right part?');
    } catch (e) {
      showStatus(false, 'Could not change video position');
      return;
    }
  } else if (name=='exit') {
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
  } else if (name=='checkhash') {
    if (window.location && '#foo'==window.location.hash) {
      showStatus(true, '#foo hash is set correctly from createApplication call.');
    } else if (window.location && '#foo2'==window.location.hash) {
      showStatus(true, '#foo2 hash is set correctly from createApplication call.');
    } else {
      showStatus(false, '#foo hash is NOT set correctly from createApplication call. window.location.hash='+window.location.hash);
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
  <li name="params">Test 1: check parameters</li>
  <li name="checkhash">Test 2: check location.hash</li>
  <li name="video" automate="visual">Test 3: check access to video</li>
  <li name="exit">Test 4: start testsuite app again</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
