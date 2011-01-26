<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
window.onload = function() {
  menuInit();
  initVideo();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all tests. The last test will return to the main testsuite application. Navigate to the test using up/down, then press OK to start the test.');
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
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='params') {
    var param1 = "<?php echo htmlspecialchars($_REQUEST['param1']); ?>"; // set from URL query string
    var param2 = "<?php echo htmlspecialchars($_REQUEST['param2']); ?>"; // also set from URL query string
    if (param1=='value1' && param2=='value2') {
      showStatus(true, 'All parmeters were passed correctly.');
    } else {
      showStatus(false, 'Not all parameters passed to this application are correct: param1 is '+param1+', should be value1 / param2 is '+param2+', should be value2');
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
  } else if (name=='xmlreq1') {
    var req = new XMLHttpRequest();
    req.onreadystatechange = function() {
      if (req.readyState!=4) return;
      var succ = req.status==200 && req.responseText;
      if (succ) {
        showStatus(true, 'Call to application boundary URL succeeded.');
      } else {
        showStatus(false, 'Call to URL failed, even though signalled in AIT application boundary.');
      }
      req.onreadystatechange = null;
      req = null;
    };
    try {
      req.open('GET', boundarytesturls[0]);
      req.send(null);
    } catch (e) {
      showStatus(false, 'Request of URL failed, even though signalled in AIT application boundary.');
    }
  } else if (name=='xmlreq2') {
    var req = new XMLHttpRequest();
    req.onreadystatechange = function() {
      if (req.readyState!=4) return;
      var succ = req.status==200 && req.responseText;
      if (succ) {
        showStatus(false, 'Call to application boundary URL succeeded (but should fail, as URL is not within application boundary).');
      } else {
        showStatus(true, 'Call to URL failed, probably because it is not signalled in AIT application boundary (this is good!).');
      }
      req.onreadystatechange = null;
      req = null;
    };
    try {
      req.open('GET', boundarytesturls[1]);
      req.send(null);
    } catch (e) {
      showStatus(true, 'Request of URL failed, probably because it is not signalled in AIT application boundary (this is good!).');
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
  <li name="params">Test 1: check parameters</li>
  <li name="video">Test 2: check access to video</li>
  <li name="xmlreq1">Test 3: application boundary (ok)</li>
  <li name="xmlreq2">Test 4: application boundary (not ok)</li>
  <li name="runapp">Test 5: start testsuite app again</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
