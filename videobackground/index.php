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
  setInstr('Below, you should see the lower part of the broadcast video, as the background of the body is set to transparent.');
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
}
//]]>
</script>

</head><body style="background: transparent;">

<div style="left: 0px; top: 0px; width: 1280px; height: 360px; background-color: #132d48;" />

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
