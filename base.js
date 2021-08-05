var opts = false;
var selected = 0;
var onMenuSelect = null;
var automate = {"cookie":"mxphbbtvauto", "pin":0, "testrun":0};

function initVideo() {
  try {
    document.getElementById('video').bindToCurrentChannel();
  } catch (e) {
    // ignore
  }
  try {
    document.getElementById('video').setFullScreen(false);
  } catch (e) {
    // ignore
  }
}

function initApp() {
  try {
    var app = document.getElementById('appmgr').getOwnerApplication(document);
    app.show();
    app.activate(); // this is for HbbTV 0.5 backwards-compliance. It will throw an ignored exception on HbbTV 1.x devices, which is fine
  } catch (e) {
    // ignore
  }
  setKeyset(0x1+0x2+0x4+0x8+0x10);
}

function setKeyset(mask) {
  var elemcfg, app;
  // for HbbTV 0.5:
  try {
    elemcfg = document.getElementById('oipfcfg');
    elemcfg.keyset.value = mask;
  } catch (e) {
    // ignore
  }
  try {
    elemcfg = document.getElementById('oipfcfg');
    elemcfg.keyset.setValue(mask);
  } catch (e) {
    // ignore
  }
  // for HbbTV 1.0:
  try {
    app = document.getElementById('appmgr').getOwnerApplication(document);
    app.privateData.keyset.setValue(mask);
  } catch (e) {
    // ignore
  }
}

function registerKeyEventListener() {
  document.addEventListener("keydown", function(e) {
    if (handleKeyCode(e.keyCode)) {
      e.preventDefault();
    }
  }, false);
}
function registerMenuListener(execTest, noPreventDefault) {
  automate.execSelectedTest = function(execBefore) {
    var i, liid = opts[selected].getAttribute('name');
    automate.stepid = testPrefix+(testPrefix?'.':'')+liid;
    automate.note = opts[selected].getAttribute('automate')||null;
    if (execBefore) {
      execBefore();
    }
    i = liid.indexOf('#');
    if (i>0) {
      liid = liid.substring(0, i);
    }
    execTest(liid);
  };
  document.addEventListener("keydown", function(e) {
    var kc = e.keyCode;
    if (kc===VK_UP) {
      menuSelect(selected-1);
      if (!noPreventDefault) {
        e.preventDefault();
      }
    } else if (kc===VK_DOWN) {
      menuSelect(selected+1);
      if (!noPreventDefault) {
        e.preventDefault();
      }
    } else if (kc===VK_LEFT) {
      menuSelect(selected-6);
      if (!noPreventDefault) {
        e.preventDefault();
      }
    } else if (kc===VK_RIGHT) {
      menuSelect(selected+6);
      if (!noPreventDefault) {
        e.preventDefault();
      }
    } else if (kc===VK_ENTER) {
      if (!noPreventDefault) {
        e.preventDefault();
      }
      automate.execSelectedTest(null);
    } else if (kc===VK_BLUE) {
      setTimeout(function() {
        stopAutomation();
      }, 10);
    }
  }, false);
}

function menuInit() {
  opts = document.getElementById('menu').getElementsByTagName('li');
  menuSelect(0);
}

function menuSelect(i) {
  if (i<=0) {
    i = 0;
  } else if (i>=opts.length) {
    i = opts.length-1;
  }
  selected = i;
  var scroll = Math.max(0, Math.min(opts.length-13, selected-6));
  for (i=0; i<opts.length; i++) {
    opts[i].style.display = (i>=scroll && i<scroll+13) ? 'block' : 'none';
    opts[i].className = selected==i ? 'lisel' : '';
  }
  if (onMenuSelect) {
    onMenuSelect();
  }
}
function menuSelectByName(snam) {
  var i, check;
  if (!snam) return;
  for (i=0; i<opts.length; i++) {
    check = opts[i].getAttribute('name');
    if (check===snam) {
      menuSelect(i);
      break;
    }
  }
}

function reportStatus(stepid, succss, note, txt) {
  var url, req = null;
  if (!stepid) {
    return null;
  }
  try {
    url = '../report.php?step='+encodeURIComponent(''+stepid);
    url += '&succss='+(succss===2?2:(succss?0:1));
    url += '&pin='+encodeURIComponent(''+automate.pin);
    url += '&run='+encodeURIComponent(''+automate.testrun);
    url += '&note='+encodeURIComponent(''+note);
    url += '&txt='+encodeURIComponent(''+txt);
  } catch (ignore) {
  }
  return req;
}

function showStatus(succss, txt) {
  var req, elem = document.getElementById('status');
  elem.className = succss===2 ? 'statwarn' : (succss ? 'statok' : 'statfail');
  if (!txt) {
    elem.innerHTML = '';
    return;
  }
  try {
    elem.innerHTML = '<b>Status:<'+'/b><br />'+txt;
  } catch (ignore) {
    elem.innerHTML = '<b>Status:<'+'/b><br />Cannot display message.';
  }
  if (succss) {
    setInstr('Test succeeded, please execute the next test<br />(press OK).');
    if (opts) menuSelect(selected+1);
  } else {
    setInstr('Test failed, please return to test menu<br />(press OK).');
    if (opts) menuSelect(opts.length-1);
  }
  if (automate.req) {
    try {
      automate.req.abort();
    } catch (ignore) {
    }
  }
  req = reportStatus(automate.stepid, succss, automate.note, txt);
  if (req) {
    automate.req = req;
    runNextAutoTest();
  }
}

function showAppStartStatus(succss, txt) {
  if (!succss) {
    showStatus(succss, txt);
    return;
  }
  setInstr(txt);
}

function setInstr(txt) {
  var instr = document.getElementById('instr');
  try {
    instr.innerHTML = txt;
  } catch (ignore) {
    instr.innerHTML = "Cannot display message.";
  }
}

function runNextAutoTest(forceStart) {
  var txt, i, j, blanks, eq, goingDeep = null, nextName = null, selidx = 0, cookieval = [];
  try {
    txt = document.cookie.split(";");
    for (i=0; i<txt.length; i++) {
      j = txt[i].indexOf("=");
      blanks = 0;
      while (j>0 && blanks<txt[i].length && txt[i].substring(blanks, blanks+1)===" ") {
        blanks++;
      }
      if (j>0 && automate.cookie===txt[i].substring(blanks, j)) {
        cookieval = decodeURIComponent(txt[i].substring(j+1)).split('.');
      }
    }
  } catch (e) {
    // ignore
  }
  if (forceStart && !isNaN(automate.pin) && automate.pin>0) {
    cookieval[0] = automate.pin;
    cookieval[1] = Math.floor(Math.random()*999999)+1;
  } else if (cookieval.length<2) {
    return;
  } else {
    for (i=0; i<2; i++) {
      cookieval[i] = parseInt(cookieval[i], 10);
      if (isNaN(cookieval[i]) || cookieval[i]<1) {
        return;
      }
    }
  }
  automate.pin = cookieval[0];
  automate.testrun = cookieval[1];
  txt = testPrefix ? testPrefix.split('.') : [];
  eq = txt.length<cookieval.length-2;
  for (i=0; eq&&i<txt.length; i++) {
    eq = txt[i]===cookieval[i+2];
  }
  if (!forceStart && eq && cookieval[txt.length+2]) {
    nextName = cookieval[txt.length+2];
    menuSelectByName(nextName);
    selidx = selected;
    goingDeep = cookieval.length>txt.length+3 ? cookieval[txt.length+3] : null;
    if (!goingDeep || 'exit'===goingDeep) {
      selidx++;
      goingDeep = null;
    }
  }
  while (selidx<opts.length && 'ignore'===opts[selidx].getAttribute('automate')) {
    reportStatus(testPrefix+(testPrefix?'.':'')+opts[selidx].getAttribute('name'), 2, 'ignore', 'This test is not part of the automated testsuite. Please run it manually.');
    selidx++;
  }
  if (selidx>=opts.length) {
    if (!nextName || nextName!=='exit') {
      stopAutomation();
    }
    return;
  }
  if (automate.timer) {
    clearTimeout(automate.timer);
  }
  automate.timer = setTimeout(function() {
    automate.timer = null;
    menuSelect(selidx);
    automate.execSelectedTest(function() {
      if (!goingDeep) {
        document.cookie = automate.cookie+'='+automate.pin+'.'+automate.testrun+'.'+automate.stepid+';expires='+((new Date(new Date().getTime()+600000)).toGMTString())+';path=/';
      }
    });
  }, 2000);
  try {
    i = document.getElementById('bgdiv');
    if (i) {
      i.style.backgroundColor = '#300040';
    }
  } catch (ignore) {
  }
}
function stopAutomation() {
  document.cookie = automate.cookie+'=0;expires='+((new Date()).toGMTString())+';path=/';
  if (automate.timer) {
    clearTimeout(automate.timer);
  }
  try {
    var i = document.getElementById('bgdiv');
    if (i) {
      i.style.backgroundColor = '#132d48';
    }
  } catch (ignore) {
  }
}

