<?php

// Copy this file to config.php, then change 
// KEY and SECRET to your values
//
// You'll also need to change $invitee
// 
//
// when you get a 301 / 302 error, update $gsession_id with 
// the new session ID

// Establish an OAuth consumer based on our admin 'credentials'
$CONSUMER_KEY = 'KEY'; 
$CONSUMER_SECRET = 'SECRET';

// the user whose calendar is being updated
$user1 = 'user1@example.com';

// another user account within the same domain
$user2 = 'user2@example.com';

// a google resource calendar within the same domain
$res_cal = 'example.com_XXXXXXXXX@resource.calendar.google.com';

// another account within the domain that can add events to the 
// resource calendar
$res_mgr = 'admin@example.com';

// a user from an external domain
$ext_user = 'user@example.org';

$gession_id = '';

?>
