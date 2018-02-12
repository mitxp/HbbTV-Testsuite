<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;
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

function getEitPfEvent(idx) {
  var vid = document.getElementById('video');
  var evt = vid.programmes[idx];
  return getEitEventText(evt);
}
function getEitEventText(evt) {
  var dstart = new Date(evt.startTime * 1000);
  var dend = new Date((evt.startTime+evt.duration) * 1000);
  var fromhrs = dstart.getHours();
  var frommin = dstart.getMinutes();
  var tohrs = dend.getHours();
  var tomin = dend.getMinutes();
  var ename = (""+evt.name).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/>/g, "&gt;");
  return (fromhrs<10?'0':'')+fromhrs+(frommin<10?':0':':')+frommin+' - '+(tohrs<10?'0':'')+tohrs+(tomin<10?':0':':')+tomin+'<br />'+ename;
}

function getPresentFollowing() {
  try {
    var vid = document.getElementById('video');
    if (!vid.programmes) {
      showStatus(false, 'video/broadcast object does not have .programmes property');
      return;
    }
    if (!vid.programmes.length || vid.programmes.length<2) {
      showStatus(false, 'video/broadcast .programmes property has invalid length (below 2)');
      return;
    }
    var epresent = getEitPfEvent(0);
    var efollowing = getEitPfEvent(1);
    var txt = 'Now running: '+epresent+'<br />Followed by: '+efollowing;
    setInstr(txt);
    if (epresent.indexOf("Event 1, umlaut \u00e4")>0 || epresent.indexOf("Event 2, umlaut \u00f6")>0 || epresent.indexOf("Event 01, umlaut \u00e4")>0 || epresent.indexOf("Event 02, umlaut \u00f6")>0) {
      showStatus(true, 'Test passed');
    } else {
      showStatus(false, 'Test failed as returned data is not correct (present event text is &quot;'+epresent+'&quot;, should contain either &quot;Event 1, umlaut ä&quot; or &quot;Event 2, umlaut ö&quot;');
    }
  } catch (e) {
    showStatus(false, 'Test failed');
  }
}
function getPresentDescr() {
  var i, vid, evt = null, descr;
  var descrcodes = [ 0x5f, 4, 0, 0, 0, 5 ]; // expected private_data_specifier_descriptor
  try {
    vid = document.getElementById('video');
    evt = vid.programmes[0];
  } catch (e) {
    // error handled below
  }
  if (!evt) {
    showStatus(false, 'Retrieval of present event failed');
    return;
  }
  try {
    descr = evt.getSIDescriptors(descrcodes[0]);
  } catch (e2) {
    showStatus(false, 'Retrieval of descriptors failed');
    return;
  }
  if (!descr || descr.length<1) {
    showStatus(false, 'No descriptor returned');
    return;
  }
  descr = descr[0]; // test first descriptor returned
  if (descr.length!==descrcodes.length) {
    showStatus(false, 'Descriptor length is invalid');
    return;
  }
  try {
    for (i=0; i<descrcodes.length; i++) {
      if (descrcodes[i] !== descr.charCodeAt(i)) {
        showStatus(false, 'Descriptor byte '+i+' is invalid.');
        return;
      }
    }
  } catch (e3) {
    showStatus(false, 'Exception while comparing descriptor bytes');
    return;
  }
  showStatus(true, 'Test passed');
}
function getSchedule() {
  var i, srchchannel = null, chk, mdsrch, ret;
  var smgr = document.getElementById('srchmgr');
  try {
    var currentch = document.getElementById('video').currentChannel;
    var ccfg = smgr.getChannelConfig();
    var clist = ccfg.channelList;
    for (i=0; i<clist.length; i++) {
      chk = clist.item(i);
      if (chk.onid===currentch.onid && chk.tsid===currentch.tsid && chk.sid===currentch.sid) {
        srchchannel = chk;
        break;
      }
    }
  } catch (e1) {
    showStatus(false, 'Exception while trying to find current channel');
    return;
  }
  if (!srchchannel) {
    showStatus(false, 'Current channel not found in channel config.');
    return;
  }
  try {
    mdsrch = smgr.createSearch(1);
  } catch (e2) {
    showStatus(false, 'Cannot create search');
    return;
  }
  setInstr('requesting findProgrammesFromStream...');
  try {
    mdsrch.findProgrammesFromStream(srchchannel, streamtime[0]-streamtime[1]*3600-streamtime[2]*60);
  } catch (e3) {
    showStatus(false, 'Cannot call findProgrammesFromStream');
    smgr.onMetadataSearch = null;
    return;
  }
  setInstr('retrieving results...');
  smgr.onMetadataSearch = function(msearch, sstate) {
    if (sstate===0) {
      setInstr('state 0, result complete, search terminated, update results');
      smgr.onMetadataSearch = null;
      getScheduleResults(mdsrch.result);
    } else if (sstate===3) {
      setInstr('state 3, search aborted, waiting for new result data...');
    } else {
      showStatus(false, 'Invalid state '+sstate+' received');
      smgr.onMetadataSearch = null;
    }
  };
  try {
    mdsrch.result.getResults(0, 10);
  } catch (e4) {
    showStatus(false, 'Cannot call getResults on MetadataSearch.result');
    smgr.onMetadataSearch = null;
    return;
  }
}
function getScheduleResults(result) {
  if (!result || !result.length) {
    showStatus(false, 'No results retrieved after findProgrammesFromStream.');
    return;
  }
  var i, txt = "", evt;
  for (i=0; i<result.length; i++) {
    try {
      evt = result.item(i);
      txt += i+": "+getEitEventText(evt)+"<br />";
      if (i==1 && result.length>3) {
        txt += "...<br />";
        i = result.length-2;
      }
    } catch (e) {
      showStatus(false, 'Error while receiving event '+i+' of search result.');
    }
  }
  if (result.length<3) {
    showStatus(false, 'Not enough events were received, got only '+result.length+', expected at least 3');
  } else {
    showStatus(true, 'Test passed (if displayed schedule is correct)');
  }
  setInstr(txt);
}

function runStep(name) {
  setInstr('Executing step...');
  showStatus(true, '');
  if (name==="pfevent") {
    getPresentFollowing();
  } else if (name==="pfdescr") {
    getPresentDescr();
  } else if (name==="schedevent") {
    getSchedule();
  }
}

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo videoObject(100, 480, 320, 180); ?>
<?php echo appmgrObject(); ?>
<object id="srchmgr" type="application/oipfSearchManager" style="position: absolute; left: 0px; top: 0px; width: 0px; height: 0px;"></object>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="pfevent">Present/following event (HbbTV 1.1.1)</li>
  <li name="pfdescr">Present event descriptors (HbbTV 1.2.1)</li>
  <li name="schedevent">Scheduled events (HbbTV 1.2.1)</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
