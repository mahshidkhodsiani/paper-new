<?php
require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('420518057369-1v9f8r5vgad3nc9s8r686qckba3bb95i.apps.googleusercontent.com');
$client->setClientSecret('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');
// $client->setRedirectUri('http://localhost/auth/google/callback.php');
$client->setRedirectUri('http://moonshid.ir/paper/auth/google/callback.php');
$client->addScope('email');
$client->addScope('profile');