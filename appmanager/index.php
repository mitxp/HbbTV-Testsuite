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
  registerKeyEventListener();
  initApp();
  setInstr('Please run both tests. Each test will destroy this application, so you need to return to this menu and execute the other test afterwards. Navigate to the test using up/down, then press OK to start the test.');
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
  if (name=='destroy') {
    setInstr('In 5 seconds, this application will be destroyed, and the autostart application should be launched: '+autostartappname+'. You should see the broadcast video during the transition.');
    setTimeout(function() {
      var succss = false;
      try {
        var mgr = document.getElementById('appmgr');
        var app = mgr.getOwnerApplication(document);
        app.destroyApplication();
        succss = true;
      } catch (e) {
        // failed
      }
      showStatus(succss, 'call to destroyApplication() '+(succss?'succeeded':'failed'));
    }, 5000);
  } else if (name=='start') {
    setInstr('In 5 seconds, this application will be destroyed, and a different testsuite application should be launched. Please run all test steps in that application. The last test step will return to this application.');
    setTimeout(function() {
      var succss = false;
      try {
        var mgr = document.getElementById('appmgr');
        var app = mgr.getOwnerApplication(document);
        if (app.createApplication(otherappurl, false)) {
          app.destroyApplication();
          succss = true;
        }
      } catch (e) {
        // failed
      }
      showStatus(succss, 'Starting application via appmgr '+(succss?'succeeded':'failed'));
    }, 5000);
  } else if (name=='startfail') {
    var app;
    try {
      var mgr = document.getElementById('appmgr');
      app = mgr.getOwnerApplication(document);
    } catch (e) {
      showStatus(false, 'Getting owner application failed');
      return;
    }
    try {
      if (app.createApplication('dvb://current.ait/13.278', false)) {
        showStatus(false, 'Starting of invalid application succeeded (this should fail!)');
      } else {
        showStatus(true, 'Starting of invalid application failed (this should fail!)');
      }
    } catch (e) {
      showStatus(false, 'Exception while calling createApplication');
      return;
    }
  } else if (name=='startxml') {
    var app;
    try {
      var mgr = document.getElementById('appmgr');
      app = mgr.getOwnerApplication(document);
    } catch (e) {
      showStatus(false, 'Getting owner application failed');
      return;
    }
    try {
      if (app.createApplication('xmlait.php', false)) {
        app.destroyApplication();
        showStatus(true, 'Starting of application via XML succeeded, please stand by...');
      } else {
        showStatus(false, 'Starting of application via XML failed.');
      }
    } catch (e) {
      showStatus(false, 'Exception while calling createApplication');
      return;
    }
  } else if (name=='hide') {
    var succss = true;
    try {
      document.getElementById('appmgr').getOwnerApplication(document).hide();
    } catch (e) {
      succss = false;
    }
    setTimeout(function() {
      try {
        document.getElementById('appmgr').getOwnerApplication(document).show();
      } catch (e) {
        succss = false;
      }
      if (succss) {
         showStatus(true, 'Application should have been invisible (broadcast video fullscreen visible) for the last 5 seconds.');
      } else {
         showStatus(false, 'Application.hide() or Application.show() call failed.');
      }
    }, 3000);
  } else if (name=='freemem') {
    try {
      var freemem = document.getElementById('appmgr').getOwnerApplication(document).getFreeMem();
      showStatus(true, 'Application.getFreeMem() returned: '+freemem);
    } catch (e) {
      showStatus(false, 'Application.getFreeMem() call failed.');
    }
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="destroy">Test 1: destroy application</li>
  <li name="start">Test 2: start other app</li>
  <li name="startfail">Test 3: start non-existing app</li>
  <li name="startxml">Test 4: start app via XML AIT</li>
  <li name="hide">Test 5: app.hide() and show()</li>
  <li name="freemem">Test 6: app.getFreeMem()</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
