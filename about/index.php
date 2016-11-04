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
  runNextAutoTest();
};
function runStep(name) {
  var txt = '';
  if (name=='info') {
    txt = '<u>About this testsuite:<'+'/u><br /'+'>This test suite is for HbbTV terminal developers to test their implementation of the HbbTV standard. Although this test suite contains a lot of test, it is not complete. It contains the most important interoperability issues disvocered in current applications. Tested parts are not covered by 100%, but the most importent checks are performed.<br /'+'><br /'+'>More information about missing and untested parts can be found at<br /'+'>https://github.com/mitxp/HbbTV-Testsuite/wiki/TODOs<br /'+'><br /'+'><u>Privacy information:<'+'/u><br /'+'>Test results may get reported back to the web server serving this testsuite. Reported information is: IP address, User agent, test PIN, test ID, test result.';
  } else if (name=='contrib') {
    txt = "Contributors to this testsuite are so far:<ul><li>Dora Babu<"+"/li><li>PPAT<"+"/li><li>Sungguk Lim<"+"/li><"+"/ul><br /><br />In case you have an additional test(s), we would be very happy to include your test in this testsuite. Please contact us (see cwleft sidee) to discuss further details.";
  } else if (name=='automation') {
    txt = '<u>About:<'+'/u><br /'+'>Automation allows you to run all tests without user interaction.<br /'+'><br /'+'><u>Usage:<'+'/u><br /'+'>To run automated tests, press the blue color key in the main menu. To interrupt the automated testing, press the blue color key again.<br /'+'><br /'+'><u>Test results:<'+'/u><br /'+'>You will need an account to retrieve your test results. Please contact us for plans and prices.<br /'+'><br /'+'><u>Excluded tests:<'+'/u><br /'+'>The following tests cannot be automated and are not included in an automation test run: Key codes / key events, Keyset mask, Keypress events, AIT transport protocol order, localStorage in DSMCC';
  }
  document.getElementById('txtdiv').innerHTML = txt;
  menuSelect(selected+1);
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div class="txtdiv" style="left: 110px; top: 290px; width: 400px; height: 420px;"><u>Contact / Imprint:</u><br />
MIT-xperts GmbH<br />
Poccistr. 13<br />
80336 Munich, Germany<br />
info &#x40; mit-xperts&#x2e;com<br />
Phone: +49 89 76756380<br /><br />
<u>This project is open source:</u><br />
Contribute and share at<br />https://github.com/mitxp/HbbTV-Testsuite<br /><br />
<u>Talk to us:</u><br />
In case you think a test may need a fix, please contact us (or submit a fix yourself).
</div>

<div id="txtdiv" class="txtdiv" style="left: 650px; top: 90px; width: 450px; height: 600px;">Please select your desired topic in the menu on the left.</div>

<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="info">Show general info</li>
  <li name="contrib">Show contributors</li>
  <li name="automation">Automated testing</li>
  <li name="exit">Return to test menu</li>
</ul>

</body>
</html>
