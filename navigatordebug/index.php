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
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.');
  runNextAutoTest();
};
function runStep(name) {
  if (name=='navigator') {
    try {
      var txt = 'appName='+navigator.appName+', appVersion='+navigator.appVersion;
      txt = txt.replace(/&/g,"&amp;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").split('<').join('&lt;');
      showStatus(true, txt);
    } catch (e) {
      showStatus(false, 'navigator object not available.');
    }
  } else {
    try {
      debug('test');
      showStatus(true, 'debug() call succeeded.');
    } catch (e) {
      showStatus(false, 'debug() call failed.');
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
  <li name="navigator">Navigator (OIPF DAE 7.15.4)</li>
  <li name="debug">Debug (OIPF DAE 7.15.5)</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
