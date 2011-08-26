var service1 = [1,65283,28186,'430b010847000192a102200004','HbbTV-Testsuite1'];
var service2 = [1,65283,28187,service1[3],'HbbTV-Testsuite2'];
var autostartappname = 'This testsuite application';
var otherappurl = 'dvb://current.ait/13.1f5';
var myappurl = 'dvb://current.ait/13.a';
var isdsmcc = false;
var dsmccctag = 23;
var seventctag = 19;
var vbcomponents = {
  'vid' : [ {'encoding':'MPEG2_SD_25', 'encrypted':false, 'aspectRatio':1.78} ],
  'aud' : [ {'encrypted':false, 'language':'deu', 'audioDescription':false}, {'encrypted':false, 'language':'fra', 'audioDescription':false} ],
  'sub' : [ {'encrypted':false, 'language':'deu', 'hearingImpaired':false } ]
};
var streamtime = [ 1315669260, 17, 41 ];
