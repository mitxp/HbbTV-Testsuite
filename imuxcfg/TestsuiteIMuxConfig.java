package com.mitxp.hbbtvtest.imuxcfg;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.IOException;
import java.util.Vector;

import com.mitxp.imux.api.components.AIT;
import com.mitxp.imux.api.components.AITItem;
import com.mitxp.imux.api.components.Application;
import com.mitxp.imux.api.components.Carousel;
import com.mitxp.imux.api.components.Service;
import com.mitxp.imux.api.components.ServiceTStreamItem;
import com.mitxp.imux.api.components.TStream;
import com.mitxp.imux.api.components.TStreamEIT;
import com.mitxp.imux.api.components.TStreamEITConfig;
import com.mitxp.imux.api.components.TStreamEITEvent;
import com.mitxp.imux.api.components.TStreamEITService;
import com.mitxp.imux.api.components.TStreamPackets;
import com.mitxp.imux.api.components.TStreamPacketsPID;
import com.mitxp.imux.api.IMuxConnection;
import com.mitxp.imux.api.SimpleCommands;


/**
 * Installs the HbbTV test suite on an iMux server.
 *
 * @author Johannes Schmid, MIT-xperts GmbH
 */
public class TestsuiteIMuxConfig {

  /** the original network id. */
  private static final int ONID = 1;

  /** the transport stream id. */
  private static final int TSID = 65283;

  /** the carousel component tag id. */
  private static final int DSMCC_CTAG = 23;

  /** the stream event component tag id. */
  private static final int SEVENT_CTAG = 19;

  /** the service IDs of the two testsuite services. */
  private static final int[] SERVICE_IDS = new int[] { 28186, 28187 };


  /**
   * The main command line method.
   *
   * @param args the command line arguments. Call with <code>--help</code>
   * to get usage information.
   */
  public static void main(String[] args) {
    String websrvr = null;
    String imuxhost = "127.0.0.1", user = "admin", password = "imux";
    File carouselDir = new File("dsmcc");
    File tsFilesDir = new File("tsfiles"); 
    if (args.length==0 || "--help".equalsIgnoreCase(args[0])) {
      printUsage();
    }
    for (int i=0; i+1<args.length; i += 2) {
      String opt = args[i];
      if ("--host".equals(opt)) {
        imuxhost = args[i+1];
      } else if ("--user".equals(opt)) {
        user = args[i+1];
      } else if ("--password".equals(opt)) {
        password = args[i+1];
      } else if ("--carouseldir".equals(opt)) {
        carouselDir = new File(args[i+1]);
      } else if ("--tsdir".equals(opt)) {
        tsFilesDir = new File(args[i+1]);
      } else if ("--websrvr".equals(opt)) {
        websrvr = args[i+1];
      } else if ("--help".equals(opt)) {
        printUsage();
      } else {
        System.err.println("Invalid option: "+opt);
        System.exit(1);
      }
    }
    if (websrvr==null) {
      System.err.println("Missing web server URL");
      System.exit(1);
    }
    if (!websrvr.startsWith("http://")) {
      System.err.println("Invalid web server URL: "+websrvr);
      System.exit(1);
    }
    if (!websrvr.endsWith("/")) {
      websrvr += "/";
    }
    if (!tsFilesDir.isDirectory()) {
      System.err.println("invalid TS files directory: "+tsFilesDir.getAbsolutePath());
      System.exit(1);
    }
    if (!carouselDir.isDirectory()) {
      System.err.println("invalid carousel directory: "+carouselDir.getAbsolutePath());
      System.exit(1);
    }
    IMuxConnection conn = null;
    try {
      conn = IMuxConnection.connect(imuxhost, user, password);
    } catch (Exception e) {
      System.err.println("Cannot connect to iMux host "+user+"@"+imuxhost+": "+e);
      System.exit(1);
    }
    boolean failed = true;
    try {
      System.out.println("Apps...");
      Application[] aitApps = configureApps(conn, websrvr, carouselDir);
      System.out.println("AIT...");
      AIT ait = configureAIT(conn, aitApps);
      System.out.println("Streams...");
      TStreamPackets[] tsStreams = configureStreams(conn, tsFilesDir);
      System.out.println("EIT...");
      TStreamEIT eit = configureEit(conn);
      TStream[] svcStreams = new TStream[tsStreams.length+1];
      System.arraycopy(tsStreams, 0, svcStreams, 0, tsStreams.length);
      svcStreams[svcStreams.length-1] = eit;
      System.out.println("Services...");
      configureServices(conn, ait, svcStreams);
      System.out.println("Activate...");
      new SimpleCommands(conn).activate(true);
      System.out.println("Done.");
      failed = false;
    } catch (Exception e) {
      System.err.println("iMux configuration failed.");
      e.printStackTrace();
    } finally {
      try {
        conn.close();
      } catch (Exception e) {
        // ignore
      }
    }
    System.exit(failed ? 1 : 0);
  }

  /**
   * Print usage information.
   */
  public static void printUsage() {
    String[] lines = new String[] {
      "USAGE: [--host <imuxhost>] [--user <imuxuser>] [--password <imuxpwd>]",
      "       [--carouseldir <carouseldir>] [--tsdir <tsdir>] --websrvr <websrvrurl>",
      "",
      "  With the following options:",
      "       <imuxhost>    host name of iMux (default: 127.0.0.1)",
      "       <imuxuser>    login user name for iMux (default: admin)",
      "       <imuxpwd>     login password for iMux (default: imux)",
      "       <carouseldir> path to directory containing carousel content",
      "                     (default: dsmcc)",
      "       <tsdir>       path to directory containing transport stream files",
      "                     (default: ../streams)",
      "       <websrvrurl>  URL pointing to install path of testsuite on web server",
      "",
      "  Example arguments:",
      "  --host imux --user admin --password imux --carouseldir dsmcc \\",
      "  --tsdir tsfiles --websrvr http://itv.mit-xperts.com/hbbtvtest/",
    };
    for (String line : lines) {
      System.err.println(line);
    }
    System.exit(1);
  }

  /**
   * Configure the {@link Application}s.
   *
   * @param conn the {@link IMuxConnection}.
   * @param websrvr the URL as String pointing to the test suite root directory
   * on the web server.
   * @param carouselDir the directory containing the carousel content for
   * DSM-CC-based apps.
   * @return the array of {@link Application}s to set in the AIT.
   * @throws IOException if iMux connection fails.
   */
  private static Application[] configureApps(IMuxConnection conn, String websrvr,
  File carouselDir) throws IOException {
    StringBuffer urlb = new StringBuffer();
    urlb.append(websrvr).append(';');
    urlb.append("https:").append(websrvr.substring(5)).append(';');
    urlb.append("dvb://").append(Integer.toHexString(ONID)).append('.');
    urlb.append(Integer.toHexString(TSID)).append('.');
    urlb.append(Integer.toHexString(SERVICE_IDS[0]));
    urlb.append('.').append(Integer.toHexString(DSMCC_CTAG)).append('/');
    String urlBoundTS = urlb.toString();
    String urlBoundOther = websrvr+";"+websrvr.substring(0, websrvr.length()-1)+"1/";

    Application localStoreApp = configureApp(
      conn, "HbbTV-TestsuiteLocalStore", "", "index.html", 502, true, ""
    );
    Application dsmccPreferApp = configureApp(
      conn, "HbbTV-TestsuitePreferDsmcc", websrvr+"appmanager/",
      "preferdsmcc.html", 503, true, ""
    );
    dsmccPreferApp.setPreferDsmcc(true);
    Carousel carousel = configureCarousel(conn, carouselDir);
    localStoreApp.setDsmccSource(carousel);
    dsmccPreferApp.setDsmccSource(carousel);
    
    Application[] ret = new Application[] {
      configureApp(conn, "HbbTV-Testsuite", websrvr, "index.php", 10, false, urlBoundTS),
      configureApp(conn, "HbbTV-TestsuiteOther", websrvr,
        "appmanager/otherapp.php?param1=value1", 501, true, urlBoundOther
      ),
      configureApp(conn, "HbbTV-TestsuiteXML", websrvr+"appmanager/",
        "xmlaitapp.php", 500, false, ""
      ),
      localStoreApp,
      dsmccPreferApp,
    };
    ret[2].setAITName("HbbTV Testsuite XML AIT Test");
    return ret;
  }

  /**
   * Create/configure an application entry in the AIT.
   *
   * @param conn the {@link IMuxConnection}.
   * @param name the application name.
   * @param urlbase the application base URL as String or <code>null</code>.
   * @param startPage the start page relative to the base URL.
   * @param appId the application ID.
   * @param serviceBound <code>true</code> for service bound.
   * @param urlBoundaries the URL boundaries as String.
   * @return the {@link Application}.
   * @throws IOException if iMux connection fails.
   */
  private static Application configureApp(IMuxConnection conn, String name,
  String urlbase, String startPage, int appId, boolean serviceBound, String urlBoundaries)
  throws IOException {
    int orgId = 19;
    Application[] list = Application.getByName(conn, name);
    Application ret;
    if (list.length==0) {
      ret = Application.createApplication(conn, name, orgId, appId);
    } else {
      ret = list[0];
      ret.setOrgId(orgId);
      ret.setAppId(appId);
    }
    ret.setAITName(name);
    ret.setApplicationType(Application.APPTYPE_HBBTV);
    ret.setProfile(Application.PROFILE_HBBTV_BASIC);
    ret.setLanguage(Application.LANGUAGE_ENG);
    ret.setVersion(1, 1, 1);
    ret.setWebURLs(urlbase);
    ret.setVisibility(Application.VISIBILITY_VISIBLE);
    ret.setPriority(5);
    ret.setServiceBound(serviceBound);
    ret.setInitialPage(startPage);
    if (!urlBoundaries.equals(ret.getUrlBoundaries())) {
      ret.setUrlBoundaries(urlBoundaries);
    }
    return ret;
  }

  /**
   * Configure the {@link AIT}.
   *
   * @param conn the {@link IMuxConnection}.
   * @param aitApps the array of {@link Application}s to set in the AIT.
   * @return the {@link AIT}
   * @throws IOException if iMux connection fails.
   */
  private static AIT configureAIT(IMuxConnection conn, Application[] aitApps)
  throws IOException {
    String name = "HbbTV-Testsuite";
    AIT[] list = AIT.getByName(conn, name);
    AIT ret;
    if (list.length==0) {
      ret = AIT.createAIT(conn, name);
    } else {
      ret = list[0];
    }
    AITItem[] aitItems = ret.getAITItems();
    Application autostart = aitApps[0];
    Application[] apps = new Application[aitApps.length];
    System.arraycopy(aitApps, 0, apps, 0, apps.length);
    for (int i=0; i<aitItems.length; i++) {
      aitItems[i].setEnabled(true);
      int id = aitItems[i].getApplication().getId();
      boolean found = false;
      for (int j=0; j<apps.length; j++) {
        if (apps[j]!=null && apps[j].getId()==id) {
          apps[j] = null;
          found = true;
        }
      }
      if (found) {
        aitItems[i] = null;
      }
    }
    for (int i=0; i<aitItems.length; i++) {
      if (aitItems[i]==null) {
        continue;
      }
      Application found = null;
      for (int j=0; j<apps.length; j++) {
        if (apps[j]!=null) {
          found = apps[j];
          apps[j] = null;
          break;
        }
      }
      if (found==null) {
        aitItems[i].delete();
      } else {
        aitItems[i].setApplication(found);
      }
    }
    for (int i=0; i<apps.length; i++) {
      if (apps[i]!=null) {
        ret.createAITItem(apps[i]);
      }
    }
    aitItems = ret.getAITItems();
    for (int i=0; i<aitItems.length; i++) {
      boolean isAutostart = aitItems[i].getApplication().getId()==autostart.getId();
      int ctrl = isAutostart? AITItem.CONTROL_CODE_AUTOSTART : AITItem.CONTROL_CODE_PRESENT;
      if (aitItems[i].getControlCode()!=ctrl) {
        aitItems[i].setControlCode(ctrl);
      }
    }
    return ret;
  }

  /**
   * Configure all {@link TStreamPackets}s.
   *
   * @param conn the {@link IMuxConnection}.
   * @param tsFilesDir the directory containing all transport stream files.
   * @return the array of {@link TStreamPackets}s to add to the service.
   * @throws IOException if iMux connection fails.
   */
  private static TStreamPackets[] configureStreams(IMuxConnection conn, File tsFilesDir)
  throws IOException {
    TStreamPackets[] ret = new TStreamPackets[3];
    File file = new File(tsFilesDir, "bcastav.ts");
    ret[0] = uploadStream(conn, "Testsuite-AV-Subtitles", file, 8115534);
    for (TStreamPacketsPID pid : ret[0].getTStreamPacketsPIDs()) {
      switch (pid.getSourcePID()) {
        case 601:
          pid.setDestinationPID(201);
          pid.setPidType(TStreamPacketsPID.PID_TYPE_VIDEO_MPEG2);
          pid.setComponentTag(1);
          break;
        case 602:
          pid.setDestinationPID(202);
          pid.setPidType(TStreamPacketsPID.PID_TYPE_AUDIO_MPEG1);
          pid.setLanguageId(Application.LANGUAGE_DEU);
          pid.setComponentTag(2);
          break;
        case 605:
          pid.setDestinationPID(204);
          pid.setPidType(TStreamPacketsPID.PID_TYPE_SUBTITLES);
          pid.setLanguageId(Application.LANGUAGE_DEU);
          pid.setComponentTag(6);
          break;
        default:
          pid.setDestinationPID(-1);
      }
    }
    file = new File(tsFilesDir, "2ndaudio.ts");
    ret[1] = uploadStream(conn, "Testsuite-2nd-Audio", file, 300000);
    for (TStreamPacketsPID pid : ret[1].getTStreamPacketsPIDs()) {
      switch (pid.getSourcePID()) {
        case 102:
          pid.setDestinationPID(203);
          pid.setPidType(TStreamPacketsPID.PID_TYPE_AUDIO_MPEG1);
          pid.setLanguageId(Application.LANGUAGE_FRA);
          pid.setComponentTag(3);
          break;
        default:
          pid.setDestinationPID(-1);
      }
    }
    file = new File(tsFilesDir, "sevent.ts");
    ret[2] = uploadStream(conn, "Testsuite-SEvent", file, 103400);
    for (TStreamPacketsPID pid : ret[2].getTStreamPacketsPIDs()) {
      switch (pid.getSourcePID()) {
        case 5006:
          pid.setDestinationPID(206);
          pid.setPidType(TStreamPacketsPID.PID_TYPE_DSMCC_HBBTV);
          pid.setCarouselId(12);
          pid.setComponentTag(DSMCC_CTAG);
          break;
        case 5007:
          pid.setDestinationPID(207);
          pid.setPidType(TStreamPacketsPID.PID_TYPE_STREAM_EVENT);
          pid.setCarouselId(-1);
          pid.setComponentTag(SEVENT_CTAG);
          break;
        default:
          pid.setDestinationPID(-1);
      }
    }
    return ret;
  }

  /**
   * Create or update a {@link TStreamPackets}.
   *
   * @param conn the {@link IMuxConnection}.
   * @param name the stream name.
   * @param tsFile the transport stream File.
   * @param bitrate the playout bitrate for the stream.
   * @return the {@link TStreamPackets}.
   * @throws IOException if iMux connection fails.
   */
  private static TStreamPackets uploadStream(IMuxConnection conn, String name,
  File tsFile, int bitrate)
  throws IOException {
    TStreamPackets ret = null;
    for (TStream check : TStream.getByName(conn, name)) {
      if (!(check instanceof TStreamPackets)) {
        continue;
      }
      ret = (TStreamPackets)check;
      break;
    }
    if (ret==null) {
      ret = TStream.createTStreamPackets(conn, name);
    }
    if (ret.getFileSize()!=tsFile.length()) {
      ret.update(tsFile, true);
    }
    if (ret.getBitrate()!=bitrate) {
      ret.setBitrate(bitrate);
    }
    return ret;
  }

  /**
   * Create/configure the EIT.
   *
   * @param conn the {@link IMuxConnection}.
   * @return the {@link TStreamEIT}.
   * @throws IOException if iMux connection fails.
   */
  private static TStreamEIT configureEit(IMuxConnection conn) throws IOException {
    String name = "EIT";
    TStreamEIT ret = null;
    for (TStream check : TStream.getByName(conn, name)) {
      if (check instanceof TStreamEIT) {
        ret = (TStreamEIT)check;
        break;
      }
    }
    if (ret==null) {
      ret = TStream.createTStreamEIT(conn, name);
    }
    int bitrate = 100000, pid = 18, pkg = TStreamEITConfig.PACKAGING_MAX_COMPAT;
    if (ret.getBitrate()!=bitrate) {
      ret.setBitrate(bitrate);
    }
    if (ret.getPID()!=pid) {
      ret.setPID(pid);
    }
    TStreamEITConfig cfg = ret.getTStreamEITConfig();
    if (!cfg.isAutoBitrate()) {
      cfg.setAutoBitrate(true);
    }
    if (cfg.getPackagingType()!=pkg) {
      cfg.setPackagingType(pkg);
    }
    if (!cfg.isManualConfig()) {
      cfg.setManualConfig(true);
    }
    if (cfg.getONId()!=ONID) {
      cfg.setONId(ONID);
    }
    if (cfg.getTSId()!=TSID) {
      cfg.setTSId(TSID);
    }
    TStreamEITService[] services = new TStreamEITService[SERVICE_IDS.length];
    for (TStreamEITService check : ret.getTStreamEITServices()) {
      if (check.getONId()!=ONID || check.getTSId()!=TSID) {
        continue;
      }
      int sid = check.getSId();
      for (int i=0; i<SERVICE_IDS.length; i++) {
        if (sid==SERVICE_IDS[i]) {
          services[i] = check;
        }
      }
    }

    long now = System.currentTimeMillis();
    now -= (now%60000L);
    TStreamEITEvent present = new TStreamEITEvent(1, now-600000L, 300000L, false,
      TStreamEITEvent.RUNNINGSTATUS_RUNNING, getEITDescriptors(1, 6)
    );
    TStreamEITEvent followg = new TStreamEITEvent(2, now-300000L, 300000L, false,
      TStreamEITEvent.RUNNINGSTATUS_FEWSECONDS, getEITDescriptors(2, 6)
    );
    TStreamEITEvent[] pfEvents = new TStreamEITEvent[] {present, followg};
    TStreamEITEvent[] schedEvents = createScheduledEvents(pfEvents);

    for (int i=0; i<services.length; i++) {
      TStreamEITService service = services[i];
      if (service==null) {
        service = ret.createTStreamEITService(ONID, TSID, SERVICE_IDS[i]);
      }
      service.setEvents(pfEvents, true);
      service.setEvents(schedEvents, false);
    }
    return ret;
  }

  /**
   * Create a scheduled EIT events list.
   *
   * @param in the events to integrate into the event list (prefix 2 events
   * and add 50 more events at the end).
   * @return an array of scheduled {@link TStreamEITEvent}s.
   */
  private static TStreamEITEvent[] createScheduledEvents(TStreamEITEvent[] in) {
    Vector<TStreamEITEvent> ret = new Vector<TStreamEITEvent>();
    long start = in[0].getStartTime();
    int rstatus = TStreamEITEvent.RUNNINGSTATUS_UNDEFINED;
    for (int i=-2; i<0; i++) {
      byte[] dp = getEITDescriptors(i+1, 0);
      long s = start - i*3600000L;
      ret.add(new TStreamEITEvent(100+i, s, 3600000L, false, rstatus, dp));
    }
    for (int i=0; i<in.length; i++) {
      TStreamEITEvent e = in[i];
      ret.add(new TStreamEITEvent(e.getEventId(), e.getStartTime(),
        e.getDuration(), e.isScrambled(), rstatus, e.getDescriptors()
      ));
    }
    start = in[in.length-1].getStartTime() + in[in.length-1].getDuration();
    for (int i=0; i<50; i++) {
      byte[] dp = getEITDescriptors(i+3, 0);
      long s = start + i*3600000L;
      ret.add(new TStreamEITEvent(100+i, s, 3600000L, false, rstatus, dp));
    }
    TStreamEITEvent[] retarr = new TStreamEITEvent[ret.size()];
    ret.copyInto(retarr);
    return retarr;
  }

  /**
   * Get SI descriptors for an EIT event.
   *
   * @param num the event number (to be placed in the event name, -9..99).
   * @param pcontrolage age if parental control is advised, <code>0</code> otherwise.
   * @return the descriptors as byte array.
   */
  private static byte[] getEITDescriptors(int num, int pcontrolage) {
    byte[] lang = new byte[] {'D', 'E', 'U'};
    byte[] title = new byte[] {
      5, 69, 118, 101, 110, 116, 32, 48, 49, 44, 32, 117, 109, 108, 97, 117, 116, 32, -28,
    };
    String numstr = String.valueOf(num);
    if (numstr.length()==1) {
      numstr = "0"+numstr;
    }
    title[7] = (byte)numstr.charAt(0);
    title[8] = (byte)numstr.charAt(1);
    byte[] subtitle = new byte[] { 5, 115, 117, 98, 116, 105, 116, 108, 101 };
    ByteArrayOutputStream bos = new ByteArrayOutputStream();
    bos.write(0x4d); // short_event_descriptor
    bos.write(3+1+title.length+1+subtitle.length); // length
    bos.write(lang, 0, lang.length);
    bos.write(title.length);
    bos.write(title, 0, title.length);
    bos.write(subtitle.length);
    bos.write(subtitle, 0, subtitle.length);
    if (pcontrolage>3 && pcontrolage<19) {
      bos.write(0x55); // parental rating descriptor
      bos.write(4); // length
      bos.write(lang, 0, lang.length);
      bos.write(pcontrolage-3); // minimum age 18
    }
    // component descriptor (required for AVComponents test)
    bos.write(0x50);
    bos.write(6); // length
    bos.write(0xf1); // video
    bos.write(0x03); // MPEG SD, 16:9
    bos.write(0x01); // component_tag 1
    bos.write(0x6d); // language code: (m)is
    bos.write(0x69); // language code: m(i)s
    bos.write(0x73); // language code: mi(s)
    // private_data_specifier_descriptor
    bos.write(0x5f);
    bos.write(0x04);
    bos.write(0x00);
    bos.write(0x00);
    bos.write(0x00);
    bos.write(0x05); // ARD, ZDF, ORF
    return bos.toByteArray();
  }

  /**
   * Configure the services on the iMux.
   *
   * @param conn the {@link IMuxConnection}.
   * @param ait the service {@link AIT}.
   * @param svcStreams the service streams as {@link TStream}.
   * @throws IOException if iMux connection fails.
   */
  private static void configureServices(IMuxConnection conn, AIT ait,
  TStream[] svcStreams) throws IOException {
    int[] pmtPids = new int[] { 406, 407 };
    for (int svcidx=0; svcidx<SERVICE_IDS.length; svcidx++) {
      String name = "HbbTV-Testsuite"+(svcidx+1);
      int serviceId = SERVICE_IDS[svcidx];
      Service svc = null;
      Service[] find = Service.getByName(conn, name);
      if (find.length==0) {
        svc = Service.createService(conn, name, serviceId);
      } else {
        svc = find[0];
        if (svc.getServiceId()!=serviceId) {
          svc.setServiceId(serviceId);
        }
      }
      svc.setPmtPID(pmtPids[svcidx]);
      svc.setPcrPID(201);
      svc.setEnabled(true);
      svc.setEITReferenceEnabled(true);
      svc.setServiceType(Service.SERVICE_TYPE_TV);
      svc.setAIT(ait);
      ServiceTStreamItem[] streamItems = svc.getServiceTStreamItems();
      TStream[] streams = new TStream[svcStreams.length];
      System.arraycopy(svcStreams, 0, streams, 0, streams.length);
      for (int i=0; i<streamItems.length; i++) {
        streamItems[i].setEnabled(true);
        int id = streamItems[i].getTStream().getId();
        boolean found = false;
        for (int j=0; j<streams.length; j++) {
          if (streams[j]!=null && streams[j].getId()==id) {
            streams[j] = null;
            found = true;
          }
        }
        if (found) {
          streamItems[i] = null;
        }
      }
      for (int i=0; i<streamItems.length; i++) {
        if (streamItems[i]==null) {
          continue;
        }
        TStream found = null;
        for (int j=0; j<streams.length; j++) {
          if (streams[j]!=null) {
            found = streams[j];
            streams[j] = null;
          }
        }
        if (found==null) {
          streamItems[i].delete();
        } else {
          streamItems[i].setTStream(found);
        }
      }
      for (int i=0; i<streams.length; i++) {
        if (streams[i]!=null) {
          svc.createServiceTStreamItem(streams[i]);
        }
      }
    }
  }

  /**
   * Configure the {@link Carousel} for DSM-CC-based apps.
   *
   * @param conn the {@link IMuxConnection}.
   * @param carouselDir the directory containing the carousel content for
   * DSM-CC-based apps.
   * @return the {@link Carousel}.
   * @throws IOException if iMux connection fails.
   */
  private static Carousel configureCarousel(IMuxConnection conn, File carouselDir)
  throws IOException {
    String name = "HbbTV-TestsuiteDsmcc";
    Carousel[] list = Carousel.getByName(conn, name);
    Carousel ret;
    int carouselId = 20;
    int dataPid = 208;
    int dataCTag = carouselId;
    if (list.length==0) {
      ret = Carousel.createCarousel(conn, name, carouselId, dataPid, dataCTag);
    } else {
      ret = list[0];
      ret.setCarouselId(carouselId);
      ret.setDataPID(dataPid);
      ret.setDataComponentTag(dataCTag);
    }
    ret.setDataBitrate(200000);
    ret.objectDelete("/");
    ret.objectUploadDirectory("/", carouselDir, true);
    if (!new File(carouselDir, "settings.js").isFile()) {
      File settings = new File(carouselDir.getParentFile().getParentFile(), "settings.js");
      if (settings.isFile()) {
        throw new IOException(
          "Cannot find settings configuration: "+settings.getAbsolutePath()
        );
      }
      ret.objectUploadFile("/settings.js", settings, true);
    }
    return ret;
  }

}

