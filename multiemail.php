<?php
// Page in use
// register_au.php 
session_start();
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

$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$mode = $_POST['mode'];
	
	switch($mode) {
		
		case 'check_email_code':
			$code = $_SESSION['register_email_code'];
			$inputcode = $_POST['inputcode'];

			if ( $code == $inputcode ) {
				echo json_encode(array('result'=>'success'));
			} else {
				echo json_encode(array('result'=>'failed'));
			}
				
			break;

		case 'send_email_code':


			$emailCode = rand(100000,999999);
			$_SESSION['verify_code'] = $emailCode; 
			
			$email = $_POST['source_value'];	
			$register_with = 'email';

			$db->orWhere('wallet_phone_email', $email);
			$db->orWhere('email', $email);
			$count = $db->getValue('admin_accounts', 'count(*)');
			if ( $count == 0 ) {
			
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
						$_SESSION['register_email_code'] = $emailCode; 
						echo json_encode(array('result'=>'success'));
						
					} catch (Exception $e) {
						
						echo json_encode(array('result'=>'failed'));

					}
			} else {
						echo json_encode(array('result'=>'duple'));
			}
				//echo json_encode(array('result'=>$emailCode));
			
			break;

	} //
			
}
?>