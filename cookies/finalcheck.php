<?php
$ROOTDIR='..';
require("$ROOTDIR/base.php");

sendContentType();
openDocument();

?>
<script type="text/javascript">
//<![CDATA[
window.onload = function() {
  initApp();
  setInstr('Verifying cookie...');
  var found = false;
  var needfind = <?php echo $_REQUEST['isset']?'true':'false'; ?>;
  var allCookies = document.cookie.split(';');
  for (var i=0; i<allCookies.length; i++) {
    var c = allCookies[i];
    while (c.charAt(0)==' ') {
      c = c.substring(1, c.length);
    }
    if (c.indexOf('mxphbbtv=testsuite')==0) {
      found = true;
      break;
    }
  }
  document.location.href = 'index.php?select=<?php echo (int)$_REQUEST['back']; ?>&found='+(found?1:0)+'&isok='+(found===needfind?1:0);
};

//]]>
</script>

</head><body>

<div style="left: 0px; top: 0px; width: 1280px; height: 720px; background-color: #132d48;" />

<?php echo appmgrObject(); ?>

<div class="txtdiv txtlg" style="left: 110px; top: 60px; width: 500px; height: 30px;">MIT-xperts HBBTV tests</div>
<div id="instr" class="txtdiv" style="left: 700px; top: 110px; width: 400px; height: 360px;"></div>
<ul id="menu" class="menu" style="left: 100px; top: 100px;">
  <li name="exit">Return to cookie test menu</li>
</ul>
<div id="status" style="left: 700px; top: 480px; width: 400px; height: 200px;"></div>

</body>
</html>
