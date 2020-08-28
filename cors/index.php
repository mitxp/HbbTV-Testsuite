<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
header('Pragma: no-cache');
header('Cache-Control: no-cache');

sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var logtxt = '';
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
  runNextAutoTest();
}
function runStep(name) {
  if (name==='testhttp' || name==='testhttps') {
    var testurl = (name==='testhttps' ? 'https' : 'http') + '://www.mit-xperts.com/hbbtvtest/cors.php';
    logtxt = 'Connecting to '+testurl+' ...';
    setInstr(logtxt);
    var r = new XMLHttpRequest();
    var timr = setTimeout(function() {
      timr = null;
      r.onreadystatechange = null;
      showStatus(false, 'CORS Request timed out');
    }, 5000);
    r.onreadystatechange = function() {
      if (r.readyState!=4) return;
      if (timr) {
        clearTimeout(timr);
      }
      r.onreadystatechange = null;
      if (r.status===200 && r.responseText==="1") {
        showStatus(true, 'CORS Request succeeded');
      } else {
        showStatus(false, 'CORS Request failed with status='+r.status+', responseText='+r.responseText);
      }
    };
    r.open('POST', testurl);
    r.send(null);
  }
};
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="testhttp">Test Cross-Origin XHR via HTTP</li>
  <li name="testhttps">Test Cross-Origin XHR via HTTPS</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
