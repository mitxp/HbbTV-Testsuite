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
  setInstr('Please check whether the width and the line-height of the rendered font matches the reference images.');
};
function handleKeyCode(kc) {
  if (kc==VK_UP) {
    menuSelect(selected-1);
    return true;
  } else if (kc==VK_DOWN) {
    menuSelect(selected+1);
    return true;
  } else if (kc==VK_ENTER) {
    var liid = opts[selected].getAttribute('name');
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
    return true;
  }
  return false;
}
function runStep(name) {
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

<div class="imgdiv" style="left: 100px; top: 200px; width: 419px; height: 193px; background-image: url(width.png);"></div>
<div class="txtdiv" style="left: 102px; top: 260px; width: 600px; height: 30px; font-size: 24px; font-family: Tiresias;">The quick brown fox jumps äöüß#?!_</div>
<div class="txtdiv" style="left: 102px; top: 358px; width: 600px; height: 30px; font-size: 24px; font-family: 'Letter Gothic 12 Pitch';">The quick brown fox äöüß#?!_</div>
<div class="txtdiv" style="left: 102px; top: 460px; width: 600px; height: 120px; font-size: 20px; font-family: monospace;">font-family: monospace;<br />The quick brown fox jumps äöüß#?!_<br />12345678901234567890<br />890a23b56w890i23m567</div>

<div class="txtdiv" style="left: 700px; top: 300px; width: 400px; height: 30px; color: #000000; font-size: 24px;">line-height test:</div>
<div style="left: 680px; top: 330px; width: 440px; height: 240px; background-image: url(height.png);"></div>
<div class="txtdiv" style="left: 700px; top: 330px; width: 400px; height: 240px; line-height: 30px; font-weight: bold; white-space: normal;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</div>

</body>
</html>
