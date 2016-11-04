<?php
$ROOTDIR='.';
require("$ROOTDIR/base.php");
$referer = $_SERVER['HTTP_REFERER'];
$i = strrpos($referer, '/');
$referer = substr($referer, 0, $i);
$referer = substr(strrchr($referer, '/'), 1);
$referer = addcslashes($referer, "\0..\37'\\");

sendContentType();
openDocument();
?>
<script type="text/javascript">
//<![CDATA[
var testPrefix = "";
var pinEntry = false, pinCode = '';
window.onload = function() {
  initVideo();
  onMenuSelect = function() {
    document.getElementById('descr').innerHTML = opts[selected].getAttribute('descr');
  };
  menuInit();
  registerMenuListener(function(liid) {
    document.location.href = liid+'/';
  });
  registerKeyEventListener();
  initApp();
  menuSelectByName('<?php echo $referer; ?>');
  document.getElementById('relid').innerHTML = releaseinfo;
  runNextAutoTest();
};
function handleKeyCode(kc) {
  if (kc===VK_BLUE && !automate.timer) {
    pinEntry = !pinEntry;
    document.getElementById("automatepin").style.display = pinEntry ? "block" : "none";
    pinCode = '';
    updatePinCode();
    return true;
  }
  if (pinEntry && kc>=VK_0 && kc<=VK_9) {
    pinCode += ''+(kc-VK_0);
    updatePinCode();
    if (pinCode.length>=4) {
      pinEntry = false;
      automate.pin = parseInt(pinCode, 10);
      setTimeout(function() {
        document.getElementById("automatepin").style.display = "none";
        runNextAutoTest(true);
      }, 1000);
    }
    return true;
  }
  if (pinEntry && kc===VK_BACK) {
    if (pinCode.length>0) {
      pinCode = pinCode.substring(0, pinCode.length-1);
      updatePinCode();
    } else {
      pinEntry = false;
      document.getElementById("automatepin").style.display = "none";
    }
    return true;
  }
  return false;
}
function updatePinCode() {
  var i, txt = '';
  for (i=0; i<4; i++) {
    if (pinCode.length<=i) {
      txt += "_ ";
    } else {
      txt += pinCode.substring(i, i+1)+" ";
    }
  }
  document.getElementById("automatepinentry").innerHTML = txt;
}

//]]>
</script>

</head><body>

<?php
echo videoObject();
echo appmgrObject();
?>

<div id="bgdiv" style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />
<div class="txtdiv txtlg" style="left: 111px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV testsuite</div>
<div class="txtdiv" style="left: 111px; top: 640px; width: 500px; height: 30px;">Testsuite release: <span id="relid"></span></div>
<div style="left: 690px; top: 56px; width: 590px; height: 130px; background-color: #ffffff;">
  <div class="txtdiv" style="left: 10px; top: 4px; width: 500px; height: 30px; color: #000000;">HBBTV testsuite project initiated/maintained by:</div>
  <div class="imgdiv" style="left: 10px; top: 34px; width: 356px; height: 44px; background-image: url(logo.png);"></div>
</div>
<div class="txtdiv" style="left: 700px; top: 200px; width: 450px; height: 500px;"><u>Instructions:</u><br />
Please select the desired test using the cursor keys, then press OK. After that, test-specific instructions will appear. More information is available under &quot;About / Imprint&quot;.<br />
In case you have questions and/or comments, you can reach us at info&#160;&#x0040;&#160;mit-xperts&#x002e;com<br /><br />
<u>Test description:</u><br />
<span id="descr">&#160;</span>
</div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="about" automate="ignore" descr="Displays more information about this testsuite (this is no test).">About / Imprint</li>
  <li name="appmanager" descr="Start applications, destroy application.">Application manager</li>
  <li name="channels" descr="Performs channel operations on the video/broadcast object.">Get and set channel</li>
  <li name="channellist" descr="Accesses the ChannelList class.">Channel list</li>
  <li name="videoscale" descr="Exchange the video object on the page, switch between broadcast and streaming video. For both, scale the video object.">Video swapping and scaling</li>
  <li name="videocontrol" descr="Sets the play speed and play position on a streaming video.">Video controls</li>
  <li name="playerevents" descr="Checks if streaming video playback sends correect events.">Streaming video playback events</li>
  <li name="videoformats" descr="Check whether videos from various applications run on your device.">Streaming video/audio formats</li>
  <li name="videocomponents" descr="Retrieval of audio/video/subtitle components, as well as selecting and unselecting them.">AVComponents in video/broadcast</li>
  <li name="dolby" descr="MPEG-DASH + AC3 + audio component selection + DRM tests.">DOLBY video format / AVComponents</li>
  <li name="memoryaudio" descr="Playback audio from memory (for instant playback, see OIPF DAE 7.14.10).">Memory audio</li>
  <li name="videobackground" automate="ignore" descr="Broadcast video in background without own video object.">Broadcast in background</li>
  <li name="eitevent" descr="Retrieve EIT events.">EIT events</li>
  <li name="keycodes" automate="ignore" descr="Check for correctly defined key codes and key events.">Key codes / key events</li>
  <li name="keyset" automate="ignore" descr="Set keyset mask for user-input keys.">Keyset mask</li>
  <li name="keypress" automate="ignore" descr="Check whether keypress event is sent for non-unicode characters.">Keypress events</li>
  <li name="capabilities" descr="Check the application/oipfCapabilities object.">OIPF Capabilities</li>
  <li name="configuration" descr="Check the application/oipfConfiguration object.">OIPF Configuration</li>
  <li name="parentalcontrol" descr="Check the application/oipfParentalControlManager object.">OIPF Parental Control</li>
  <li name="navigatordebug" descr="Check access to the Navigator class and the Debug print API.">Navigator and Debug</li>
  <li name="fontrendering" descr="Check whether the device performs correct font rendering.">Font rendering</li>
  <li name="clientssl" descr="Check support for client SSL certificates.">Client SSL certificate</li>
  <li name="cookies" descr="Tests for HTML5 local storage (HbbTV 1.3) and for cookies (HbbTV 1.1).">Cookies / HTML localStorage</li>
  <li name="useragent" descr="Validate user agent.">User agent</li>
  <li name="datetime" descr="Check whether box has correct date and time.">Date and time</li>
  <li name="animation" descr="Check the performance of a Set-Top-Box graphics renderer.">Animation</li>
  <li name="streamevent" descr="Receive StreamEvents.">StreamEvent</li>
  <li name="dvburl" descr="Access DSM-CC via dvb:// URLs.">dvb URLs</li>
  <li name="css3" descr="CSS3 tests">HbbTV 1.3 CSS3</li>
  <li name="html5vid" descr="HTML5 video tests">HbbTV 1.3 HTML5 video</li>
</ul>

<div id="automatepin" class="txtdiv" style="left: 400px; top: 200px; width: 440px; background-color: #000000; color: #ffffff; padding: 20px; text-align: center; font-size: 24px; line-height: 30px; display: none;">
  Enter your 4-digit automation ID<br/>(user account number):<br/><br/>
  <span id="automatepinentry">_ _ _ _</span>
</div>

</body>
</html>

