<?php
// Page in use

//error_reporting("E_ALL");
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

if ( empty($_SESSION['user_id']) ) {
	$_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : "The wrong approach.";
	header('location: index.php');
	exit();
}

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $userId);
$userData = $db->getOne('admin_accounts');
 

if ( empty($userData['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : "The wrong approach.";
	header('location: profile.php');
	exit();
}



if (!empty($userData)) {

	$name = get_user_real_name($userData['auth_name'], $userData['name'], $userData['lname']);
	$emailAddr = $userData['email'];
	$myVcode = rand(100000,999999);
	$generateVcode = generateVcode($myVcode);
	//$vCode = $userData['register_with'] == 'email' ? md5($email.time()) : $generateVcode ;
	$vCode = $generateVcode;
	$db = getDbInstance();
	$db->where("id", $userId);
	$last_id = $db->update('admin_accounts', ['vcode'=>$vCode]);

	$date = date('Y');

	$cybertchain_verification_code = $langArr['cybertchain_verification_code'];	
	
	if ( $userData['register_with'] == 'email') {
		
		$hi = $langArr['hi'];
		$thanks = $langArr['thanks'];
		$team_support = $langArr['team_support'];
		$mailHtml = '<table align="center" width="600"  style=" background:#fff; ">
							<tbody>
							<tr align="center"><td><img src="http://'.$_SERVER['HTTP_HOST'].'/wallet2/images/logo3.png" /></td></tr>	
							<tr><td><h4 style="text-align: left;padding-left: 16px; margin:0px;">'.$hi.' '.$name.',</h4></td></tr>
							<tr><td align="center";><div style=" font-weight:bold;   padding: 12px 35px;color: #fff;border-radius:5px;text-align:center;font-size: 14px;margin: 10px 0 20px;background: #ec552b;display: inline-block;text-decoration: none;">'.$cybertchain_verification_code.' : '.$vCode.'</div></td></tr>
							
							<tr align="center"><td><p style="padding:0 3%; line-height:25px;text-align: justify;margin:0px;">'.$thanks.' <br/>'.$team_support.'</p></td></tr>
							<tr><td style="color:#b7bbc1;"><h4>©'.$date.' All right reserved</h4></td></tr>
							</tbody>
							</table>
		';
		
		require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)
		
		$email = new \SendGrid\Mail\Mail();
		$email->setFrom("michael@cybertronchain.com", "CyberTron Coin");
		$email->setSubject($cybertchain_verification_code);
		$email->addTo($emailAddr);
		
		$email->addContent("text/html", $mailHtml);
		
		$sendgrid = new \SendGrid('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');
		
		try {
			
			$response = $sendgrid->send($email);
		
		} catch (Exception $e) {
			
			//echo 'Caught exception: '.  $e->getMessage(). "\n";
			
			$_SESSION['failure'] = !empty($langArr['unable_verification_code_send_no']) ? $langArr['unable_verification_code_send_no'] : "Unable To Send Verification Code! Try Again";
			header('location: profile.php');
			exit();
		
		}
		
		$_SESSION['success'] = !empty($langArr['verification_code_send_no2']) ? $langArr['verification_code_send_no2'] : 'Verification Code Send To Your Email';
		header('location: change_transfer_resetpass.php');
		exit();
	}
	
	else {
		// send sms start
		
		$country = $userData['n_country'];
		$phone2 = $userData['n_phone'];

		try {

			$phone3 = preg_replace('/[\(\)\-\_\.~\s]/i', '', $phone2);
			
			$rest = new Message($n_api_key, $n_api_secret);

			$options = new stdClass();
			$options->to = $phone3; // 수신번호
			$options->from = $n_sms_from_tel; // 발신번호
			
			$options->country = $country;
			$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
			$options->text = $cybertchain_verification_code." : ".$vCode; // 문자내용
			
			$result = $rest->send($options);     

			if($result->success_count == '1')
			{
				//echo 'success';
				$_SESSION['success'] = !empty($langArr['verification_code_send_no']) ? $langArr['verification_code_send_no'] : 'Verification Code Send To Your Number';
				header('location: change_transfer_resetpass.php');
				exit();
			}
			else
			{
				//echo 'fail';
				$_SESSION['failure'] = !empty($langArr['unable_verification_code_send_no']) ? $langArr['unable_verification_code_send_no'] : "Unable To Send Verification Code! Try Again";
				header('location: profile.php');
				exit();
			}

		} catch(CoolsmsException $e) {

				//echo 'fail';
				$_SESSION['failure'] = !empty($langArr['unable_verification_code_send_no']) ? $langArr['unable_verification_code_send_no'] : "Unable To Send Verification Code! Try Again";
				header('location: profile.php');
				exit();

//				echo $e->getMessage(); // get error message
//				echo $e->getCode(); // get error code
		}

		// send sms end
	}
	
	
	
}
	   
else{
	$_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : "The wrong approach.";
	header('location: profile.php');
	exit();
 
} 


//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

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;


require_once 'includes/header.php'; 
?>


</head>

<body>

</body>
</html>


<?php include_once 'includes/footer.php'; ?>
