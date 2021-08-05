var service1 = [1,1073,28221,'430b0121095001928102750003','ARD-TEST-1'];
var service2 = [1,1073,28205,service1[3],'rbb Brandenburg'];
var serviceNetworkId = 1;
var autostartappname = 'The ARD start bar';
var otherappurl = 'dvb://current.ait/13.1f5';
var localstorageappurl = 'dvb://current.ait/13.1f6';
var dsmccpreferappurl = 'dvb://current.ait/13.1f7';
var myappurl = 'dvb://current.ait/13.a';
var isdsmcc = false;
var dsmccctag = 23;
var seventctag = 19;
var vbcomponents = {
  'vid' : [ {'encoding':'MPEG2_SD_25', 'encrypted':false, 'aspectRatio':1.78} ],
  'aud' : [ {'encrypted':false, 'language':'deu', 'audioDescription':false}, {'encrypted':false, 'language':'fra', 'audioDescription':false} ],
  'sub' : [ {'encrypted':false, 'language':'deu', 'hearingImpaired':false } ]
};
