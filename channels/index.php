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
  initVideo();
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
  setInstr('Executing step...');
  showStatus(true, '');
  if (name=='get') {
    try {
      var ch = document.getElementById('video').currentChannel;
      var type = ch.channelType;
      type = type==ch.TYPE_TV?'TV':(type==ch.TYPE_RADIO?'Radio':'unknown');
      var chinf = ch.sid==service1[2] ? service1 : service2;
      var succarr = [
        type==='TV',
        ch.onid===chinf[0],
        ch.tsid===chinf[1],
        ch.sid===chinf[2],
        ch.name===chinf[4]
      ];
      var i, succ = true, succtxt = "";
      for (i=0; i<succarr.length; i++) {
        succ &= succarr[i];
        succtxt += (succtxt?",":"")+(succarr[i]?"OK":"NOK");
      }
      showStatus(succ, 'channel=DVB triple('+ch.onid+'.'+ch.tsid+'.'+ch.sid+'), type='+type+', name='+ch.name+' ('+succtxt+')');
    } catch (e) {
      showStatus(false, 'cannot determine current channel');
    }
  } else if (name=='set') {
    // try to determine current channel to get IDs for other channel
    var onid, tsid, sid;
    var vid = document.getElementById('video');
    try {
      var ch = vid.currentChannel;
      if (ch.sid==service1[2]) {
        onid = service2[0];
        tsid = service2[1];
        sid = service2[2];
      } else {
        onid = service1[0];
        tsid = service1[1];
        sid = service1[2];
      }
    } catch (e) {
      showStatus(false, 'cannot determine current channel');
      return;
    }
    var lst = null;
    try {
      lst = vid.getChannelConfig().channelList;
    } catch (e) {
      lst = null;
    }
    if (!lst) {
      showStatus(false, 'could not get channelList.');
      return;
    }
    var ch = null;
    try {
      ch = lst.getChannelByTriplet(onid, tsid, sid);
    } catch (e) {
      showStatus(false, 'getChannelByTriplet failed for '+onid+'.'+tsid+'.'+sid);
      return;
    }
    if (!ch) {
      showStatus(false, 'getChannelByTriplet did not return anything.');
      return;
    }
    try {
      vid.onChannelChangeSucceeded = function() {
        vid.onChannelChangeSucceeded = null;
        var ch = vid.currentChannel;
        if (ch.onid==onid&&ch.tsid==tsid&&ch.sid==sid) {
          showStatus(true, 'setChannel succeeded.');
        } else {
          showStatus(false, 'setChannel changed to the wrong channel.');
        }
      };
      setInstr('Setting channel, waiting for onChannelChangeSucceeded...');
      vid.setChannel(ch, false);
    } catch (e) {
      showStatus(false, 'setChannel('+ch+') failed.');
      return;
    }
  } else if (name=='wait') {
    // try to determine current channel to get IDs for other channel
    var onid, tsid, sid;
    var vid = document.getElementById('video');
    try {
      var ch = vid.currentChannel;
      if (ch.onid==service1[0]&&ch.tsid==service1[1]&&ch.sid==service1[2]) {
        onid = service2[0];
        tsid = service2[1];
        sid = service2[2];
      } else {
        onid = service1[0];
        tsid = service1[1];
        sid = service1[2];
      }
    } catch (e) {
      showStatus(false, 'cannot determine current channel');
      return;
    }
    setInstr('Please change to other HbbTV testsuite channel using the remote control. Channel is located on '+onid+'.'+tsid+'.'+sid);
    vid.onChannelChangeSucceeded = function() {
      vid.onChannelChangeSucceeded = null;
      var ch = vid.currentChannel;
      if (ch.onid==onid&&ch.tsid==tsid&&ch.sid==sid) {
        showStatus(true, 'received onChannelChangeSucceeded.');
      } else {
        showStatus(false, 'wrong service reported.');
      }
    };
  } else if (name=='getprivdata') {
    try {
      var ch = document.getElementById('appmgr').getOwnerApplication(document).privateData.currentChannel;
      var type = ch.channelType;
      type = type==ch.TYPE_TV?'TV':(type==ch.TYPE_RADIO?'Radio':'unknown');
      var succ = type=='TV'
      && ((ch.onid==service1[0]&&ch.tsid==service1[1]&&ch.sid==service1[2])
       || (ch.onid==service2[0]&&ch.tsid==service2[1]&&ch.sid==service2[2]));
      showStatus(succ, 'channel=DVB triple('+ch.onid+'.'+ch.tsid+'.'+ch.sid+'), type='+type+', name='+ch.name);
    } catch (e) {
      showStatus(false, 'cannot determine current channel');
    }
  } else if (name=='nextc') {
    var vid = document.getElementById('video');
    try {
      vid.onChannelChangeSucceeded = function() {
        vid.onChannelChangeSucceeded = null;
        if (vid.currentChannel) {
          showStatus(true, 'channel change succeeded.');
        } else {
          showStatus(false, 'channel change failed.');
        }
      };
      setInstr('Setting channel, waiting for onChannelChangeSucceeded...');
      vid.nextChannel();
    } catch (e) {
      showStatus(false, 'nextChannel() failed.');
      return;
    }
  } else if (name=='prevc') {
    var vid = document.getElementById('video');
    try {
      vid.onChannelChangeSucceeded = function() {
        vid.onChannelChangeSucceeded = null;
        if (vid.currentChannel) {
          showStatus(true, 'channel change succeeded.');
        } else {
          showStatus(false, 'channel change failed.');
        }
      };
      setInstr('Setting channel, waiting for onChannelChangeSucceeded...');
      vid.prevChannel();
    } catch (e) {
      showStatus(false, 'prevChannel() failed.');
      return;
    }
  } else if (name=='ctype') {
    var vid = document.getElementById('video');
    try {
      var txttype = null, numtype, cch = vid.currentChannel;
      numtype = cch.idType;
      if (numtype===cch.ID_DVB_T) {
        txttype = "DVB-T";
      } else if (numtype===cch.ID_DVB_S) {
        txttype = "DVB-S";
      } else if (numtype===cch.ID_DVB_C) {
        txttype = "DVB-C";
      }
      if (txttype) {
        showStatus(true, 'Channel delivery type is '+txttype+' ('+numtype+').');
      } else {
        showStatus(false, 'Unexpected channel delivery type: '+numtype);
      }
    } catch (e) {
      showStatus(false, 'query of Channel idType failed.');
      return;
    }
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo videoObject(100, 480, 320, 180);
echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="get">Test 1: currentChannel</li>
  <li name="set">Test 2: setChannel</li>
  <li name="wait">Test 3: onChannelChangeSucceeded</li>
  <li name="getprivdata">Test 4: currentChannel via privateData</li>
  <li name="nextc">Test 5: nextChannel()</li>
  <li name="prevc">Test 6: prevChannel()</li>
  <li name="ctype">Test 7: Channel type</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
