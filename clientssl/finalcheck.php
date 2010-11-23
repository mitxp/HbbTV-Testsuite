<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
header('Pragma: no-cache');
header('Cache-Control: no-cache');

$DOMAINNAME = $_SERVER['SERVER_NAME'];
session_set_cookie_params(172800, '/', $DOMAINNAME, false);
session_start();

sendContentType();
openDocument();

$isok = $_SESSION['csslok'] && $_SESSION['REMOTE_ADDR']==$_SERVER['REMOTE_ADDR'];
?>
<script type="text/javascript">
//<![CDATA[
window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  showStatus(<?php echo $isok ? 'true' : 'false'; ?>, '<?php echo $isok ? 'Client SSL certificate could be verified.' : 'Client SSL verification information was not found in cookie.'; ?>');
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

</body>
</html>
