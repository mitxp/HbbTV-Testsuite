var opts = false;
var selected = 0;

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
  // for HbbTV 0.5:
  try {
    var elemcfg = document.getElementById('oipfcfg');
    elemcfg.keyset.value = mask;
  } catch (e) {
    // ignore
  }
  try {
    var elemcfg = document.getElementById('oipfcfg');
    elemcfg.keyset.setValue(mask);
  } catch (e) {
    // ignore
  }
  // for HbbTV 1.0:
  try {
    var app = document.getElementById('appmgr').getOwnerApplication(document);
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
}

function showStatus(succss, txt) {
  var elem = document.getElementById('status');
  elem.className = succss===2 ? 'statwarn' : (succss ? 'statok' : 'statfail');
  if (!txt) {
    elem.innerHTML = '';
    return;
  }
  elem.innerHTML = '<b>Status:<'+'/b><br />'+txt;
  if (succss) {
    setInstr('Test succeeded, please execute the next test<br />(press OK).');
    if (opts) menuSelect(selected+1);
  } else {
    setInstr('Test failed, please return to test menu<br />(press OK).');
    if (opts) menuSelect(opts.length-1);
  }
}

function setInstr(txt) {
  document.getElementById('instr').innerHTML = txt;
}

