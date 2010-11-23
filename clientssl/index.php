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
var logtxt = 'Performing validation...';

window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
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
};
function handleKeyCode(kc) {
  if (kc==VK_UP) {
    menuSelect(selected-1);
    return true;
  } else if (kc==VK_DOWN) {
    menuSelect(selected+1);
    return true;
  } else if (kc==VK_ENTER) {
    document.location.href = '../index.php';
    return true;
  }
  return false;
}
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
        document.location.href = 'finalcheck.php';
      } else {
	showStatus(false, 'Client SSL certificate verification failed.');
        setInstr(logtxt);
      }
    };
    r.open('GET', cookiecheckurl);
    r.send(null);
    debug('Get URL: '+cookiecheckurl);
  };
  r.open('GET', certcheckurl);
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
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>
<div class="txtdiv" style="left: 110px; top: 200px; width: 400px; height: 400px; font-face: bold">Important: For this test to succeed, you need to send us the client SSL certificate stored on your device first.<br /><br />If you don't have one, you can temporarily use our test certificate. Download it from https://itv.mit-xperts.com/clientssl/issue/dload/index.php?id=1330221600</div>

</body>
</html>
