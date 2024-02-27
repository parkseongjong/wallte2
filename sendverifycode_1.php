<?php
require_once './config/config.php';

require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS;


use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

//$api_key = '1234';
//$api_secret = '1234';
$api_key = 'NCSWM2DJ81J4J5V9';
$api_secret = 'WATOR3FYBG4MOJONQYTXDY5TPYLBPE4C';


session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$emailCode = rand(100000,999999);
	$_SESSION['verify_code'] = $emailCode; 
	
	$email = $_POST['source_value'];	
	$phone = $_POST['source_value'];
	$country = $_POST['source_country'];	
	$phone2 = $_POST['source_value2'];	
	
	$register_with = $_POST['source_type'];	
	
	$_SESSION['source_value_email'] = $email; 
	$_SESSION['source_value_phone'] = $phone; 
	$_SESSION['source_type'] = $register_with; 
	
	
	if($register_with == 'email') {
		$date = date('Y');
			$mailHtml = '<table style="background:#f6f6f6; width:100%;    height: 100vh;">
		<tr>
			<td>
				<table align="center" width="600"  style=" background:#fff; ">
			<tbody>
		
			  <tr>
			  <td><h4 style="text-align: left;
	padding-left: 16px; margin:0px;">Hi User,</h4></td>
			  </tr>
	   
			  
			  <tr align="center">
				<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">Below is your Authentication code </p></td>
			  </tr>
			  
			   <tr>
					  <td align="center";><div style=" font-weight:bold;   padding: 12px 35px;
				color: #fff;
				border-radius:5px;
				text-align:center
				font-size: 14px;
				margin: 10px 0 20px;
				background: #ec552b;
				display: inline-block;
				text-decoration: none;">Authentication Code: '.$emailCode.'</div></td>
				</tr>
			  
			  <tr align="center">
				<td><p style="padding:0 3%; line-height:25px;    text-align: justify;
				margin:0px;">Thanks, <br/>Team Support</p></td>
			  </tr>

			  
		
		</tbody>
		</table>
		
	  <table align="center" width="600"  style=" background:#f3f5f7; color:#b7bbc1 ">
			  
		<tr>
		<td>
		<h4>©'.$date.' All right reserved</h4>
		</td>
		</tr>  
		
			  
			  
			 
			</table>';
			 
			require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)

			$emailObj = new \SendGrid\Mail\Mail();
			$emailObj->setFrom("michael@cybertronchain.com", "CyberTron Coin");
			$emailObj->setSubject("Verification of CyberTron Coin For Send Token");
			$emailObj->addTo($email);//$email_id;
			//$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
			$emailObj->addContent("text/html", $mailHtml);


			$sendgrid = new \SendGrid('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');
			try {
				$response = $sendgrid->send($emailObj);
				
				  print $response->statusCode() . "\n";
				//print_r($response->headers());
				print $response->body() . "\n";die;
			} catch (Exception $e) {
				
				echo 'Caught exception: '.  $e->getMessage(). "\n";die;
				/*=====================Mail========================*/

			}
		}
		else {
			// send sms start
			
						

			try {

				$rest = new Message($api_key, $api_secret);

				$options = new stdClass();
				$options->to = $phone2; // 수신번호
				$options->from = '0234893237'; // 발신번호
				
				$options->country = $country;
				$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
				$options->text = "CyberTChain Verification Code : $emailCode"; // 문자내용



				$result = $rest->send($options);     

				if($result->success_count == '1')
					echo 'success';
				else
					echo 'fail';

			} catch(CoolsmsException $e) {

				echo 'fail';

//				echo $e->getMessage(); // get error message
//				echo $e->getCode(); // get error code
			}

/*						
			// Configure HTTP basic authorization: basicAuth
			$config = Configuration::getDefaultConfiguration()
				->setUsername('18b81e07d18425210db7925f39b3eb7c')
				->setPassword('31a06fb96198843422635716b114a32a');

			$apiInstance = new OmnimessageApi(
				new GuzzleHttp\Client(),
				$config
			);
			 
			$omnimessage = new Omnimessage([
				"to" => $phone
			]);


			$sms = new SMS(
				["text" => "CyberTChain Verification Code : ".$emailCode, "sender" => "CyberTChain"]
			);


			$omnimessage->setMessages([$sms]);
			try {
				$result = $apiInstance->sendOmnimessage($omnimessage);
				print_r($result);
			} catch (Exception $e) {
				print_r($e);
			}	
*/

			// send sms end
		}
	
}
?>