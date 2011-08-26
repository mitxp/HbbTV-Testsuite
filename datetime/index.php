<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

$time = time();
$hrs = (int)date('H', $time);
$min = (int)date('i', $time);
?>
<script type="text/javascript">
//<![CDATA[
var tim = <?php echo $time; ?>;
var hrs = <?php echo $hrs; ?>;
var min = <?php echo $min; ?>;

window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  var myd = new Date();
  var myt = Math.floor(myd.getTime()/1000);
  if (myt-120<streamtime[0] && myt+120>streamtime[0]) {
    tim = streamtime[0];
    hrs = streamtime[1];
    min = streamtime[2];
  }
  if (tim-300>myt || myt>tim+300) {
    showStatus(false, 'Date.getTime() GMT timestamp is not valid. Is '+myt+', should be '+tim+'.');
    return;
  }
  var localremote = hrs*60+min;
  var localremotestr = (hrs<10?'0':'')+hrs+(min<10?':0':':')+min;
  hrs = myd.getHours();
  min = myd.getMinutes();
  var localmy = hrs*60+min;
  var localmystr = (hrs<10?'0':'')+hrs+(min<10?':0':':')+min;
  if (localremote-5>localmy || localmy>localremote+5) {
    showStatus(false, 'GMT timestamp is correct, but timezone is wrong. Local time is '+localmystr+', should be '+localremotestr+'.');
    return;
  }
  showStatus(true, 'GMT timestamp and local time within 5 minutes of correct time.');
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
