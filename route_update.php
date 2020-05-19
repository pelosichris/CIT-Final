<?php

require_once "vendor/autoload.php";
require_once "src/Configuration.php";
use FlowrouteNumbersAndMessagingLib\Models;

// Access your Flowroute API credentials as local environment variables
$username = getenv('FR_ACCESS_KEY', true) ?: getenv('FR_ACCESS_KEY');
$password = getenv('FR_SECRET_KEY', true) ?: getenv('FR_SECRET_KEY');

$client = new FlowrouteNumbersAndMessagingLib\FlowrouteNumbersAndMessagingClient($username, $password);

// GET & DECODE JSON
$json = file_get_contents("php://input");
$json_decode = json_decode($json);
// END

// GET Text Content
$text_body_raw = trim($json_decode->data->attributes->body);
$text_body = strtolower($text_body_raw);
$text_from = $json_decode->data->attributes->from;
// END

$allowed_numbers = array("19141234567", "19143454567");
$txt_ack_number = "19143454567";

$DID1 = "19143453122"; // Flowroute DID1
$DID2 = "19141243221"; // Flowroute DID2
$DID3 = "19145463455"; // Flowroute DID3
$route_id_sip = "0";
$route_id_mobile = "118624"; // Route Identifier
$route_id_answeringsrv = "118894"; // Route Identifier
$main_office_did = "1914242112"; // Route Identifier


if ($text_body =="forward" && in_array($text_from,$allowed_numbers)){

    UpdatePrimaryRoute($client, $DID1, $route_id_answeringsrv);
    UpdatePrimaryRoute($client, $DID2, $route_id_answeringsrv);
    UpdatePrimaryRoute($client, $DID3, $route_id_answeringsrv);
    $text_acknowledge = "Forwarding Calls To The Answering Service!";
    SendSMS($client, $main_office_did, $txt_ack_number, $text_acknowledge);

}

if ($text_body =="mobile" && in_array($text_from,$allowed_numbers)){

    UpdatePrimaryRoute($client, $DID1, $route_id_mobile);
    UpdatePrimaryRoute($client, $DID2, $route_id_mobile);
    UpdatePrimaryRoute($client, $DID3, $route_id_mobile);
    $text_acknowledge = "Forwarding Calls To Your Mobile!";
    SendSMS($client, $main_office_did, $txt_ack_number, $text_acknowledge);


}

if ($text_body =="office" && in_array($text_from,$allowed_numbers)){

    file_put_contents("output.txt", $text_from);
    UpdatePrimaryRoute($client, $DID1, $route_id_sip);
    UpdatePrimaryRoute($client, $DID2, $route_id_sip);
    UpdatePrimaryRoute($client, $DID3, $route_id_sip);
    $text_acknowledge="Routing Calls Back To The Office!";
    SendSMS($client, $main_office_did, $txt_ack_number, $text_acknowledge);


}


function UpdatePrimaryRoute($client, $DID, $route_id)
{
	$routes = $client->getRoutes();
    $result = $routes->UpdatePrimaryVoiceRouteForAPhoneNumber($DID, $route_id);
}


function SendSMS($client, $main_office_did, $txt_ack_number, $text_acknowledge){
    $msg = new Models\Message();
    $msg->from = $main_office_did;
    $msg->to = $txt_ack_number; // Replace with your mobile number to receive messages from your Flowroute account
    $msg->body = $text_acknowledge;
    $messages = $client->getMessages();
    $result = $messages->CreateSendAMessage($msg);

}




?>
