<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
header('Pragma: no-cache');
header('Cache-Control: no-cache');

sendContentType();
openDocument();

$baseurl = '//'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/';
?>
<script type="text/javascript">
//<![CDATA[
var baseurl = '<?php echo $baseurl; ?>';
var certcheckurl = 'https:'+baseurl+'ssl/certcheck.php';
var cookiecheckurl = 'http:'+baseurl+'cookiecheck.php';
var logtxt = '';
var isok = '<?php echo array_key_exists('isok', $_REQUEST) ? (int)$_REQUEST['isok'] : ''; ?>';
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
  if (isok!=='') {
    menuSelect(1);
  }
  runNextAutoTest();
}
function runStep(name) {
  if (name==='validate') {
    logtxt = 'Performing validation...';
    setInstr(logtxt);
    var r = new XMLHttpRequest();
    r.onreadystatechange = function() {
      if (r.readyState!=4) return;
      debug('Killed old cookie');
      r.onreadystatechange = null;
      r = null;
      performauth();
    };
    r.open('GET', cookiecheckurl+'?kill=1');
    r.send(null);
  } else if (name==='check') {
    showStatus(isok==='1'?true:2, isok==='1' ? 'Client SSL certificate could be verified.' : 'Client SSL verification information was not found in cookie. As this certificate is not mandatory, this might be expected.');
  } else if (name==='loadimg') {
    var ee, timr, e = document.getElementById("instr");
    e.innerHTML = "";
    ee = document.createElement("img");
    timr = setTimeout(function() {
      timr = null;
      ee.onload = null;
      showStatus(false, 'Image could not be loaded.');
    }, 5000);
    ee.onload = function() {
      if (timr) {
        clearTimeout(timr);
      }
      if (ee.width) {
        showStatus(true, 'Image could be loaded.');
      } else {
        showStatus(false, 'Image onload was called, but image has no width.');
      }
    };
    ee.src = "https://itv.mit-xperts.com/hbbtvtest/logo.png";
    e.appendChild(ee);
  }
};
function performauth() {
  var r = new XMLHttpRequest();
  r.onreadystatechange = function() {
    if (r.readyState!=4) return;
    debug('reply = '+r.responseText);
    r.onreadystatechange = null;
    r = new XMLHttpRequest();
    r.onreadystatechange = function() {
      if (r.readyState!=4) return;
      var authok = r.responseText=='OK';
      debug('reply = '+r.responseText);
      debug('authok = '+authok);
      r.onreadystatechange = null;
      r = null;
      if (authok) {
	showStatus(true, 'Client SSL URL could be verified, please stand by for final check...');
        setTimeout(function() {
          document.location.href = 'finalcheck.php';
        }, 100);
      } else {
	showStatus(2, 'Client SSL certificate verification failed. As this certificate is not mandatory, this might be expected.');
      }
    };
    r.open('GET', cookiecheckurl);
    r.send(null);
    debug('Get URL: '+cookiecheckurl);
  };
  r.open('GET', certcheckurl);
  r.withCredentials = "true";
  r.send(null);
  debug('Get URL: '+certcheckurl);
}
function debug(txt) {
  logtxt += '<br />&gt;'+txt;
  setInstr(logtxt);
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="validate">Test ClientSSL</li>
  <li name="check">Test test result cookie</li>
  <li name="loadimg">Load image via HTTPS</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>
<div class="txtdiv" style="left: 110px; top: 300px; width: 400px; height: 400px; font-face: bold">Important: For this test to succeed, you need to send us the client SSL certificate stored on your device first.<br /><br />If you don't have one, you can temporarily use our test certificate. Download it from https://itv.mit-xperts.com/clientssl/issue/dload/sample.php</div>

</body>
</html>
