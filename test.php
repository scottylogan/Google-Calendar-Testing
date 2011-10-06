<?php
require_once('OAuth.php');
require_once('config.php');

$consumer = new OAuthConsumer($CONSUMER_KEY, $CONSUMER_SECRET, NULL);

// Simple scenario - add an event to $user1's calendar
// making them the organizer

$invitee = $ext_user;
$organizer = $res_mgr;
$requestor_id = $res_mgr;
$calendar = $res_cal;

$base_feed = 'https://www.google.com/calendar/feeds/' . $calendar . '/private/full/';
$params = array('max-results' => 10, 'xoauth_requestor_id' => $requestor_id, 'gsessionid' => $gsession_id);
$request = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $base_feed, $params);

// Sign the constructed OAuth request using HMAC-SHA1
$request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

// Make signed OAuth request to the Contacts API server
$url = $base_feed . '?' . implode_assoc('=', '&', $params);

$start_time = intval(time()/3600) * 3600 + 3600;
$end_time   = $start_time + 3600;

$fmt = 'Y-m-d\TH:i:sP';
$start = date($fmt, $start_time);
$end   = date($fmt, $end_time);

echo send_request($request->get_normalized_http_method(), $url, $request->to_header(), "<entry xmlns='http://www.w3.org/2005/Atom'
    xmlns:gd='http://schemas.google.com/g/2005'
    xmlns:gCal='http://schemas.google.com/gCal/2005'>
  <category scheme='http://schemas.google.com/g/2005#kind'
    term='http://schemas.google.com/g/2005#event'></category>
  <title type='text'>Test Event</title>
  <content type='text'>&lt;h1&gt;Test Event&lt;/h1&gt;&lt;p&gt;Test event &lt;em&gt;via&lt;/em&gt; &lt;a href='http://oauth.net/'&gt;OAuth&lt;/a&gt;.&lt;/p&gt;</content>
  <gd:who email='" . $invitee . "' rel='http://schemas.google.com/g/2005#event.attendee' valueString='" . $invitee . "'>
      <gd:attendeeStatus value='http://schemas.google.com/g/2005#event.invited'/>
  </gd:who>
  <gd:who email='" . $organizer . "' rel='http://schemas.google.com/g/2005#event.organizer' valueString='" . $organizer . "'/>
  <gd:transparency value='http://schemas.google.com/g/2005#event.opaque'/>
  <gd:eventStatus value='http://schemas.google.com/g/2005#event.confirmed'/>
  <gd:where valueString='Redwood G17'/>
  <gd:when startTime='" . $start . "' endTime='" . $end . "'/>
  <gd:visibility value='http://schemas.google.com/g/2005#event.default'/>
  <gCal:anyoneCanAddSelf value='false'/>
  <gCal:guestsCanInviteOthers value='false'/>
  <gCal:guestsCanModify value='false'/>
  <gCal:guestsCanSeeGuests value='true'/>
</entry>");
 
/**
 * Makes an HTTP request to the specified URL
 * @param string $http_method The HTTP method (GET, POST, PUT, DELETE)
 * @param string $url Full URL of the resource to access
 * @param string $auth_header (optional) Authorization header
 * @param string $postData (optional) POST/PUT request body
 * @return string Response body from the server
 */
function send_request($http_method, $url, $auth_header=null, $postData=null) {
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_FAILONERROR, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

  switch($http_method) {
    case 'GET':
      if ($auth_header) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header)); 
      }
      break;
    case 'POST':
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml', 
                                                   $auth_header)); 
      curl_setopt($curl, CURLOPT_POST, 1);                                       
      curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
      break;
    case 'PUT':
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/atom+xml', 
                                                   $auth_header)); 
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
      break;
    case 'DELETE':
      curl_setopt($curl, CURLOPT_HTTPHEADER, array($auth_header)); 
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_method); 
      break;
  }
  $response = curl_exec($curl);
  if (!$response) {
    $response = curl_error($curl);
    echo "ERROR: " . $response . "\n";
  }
  curl_close($curl);
  return $response;
}

/**
 * Joins key:value pairs by inner_glue and each pair together by outer_glue
 * @param string $inner_glue The HTTP method (GET, POST, PUT, DELETE)
 * @param string $outer_glue Full URL of the resource to access
 * @param array $array Associative array of query parameters
 * @return string Urlencoded string of query parameters
 */
function implode_assoc($inner_glue, $outer_glue, $array) {
  $output = array();
  foreach($array as $key => $item) {
    $output[] = $key . $inner_glue . urlencode($item);
  }
  return implode($outer_glue, $output);
}
?>
