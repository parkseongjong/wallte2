<?php
require_once(__DIR__ . '/../vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS;



// Configure HTTP basic authorization: basicAuth
$config = Configuration::getDefaultConfiguration()
	->setUsername('18b81e07d18425210db7925f39b3eb7c')
	->setPassword('31a06fb96198843422635716b114a32a');

$apiInstance = new OmnimessageApi(
	new GuzzleHttp\Client(),
	$config
);
 
$omnimessage = new Omnimessage([
	"to" => "+919782632174"
]);
/*
$viber = new Viber(
	["text" => "Hello Viber!", "sender" => "MyViberSender"]
); */

$sms = new SMS(
	["text" => "Hello SMS!", "sender" => "CyberTChain"]
);

/* 
$whatsAppText = new WhatsAppText(["body" => "Hello WhatsApp!"]);

$whatsapp = new WhatsApp(
	['text' => $whatsAppText, "sender" => "MyWhatsAppSender"]
); */

$omnimessage->setMessages([$sms]);


try {
    $result = $apiInstance->sendOmnimessage($omnimessage);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OmnimessageApi->sendOmnimessage: ', $e->getMessage(), PHP_EOL;
}
