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
  initVideo();
  registerMenuListener(function(liid) {
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
  });
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. Please note that these tests require DSM-CC support.');
  runNextAutoTest();
};
function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  var urlprefix = 'dvb://'+service1[0].toString(16)+'.'+service1[1].toString(16)+'.'+service1[2].toString(16)+'.'+dsmccctag.toString(16)+'/';
  if (name=='file') {
    checkUrl(urlprefix+'index.html', '<html');
  } else if (name=='directory') {
    checkUrl(urlprefix, 'index.html');
  }
}
function checkUrl(geturl, checkcontent) {
  req = new XMLHttpRequest();
  req.onreadystatechange = function() {
    if (req.readyState!=4) return;
    var isok = req.status==200 && req.responseText.indexOf(checkcontent)>=0;
    if (isok) {
      showStatus(true, 'URL was retrieved correctly');
    } else {
      showStatus(false, 'URL was not received correctly, status was '+req.status);
    }
    req.onreadystatechange = null;
    req = null;
  };
  setInstr('Requesting URL '+geturl);
  try {
    req.open('GET', geturl);
    req.send(null);
  } catch (e) {
    showStatus(false, 'Cannot make XMLHttpRequest for URL '+geturl);
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo videoObject(100, 480, 320, 180);
echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="file">Test 1: get DSM-CC file</li>
  <li name="directory">Test 2: get DSM-CC directory</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
