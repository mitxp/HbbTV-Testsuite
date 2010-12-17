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
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.');
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
  var co = document.getElementById('capobj');
  if (name=='xml') {
    var xmlp = false;
    try {
      xmlp = co.xmlCapabilities;
    } catch (e) {
      // will be handled below
    }
    if (!xmlp) {
      showStatus(false, 'retrieving xmlCapabilities failed.');
      return;
    }
    try {
      var serializer = new XMLSerializer();
      xmlp = serializer.serializeToString(xmlp);
      xmlp = encodeURIComponent(xmlp);
    } catch (e) {
      showStatus(false, 'serializing capabilities XML failed.');
      return;
    }
    req = new XMLHttpRequest();
    req.onreadystatechange = function() {
      if (req.readyState!=4 || req.status!=200) return;
      var succ = req.responseText.substring(0, 1);
      if (succ=='1') {
        showStatus(true, 'XML capabilities are valid: '+req.responseText.substring(1));
      } else {
        showStatus(false, 'XML capabilities are invalid: '+req.responseText.substring(1));
      }
      req.onreadystatechange = null;
      req = null;
    }
    req.open('GET', 'validate.php?data='+xmlp);
    req.send(null);
  } else if (name=='extrasd') {
    var num = 'undefined';
    try {
      num = co.extraSDVideoDecodes;
    } catch (e) {
      // ignore
    }
    if (isNaN(num)) {
      showStatus(false, 'extraSDVideoDecodes oipfCapabilities extension (A.2.3) is not numeric: '+num);
    } else {
      showStatus(false, 'extraSDVideoDecodes = '+num);
    }
  } else if (name=='extrahd') {
    var num = 'undefined';
    try {
      num = co.extraHDVideoDecodes;
    } catch (e) {
      // ignore
    }
    if (isNaN(num)) {
      showStatus(false, 'extraHDVideoDecodes oipfCapabilities extension (A.2.3) is not numeric: '+num);
    } else {
      showStatus(false, 'extraHDVideoDecodes = '+num);
    }
  } else {
    try {
      showStatus(true, name+' capability is '+(co.hasCapability(name)?'':'not ')+'available.');
    } catch (e) {
      showStatus(false, 'calling hasCapability('+name+') failed.');
    }
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="capobj" type="application/oipfCapabilities" style="position: absolute; left: 0px; top: 0px; width: 0px; height: 0px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="xml">Show xmlCapabilities</li>
  <li name="+DL">Test +DL capability</li>
  <li name="+PVR">Test +PVR capability</li>
  <li name="+RTSP">Test +RTSP capability</li>
  <li name="extrasd">Show number of SD video decoders</li>
  <li name="extrahd">Show number of HD video decoders</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 300px; width: 400px; height: 400px;"></div>

</body>
</html>
