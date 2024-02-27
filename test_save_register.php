<?php

session_start();
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://127.0.0.1:8545/');
$eth = $web3->eth;
	$personal = $web3->personal;

	
require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS;



//error_reporting(E_ALL);
if(empty($_SESSION['lang'])) {
	$_SESSION['lang'] = "ko";
}
$langFolderPath = file_get_contents("lang/".$_SESSION['lang']."/index.json");
$langArr = json_decode($langFolderPath,true);

//If User has already logged in, redirect to dashboard page.
//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	
	
	
	
	
	//Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
	$verify_code = $data_to_store['verify_code'];
	
	
	
	
	$userIP = getUserIpAddr();
/* 	$blockIpArr = ['118.96.203.228','114.5.215.24',"120.188.92.235"];
	if(in_array($userIP,$blockIpArr)){
		header('location: login.php');
		exit();
	} */
	// blocked IP Code 
	
	$db = getDbInstance();
	$db->where("ip_name", $userIP);
	$row = $db->get('blocked_ips');
	if ($db->count > 0) { 
		header('location: login.php');
		exit();
	}
	
	$email = $_POST['email'];//filter_input(INPUT_POST, 'email');
	$pass =  $_POST['passwd']; //filter_input(INPUT_POST, 'passwd');
	$phone =  str_replace("-","",$_POST['phone']); //filter_input(INPUT_POST, 'passwd');
	$phone =  str_replace(" ","",$phone); //filter_input(INPUT_POST, 'passwd');

	/* $checkMobile = preg_match('/^[0-9]{10}+$/', $phone);
	if($checkMobile==0){
		$_SESSION['login_failure'] = "Please Enter Valid Phone Number!";
    	header('location: register.php');
    	exit();
	} */
	if(!empty($email) && strpos($email, '@etlgr.com') !== false) {
		$_SESSION['login_failure'] = $langArr['registration_is_closed_at_this_time'];
		header('location: test_register.php');
		exit();
	}

	if(empty($email) && empty($phone)) {
		$_SESSION['login_failure'] = $langArr['plz_fill_eth_em_ph'];
		header('location: test_register.php');
		exit();
	}

	if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		 
		$_SESSION['login_failure'] = $langArr['plz_e_valid_id'];
		header('location: test_register.php');
		exit();
	}
	

	
	
	
	
	//Get DB instance. function is defined in config.php
	if(!empty($email)) {
		$db = getDbInstance();
		$db->where("email", $email);
		$row = $db->get('admin_accounts');
		//print_r($row); die;

		if ($db->count > 0) { 
			$_SESSION['login_failure'] = $langArr['email_already_rg'];
			header('location: test_register.php');
			exit();
		}
    }  
	
	if(!empty($phone)) {
		$db = getDbInstance();
		$db->where("phone", $phone);
		$row = $db->get('admin_accounts');
		 //print_r($row); die;

		if ($db->count > 0) {
			$_SESSION['login_failure'] = $langArr['phone_already_rg'];
			header('location: test_register.php');
			exit();
		}
	}
	
	
//if(empty($row)) {
// create account
	//$personal->newAccount($_POST['email'].'ZUMBAE54R2507c16VipAjaImmuAM', function ($err, $account) use (&$newAccount) {
	/* if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	} */
	
	//$newAccount = $account;
	//echo 'New account: ' . $account . PHP_EOL;
	$register_with = !empty($phone) ? 'phone' : 'email';
	$mainEmail = !empty($email) ? $email : $phone;
	
	if((empty($verify_code) || ($verify_code != $_SESSION['verify_code'])) || $mainEmail!=$_SESSION['source_value_'.$register_with] ){
		$_SESSION['login_failure'] = $langArr['invalid_verification_code'];
		header('location: test_register.php');
		exit();
	}	
	
	
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
	$data_to_store['admin_type'] = 'user';
	$data_to_store['user_name'] = "oo";
	$data_to_store['name'] = trim($_POST['name']);
	$data_to_store['lname'] = (isset($_POST['lname'])) ? trim($_POST['lname']) : "";
	$data_to_store['email'] = !empty($email) ? $email : $phone;
	$data_to_store['phone'] = $phone;
	$data_to_store['register_with'] = $register_with;
	$data_to_store['user_ip'] = $userIP;
	 //print_r($mb); die;
	//$phoneNumber = $_POST['mobileno'];
	/* if(!empty($data_to_store['phone'])) // phone number is not empty
	{
		if(preg_match('/^\d{10}$/',$data_to_store['phone'])) // phone number is valid
		{
		  $data_to_store['phone'] = '0' . $data_to_store['phone'];

		  // your other code here
		}
		else // phone number is not valid
		{
		  echo 'Phone number invalid !';
		}
	}
	else // phone number is empty
	{
	  echo 'You must provid a phone number !';
	} */
	$newAccount = '';
	$personal->newAccount($data_to_store['email'].'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM', function ($err, $account) use (&$newAccount) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			
		}
		else {
			$newAccount = $account;
		}
	});
	
	
	
	
	
	$myVcode = rand(100000,999999);
	$generateVcode = generateVcode($myVcode);
	$vCode = ($register_with=='email') ? md5($_POST[$register_with].time()) : $generateVcode;
	$data_to_store['passwd'] = md5($_POST['passwd']);
	//$data_to_store['passwd_b'] = $_POST['passwd'];
	//$data_to_store['vcode'] = $vCode;
	$data_to_store['email_verify'] = 'Y';
	$data_to_store['wallet_address'] = $newAccount;
	//$data_to_store['wallet_address'] = "";
	
	//print_r($data_to_store); die;
	
    $db = getDbInstance();
	
	unset($data_to_store['getlang']);
	unset($data_to_store['verify_code']);
	unset($data_to_store['cofirm_passwd']);
	unset($_SESSION['verify_code']);
    $last_id = $db->insert('admin_accounts', $data_to_store);
	
    if($last_id)
    {
		$userId = $last_id;	
		/* if($register_with=="email") {
			$date = date('Y');
			$fname = $_POST['name'];
			$email = $_POST['email'];
			$verifyLink = "http://".$_SERVER['HTTP_HOST']."/verify.php?vcode=".$vCode;
			$mailHtml = '<table style="background:#f6f6f6; width:100%;    height: 100vh;">
				<tr>
					<td>
						<table align="center" width="600"  style=" background:#fff; ">
					<tbody>
					 
					<tr align="center" > 
						<td>
							<img src="http://'.$_SERVER['HTTP_HOST'].'/images/logo3.png" />
						</td>
					</tr>		 
					  <tr>
					  <td><h4 style="text-align: left;
			padding-left: 16px; margin:0px;">Hi '.$fname.',</h4></td>
					  </tr>
					  
					  <tr align="center">
						<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">Thanks for signing up</p></td>
					  </tr>
					  
					 
					
					  <tr align="center">
						<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">Please find your login details</p></td>
					  </tr>
					  
					 
					  
					  <tr align="center">
						<td><p style="padding:0 3%; line-height:25px;    text-align: justify;">Email: '.$email.'</p></td>
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
						text-decoration: none;">Verify Link: <a href="'.$verifyLink.'">'.$verifyLink.'</a></div></td>
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
			// send verification email
			require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)

			$emailObj = new \SendGrid\Mail\Mail();
			$emailObj->setFrom("michael@cybertronchain.com", "CyberTron Coin");
			$emailObj->setSubject("Verification of CyberTron Coin");
			$emailObj->addTo($email);//$email_id;
			//$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
			$emailObj->addContent("text/html", $mailHtml);
			
			$sendgrid = new \SendGrid('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');

			try {
				$response = $sendgrid->send($emailObj);
				 // print $response->statusCode() . "\n";
				//print_r($response->headers());
			  // print $response->body() . "\n"; die;
			} catch (Exception $e) {
				//echo 'Caught exception: '.  $e->getMessage(). "\n";
			

			}
			$_SESSION['success'] = $langArr['reg_success_email'];
			header('location: login.php');
		}
		else {
			
			// send sms start
						
						
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
				["text" => "CyberTChain Verification Code : ".$vCode, "sender" => "CyberTChain"]
			);


			$omnimessage->setMessages([$sms]);
			try {
				$result = $apiInstance->sendOmnimessage($omnimessage);
				$_SESSION['success'] = $langArr['reg_success_phone'];
				header('location: phoneverify.php');
			} catch (Exception $e) {
				$db = getDbInstance();
				$db->where('id', $last_id);
				$db->delete('admin_accounts');
				//echo 'Exception when calling OmnimessageApi->sendOmnimessage: ', $e->getMessage(), PHP_EOL;
				$_SESSION['login_failure'] = $langArr['unable_to_reg'];
				header('location: register.php');
			}	
			// send sms end
			
			
			
		}
		
    	exit(); */
		
		
		
		// send 50 token to new register users start
	if($register_with == 'phone') {
		

		$getCountryCode = substr($phone, 0, 3);
		if($getCountryCode == "+82") {
				
		
			$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
			$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
			// unlock account

			$personal = $web3->personal;
			$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
				if ($err !== null) {
					echo 'Error: ' . $err->getMessage();
					return;
				}
				if ($unlocked) {
					//echo 'New account is unlocked!' . PHP_EOL;
				} else {
					//echo 'New account isn\'t unlocked' . PHP_EOL;
				}
			});
			
			
			$fromAccount = $adminAccountWalletAddress;
			$toAccount = $newAccount;
			//$amountToSendInteger = 30;
			$amountToSendInteger = 5;
			$amountToSend = $amountToSendInteger*1000000000000000000;

			$amountToSend = dec2hex($amountToSend);
			$gas = '0x9088';
			$transactionId = '';
			$contract = new Contract($web3->provider, $testAbi);
			$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
				'from' => $fromAccount,
				'gas' => '0x186A0',   //100000
				'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
				//'gas' => '0xD2F0'
			], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger) {
				/* if ($err !== null) {
					throw $err;
				} */
				/* if ($result) {
					$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					$_SESSION['success'] = $msg;
				} */
				$transactionId = $result;
				if(!empty($transactionId))
				{
					
					$data_to_store = filter_input_array(INPUT_POST);
					$data_to_store = [];
					$data_to_store['created_at'] = date('Y-m-d H:i:s');
					$data_to_store['sender_id'] = 45;
					$data_to_store['reciver_address'] = $toAccount;
					$data_to_store['amount'] = $amountToSendInteger;
					$data_to_store['fee_in_eth'] =0;
					$data_to_store['status'] = 'completed';
					$data_to_store['fee_in_gcg'] = 0;
					$data_to_store['transactionId'] = $transactionId;
					
					//print_r($data_to_store);die;
					$db = getDbInstance();
					$last_id = $db->insert('user_transactions', $data_to_store);
					
					
				}  
				else {
					//$_SESSION['failure'] = "Unable to send Token ! Try Again";
				}
			}); 
			
			
				// send transaction
				$eth->sendTransaction([
					'from' => $fromAccount,
					'to' => $toAccount,
					//'value' => '0x27CA57357C000'
					'value' => '0xAA87BEE538000',
					'gas' => '0x186A0',   //100000
					'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
					
				], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
					if ($err !== null) {
						echo 'Error: ' . $err->getMessage();
						//die;
					}
					else {
						$getTxId = $transaction;
					}

				});
				$amountToSend = 0.003;
				if(!empty($getTxId)) {
					$db = getDbInstance();
					$data_to_store = [];
					$data_to_store['user_id'] = $userId;
					$data_to_store['coin_type'] = 'eth';
					$data_to_store['tx_id'] = $getTxId;
					$data_to_store['ethmethod'] = "sendTransaction";
					$data_to_store['amount'] = $amountToSend;
					$data_to_store['to_address'] = $toAccount;
					$data_to_store['from_address'] = $fromAccount;
					$last_id = $db->insert('ethsend', $data_to_store);
					//die;
				}
		}
	}
	// send 50 token to new register users end
		
		
		
		
		$_SESSION['success'] = $langArr['reg_success_phone'];
		header('location: login.php');
    } else{
		
		$_SESSION['success'] = "error!";
    	header('location: test_register.php');
    	exit();
	}
	
//});
//}

	
}

function validate_mobile($mobile)
{
    return preg_match('/^[0-9]{10}+$/', $mobile);
}

function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}
?>