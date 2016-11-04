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
var myd = new Date();
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
  var myt = Math.floor(myd.getTime()/1000);
  if (myt-120<streamtime[0] && myt+120>streamtime[0]) {
    tim = streamtime[0];
    hrs = streamtime[1];
    min = streamtime[2];
  }
  runNextAutoTest();
};
function printTime(tim) {
  var h, m, s, d = new Date(tim*1000);
  h = d.getHours();
  m = d.getMinutes();
  s = d.getSeconds();
  return tim+"["+(h<10?"0":"")+h+":"+(m<10?"0":"")+m+":"+(s<10?"0":"")+s+"]";
}
function runStep(name) {
  var myt, localremote, localremotestr, h, m, localmy, localmystr;
  if (name==='time') {
    myt = Math.floor(myd.getTime()/1000);
    if (tim-300>myt || myt>tim+300) {
      showStatus(false, 'Date.getTime() UTC timestamp is not valid. Is '+printTime(myt)+', should be '+printTime(tim)+'.');
    } else {
      showStatus(true, 'Date.getTime() UTC timestamp is within 5 minutes of correct time.');
    }
  } else if (name==='tzone') {
    localremote = hrs*60+min;
    localremotestr = (hrs<10?'0':'')+hrs+(min<10?':0':':')+min;
    h = myd.getHours();
    m = myd.getMinutes();
    localmy = h*60+m;
    localmystr = (h<10?'0':'')+h+(m<10?':0':':')+m;
    if (localremote-5>localmy || localmy>localremote+5) {
      showStatus(false, 'Timezone is wrong. Local time is '+localmystr+', should be '+localremotestr+'.');
    } else {
      showStatus(true, 'Local time within 5 minutes of correct time: '+localmystr);
    }
  }
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="time">Check UTC timestamp</li>
  <li name="tzone">Check timezone</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
