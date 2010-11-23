<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
require("validate.php");
sendContentType();
openDocument();

$uagent = $_SERVER['HTTP_USER_AGENT'];
$uagentok = validateUserAgent($uagent, $uagentmsg);
if ($uagentok) {
  $uagentmsg = 'User agent '.htmlspecialchars($uagent).' is valid.';
} else {
  $uagentmsg = 'User agent '.htmlspecialchars($uagent).' is invalid: '.htmlspecialchars($uagentmsg);
}

$id = rand();
$videourl = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/checkvideo.php/test.mp4?id='.$id;
?>
<script type="text/javascript">
//<![CDATA[
window.onload = function() {
  menuInit();
  registerKeyEventListener();
  initApp();
  var success = <?php echo $uagentok ? 'true' : 'false'; ?>;
  showStatus(success, '<?php echo $uagentmsg; ?>');
  if (success) {
    setInstr('Browser user agent OK, checking video player user agent...');
    var vid = document.getElementById('video');
    vid.data = '<?php echo $videourl; ?>';
    try {
      vid.play(1);
    } catch (e) {
      // ignore
    }
    setTimeout(function() {checkPlayer();}, 5000);
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
function checkPlayer() {
  req = new XMLHttpRequest();
  req.onreadystatechange = function() {
    if (req.readyState!=4 || req.status!=200) return;
    var s = req.responseText;
    if (s=='OK') {
      showStatus(true, 'All user agents OK');
    } else {
      showStatus(false, 'Video player user agent invalid: '+s);
    }
    req.onreadystatechange = null;
    req = null;
  }
  req.open('GET', 'getresult.php?id=<?php echo $id; ?>');
  req.send(null);
}
//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="video" type="video/mp4" style="position: absolute; left: 100px; top: 480px; width: 320px; height: 180px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
