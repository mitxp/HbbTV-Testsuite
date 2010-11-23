<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");

sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  setInstr('Verifying cookie...');
  var found = false;
  var allCookies = document.cookie.split(';');
  for (var i=0; i<allCookies.length; i++) {
    var c = allCookies[i];
    while (c.charAt(0)==' ') {
      c = c.substring(1, c.length);
    }
    if (c.indexOf('mxphbbtv=testsuite')==0) {
      found = true;
      break;
    }
  }
  showStatus(found, 'Cookie mxphbbtv was '+(found?'':'not ')+'found.');
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
