var releaseinfo = '1.4.0 (20110224)';
var service1 = [1,65282,28194,'430b010847000192a102200004'];
var service2 = [1,65282,28194,service1[3]];
var autostartappname = 'This testsuite application';
var otherappurl = 'dvb://current.ait/13.1f5?param2=value2';
var myappurl = 'dvb://current.ait/13.a';
var isdsmcc = false;
var dsmccctag = 23;
var seventctag = 19;
var vbcomponents = {
  'vid' : [ {'encoding':'MPEG2_SD_25', 'encrypted':false, 'aspectRatio':1.78} ],
  'aud' : [ {'encoding':'MPEG1_L2', 'encrypted':false, 'language':'deu', 'audioDescription':false, 'audioChannels':2}, {'encoding':'MPEG1_L2', 'encrypted':false, 'language':'fra', 'audioDescription':false, 'audioChannels':2} ],
  'sub' : [ {'encrypted':false, 'language':'deu', 'hearingImpaired':false } ]
};
