<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");
sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
var testPrefix = <?php echo json_encode(getTestPrefix()); ?>;
var mediawsconn = null;
var supportInfo = { };

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
  setInstr('Executing step...');
  if (name=="supportinfo") {
    getSupportInfo();
  } else if (name=="featuresettings") {
    getFeatureSettings();
  } else if (name=="negotiate") {
    negotiateMethods();
  } else if (name=="voiceready") {
    sendVoiceReady(true);
  } else if (name=="voiceunready") {
    sendVoiceReady(false);
  } else if (name=="playvideo") {
    playVoiceVideo();
  } else if (name.substring(0, 5)=="suppr") {
    suppressFeature(name.substring(5));
  }
}

function getRpcWebsocketUrl() {
  var i, c, caps, wsurl = "";
  setInstr('Getting json_rpc_server...');
  // return "ws:fake"; // TODO remove
  try {
    caps = oipfObjectFactory.createCapabilitiesObject().xmlCapabilities.querySelectorAll("json_rpc_server");
    for (i=0; i<caps.length; i++) {
      c = caps.item(i).getAttribute("version");
      if (c !== "1.7.1") {
        continue;
      }
      wsurl = caps.item(i).getAttribute("url");
      if (wsurl) {
        return wsurl;
      }
    }
  } catch (e) {
    showStatus(false, "Cannot query XML Capabilities: "+fixhtml(e));
    return;
  }
  showStatus(false, "Could not find correct json_rpc_server in XML Capabilities.");
}

function buildFakeWebSocket() {
  var ret = { "readyState": 0 };
  ret.close = function() {};
  ret.send = function(str) {
    var msg = JSON.parse(str);
    var cbo = { "method": msg.method, "feature": msg.params.feature };
    var ok = false;
    switch (msg.method) {
      case "org.hbbtv.af.featureSuppress":
        cbo.value = "suppressing";
        ok = true;
        break;
      case "org.hbbtv.af.featureSettingsQuery":
        cbo.value = {"enabled": false};
        ok = true;
        break;
      case "org.hbbtv.af.featureSupportInfo":
        cbo.value = "tvosAndHbbTV";
        ok = true;
        break;
      case "org.hbbtv.negotiateMethods":
        cbo.terminalToApp = msg.params.terminalToApp;
        cbo.appToTerminal = msg.params.appToTerminal;
        ok = true;
        break;
    }
    if (ok) {
      setTimeout(function() {
        ret.onmessage({ "data": JSON.stringify({ "jsonrpc": "2.0", "result": cbo, "id": msg.id }) });
      }, 100);
    }
  };
  return ret;
}

function createRpcWebsocket() {
  var wsconn, wsurl = getRpcWebsocketUrl();
  if (!wsurl) {
    return;
  }
  setInstr("Connecting to WebSocket: "+fixhtml(wsurl));
  try {
    wsconn = { "isClosed": false, "lastMessageReceived": 0 };
    if (wsurl==="ws:fake") {
      wsconn.ws = buildFakeWebSocket(); // TODO
    } else {
      wsconn.ws = new WebSocket(wsurl);
    }
    wsconn.close = function() {
      wsconn.isClosed = true;
      try {
        wsconn.ws.close();
      } catch (ignore) {
        // ignore
      }
    };
    wsconn.sendPrepare = function(msg) {
      return {"jsonrpc": "2.0", "method": msg.method, "params": msg.params, "id": msg.id || ("id"+(new Date().getTime() % 10000000)+"x")};
    };
    wsconn.send = function(msg) {
      wsconn.waitForConnection(function() {
        msg = wsconn.sendPrepare(msg);
        msg = JSON.stringify(msg);
        setInstr("Sending JSON-RPC...");
        wsconn.ws.send(msg);
      }, 0);
    };
    wsconn.waitForConnection = function(callback, cnt) {
      if (wsconn.ws.readyState===1) {
        callback();
        return;
      }
      if (cnt>25) {
        if (wsconn.ws && wsconn.ws.onerror) {
          wsconn.ws.onerror("Websocket did not become ready!");
        }
        return;
      }
      setTimeout(function() {
        wsconn.waitForConnection(callback, cnt+1);
      }, 200);
    };
    wsconn.ws.onclose = function() {
      wsconn.isClosed = true;
    };
    wsconn.ws.onerror = function(err) {
      if (wsconn.isClosed) {
        return;
      }
      showStatus(false, "WebSocket error: "+fixhtml(err));
      wsconn.close();
    };
    wsconn.ws.onmessage = function(evt) {
      if (wsconn.isClosed) {
        return;
      }
      wsconn.lastMessageReceived = new Date().getTime();
      if (wsconn.autoCloseOnFirstMessage) {
        wsconn.close();
      }
      if (!evt || !evt.data) {
        setInstr("No JSON-RPC data recieved.");
        return;
      }
      try {
        evt = JSON.parse(evt.data);
      } catch (ignore) {
        evt = false;
      }
      if (typeof evt !== "object"){
        showStatus(false, "Non-JSON response for JSON-RPC");
        return;
      }
      if (evt.error) {
        showStatus(false, "Error response for JSON-RPC: "+fixhtml(JSON.stringify(evt.error)));
        return;
      }
      setInstr("Received JSON-RPC message: "+fixhtml(JSON.stringify(evt)));
      if (wsconn.onmessage) {
        wsconn.onmessage(evt);
      }
    };
    return wsconn;
  } catch (e) {
    showStatus(false, "Cannot create WebSocket: "+fixhtml(e));
  }
}

function doRpc(msg, cb) {
  var wsconn = !cb && mediawsconn && !mediawsconn.isClosed ? mediawsconn : createRpcWebsocket();
  if (!wsconn) {
    return;
  }
  msg = wsconn.sendPrepare(msg);
  try {
    if (cb) {
      wsconn.autoCloseOnFirstMessage = true;
      wsconn.onmessage = function(evt) {
        if (evt && evt.method==="org.hbbtv.notify") {
          return; // ignore notify events here
        }
        if (!evt || !evt.id || evt.id!==msg.id) {
          showStatus(false, "Unexpected response for JSON-RPC, id should be "+msg.id);
          return;
        }
        if (!evt.result) {
          showStatus(false, "Missing result in response for JSON-RPC, id="+msg.id);
          return;
        }
        if (evt.result.method !== msg.method) {
          showStatus(false, "Invalid result object in JSON-RPC response, method should be "+msg.method);
          return;
        }
        cb(evt.result);
      };
    } else if (wsconn!==mediawsconn) {
      wsconn.autoCloseOnFirstMessage = true;
      setTimeout(wsconn.close, 2000);
    }
    wsconn.send(msg);
    return true;
  } catch (e) {
    showStatus(false, "Cannot send JSON-RPC message: "+fixhtml(e));
    console.log(e);
  }
}


function getSupportInfo() {
  var features = [ {"id": "subtitles"}, {"id": "dialogueEnhancement"}, {"id": "uiMagnifier"}, {"id": "highContrastUI"}, {"id": "screenReader"}, {"id": "responseToUserAction"}, {"id": "audioDescription"}, {"id": "inVisionSigning"}];
  supportInfo.queried = true;
  function getSingleFeature(idx) {
    var txt = "", f = features[idx];
    if (!f) {
      // we are finished, print result
      for (idx=0; idx<features.length; idx++) {
        f = features[idx];
        txt += (txt?", ":"") + f.id + "=" + f.value;
      }
      showStatus(true, "Got feature result: "+fixhtml(txt));
      return;
    }
    doRpc({"method": "org.hbbtv.af.featureSupportInfo", "params": {"feature": f.id}}, function(result) {
      if (result.feature !== f.id) {
        showStatus(false, "Invalid result object in featureSupportInfo response, feature should be "+f.id);
        return;
      }
      if (result.value !== "notSupported" && result.value !== "tvosSettingOnly" && result.value !== "tvosOnly" && result.value !== "tvosAndHbbTV" && result.value !== "supportedNoSetting") {
        showStatus(false, "Invalid result object in featureSupportInfo response, feature value is not valid: "+fixhtml(result.value));
        return;
      }
      f.value = result.value;
      supportInfo[f.id] = result.value;
      getSingleFeature(idx+1);
    });
  }
  getSingleFeature(0);
}

function suppressFeature(id) {
  doRpc({"method": "org.hbbtv.af.featureSupportInfo", "params": {"feature": id}}, function(featureQueryResult) {
    featureQueryResult = featureQueryResult ? fixhtml(featureQueryResult.value) : null;
    doRpc({"method": "org.hbbtv.af.featureSuppress", "params": {"feature": id}}, function(result) {
      if (result.feature !== id) {
        showStatus(false, "Invalid result object in featureSuppress response, feature should be "+id);
        return;
      }
      if (result.value !== "suppressing" && result.value !== "notSuppressing" && result.value !== "featureNotSupported") {
        showStatus(false, "Invalid result object in featureSuppress response, feature value is not valid: "+fixhtml(result.value));
        return;
      }
      if (result.value==="featureNotSupported") {
        if (featureQueryResult!=="notSupported" && featureQueryResult!=="tvosOnly" && featureQueryResult!=="tvosSettingOnly") {
          showStatus(false, "Invalid result object in featureSuppress response, feature value was "+result.value+" but query result was "+featureQueryResult+" (not matching according to 15.2.2.2.2)");
          return;
        }
      } else {
        if (featureQueryResult!=="tvosAndHbbTV" && featureQueryResult!=="supportedNoSetting") {
          showStatus(false, "Invalid result object in featureSuppress response, feature value was "+result.value+" but query result was "+featureQueryResult+" (not matching according to 15.2.2.2.2)");
          return;
        }
      }
      if (result.value==="suppressing") {
        showStatus(true, "Feature "+id+" is now suppressed, response was "+result.value);
      } else {
        showStatus(true, "Feature "+id+" is not supported, response was "+result.value);
      }
    });
  });
}

function getFeatureSettings() {
  var validationErr = "", features = [
    {"id": "subtitles", "mandatory": {"enabled": "boolean"}},
    {"id": "dialogueEnhancement", "mandatory": {"dialogueEnhancementGainPreference": "number", "dialogueEnhancementGain": "number", "dialogueEnhancementLimit": "object"}},
    {"id": "uiMagnifier", "mandatory": {"enabled": "boolean"}},
    {"id": "highContrastUI", "mandatory": {"enabled": "boolean"}},
    {"id": "screenReader", "mandatory": {"enabled": "boolean"}},
    {"id": "responseToUserAction", "mandatory": {"enabled": "boolean"}},
    {"id": "audioDescription", "mandatory": {"enabled": "boolean"}},
    {"id": "inVisionSigning", "mandatory": {"enabled": "boolean"}}
  ];
  function getSingleFeature(idx) {
    var txt = "", f = features[idx];
    if (!f) {
      // we are finished, print result
      for (idx=0; idx<features.length; idx++) {
        f = features[idx];
        txt += (txt?", ":"") + f.id + "=" + JSON.stringify(f.value);
      }
      if (validationErr) {
        showStatus(false, "Got invalid settings result (first error was "+fixhtml(validationErr)+"): "+fixhtml(txt));
      } else {
        showStatus(true, "Got valid settings result: "+fixhtml(txt));
      }
      return;
    }
    if (supportInfo.queried && (supportInfo[f.id]==="notSupported" || supportInfo[f.id]==="supportedNoSettings")) {
      f.value = "(not queried)";
      getSingleFeature(idx+1);
      return;
    }
    doRpc({"method": "org.hbbtv.af.featureSettingsQuery", "params": {"feature": f.id}}, function(result) {
      var k, v;
      if (result.feature !== f.id) {
        showStatus(false, "Invalid result object in featureSettingsQuery response, feature should be "+f.id);
        return;
      }
      if (typeof result.value !== "object") {
        showStatus(false, "Invalid result object in featureSettingsQuery response, feature value is no object");
        return;
      }
      f.value = result.value;
      if (!validationErr) {
        try {
          for (k in f.mandatory) {
            if (!f.mandatory.hasOwnProperty(k)) {
              continue;
            }
            v = typeof f.value[k];
            if (v !== f.mandatory[k]) {
              validationErr = "invalid property "+k+" in featureSettingsQuery "+f.id+" response, should be "+f.mandatory[k]+" but is "+v;
            }
          }
        } catch (err) {
          validationErr = "internal error while validating response "+JSON.stringify(f.value)+": "+err;
        }
      }
      getSingleFeature(idx+1);
    });
  }
  getSingleFeature(0);
}

function negotiateMethods() {
  doRpc({"method": "org.hbbtv.negotiateMethods",
      "params": {
        "terminalToApp": [
          "org.hbbtv.app.intent.media.play",
          "org.hbbtv.app.intent.media.pause",
          "org.hbbtv.app.intent.media.stop",
          "org.hbbtv.app.intent.media.seek-content",
          "org.hbbtv.app.intent.media.seek-relative",
          "org.hbbtv.app.intent.media.fast-forward",
          "org.hbbtv.app.intent.media.fast-reverse",
          "org.hbbtv.app.intent.media.seek-live",
          "org.hbbtv.app.intent.media.seek-wallclock",
          "org.hbbtv.app.intent.search",
          "org.hbbtv.notify"
        ],
        "appToTerminal": [
          "org.hbbtv.negotiateMethods",
          "org.hbbtv.subscribe",
          "org.hbbtv.unsubscribe",
	  "org.hbbtv.unsubscribe",
	  "org.hbbtv.af.featureSettingsQuery",
          "org.hbbtv.af.featureSupportInfo",
          "org.hbbtv.af.featureSuppress",
          "org.hbbtv.app.voice.ready",
          "org.hbbtv.app.state.media"
        ]
      }
    }, function(result) {
      if (typeof result.terminalToApp !== "object") {
        showStatus(false, "Invalid result object in negotiateMethods response, result.terminalToApp is no object");
        return;
      }
      if (typeof result.appToTerminal !== "object") {
        showStatus(false, "Invalid result object in negotiateMethods response, result.appToTerminal is no object");
        return;
      }
      showStatus(true, "Negotiated methods: "+JSON.stringify(result));
    }
  );
}

function sendVoiceReady(isReady) {
  if (doRpc({ "method": "org.hbbtv.app.voice.ready", "params": { "ready": isReady } })) {
    showStatus(true, "Voice ready = "+isReady+" sent.");
  }
}

function playVoiceVideo() {
  var vid = null, wasPlaying = false, aborted = false, receivedVoice = false;
  if (mediawsconn) {
    if (mediawsconn.timr) {
      clearTimeout(mediawsconn.timr);
    }
    mediawsconn.close();
  }
  mediawsconn = createRpcWebsocket();
  if (!mediawsconn) {
    return;
  }
  try {
    vid = document.getElementById("videobc");
    vid.stop();
  } catch (err) {
    setInstr("Cannot stop video/broadcast");
  }
  vid = null;
  function showUpdate(kind, txt) {
    var elem = document.getElementById("status");
    if (aborted) {
      return;
    }
    elem.className = "statwarn";
    elem.innerHTML = "<b>"+kind+":</b><br/>"+fixhtml(txt);
  }
  function doAbort() {
    aborted = true;
    if (mediawsconn.timr) {
      clearTimeout(mediawsconn.timr);
      mediawsconn.timr = null;
    }
    if (vid && vid.duration && !vid.error && !vid.ended) {
      vid.currentTime = vid.duration;
      vid.pause();
    }
  }
  function sendNotifyTimer() {
    mediawsconn.timr = setTimeout(function() {
      var msg, plyng = false;
      if (!mediawsconn.timr || aborted) {
        return;
      }
      mediawsconn.timr = null;
      if (!mediawsconn || mediawsconn.isClosed) {
        return;
      }
      msg = {
        "state": "no-media",
        "kind": "audio-video",
        "type": "on-demand",
        "metadata": { "title": "MIT-xperts HbbTV testsuite test video" },
      };
      if (vid.error) {
        msg.state = "error";
      } else if (!vid.duration) {
        msg.paused = "no-media";
      } else if (vid.ended) {
        msg.state = "stopped";
      } else if (vid.paused) {
        msg.paused = "paused";
        plyng = true;
      } else if (vid.seeking ||vid.readyState<4) {
        msg.state = "buffering";
        plyng = true;
      } else {
        msg.state = "playing";
        plyng = true;
      }
      if (plyng) {
        wasPlaying = true;
      }
      msg.availableActions = {
        "pause": plyng && msg.paused!=="paused",
        "play": plyng && msg.paused==="paused",
        "stop": plyng,
        "seek-content": plyng,
        "seek-relative": plyng,
        "seek-live": plyng,
        "seek-wallclock": plyng,
        "fast-forward": plyng,
        "fast-reverse": plyng,
      };
      if (msg.state==="paused" || msg.state==="buffering" || msg.state==="playing") {
        msg.currentTime = Math.round((vid.currentTime||0)*10)/10;
        msg.range = { "start": 0, "end": vid.duration };
        msg.accessibility = {
          "subtitles": { "enabled": false, "available": vid.textTracks.length===1 },
          "audioDescription": { "enabled": false, "available": vid.audioTracks===2 },
          "signLanguage": { "enabled": false, "available": false }
        };
        if (msg.accessibility.subtitles.available && vid.textTracks[0].mode==="showing") {
          msg.accessibility.subtitles.enabled = true;
        }
        if (msg.accessibility.audioDescription.available && vid.audioTracks[1].enabled) {
          msg.accessibility.audioDescription.enabled = true;
        }
      }
      if (msg.state!=="error" && msg.state!=="stopped" && (msg.state!=="no-media" || !wasPlaying)) {
        sendNotifyTimer();
      }
      if (mediawsconn.lastMessageReceived + 2000 < new Date().getTime()) {
        showUpdate("Media state", JSON.stringify(msg));
      }
      mediawsconn.send({ "method": "org.hbbtv.app.state.media", "params": msg });
      if (vid.ended) {
        if (receivedVoice) {
          showStatus(true, "Video ended, got at least one voice command");
        } else {
          showStatus(2, "Video ended, did not get any voice command");
        }
        doAbort();
      }
    }, 1000);
  }
  mediawsconn.timr = setTimeout(function() {
    // we now can start playing the video
    mediawsconn.timr = null;
    vid = document.getElementById("videobb");
    vid.innerHTML = '<source src="http://itv.mit-xperts.com/hbbtvtest/media/timecode.php/video.mp4"><'+'/source>';
    vid.play();
    sendNotifyTimer();
    mediawsconn.onmessage = function(evt) {
      var o;
      if (!evt || (typeof evt.method) !== "string") {
        return;
      }
      var mthd = evt.method.split(".");
      if (mthd[0]!=="org" || mthd[1]!=="hbbtv" || mthd[2]!=="app" || mthd[3]!=="intent") {
        return;
      }
      if (mthd[4]==="search") {
        if ((typeof evt.params) !== "object") {
          showStatus(false, "Received intent "+evt.method+" without params object");
          doAbort();
          return;
        }
        if ((typeof evt.params.origin) !== "string") {
          showStatus(false, "Received intent "+evt.method+" without params.origin");
          doAbort();
          return;
        }
        if ((typeof evt.params.query) !== "string") {
          showStatus(false, "Received intent "+evt.method+" without params.query");
          doAbort();
          return;
        }
        showUpdate("Received search intent", "query="+fixhtml(JSON.stringify(evt.params.query)));
      } else if (mthd[4]==="media") {
        if ((typeof evt.params) !== "object") {
          showStatus(false, "Received intent "+evt.method+" without params object");
          doAbort();
          return;
        }
        if ((typeof evt.params.origin) !== "string") {
          showStatus(false, "Received intent "+evt.method+" without params.origin");
          doAbort();
          return;
        }
        switch (mthd[5]) {
          case "pause":
            vid.pause();
            receivedVoice = true;
            break;
          case "play":
            vid.play();
            receivedVoice = true;
            break;
          case "stop":
            receivedVoice = true;
            break;
          case "seek-content":
          case "seek-relative":
          case "seek-live":
            if ((typeof evt.params.offset) !== "number") {
              showStatus(false, "Received intent "+evt.method+" with invalid params.offset = "+fixhtml(evt.params.offset));
              doAbort();
              return;
            }
            if (mthd[5]==="seek-content") {
              if (evt.params.anchor==="start") {
                o = 0;
              } else if (evt.params.anchor==="end") {
                o = vid.duration || 0;
              }
            } else {
              o = vid.currentTime || 0;
            }
            o += evt.params.offset;
            if (o<0 || o>vid.duration) {
              showStatus(false, "Received invalid seek position "+o+" from intent "+fixhtml(JSON.stringify(evt)));
              doAbort();
              return;
            }
            vid.currentTime = o;
            receivedVoice = true;
            break;
          case "fast-forward":
          case "fast-reverse":
            o = (vid.currentTime || 0) + (mthd[5]==="fast-reverse" ? -30 : 30);
            o = Math.max(0, Math.min(vid.duration-10, o));
            vid.currentTime = o;
            receivedVoice = true;
            break;
          case "seek-wallclock":
            o = evt.params["date-time"];
            if ((typeof o) !== "string" || o.length<20 || o.substring(4, 5)!=="-" || o.substring(7, 8)!=="-" || o.substring(10, 11)!=="T" || o.substring(13, 14)!==":" || o.substring(16, 17)!==":") {
              showStatus(false, "Received intent "+evt.method+" with invalid params.date-time = "+fixhtml(o));
              doAbort();
              return;
            }
            break;
          default:
            showStatus(2, "Received unknown intent "+evt.method);
            return;
        }
        showUpdate("Received media intent", JSON.stringify(evt));
      }
    };
  }, 2000);
}


//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<object id="video" type="video/mp4" style="position: absolute; left: 700px; top: 220px; width: 320px; height: 180px;"></object>
<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 90px; width: 500px; height: 360px;"></div>
<div id="vidcontainer" style="left: 1050px; top: 220px; width: 400px; height: 100px;">
  <object id="videobc" type="video/broadcast" style="position: absolute; left: 0px; top: 0px; width: 160px; height: 90px;"></object>
  <video id="videobb" style="position: absolute; left: 0px; top: 0px; width: 160px; height: 90px;"></video>
</div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="negotiate">Negotiate methods</li>
  <li name="supportinfo">SupportInfo API</li>
  <li name="supprsubtitles">Suppress Subtitles</li>
  <li name="supprdialogueEnhancement">Suppress DialogueEnhancement</li>
  <li name="suppruiMagnifier">Suppress UI Magnifier</li>
  <li name="supprhighContrastUI">Suppress high contrast UI</li>
  <li name="supprscreenReader">Suppress screen reader</li>
  <li name="supprresponseToUserAction">Suppress UA response</li>
  <li name="suppraudioDescription">Suppress Audio Description</li>
  <li name="supprinVisionSigning">Suppress in-vision Signing</li>
  <li name="featuresettings">FeatureSettings API (query)</li>
  <li name="voiceready">Voice ready</li>
  <li name="playvideo" automate="ignore">Play video for Voice Assistant API</li>
  <li name="voiceunready">Stop Voice ready</li>
  <li name="exit">Return to test menu</li>
</ul>
<div id="status" style="left: 600px; top: 300px; width: 500px; height: 400px; word-wrap: break-word;"></div>

</body>
</html>
