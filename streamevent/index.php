<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var listener = null;
var waitTimer = null;
var targeturl = null;
var eventname = 'testevent';
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
  initVideo();
  runNextAutoTest();
};
function clearInvalidChars(txt) {
  var i, ch, cc, ret = '';
  txt = ''+txt;
  for (i=0; i<txt.length; i++) {
    ch = txt.substring(i, i+1);
    cc = ch.charCodeAt(0);
    if (cc>=0x20 || cc===0x0a || cc===0x0d) {
      ret += ch;
    }
  }
  return ret;
}
function unregisterListener(failOnError) {
  var vid = document.getElementById('video');
  if (waitTimer) {
    clearTimeout(waitTimer);
    waitTimer = null;
  }
  if (listener) {
    setInstr('Unregistering StreamEventListener...');
    try {
      vid.removeStreamEventListener(targeturl, eventname, listener);
    } catch (e) {
      if (failOnError) {
        showStatus(false, 'Could not remove StreamEventListener');
      }
      return false;
    }
  }
  listener = null;
  return true;
}
function registerListener(url, invalid) {
  var vid = document.getElementById('video');
  var expectedEvents = [
    {"data": "48656c6c6f204862625456", "text": "Hello HbbTV", "found": 0},
    {"data": "54657374206576656e7420c3a4c3b6c3bc21", "text": "Test event \u00e4\u00f6\u00fc!", "found": 0},
    {"data": "cafebabe0008090a0d101fff", "text": "\r\n", "found": 0}
  ];
  listener = function(e) {
    if (!e) {
      unregisterListener(false);
      showStatus(false, 'StreamEventListener was called, but no event was passed.');
      return;
    }
    if (!listener) {
      if (waitTimer) {
        showStatus(false, 'StreamEventListener was called after listener was unregistered.');
        clearTimeout(waitTimer);
        waitTimer = null;
      }
      return;
    }
    var succss = e.data && e.data.length>3 && eventname==e.name && 'trigger'==e.status;
    var i, found, correctText = false, sedata = e.data, setext = clearInvalidChars(e.text);
    var msg = 'data='+sedata+', text='+setext+', status='+e.status;
    if (succss) {
      try {
        sedata = sedata.toLowerCase().replace(/ /g, '');
      } catch (ex) {
        succss = false;
      }
      found = false;
      for (i=0; i<expectedEvents.length; i++) {
        if (sedata===expectedEvents[i].data) {
          found = true;
          expectedEvents[i].found++;
          correctText = setext===expectedEvents[i].text;
          break;
        }
      }
      if (!found) {
        succss = false;
      }
    }
    if (invalid) {
      if ('error'===e.status) {
        showStatus(true, 'Got expected error event for invalid URL');
      } else {
        showStatus(false, 'Received invalid StreamEvent. '+msg);
      }
      unregisterListener(false);
      return;
    }
    if (!succss) {
      showStatus(false, 'Received invalid StreamEvent (data does not match). '+msg);
      unregisterListener(false);
      return;
    }
    if (!correctText) {
      showStatus(false, 'Received invalid StreamEvent (text decoded incorrectly, see chapter 8.2.1.2). '+msg);
      unregisterListener(false);
      return;
    }
    found = 0;
    for (i=0; i<expectedEvents.length; i++) {
      if (expectedEvents[i].found>1) {
        unregisterListener(false);
        showStatus(false, 'StreamEventListener received StreamEvents in incorrect order.');
        return;
      } else if (expectedEvents[i].found) {
        found++;
      }
    }
    msg = 'Received StreamEvent '+found+'/'+expectedEvents.length+'. '+msg;
    if (found<expectedEvents.length) {
      setInstr(msg);
      return;
    }
    if (!unregisterListener(true)) {
      return;
    }
    setInstr(msg+'. Waiting 5 sec to verify no event is received after unregister.');
    waitTimer = setTimeout(function() {
      waitTimer = null;
      showStatus(true, msg);
    }, 5000);
  };
  try {
    vid.addStreamEventListener(url, eventname, listener);
    targeturl = url;
    setInstr('StreamEventListener added, waiting for first event...');
  } catch (e) {
    showStatus(false, 'Could not add StreamEventListener');
  }
  if (invalid) {
    waitTimer = setTimeout(function() {
      waitTimer = null;
      showStatus(false, 'No error StreamEvent received for invalid URL');
    }, 30000);
  }
}
function runStep(name) {
  if (!unregisterListener(true)) {
    return;
  }
  setInstr('Registering StreamEventListener...');
  if (name==='xmlurl') {
    registerListener('sevent.php?ctag='+seventctag, false);
  } else if (name==='dvburl') {
    registerListener(isdsmcc ? 'sevent' : ('dvb://'+service1[0].toString(16)+'.'+service1[1].toString(16)+'.'+service1[2].toString(16)+'.'+dsmccctag.toString(16)+'/sevent'), false);
  } else if (name==='xmlinvalid') {
    registerListener('sevent.php?ctag='+(seventctag+1), true);
  } else if (name==='dvbinvalid') {
    registerListener(isdsmcc ? 'xxx' : ('dvb://'+service1[0].toString(16)+'.'+service1[1].toString(16)+'.'+service1[2].toString(16)+'.'+dsmccctag.toString(16)+'/xxx'), true);
  } 
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
  <li name="xmlurl">Register via XML file</li>
  <li name="dvburl">Register via dvb:// URL</li>
  <li name="xmlinvalid">Register via invalid XML file</li>
  <li name="dvbinvalid">Register via invalid dvb:// URL</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
