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
  var seventurl = isdsmcc ? 'sevent' : 'sevent.php?ctag='+seventctag;
  setInstr('Registering StreamEventListener...');
  var vid = document.getElementById('video');
  var listener = function(e) {
    if (!e) {
      showStatus(false, 'StreamEventListener was called, but no event was passed.');
      return;
    }
    var succss = e.data && 'testevent'==e.name && 'trigger'==e.status;
    showStatus(succss, 'Received StreamEvent. data='+e.data+', text='+e.text+', status='+e.status);
  };
  try {
    vid.addStreamEventListener(seventurl, 'testevent', listener);
    setInstr('StreamEventListener added, waiting for first event...');
  } catch (e) {
    showStatus(false, 'Could not add StreamEventListener');
  }
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

<?php echo videoObject(100, 480, 320, 180); ?>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
