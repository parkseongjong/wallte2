<?php
// Test Page
// When editing, the save_register_au.php file must also be modified
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//$web3 = new Web3('http://127.0.0.1:8545/'); 
$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;
$personal = $web3->personal;
$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	
	$gasPriceInWei = $result->toString();
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);	
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
	
	/* $_SESSION['login_failure'] = "Under Maintenance";
		header('location: register.php');
		exit();
	 */
	
	
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
		header('location: register.php');
		exit();
	}

	if(empty($email) && empty($phone)) {
		$_SESSION['login_failure'] = $langArr['plz_fill_eth_em_ph'];
		header('location: register.php');
		exit();
	}

	if(!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		 
		$_SESSION['login_failure'] = $langArr['plz_e_valid_id'];
		header('location: register.php');
		exit();
	}
	
	if( !empty($phone) && stristr($phone, '+undefined') == TRUE ) {
		$_SESSION['login_failure'] = $langArr['register_phone_code_confirm'];
		header('location: register.php');
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
			header('location: register.php');
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
			header('location: register.php');
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
	
	$source_value_register_with =  str_replace("-","",$_SESSION['source_value_'.$register_with]); 
	$source_value_register_with =  str_replace(" ","",$source_value_register_with); 

	if((empty($verify_code) || ($verify_code != $_SESSION['verify_code'])) || $mainEmail!=$source_value_register_with ){

//error insert start
		$data_to_store_error = [];

		$data_to_store_error['lname'] = trim($_POST['lname']);
		$data_to_store_error['user_name'] = trim($_POST['name']);
		$data_to_store_error['register_with'] = $register_with;
		$data_to_store_error['verify_code_sms'] = $verify_code;
		$data_to_store_error['verify_code_write'] = $_SESSION['verify_code'];
		$data_to_store_error['phone'] = $phone;
		$data_to_store_error['mainEmail'] = $mainEmail;
		$data_to_store_error['source_value_'] = $source_value_register_with;
		$data_to_store_error['user_ip'] = $userIP;
		$data_to_store_error['created_at'] = date('Y-m-d H:i:s');

		$db_error_insert = getDbInstance();
		$db_error_insert->insert('admin_accounts_error', $data_to_store_error);
//error insert end


		$_SESSION['login_failure'] = $langArr['invalid_verification_code'];
		header('location: register.php');
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
	try {
		$personal->newAccount($data_to_store['email'].$n_wallet_pass_key, function ($err, $account) use (&$newAccount) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 1);
				//echo 'Error: ' . $err->getMessage();
			}
			else {
				$newAccount = $account;
			}
		});
	} catch (Throwable $e) {
		new_fn_logSave('Exception Error (Register New Wallet Address) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	} catch (Exception $e) {
		new_fn_logSave('Exception Error (Register New Wallet Address) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	}

	
	
	
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

	if ( !empty($_POST['n_country']) ) {
		$data_to_store['n_country'] = $_POST['n_country'];
	}
	if ( !empty($_POST['n_phone']) ) {
		$data_to_store['n_phone'] = $_POST['n_phone'];
	}
	
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
	//if($register_with == 'phone') {
		

		//$getCountryCode = substr($phone, 0, 3);
		//if($getCountryCode == "+82") {
			// Air Drop Stop (20.08.11)
			// send 50 token to new register users start
			// Modified to send at once

			/*	
		
			//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
			//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
			$adminAccountWalletAddress = $n_master_wallet_address_cta;
			$adminAccountWalletPassword = $n_master_wallet_pass_cta;
			$adminAccountWalletId = $n_master_id_cta;
			
			//$adminAccountWalletAddress = "0xebE75b6272746340E31E356b6C42953CB3Ba336E";
			//$adminAccountWalletPassword = "+82-10-4398-7080ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
			//$adminAccountWalletId = 43;
			

			// Add (2020-05-22, YMJ)
			$getSendFreeCtc = $db->where("module_name", 'send_free_ctc')->getOne('settings');
			if ( !empty($getSendFreeCtc['value']) ) {
				$getSendFreeCtcVal = $getSendFreeCtc['value'];
			}
			if ( empty($getSendFreeCtcVal) ) {
				$getSendFreeCtcVal = 2;
			}
			
			// unlock account
			try {
				$personal = $web3->personal;
				$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 2);
						//echo 'Error: ' . $err->getMessage();
						//new_fn_logSave('Error (CTC airdrop unlock) : '. $err->getMessage() . ', File : ' . $_SERVER['SCRIPT_FILENAME']);
						//return;
					}
					if ($unlocked) {
						//echo 'New account is unlocked!' . PHP_EOL;
					} else {
						//echo 'New account isn\'t unlocked' . PHP_EOL;
					}
				});
			} catch (Throwable $e) {
				new_fn_logSave('Exception Error (CTC airdrop unlock) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				return;
			} catch (Exception $e) {
				new_fn_logSave('Exception Error (CTC airdrop unlock) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				return;
			}
			
			// When editing, the save_register_au.php file must also be modified
			$fromAccount = $adminAccountWalletAddress;
			$toAccount = $newAccount;
			//$amountToSendInteger = 30;
			//$amountToSendInteger = 3; // 5 -> 3 (2020.05.12 10:26, YMJ)
			$amountToSendInteger = $getSendFreeCtcVal; // (2020-05-22, YMJ)
			$amountToSend = $amountToSendInteger*1000000000000000000;

			$amountToSend = dec2hex($amountToSend);
			$gas = '0x9088';
			$transactionId = '';
			$txid = '';
			try {
				$contract = new Contract($web3->provider, $testAbi);
				$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
					'from' => $fromAccount,
					'gasprice'=>$gasPriceInWei
					//'gas' => '0x186A0',   //100000
					//'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
					//'gas' => '0xD2F0'
				], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger, &$txid, &$adminAccountWalletId) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 3);
					}
					// if ($result) {
					//	$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					//	$_SESSION['success'] = $msg;
					//}
					$transactionId = $result;
					$txid = $result;
					if(!empty($transactionId))
					{
						
						$data_to_store = filter_input_array(INPUT_POST);
						$data_to_store = [];
						$data_to_store['created_at'] = date('Y-m-d H:i:s');
						$data_to_store['sender_id'] = $adminAccountWalletId;
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
			} catch (Throwable $e) {
				new_fn_logSave('Exception Error (CTC airdrop) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			} catch (Exception $e) {
				new_fn_logSave('Exception Error (CTC airdrop) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}
			
			// Add log records (2020-05-19, YMJ)
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = 'register';
			$data_to_send_logs['coin_type'] = 'ctc';
			$data_to_send_logs['from_id'] = $adminAccountWalletId;
			if ( !empty($userId) ) {
				$data_to_send_logs['to_id'] = $userId;
			}
			$data_to_send_logs['from_address'] = $fromAccount;
			$data_to_send_logs['to_address'] = $toAccount;
			$data_to_send_logs['amount'] = $amountToSendInteger;
			$data_to_send_logs['fee'] =0;
			if ( !empty($txid) ) {
				$data_to_send_logs['transactionId'] = $txid;
			}
			$data_to_send_logs['status'] = !empty($txid) ? 'send' : 'fail';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
			
			
			
			/// send free tp3 to user  start

			$adminAccountWalletAddress = $n_master_wallet_address_tpa;
			$adminAccountWalletPassword = $n_master_wallet_pass_tpa;
			$adminAccountWalletId = $n_master_id_tpa;
			
			
			//$adminAccountWalletAddress = "0x35c937aBC9F48E01EFff1B8f2D3D38E3332cf110";
			//$adminAccountWalletPassword = "+82-10-4398-7080ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
			//$adminAccountWalletId = 44;

			$getSendFreeTp3 = $db->where("module_name", 'send_free_tp3')->getOne('settings');
			if ( !empty($getSendFreeTp3['value']) ) {
				$getSendFreeTp3Val = $getSendFreeTp3['value'];
			}
			else{
				$getSendFreeTp3Val = 100;
			}
			
			$personal = $web3->personal;
			try {
				$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 4);
						//echo 'Error: ' . $err->getMessage();
						//new_fn_logSave('Error (TP3 airdrop unlock) : '. $err->getMessage() . ', File : ' . $_SERVER['SCRIPT_FILENAME']);
						//return;
					}
					if ($unlocked) {
						//echo 'New account is unlocked!' . PHP_EOL;
					} else {
						//echo 'New account isn\'t unlocked' . PHP_EOL;
					}
				});
			} catch (Throwable $e) {
				new_fn_logSave('Exception Error (TP3 airdrop unlock) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				return;
			} catch (Exception $e) {
				new_fn_logSave('Exception Error (TP3 airdrop unlock) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				return;
			}
		
			$fromAccount = $adminAccountWalletAddress;
			$amountToSendInteger = $getSendFreeTp3Val; // (2020-05-22, YMJ)
			$amountToSend = $amountToSendInteger*$contractAddressArr['tp3']['decimal'];
			$last_id="";
			$amountToSend = dec2hex($amountToSend);
			$gas = '0x9088';
			$transactionId = '';
			$txid = '';
			try {
				$contract = new Contract($web3->provider, $contractAddressArr['tp3']['abi']);
				$contract->at($contractAddressArr['tp3']['contractAddress'])->send('transfer', $toAccount, $amountToSend, [
					'from' => $fromAccount,
					'gasprice'=>$gasPriceInWei
					//'gas' => '0x186A0',   //100000
					//'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
					//'gas' => '0xD2F0'
				], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger, &$txid, &$adminAccountWalletId) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 5);
					}
					//if ($result) {
					//	$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					//	$_SESSION['success'] = $msg;
					//}
					$transactionId = $result;
					$txid = $result;
					if(!empty($transactionId))
					{
						
						$data_to_store = filter_input_array(INPUT_POST);
						$data_to_store = [];
						$data_to_store['created_at'] = date('Y-m-d H:i:s');
						$data_to_store['sender_id'] = $adminAccountWalletId;
						$data_to_store['reciver_address'] = $toAccount;
						$data_to_store['amount'] = $amountToSendInteger;
						$data_to_store['fee_in_eth'] =0;
						$data_to_store['status'] = 'completed';
						$data_to_store['fee_in_gcg'] = 0;
						$data_to_store['coin_type'] = "tp3";
						$data_to_store['transactionId'] = $transactionId;
						
						//print_r($data_to_store);die;
						$db = getDbInstance();
						$last_id = $db->insert('user_transactions', $data_to_store);
						
						
					}  
					else {
						//$_SESSION['failure'] = "Unable to send Token ! Try Again";
					}
				}); 
			} catch (Throwable $e) {
				new_fn_logSave('Exception Error (TP3 airdrop) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			} catch (Exception $e) {
				new_fn_logSave('Exception Error (TP3 airdrop) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}
			
			// Add log records (2020-05-19, YMJ)
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = 'register';
			$data_to_send_logs['coin_type'] = 'tp3';
			$data_to_send_logs['from_id'] = $adminAccountWalletId;
			if ( !empty($userId) ) {
				$data_to_send_logs['to_id'] = $userId;
			}
			$data_to_send_logs['from_address'] = $fromAccount;
			$data_to_send_logs['to_address'] = $toAccount;
			$data_to_send_logs['amount'] = $amountToSendInteger;
			$data_to_send_logs['fee'] =0;
			if ( !empty($txid) ) {
				$data_to_send_logs['transactionId'] = $txid;
			}
			$data_to_send_logs['status'] = !empty($txid) ? 'send' : 'fail';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
			
			 
			/// send free tp3 to user  endr
			*/
			
			
			
			
			// send transaction
			/*$eth->sendTransaction([
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
			}*/
		//}
	//}
	// send 50 token to new register users end
		
		
		
		
		$_SESSION['success'] = $langArr['reg_success_phone'];
		header('location: login.php'); 
    } else{
		
		$_SESSION['success'] = "error!";
    	header('location: register.php');
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