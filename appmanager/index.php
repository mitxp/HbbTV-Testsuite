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
  registerMenuListener(function(liid) {
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
  });
  initApp();
  setInstr('Please run both tests. Each test will destroy this application, so you need to return to this menu and execute the other test afterwards. Navigate to the test using up/down, then press OK to start the test.');
  try {
    document.getElementById('video').bindToCurrentChannel();
  } catch (ignore) {
  }
  runNextAutoTest();
};
var countdownTimeout = null;
function countDown(secs, msg, runfunc) {
  countdownTimeout = null;
  setInstr('In '+secs+' seconds, '+msg);
  if (secs<=0) {
    runfunc();
    return;
  }
  secs--;
  countdownTimeout = setTimeout(function() {countDown(secs, msg, runfunc);}, 1000);
}
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (countdownTimeout) {
    clearTimeout(countdownTimeout);
  }
  if (name=='destroy') {
    countDown(5, 'this application will be destroyed, and the autostart application should be launched: '+autostartappname+'. You should see the broadcast video during the transition.', function() {
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
    });
  } else if (name=='start1') {
    countDown(5, 'this application will be destroyed, and a different testsuite application should be launched. Please run all test steps in that application. The last test step will return to this application.', function() {
      var succss = false;
      try {
        var mgr = document.getElementById('appmgr');
        var app = mgr.getOwnerApplication(document);
        if (app.createApplication(otherappurl+'?param2=value2#foo', false)) {
          succss = true;
        }
      } catch (e) {
        // failed
      }
      if (succss) {
        showAppStartStatus(true, 'Starting application...');
        app.destroyApplication();
      } else {
        showStatus(false, 'Starting application via appmgr failed');
      }
    });
  } else if (name=='start2') {
    countDown(5, 'this application will be destroyed, and a different testsuite application should be launched. Please run all test steps in that application. The last test step will return to this application.', function() {
      var succss = false;
      try {
        var mgr = document.getElementById('appmgr');
        var app = mgr.getOwnerApplication(document);
        if (app.createApplication(otherappurl+'#foo2', false)) {
          succss = true;
        }
      } catch (e) {
        // failed
      }
      if (succss) {
        showAppStartStatus(true, 'Starting application...');
        app.destroyApplication();
      } else {
        showStatus(false, 'Starting application via appmgr failed');
      }
    });
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
    var app, params = '';
    try {
      var mgr = document.getElementById('appmgr');
      app = mgr.getOwnerApplication(document);
    } catch (e) {
      showStatus(false, 'Getting owner application failed');
      return;
    }
    try {
      var channelIdType = document.getElementById('video').currentChannel.idType;
      if (channelIdType===12 || channelIdType===16) {
        params = '?dvb=t';
      }
    } catch (e) {
    }
    try {
      if (app.createApplication('xmlait.php/ait.aitx'+params, false)) { // ETSI TS 102 809 requires extension .aitx
        showAppStartStatus(true, 'Starting of application via XML succeeded, please stand by...');
        app.destroyApplication();
      } else {
        showStatus(false, 'Starting of application via XML failed.');
      }
    } catch (e) {
      showStatus(false, 'Exception while calling createApplication');
      return;
    }
  } else if (name=='hide') {
    countDown(5, 'the application will be hidden. It should re-appear after 3 seconds.', function() {
      var succss = true;
      try {
        document.getElementById('video').release();
        document.getElementById('appmgr').getOwnerApplication(document).hide();
      } catch (e) {
        succss = false;
      }
      countdownTimeout = setTimeout(function() {
        try {
          document.getElementById('appmgr').getOwnerApplication(document).show();
          document.getElementById('video').bindToCurrentChannel();
        } catch (e) {
          succss = false;
        }
        if (succss) {
           showStatus(true, 'Application should have been invisible (broadcast video fullscreen visible) for 3 seconds.');
        } else {
           showStatus(false, 'Application.hide() or Application.show() call failed.');
        }
      }, 3000);
    });
  } else if (name=='freemem') {
    try {
      var freemem = document.getElementById('appmgr').getOwnerApplication(document).privateData.getFreeMem();
      showStatus(true, 'ApplicationPrivateData.getFreeMem() returned: '+freemem);
    } catch (e) {
      showStatus(false, 'ApplicationPrivateData.getFreeMem() call failed.');
    }
  } else if (name=='tpprio') {
    countDown(5, 'this application will be destroyed, and a DSM-CC-based testsuite application should be launched. The application should not be launced from broadband.', function() {
      var succss = false;
      try {
        var mgr = document.getElementById('appmgr');
        var app = mgr.getOwnerApplication(document);
        if (app.createApplication(dsmccpreferappurl, false)) {
          succss = true;
        }
      } catch (e) {
        // failed
      }
      showAppStartStatus(succss, 'Starting application via appmgr '+(succss?'succeeded':'failed'));
      if (succss) {
        app.destroyApplication();
      }
    });
  } else if (name=='bcaccess') {
    setInstr('Switching to broadcast-independant app...');
    document.location.href = "bcindepapp.php";
  }
}

//]]>
</script>

</head><body>

<div id="bgdiv" style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>
<?php echo videoObject(700, 590, 160, 90); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="destroy" automate="ignore">Test 1: destroy application</li>
  <li name="start1">Test 2: start other app (params+hash)</li>
  <li name="start2">Test 3: start other app (hash only)</li>
  <li name="startfail">Test 4: start non-existing app</li>
  <li name="startxml" automate="ignore">Test 5: start app via XML AIT</li>
  <li name="hide" automate="visual">Test 6: app.hide() and show()</li>
  <li name="freemem">Test 7: app.getFreeMem()</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
