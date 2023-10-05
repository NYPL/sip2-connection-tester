<?php
require('vendor/cap60552/php-sip2/sip2.class.php');

$host = (string) getenv('SIP2_HOSTNAME');

const SIP2_PORT = '5550';

/**
 *  Initialize SIP2 client
 */
function sip2client () {
  global $host;

  $sipClient = new sip2();

  $sipClient->hostname = $host;
  $sipClient->port = SIP2_PORT;

  if (!$sipClient->connect()) {
    echo "\nSIP2 connection failure. Please check your configuration.";
    exit();
  }

  return $sipClient;
}

/**
 *  Perform ItemInformation SIP2 call for item by barcode. Prints result.
 */
function runItemInformationConnectionTest ($barcode) {
  echo "\nQuerying '$host' for '$barcode'";

  $sip2Client = sip2client();

  $response = $sip2Client->msgItemInformation (
      $barcode
  );

  $result = $sip2Client->parseItemInfoResponse(
    $sip2Client->get_message($response)
  );

  echo "\nItemInfo response:\n";
  print_r($result);

  echo "\nSIP2 ItemInformation connection test succeeded.";
}

/**
 *  Perform SIP2 checkin on item
 */
function checkin ($barcode, $location) {
  echo "\nPerforming checkin on barcode $barcode, location $location";

  $sip2Client = sip2client();

  $sip2RefileRequest = $sip2Client->msgCheckin(
      $barcode,
      time(),
      $location
  );

  $result = $sip2Client->parseCheckinResponse(
    $sip2Client->get_message($sip2RefileRequest)
  );

  //SIP2 Checkin finished.
  $message = $result && $result['variable'] && $result['variable']['AF'] && $result['variable']['AF'][0] ? $result['variable']['AF'][0] : '';

  echo "\nCheckin Result: $message\n";
  echo "\nRaw Checkin Result:\n";
  print_r($result);

  echo "\nSIP2 Checkin finished.";
}

/**
 *  Process event
 */
function run () {
  $barcode = (string) getenv('BARCODE');
  echo "\nUsing barcode $barcode";

  if (!$barcode) $barcode = '33433086726472';

  $location = (string) getenv('LOCATION');
  if (!$location) $location = 'ma';

  $doCheckin = (string) getenv('DO_CHECKIN');

  runItemInformationConnectionTest($barcode);

  if ($doCheckin == 'true') {
    checkin($barcode, $location);
  }
}

run();
