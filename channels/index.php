<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;
var occsTimer = null;
window.onload = function() {
  menuInit();
  initVideo();
  registerMenuListener(function(liid) {
    if (liid=='exit') {
      document.location.href = '../index.php';
    } else {
      runStep(liid);
    }
  });
  initApp();
  setInstr('Please run all steps in the displayed order. Navigate to the test using up/down, then press OK to start the test. For some tests, you may need to follow some instructions.');
  runNextAutoTest();
};
function runStep(name) {
  var vid;
  if (occsTimer) {
    clearTimeout(occsTimer);
    occsTimer = null;
  }
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
    vid = document.getElementById('video');
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
        if (occsTimer) {
          clearTimeout(occsTimer);
          occsTimer = null;
        }
        vid.onChannelChangeSucceeded = null;
        var ch = vid.currentChannel;
        if (ch.onid==onid&&ch.tsid==tsid&&ch.sid==sid) {
          showStatus(true, 'setChannel succeeded.');
        } else {
          showStatus(false, 'setChannel changed to the wrong channel.');
        }
      };
      setInstr('Setting channel, waiting for onChannelChangeSucceeded...');
      occsTimer = setTimeout(function() {
        occsTimer = null;
        vid.onChannelChangeSucceeded = null;
        showStatus(false, 'did not retrieve onChannelChangeSucceeded event');
      }, 15000);
      vid.setChannel(ch, false);
    } catch (e) {
      showStatus(false, 'setChannel('+ch+') failed.');
      return;
    }
  } else if (name=='wait') {
    // try to determine current channel to get IDs for other channel
    var onid, tsid, sid;
    vid = document.getElementById('video');
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
    vid = document.getElementById('video');
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
    vid = document.getElementById('video');
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
    vid = document.getElementById('video');
    try {
      var txttype = null, numtype, cch = vid.currentChannel;
      numtype = cch.idType;
      if (numtype===cch.ID_DVB_T || numtype===16) {
        txttype = "DVB-T";
      } else if (numtype===cch.ID_DVB_S || numtype===15) {
        txttype = "DVB-S";
      } else if (numtype===cch.ID_DVB_C || numtype===14) {
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
  } else if (name=='cnid') {
    vid = document.getElementById('video');
    try {
      var cch = vid.currentChannel;
      if (serviceNetworkId===cch.nid) {
        showStatus(true, 'Channel network_id is '+cch.nid);
      } else {
        showStatus(false, 'Unexpected channel network_id: '+cch.nid);
      }
    } catch (e) {
      showStatus(false, 'query of Channel network_id failed.');
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
  <li name="cnid">Test 2: Channel network_id</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
