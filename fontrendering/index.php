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
  setInstr('Please select the desired test from the menu, then press OK.');
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
      menuSelect(selected+1);
    }
    return true;
  }
  return false;
}
function runStep(name) {
  if (name=='test1') {
    setInstr('Please check whether the width and the line-height of the rendered font matches the reference images.');
    document.getElementById('test1').style.visibility = 'inherit';
    document.getElementById('test2').style.visibility = 'hidden';
  } else if (name=='test2') {
    setInstr('Please check whether the different rendering styles (bold, italic, underlined) are rendered correctly.');
    document.getElementById('test1').style.visibility = 'hidden';
    document.getElementById('test2').style.visibility = 'inherit';
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
  <li name="test1">Font rendering width/height</li>
  <li name="test2">Font styles</li>
  <li name="exit">Return to test menu</li>
</ul>

<div id="test1" style="left: 0px; top: 0px; width: 1280px; height: 720px; visibility: hidden;">
  <div style="left: 102px; top: 279px; width: 385px; height: 18px; background-color: #0a1826;"></div>
  <div style="left: 102px; top: 313px; width: 385px; height: 18px; background-color: #0a1826;"></div>
  <div style="left: 102px; top: 377px; width: 340px; height: 17px; background-color: #0a1826;"></div>
  <div style="left: 102px; top: 411px; width: 340px; height: 17px; background-color: #0a1826;"></div>
  <div class="imgdiv" style="left: 100px; top: 250px; width: 419px; height: 193px; background-image: url(width.png);"></div>
  <div class="txtdiv" style="left: 102px; top: 310px; width: 600px; height: 30px; font-size: 24px; font-family: Tiresias;">The quick brown fox jumps äöüß#?!_</div>
  <div class="txtdiv" style="left: 102px; top: 408px; width: 600px; height: 30px; font-size: 24px; font-family: 'Letter Gothic 12 Pitch';">The quick brown fox äöüß#?!_</div>
  <div class="txtdiv" style="left: 102px; top: 460px; width: 600px; height: 120px; font-size: 20px; font-family: monospace;">font-family: monospace;<br />The quick brown fox jumps äöüß#?!_<br />12345678901234567890<br />890a23b56w890i23m567</div>
  <div class="txtdiv" style="left: 700px; top: 300px; width: 400px; height: 30px; color: #000000; font-size: 24px;">line-height test:</div>
  <div style="left: 680px; top: 330px; width: 440px; height: 240px; background-image: url(height.png);"></div>
  <div class="txtdiv" style="left: 700px; top: 330px; width: 400px; height: 240px; line-height: 30px; font-weight: bold; white-space: normal;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</div>
</div>

<div id="test2" style="left: 0px; top: 0px; width: 1200px; height: 720px; visibility: hidden;">
  <div style="left: 90px; top: 298px; width: 1180px; height: 32px; background-color: #1b4269;"></div>
  <div class="txtdiv" style="left: 100px; top: 300px; width: 270px; height: 30px;">plain:</div>
  <div class="txtdiv" style="left: 370px; top: 300px; width: 270px; height: 30px; font-weight: bold;">bold:</div>
  <div class="txtdiv" style="left: 640px; top: 300px; width: 270px; height: 30px; font-style: italic;">italic:</div>
  <div class="txtdiv" style="left: 910px; top: 300px; width: 270px; height: 30px; text-decoration: underline;">underline:</div>
  <div class="txtdiv" style="left: 100px; top: 340px; width: 270px; height: 30px; font-family: Tiresias;">Tiresias</div>
  <div class="txtdiv" style="left: 370px; top: 340px; width: 270px; height: 30px; font-family: Tiresias; font-weight: bold;">Tiresias</div>
  <div class="txtdiv" style="left: 640px; top: 340px; width: 270px; height: 30px; font-family: Tiresias; font-style: italic;">Tiresias</div>
  <div class="txtdiv" style="left: 910px; top: 340px; width: 270px; height: 30px; font-family: Tiresias; text-decoration: underline;">Tiresias</div>
  <div class="txtdiv" style="left: 100px; top: 380px; width: 270px; height: 30px; font-family: 'Letter Gothic 12 Pitch';">Letter Gothic 12 Pitch</div>
  <div class="txtdiv" style="left: 370px; top: 380px; width: 270px; height: 30px; font-family: 'Letter Gothic 12 Pitch'; font-weight: bold;">Letter Gothic 12 Pitch</div>
  <div class="txtdiv" style="left: 640px; top: 380px; width: 270px; height: 30px; font-family: 'Letter Gothic 12 Pitch'; font-style: italic;">Letter Gothic 12 Pitch</div>
  <div class="txtdiv" style="left: 910px; top: 380px; width: 270px; height: 30px; font-family: 'Letter Gothic 12 Pitch'; text-decoration: underline;">Letter Gothic 12 Pitch</div>
  <div class="txtdiv" style="left: 100px; top: 420px; width: 270px; height: 30px; font-family: monospace;">monospace</div>
  <div class="txtdiv" style="left: 370px; top: 420px; width: 270px; height: 30px; font-family: monospace; font-weight: bold;">monospace</div>
  <div class="txtdiv" style="left: 640px; top: 420px; width: 270px; height: 30px; font-family: monospace; font-style: italic;">monospace</div>
  <div class="txtdiv" style="left: 910px; top: 420px; width: 270px; height: 30px; font-family: monospace; text-decoration: underline;">monospace</div>
  <div class="txtdiv" style="left: 100px; top: 460px; width: 270px; height: 30px; font-family: sans-serif;">sans-serif</div>
  <div class="txtdiv" style="left: 370px; top: 460px; width: 270px; height: 30px; font-family: sans-serif; font-weight: bold;">sans-serif</div>
  <div class="txtdiv" style="left: 640px; top: 460px; width: 270px; height: 30px; font-family: sans-serif; font-style: italic;">sans-serif</div>
  <div class="txtdiv" style="left: 910px; top: 460px; width: 270px; height: 30px; font-family: sans-serif; text-decoration: underline;">sans-serif</div>
</div>

</body>
</html>
