============================================================================
This is a HbbTV testsuite and is designed for testing HbbTV implementations.
============================================================================

The testsuite is inofficial and incomplete, but still contains a lot of
important tests. Please note that the HbbTV consortium currently is working
on an official testsuite, but as this takes longer than expected, this
project is designed to be a first step towards that goal. We hope we can
contribute these tests to the official testsuite some time in the future.

The testsuite is open for contributions! So if you have additional tests,
we would be very happy to integrate them into this testsuite. The
testsuite is licensed by Creative Commons Attribution Share Alike license
terms, see http://creativecommons.org/licenses/by-sa/3.0/
In addition to that, the logo displayed on the first page always needs to
include the MIT-xperts logo (you may add additional logos, though).

How to install the tests:
1. Install apache + php
2. Add the following line to your httpd.conf:
   AcceptPathInfo On
3. Set up https: access to your apache server, install a real
   SSL certificate signed by Thawte or Verisign CA
   (see chapter 11.2 of HbbTV specification)
4. Add the SSL root certificates contained in ca-bundle.pem to the file
   referenced in your apache SSL configuration (SSLCACertificateFile)
5. Extract this testsuite in a directory reachable via the web server
6. Restart your web server

How to add additional tests:
1. You should group similar tests into one page (see other test pages)
2. Each test page should reside in a separate directory, together with
   all files required by this test(s)
3. Each test set (directory) is referenced in the index.php in the <ul>:
   <li name="DIRNAME" descr="LONGDESCR">SHORTDESCR</li>
   DIRNAME: the name of the directory, containing an index.php
   SHORTDESCR: short test group description (max. 30 chars)
   LONGDESCR: longer test group description displayed when selecting entry

The testsuite is currently maintained by:
   MIT-xperts GmbH
   Poccistr. 13
   80336 Munich
   Germany
   info@mit-xperts.com

In case of questions, or before implementing any tests, please contact
us, so we can help.

