<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var animtimer = false;
var textsize = 0;
var textopacity = 0;
var textpos = 0;
var imgsize = 0;
var moretxt = false;
var aimg = false;

window.onload = function() {
  moretxt = document.getElementById('moretxt');
  aimg = document.getElementById('aimg');
  menuInit();
  registerKeyEventListener();
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions. Please note that this tests the performance of your implementation. The specification does not say anything about how fast this animation should be. Most implementations still perform quite well.');
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
  setInstr('Executing step...');
  showStatus(true, '');
  // stop old animation
  if (animtimer) {
    clearTimeout(animtimer);
    animtimer = false;
    moretxt.style.fontSize = 22;
    moretxt.style.opacity = 1;
    moretxt.style.left = '100px';
    aimg.style.left = '122px';
    aimg.style.top = '28px';
    aimg.style.width = '256px';
    aimg.style.height = '44px';
    aimg.style.opacity = 1;
  }

  if (name=='tsize') {
    animTextSize();
  } else if (name=='topacity') {
    animTextOpacity();
  } else if (name=='tpos') {
    animTextPos();
  } else if (name=='isize') {
    animImageSize();
  } else if (name=='iopacity') {
    animImageOpacity();
  }
}
function animTextSize() {
  if (textsize>9) {
    textsize = -9;
  } else {
    textsize++;
  }
  moretxt.style.fontSize = (22+(textsize<0?-textsize:textsize))+'px';
  animtimer = setTimeout(function() {animTextSize();}, 40);
}
function animTextOpacity() {
  if (textopacity>19) {
    textopacity = -19;
  } else {
    textopacity++;
  }
  moretxt.style.opacity = (textopacity<0?-textopacity:textopacity)/20;
  animtimer = setTimeout(function() {animTextOpacity();}, 40);
}
function animTextPos() {
  if (textpos>19) {
    textpos = -19;
  } else {
    textpos++;
  }
  moretxt.style.left = (100+(textpos<0?-textpos:textpos)*4)+'px';
  animtimer = setTimeout(function() {animTextPos();}, 40);
}
function animImageSize() {
  if (imgsize>17) {
    imgsize = -17;
  } else {
    imgsize++;
  }
  var w = 1+(imgsize<0?-imgsize:imgsize)*0.05;
  var h = Math.floor(44*w);
  w = Math.floor(256*w);
  aimg.style.left = 250-Math.floor(w/2)+'px';
  aimg.style.top = 50-Math.floor(h/2)+'px';
  aimg.style.width = w+'px';
  aimg.style.height = h+'px';
  animtimer = setTimeout(function() {animImageSize();}, 40);
}
function animImageOpacity() {
  if (textopacity>19) {
    textopacity = -19;
  } else {
    textopacity++;
  }
  aimg.style.opacity = (textopacity<0?-textopacity:textopacity)/20;
  animtimer = setTimeout(function() {animImageOpacity();}, 40);
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>

<div id="moretxt" class="txtdiv txtlg" style="left: 100px; top: 440px; width: 450px; height: 158px;">Lorem ipsum dolor sit amet,<br />consectetur adipiscing elit.<br />Praesent ac nulla sit amet felis<br />condimentum tempus.<br />Praesent dictum molestie<br />at luctus mauris adipiscing non. Vestibulum rutrum aliquam hendrerit.</div>
<div style="left: 700px; top: 480px; width: 500px; height: 100px; background-color: #ffffff;">
  <img id="aimg" src="logo.png" style="left: 122px; top: 28px; width: 256px; height: 44px; position: absolute;" />
</div>

<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="tsize">Test 1: animate text size</li>
  <li name="topacity">Test 2: animate text opacity</li>
  <li name="tpos">Test 3: animate text position</li>
  <li name="isize">Test 4: animate image size</li>
  <li name="iopacity">Test 5: animate image opacity</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
