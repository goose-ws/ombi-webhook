<?php

// Begin config
$tgBotId = ""; // Telegram Bot ID goes here
$tgChanId = ""; // Super group ID goes here
$tgThreadId = ""; // Telegram super group thread ID goes here
$authToken = ""; // Ombi > Settings > Notifications > Webhook > Application Token -- This can be anything you want, recommend setting it to a random string of characters with a good length
// End config


$headers = array();
foreach($_SERVER as $key => $value) {
    if (substr($key, 0, 5) <> "HTTP_") {
        continue;
    }
    $headers[$key] = ($value);
}

$accessToken = $headers["HTTP_ACCESS_TOKEN"] ?? "";

if (strcmp($authToken, $accessToken) == 0 ):
        $sender = "<b>Ombi</b>";
else:
        header("HTTP/1.1 401 Authorization required");
        exit();
endif;
// We have authenticated

// Get the POST data
$jsonObj = json_decode(file_get_contents("php://input"));

/* Possible POST data fields:
requestId
requestedUser
title
requestedDate
type
additionalInformation
longDate
shortDate
longTime
shortTime
overview
year
episodesList
seasonsList
posterImage
applicationName
applicationUrl
issueDescription
issueCategory
issueStatus
issueSubject
newIssueComment
issueUser
userName
alias
requestedByAlias
userPreference
denyReason
availableDate
requestStatus
providerId
partiallyAvailableEpisodeNumbers
partiallyAvailableSeasonNumber
partiallyAvailableEpisodesList
partiallyAvailableEpisodeCount
notificationType
*/

// The message we send will be dependent on the "notificationType" field from above
$reqType = $jsonObj->notificationType ?? null;

if (strcmp($reqType, "Test") == 0 ):
	// It's a Test event
	$message = $sender . PHP_EOL . "This is a test notification";
elseif (strcmp($reqType, "NewRequest") == 0 ):
	// It's a New Request event
	$reqItem = $jsonObj->title ?? null;
	$reqItemType = $jsonObj->type ?? null;
	$message = $sender . PHP_EOL . "New request for " . $reqItemType . ": " . $reqItem;
elseif (strcmp($reqType, "RequestAvailable") == 0 ):
	// It's a Request Available event
	$reqItem = $jsonObj->title ?? null;
	$reqItemType = $jsonObj->type ?? null;
	$message = $sender . PHP_EOL . "Request fulfilled for " . $reqItemType . ": " . $reqItem;
elseif (strcmp($reqType, "PartiallyAvailable") == 0 ):
	// Request partially available
	// Don't care for this notification, so just exit
	exit();
elseif (strcmp($reqType, "ItemAddedToFaultQueue") == 0 ):
	// Item added to fault queue
	// Don't care for this notification, so just exit
	exit();
elseif (strcmp($reqType, "RequestApproved") == 0 ):
	// Request was approved
	// Don't care for this notification, so just exit
	exit();
else:
	// It's something else, send it for debug purposes
	ob_start();
	var_dump($jsonObj);
	$result = ob_get_clean();
	$message = $sender . PHP_EOL . "Unregistered event:" . PHP_EOL . "<pre>" . $result . "</pre>";
endif;

// Define Telegram url
$website = "https://api.telegram.org/bot".$tgBotId;
$params=[
        "parse_mode"=>"html",
        "chat_id"=>$tgChanId,
        "message_thread_id"=>$tgThreadId,
        "text"=>$message,
];
$ch = curl_init($website . "/sendMessage");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
curl_close($ch);
echo $result . PHP_EOL;
?>
