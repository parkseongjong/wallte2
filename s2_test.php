<?php
//return false;
//exit;
// Test Page
use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

//$api_key = '1234';
//$api_secret = '1234';

$api_key = 'NCSWM2DJ81J4J5V9';
$api_secret = 'WATOR3FYBG4MOJONQYTXDY5TPYLBPE4C';

try {

    $rest = new Message($api_key, $api_secret);

    $options = new stdClass();
	$options->country = '82';
//	$options->country = '992';

    $options->to = '01049138089'; // 수신번호	
//    $options->to = '908885665'; // 수신번호
    $options->from = '0234893237'; // 발신번호
    $options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
    $options->text = 'Test Verification Code : \n000000'; // 문자내용

    $result = $rest->send($options);     

	if($result->success_count == '1')
		echo 'success';
	else
		echo 'fail';


print_r($result);
} catch(CoolsmsException $e) {

	echo 'fail';

    echo $e->getMessage(); // get error message
    echo $e->getCode(); // get error code
}
?>