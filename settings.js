var service1 = [1,65283,28186,'430b010847000192a102200004','HbbTV-Testsuite1'];
var service2 = [1,65283,28187,service1[3],'HbbTV-Testsuite2'];
var serviceNetworkId = 1;
var autostartappname = 'This testsuite application';
var otherappurl = 'dvb://current.ait/13.1f5';
var localstorageappurl = 'dvb://current.ait/13.1f6';
var dsmccpreferappurl = 'dvb://current.ait/13.1f7';
var myappurl = 'dvb://current.ait/13.a';
var isdsmcc = false;
var dsmccctag = 23;
var seventctag = 19;
var vbcomponents = {
  'vid' : [ {'encrypted':false, 'aspectRatio':1.78} ],
  'aud' : [ {'encrypted':false, 'language':'deu', 'audioDescription':false}, {'encrypted':false, 'language':'fra', 'audioDescription':false} ],
  'sub' : [ {'encrypted':false, 'language':'deu', 'hearingImpaired':false } ]
};
