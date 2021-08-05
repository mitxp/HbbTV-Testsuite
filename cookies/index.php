<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var testvalue = "abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
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
  setInstr('Please select the desired test from the menu, then press OK.');
  <?php if (array_key_exists('isok', $_REQUEST) && array_key_exists('found', $_REQUEST)) { ?>
    menuSelect(<?php echo (int)$_REQUEST['select']; ?>);
    showStatus(<?php echo $_REQUEST['isok']==='1'?'true':'false'; ?>, 'Cookie mxphbbtv was <?php echo $_REQUEST['found']==='1'?'':'not '; ?>found.');
  <?php } ?>
  runNextAutoTest();
};
function runStep(name) {
  var cvalue = false, isset = true;
  if (name=="clear") {
    cvalue = 'mxphbbtv=testsuite;expires='+(new Date(0).toGMTString());
    document.cookie = cvalue;
    cvalue += ";path=/";
    isset = false;
  } else if (name=="setsession") {
    cvalue = 'mxphbbtv=testsuite';
  } else if (name=="setexpire") {
    cvalue = 'mxphbbtv=testsuite;expires='+((new Date(new Date().getTime()+600000)).toGMTString());
  } else if (name=="setxhr") {
    setInstr('Making request...');
    var req = new XMLHttpRequest();
    req.onreadystatechange = function() {
      if (req.readyState!=4 || req.status!=200) return;
      req.onreadystatechange = null;
      req = null;
      performFinalCheck(isset);
    };
    req.open('GET', 'setcookie.php');
    req.send(null);
    return;
  } else if (name=="check") {
    cvalue = false;
  } else if (name=="storagewrite") {
    try {
      localStorage.setItem("mxphbbtv", testvalue);
      showStatus(true, 'localStorage.setItem succeeded.');
    } catch (e) {
      showStatus(false, 'localStorage.setItem failed: '+e);
    }
    return;
  } else if (name=="storageread") {
    try {
      var cvalue = localStorage.getItem("mxphbbtv");
      if (cvalue===testvalue) {
        showStatus(true, 'localStorage.getItem succeeded.');
      } else {
        showStatus(false, 'localStorage.getItem returned incorrect value: '+cvalue);
      }
    } catch (e) {
      showStatus(false, 'localStorage.getItem failed: '+e);
    }
    return;
  } else if (name=="storageremove") {
    try {
      localStorage.removeItem("mxphbbtv");
      showStatus(true, 'localStorage.removeItem succeeded.');
    } catch (e) {
      showStatus(false, 'localStorage.removeItem failed: '+e);
    }
    return;
  } else if (name=="storagedsmcc") {
    var succss = false;
    try {
      var mgr = document.getElementById('appmgr');
      var app = mgr.getOwnerApplication(document);
      if (app.createApplication(localstorageappurl, false)) {
        app.destroyApplication();
        succss = true;
      }
    } catch (e) {
      // failed
    }
    showStatus(succss, 'Starting DSM-CC application via appmgr '+(succss?'succeeded':'failed'));
    return;
  } else {
    showStatus(false, 'Invalid step name '+name);
    return;
  }
  if (cvalue) {
    try {
      document.cookie = cvalue;
    } catch (e) {
      showStatus(false, 'Could not set cookie: '+e);
      return;
    }
  }
  performFinalCheck(isset);
}
function performFinalCheck(isset) {
  setInstr('Going to different page to verify cookie...');
  setTimeout(function() {
    document.location.href = 'finalcheck.php?isset='+(isset?1:0)+'&back='+selected;
  }, 1000);
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="clear">Clear cookie</li>
  <li name="setsession">Set session cookie</li>
  <li name="setexpire">Set cookie with expire date</li>
  <li name="setxhr">Set cookie using XMLHttpRequest</li>
  <li name="check">Check whether cookie is set</li>
  <li name="storagewrite">Write to localStorage (HbbTV 1.3.1)</li>
  <li name="storageread">Read from localStorage (HbbTV 1.3.1)</li>
  <li name="storageremove">Remove from localStorage (HbbTV 1.3.1)</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
