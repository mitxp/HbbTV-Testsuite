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
    setInstr('Please check visual result.');
    document.getElementById('test1').style.display = 'block';
    document.getElementById('test2').style.display = 'none';
  } else if (name=='test2') {
    setInstr('Please check visual result.');
    document.getElementById('test1').style.display = 'none';
    document.getElementById('test2').style.display = 'block';
  }
}

//]]>
</script>
<style rel="stylesheet" type="text/css">
@keyframes swoosh { 100% {top: 100px; } }
</style>
<link href="http://fonts.googleapis.com/css?family=Satisfy" rel="stylesheet" />

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="test1">Show CSS3 tests 1</li>
  <li name="test2">Show CSS3 tests 2</li>
  <li name="exit">Return to test menu</li>
</ul>

<div id="test1" style="left: 0px; top: 0px; width: 1280px; height: 720px; display: none;">
  <div style="left: 700px; top: 200px; width: 300px; height: 30px; padding: 3px 0px 0px 10px; color: #ffffff; background-color: #3073b8; text-shadow: 3px 3px 2px #000000;">CSS3 text shadow</div>
  <div style="left: 700px; top: 250px; width: 300px; height: 30px; padding: 3px 0px 0px 10px; color: #ffffff; background-color: #3073b8; box-shadow: 3px 3px 3px 0px rgba(0,0,0,0.75);">CSS3 box shadow</div>
  <div style="left: 700px; top: 300px; width: 300px; height: 30px; padding: 3px 0px 0px 10px; color: #ffffff; background-color: #3073b8; border-radius: 10px;">CSS3 border radius</div>
  <div style="left: 700px; top: 350px; width: 300px; height: 30px; padding: 3px 0px 0px 10px; color: #ffffff; background: linear-gradient(to right, #3073b8, #89c3ff);">CSS3 linear gradient</div>
  <div style="left: 700px; top: 400px; width: 300px; height: 80px; padding: 3px 0px 0px 10px; color: #ffffff; background-color: #3073b8;">CSS3 blur filter
    <img src="../animation/logo.png" style="left: 10px; top: 30px; width: 256px; height: 44px; position: absolute; filter: blur(5px);" />
  </div>
  <div style="left: 700px; top: 500px; width: 300px; height: 80px; padding: 3px 0px 0px 10px; color: #ffffff; background-color: #3073b8;">CSS3 animation
    <img src="../animation/logo.png" style="left: 10px; top: 30px; width: 256px; height: 44px; position: absolute; animation: swoosh 2s ease-in infinite; animation-direction: alternate;" />
  </div>
  <div style="left: 700px; top: 600px; width: 300px; height: 80px; padding: 3px 0px 0px 10px; color: #ffffff; background-color: #3073b8;">CSS3 translate2d
    <img src="../animation/logo.png" style="left: 30px; top: 30px; width: 256px; height: 44px; background-color: #d0d0d0; position: absolute; transform: rotate(-7deg);" />
  </div>
</div>
<div id="test2" style="left: 0px; top: 0px; width: 1280px; height: 720px; display: none;">
  <div style="left: 700px; top: 200px; width: 300px; height: 80px; padding: 3px 0px 0px 10px; color: #ffffff; background: repeating-linear-gradient(-65deg, #89c3ff, #89c3ff 5px, #3073b8 5px, #3073b8 10px);">CSS3 striped gradient
    <img src="../animation/logo.png" style="left: 10px; top: 30px; width: 256px; height: 44px; position: absolute;" />
  </div>
  <div style="left: 700px; top: 300px; width: 300px; height: 47px; padding: 8px 0px 3px 10px; color: #ffffff; background-color: #3073b8; font-family: Satisfy, cursive; font-weight: normal; font-size: 36px; line-height: 44px;">CSS3 web font</div>
  <div style="left: 700px; top: 370px; width: 300px; height: 110px; padding: 3px 0px 3px 10px; color: #000000; background:url('../animation/logo.png') no-repeat center 50px, url('paperbg.jpg') no-repeat 0px 0px;">CSS3 multiple backgrounds</div>
</div>

</body>
</html>
