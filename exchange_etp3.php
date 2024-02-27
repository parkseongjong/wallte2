<?php 
// Page in use -> Page Not Use : exchange_etoken.php�� ��ü��
// TP3 -> eTP3
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;

//$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	if ( !empty($result) ) {
		$gasPriceInWei = $result->toString();
	}
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');


$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];
$transfer_approved = $row[0]['transfer_approved'];

// When connecting with a domestic IP, only users who have completed authentication are allowed to send
// If you access overseas IP, you can send without authentication.
$user_id_auth = 'N';
if ( !empty($row[0]['id_auth']) && $row[0]['id_auth'] == 'Y' ) {
	$user_id_auth = 'Y';
}
$ip_kor = '';
$ip_kor = trim(new_ipinfo_ip_chk('2'));

if ($ip_kor == 'KR' && $user_id_auth != 'Y') {
	$_SESSION['failure'] = !empty($langArr['send_auth_need']) ? $langArr['send_auth_need'] : 'Can be used after authentication. Please use after verifying your identity in [My Info].';
	header('Location:profile.php');
	exit();
}

if ( empty($row[0]['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['transfer_pw_message4']) ? $langArr['transfer_pw_message4'] : 'Please set payment password.';
	header('Location:profile.php');
	exit();
}

$token = 'tp3';
$send_type = 'exchange_eToken';
$return_page = 'exchange_etp3.php';
$masterAddress = $n_master_etp3_wallet_address;

// �ܾ�
$getNewBalance = 0;
$getNewCoinBalance = 0;
$getCtcTokenBalance = 0 ;
$getNewBalance = $wi_wallet_infos->wi_get_balance('2', 'eth', $walletAddress, $contractAddressArr);
$getNewCoinBalance = $wi_wallet_infos->wi_get_balance('2', $token, $walletAddress, $contractAddressArr);
$getCtcTokenBalance = $wi_wallet_infos->wi_get_balance('2', 'ctc', $walletAddress, $contractAddressArr);

$db = getDbInstance();
// ������
$getTokenFee = $db->where("module_name", 'send_etoken_fee2')->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];

// �ּ� ���۱ݾ�
$getMinAmountVal = 0;
$getMinAmount = $db->where("module_name", 'min_transfer_'.$token.'_to_e'.$token)->getOne('settings');
$getMinAmountVal = $getMinAmount['value'];

// ��ȯ����
$getExchangeRateVal = '';
$getExchangeRate = $db->where("module_name", 'exchange_e'.$token.'_per_'.$token)->getOne('settings');
$getExchangeRateVal = $getExchangeRate['value'];

// approve Ȯ��
$columnName = ($token=='tp3') ? 'tp_approved' : $token.'_approved'; 
$tokenApproved = $row[0][$columnName];
$sendApproved = $row[0]['sendapproved'];
$columnShortName = ($token=='tp3') ? 'tp' : $token; 
$columnNameCompleted = $columnName."_completed"; 
$tokenApprovedCompleted = $row[0][$columnNameCompleted];
$sendApprovedCompleted = $row[0]['sendapproved_completed'];

$tokenArr = $contractAddressArr[$token];
$tokenAbi = $tokenArr['abi'];
$tokenContractAddress = $tokenArr['contractAddress'];
$decimalDigit = $tokenArr['decimal'];

///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

	// 20.08.12 12:58 -> 20.08.14
	$db = getDbInstance();
	$db->where("module_name", 'lock_sending');
	$getlockSending = $db->getOne('settings');
	$getlockSendingVal = '';
	if ( isset($getlockSending) && !empty($getlockSending['value']) ) {
		$getlockSendingVal = $getlockSending['value'];
	}
	if ( $accountType!='admin' && $transfer_approved == 'C' && $getlockSendingVal == 'C' ) {
		$_SESSION['failure'] = !empty($langArr['waiting_message']) ? $langArr['waiting_message'] : 'Please try again later.';
		header('Location: ' . $return_page);
		exit();
	}
		
	$totalAmt = trim($_POST['amount']);
	
	// �ּ����۱ݾ� üũ
	if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $totalAmt < $getMinAmountVal) {
		$ma_tmp = $getMinAmountVal.' '.strtoupper($token);
		$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
		header('location: '.$return_page);
		exit();
	}			
		
	if($sendApproved=='N' && ($accountType=='user' || $accountType=='store') && $transfer_approved == 'C'){ // Fee choice : 20.08.04
		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = $send_type;
		$data_to_sendlog['coin_type'] = 'ctc';
		$data_to_sendlog['user_id'] = $_SESSION['user_id'];
		$data_to_sendlog['msg_type'] = 'permission'; // error, permission
		$data_to_sendlog['message'] = 'ctc_not_approved';
		$db = getDbInstance();
		$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

		$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
		header('location: '.$return_page);
		exit();
	}

	if($sendApprovedCompleted=='N' && ($accountType=='user' || $accountType=='store') && $transfer_approved == 'C'){
		
		$db = getDbInstance();
		$db->where ("user_id", $userId);
		$db->where ("coin_type", 'ctc');
		$db->where ("ethmethod", 'approve');
		$db->where ("del", 'use'); // See the authenticate.php annotation
		$ethSendRowFound = $db->get('ethsend');
		if($db->count>0){
			$txId = $ethSendRowFound[0]['tx_id'];
			
			$eth_approve_result = $wi_wallet_infos->get_txId_result($txId);
			if ($eth_approve_result ==1) {
				$db = getDbInstance();
				$db->where("id", $userId);
				$updateColData = $db->update('admin_accounts', ['sendapproved_completed'=>'Y']);
			} else {
				$data_to_sendlog = [];
				$data_to_sendlog['send_type'] = $send_type;
				$data_to_sendlog['coin_type'] = $token;
				$data_to_sendlog['user_id'] = $_SESSION['user_id'];
				$data_to_sendlog['msg_type'] = 'permission'; // error, permission
				$data_to_sendlog['message'] = 'ctc_approve_result_not_success';
				$db = getDbInstance();
				$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

				$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
				header('location: '.$return_page);
				exit();
			}
		}	
	}		
	
	if($tokenApproved=='N' && ($accountType=='user' || $accountType=='store') && $transfer_approved == 'C'){
		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = $send_type;
		$data_to_sendlog['coin_type'] = $token;
		$data_to_sendlog['user_id'] = $_SESSION['user_id'];
		$data_to_sendlog['msg_type'] = 'permission'; // error, permission
		$data_to_sendlog['message'] = 'token_not_approved';
		$db = getDbInstance();
		$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

		$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
		header('location: '.$return_page);
		exit();
	}
	if($tokenApprovedCompleted=='N' && ($accountType=='user' || $accountType=='store') && $transfer_approved == 'C'){
		
		$db = getDbInstance();
		$db->where ("user_id", $userId);
		$db->where ("coin_type", $columnShortName);
		$db->where ("ethmethod", 'approve');
		$db->where ("del", 'use'); // See the authenticate.php annotation
		$ethSendRowFound = $db->get('ethsend');
		if($db->count>0){
			$txId = $ethSendRowFound[0]['tx_id'];

			$eth_approve_result2 = $wi_wallet_infos->get_txId_result($txId);
			if ( $eth_approve_result2 == 1 ) {
				$db = getDbInstance();
				$db->where("id", $userId);
				$updateColData = $db->update('admin_accounts', [$columnNameCompleted=>'Y']);
			} else {
				$data_to_sendlog = [];
				$data_to_sendlog['send_type'] = $send_type;
				$data_to_sendlog['coin_type'] = $token;
				$data_to_sendlog['user_id'] = $_SESSION['user_id'];
				$data_to_sendlog['msg_type'] = 'permission'; // error, permission
				$data_to_sendlog['message'] = 'token_approve_result_not_success';
				$db = getDbInstance();
				$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

				$_SESSION['failure'] = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
				header('location: '.$return_page);
				exit();
			}
		}
	}


	// send transactions start
	
	if($_SESSION['user_id']==$n_master_id){
		$_SESSION['failure'] = !empty($langArr['exchange_message2']) ? $langArr['exchange_message2'] : 'You are not allowed to exchange.';
		header('location: '.$return_page);
		exit();
	}
	
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');

	$password =	$row[0]['email'].$n_wallet_pass_key;
	$walletAddress = $row[0]['wallet_address'];

	$toAccount = $masterAddress;
	$fromAccount = $walletAddress;
	$amountToSend = trim($_POST['amount']);
	
	// unlock
	$personal = $web3->personal;
	try {
		$personal->unlockAccount($walletAddress, $password, function ($err, $unlocked) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 1);
			}
		});
	} catch (Exception $e) {

		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = $send_type;
		$data_to_sendlog['coin_type'] = $token;
		$data_to_sendlog['user_id'] = $_SESSION['user_id'];
		$data_to_sendlog['msg_type'] = 'error'; // error, permission
		$data_to_sendlog['message'] = 'unlock';
		$db = getDbInstance();
		$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

		new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
		header('Location: ' . $return_page);
		exit();
	}

	try {
		$personal = $web3->personal;
		$personal->unlockAccount($n_master_wallet_address, $n_master_wallet_pass, function ($err, $unlocked) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 5);
			}
		});
	} catch (Exception $e) {
		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = $send_type;
		$data_to_sendlog['coin_type'] = $token;
		$data_to_sendlog['user_id'] = $_SESSION['user_id'];
		$data_to_sendlog['msg_type'] = 'error'; // error, permission
		$data_to_sendlog['message'] = 'admin_unlock';
		$db = getDbInstance();
		$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

		new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
		header('Location: ' . $return_page);
		exit();
	}

	// if admin send token than call transfer Method 
	if($accountType=='admin' || $transfer_approved != 'C') {
		
		// Token �ܾ� üũ
		if($getNewCoinBalance < trim($_POST['amount']) ) {
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: '.$return_page);
			exit();
		}
		// ETH �ܾ� üũ
		if($getNewBalance < 0.008){
			$_SESSION['failure'] = !empty($langArr['insufficient_eth_balance']) ? $langArr['insufficient_eth_balance'] : "Insufficient Eth Balance";
			header('Location: '.$return_page);
			exit();
		}
			
		$amountToSend = $amountToSend*$decimalDigit;

		$amountToSend = dec2hex($amountToSend);
		$amountToSend = '0x'.$amountToSend; // Must add 0x
		$gas = '0x9088';
		$transactionId = '';
		
		$otherTokenContract = new Contract($web3->provider, $tokenAbi);
		try {
			$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
				'from' => $fromAccount,
				'gas' => '0x186A0',   //100000
				'gasprice'=>$gasPriceInWei
			], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId) {
				if ($err !== null) {
					throw new Exception($err->getMessage(), 2);
				}
				$transactionId = $result;
			});

		} catch (Exception $e) {
			$send_error_msg = '';
			if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
				$send_error_msg = '(gas required exceeds allowance)';
			} else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
				$send_error_msg = '(insufficient funds)';
			}

			$data_to_sendlog = [];
			$data_to_sendlog['send_type'] = $send_type;
			$data_to_sendlog['coin_type'] = $token;
			$data_to_sendlog['user_id'] = $_SESSION['user_id'];
			$data_to_sendlog['to_address'] = $toAccount;
			$data_to_sendlog['msg_type'] = 'error'; // error, permission
			$data_to_sendlog['message'] = 'send'.$send_error_msg;
			$db = getDbInstance();
			$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

			new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			
			if ( !empty($send_error_msg) ) {
				$_SESSION['failure'] = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : "The balance is insufficient.";
			} else {
				$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
			}
			header('Location: ' . $return_page);
			exit();
		}

		// Add log records
		$data_to_send_logs = [];
		$data_to_send_logs['send_type'] = $send_type;
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['from_id'] = $_SESSION['user_id'];
		//$data_to_send_logs['to_id'] = '';
		$data_to_send_logs['from_address'] = $fromAccount;
		$data_to_send_logs['to_address'] = $toAccount;
		$data_to_send_logs['amount'] = $_POST['amount'];
		$data_to_send_logs['fee'] = 0;
		if ( !empty($transactionId) ) {
			$data_to_send_logs['transactionId'] = $transactionId;
		}
		$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
		$data_to_send_logs['etoken_send'] = 'P';
		$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

		$db = getDbInstance();
		$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);

		if(!empty($transactionId)) {
			
			$data_to_store = filter_input_array(INPUT_POST);
			$data_to_store = [];
			$data_to_store['created_at'] = date('Y-m-d H:i:s');
			$data_to_store['coin_type'] = $token;
			$data_to_store['sender_id'] = $_SESSION['user_id'];
			$data_to_store['reciver_address'] = $toAccount;
			$data_to_store['amount'] = $_POST['amount'];
			$data_to_store['fee_in_eth'] = 0;
			$data_to_store['status'] = 'completed';
			$data_to_store['fee_in_gcg'] = 0;
			$data_to_store['transactionId'] = $transactionId;
			
			$db = getDbInstance();
			$last_id = $db->insert('user_transactions', $data_to_store);
			
			$_SESSION['success'] = !empty($langArr['send_success_message1']) ? $langArr['send_success_message1'] : "Transmission was successful.";
			header('location: '.$return_page);
			exit();
			
		} else {
			$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again.";
			header('location: '.$return_page);
			exit();
		}

	}
	else {
		
		$feePercent = $getTokenFeeVal;
		$adminFee = $getTokenFeeVal;
		$adminFee = number_format((float)$adminFee,2);
		$actualAmountToSend = $amountToSend;
		$actualAmountToSendWithoutDecimal = $actualAmountToSend;
		$actualAmountToSend = $actualAmountToSend*$decimalDigit;
		
		// CTC ������ �ܾ� üũ
		if($getCtcTokenBalance < $adminFee){
			$_SESSION['failure'] = !empty($langArr['send_message3']) ? $langArr['send_message3'] : "Insufficient CTC Fee for trasfer Token";
			header('location: '.$return_page);
			exit();
		}
		// �ܾ� üũ
		if($getNewCoinBalance < trim($_POST['amount'])){
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('location: '.$return_page);
			exit();
		}
		
		$actualAmountToSend = dec2hex($actualAmountToSend);
		$actualAmountToSend = '0x'.$actualAmountToSend; // Must add 0x
		$gas = '0x9088';
		$transactionId = '';
		
		$senderAccount = $n_master_wallet_address;
		$ownerAccount = $walletAddress;
		$nonce = "";
		  $eth->getTransactionCount($senderAccount,'pending', function ($err, $getNonce) use (&$nonce) {
				if ($err !== null) {
					$nonce = "";
				}
				else {
					$nonce = $getNonce->toString();
					$nonce = (int)$nonce+1;
				}
			});

		$contract = new Contract($web3->provider, $testAbi);
		try {
			$otherTokenContract = new Contract($web3->provider, $tokenAbi);
			$otherTokenContract->at($tokenContractAddress)->send('transferFrom',$ownerAccount, $toAccount, $actualAmountToSend, [
				'from' => $senderAccount,
				'nonce' => '0x'.dechex($nonce),
				'gasprice'=>$gasPriceInWei
			], function ($err, $result) use ($contract, $ownerAccount, $toAccount, &$transactionId) {
				if ($err !== null) {
					throw new Exception($err->getMessage(), 3);
				}
				else {
					$transactionId = $result;
				}
			});
		} catch (Exception $e) {
			$send_error_msg = '';
			if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
				$send_error_msg = '(gas required exceeds allowance)';
			} else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
				$send_error_msg = '(insufficient funds)';
			}

			$data_to_sendlog = [];
			$data_to_sendlog['send_type'] = $send_type;
			$data_to_sendlog['coin_type'] = $token;
			$data_to_sendlog['user_id'] = $_SESSION['user_id'];
			$data_to_sendlog['to_address'] = $toAccount;
			$data_to_sendlog['msg_type'] = 'error'; // error, permission
			$data_to_sendlog['message'] = 'send'.$send_error_msg;
			$db = getDbInstance();
			$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

			new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ', ' . $actualAmountToSendWithoutDecimal . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
			header('Location: ' . $return_page);
			exit();
		}

		// Add log records
		$data_to_send_logs = [];
		$data_to_send_logs['send_type'] = $send_type;
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['from_id'] = $_SESSION['user_id'];
		//$data_to_send_logs['to_id'] = '';
		$data_to_send_logs['from_address'] = $ownerAccount;
		$data_to_send_logs['to_address'] = $toAccount;
		$data_to_send_logs['amount'] = $actualAmountToSendWithoutDecimal;
		$data_to_send_logs['fee'] = $adminFee;
		if ( !empty($transactionId) ) {
			$data_to_send_logs['transactionId'] = $transactionId;
		}
		$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
		$data_to_send_logs['etoken_send'] = 'P';
		$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

		$db = getDbInstance();
		$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);

		if(!empty($transactionId))
		{
			
			$data_to_store = filter_input_array(INPUT_POST);
			$data_to_store = [];
			$data_to_store['created_at'] = date('Y-m-d H:i:s');
			$data_to_store['sender_id'] = $_SESSION['user_id'];
			$data_to_store['reciver_address'] = $toAccount;
			$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
			$data_to_store['fee_in_eth'] = 0;
			$data_to_store['coin_type'] = $token;
			$data_to_store['status'] = 'completed';
			$data_to_store['fee_in_gcg'] = $adminFee;
			$data_to_store['transactionId'] = $transactionId;
			
			$db = getDbInstance();
			$last_id = $db->insert('user_transactions', $data_to_store);
			
			
			// send CTC Token to destination Address START
			
			$adminTransactionId = '';
			
			$adminFeeInDecimal = $adminFee*1000000000000000000;
			$adminFeeInDecimal = dec2hex($adminFeeInDecimal);
			$adminFeeInDecimal = '0x'.$adminFeeInDecimal; // Must add 0x

			$senderAccount = $n_master_wallet_address;
			$toAccount2 = $n_master_etoken_ctc_fee_wallet_address;	

			try {
				$contract = new Contract($web3->provider, $testAbi);
				$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount2, $adminFeeInDecimal, [
					'from' => $senderAccount,
					'gas' => '0x'.dechex(100000),   //100000
					'gasprice'=>$gasPriceInWei
				], function ($err, $result) use ($contract, $ownerAccount,  &$adminTransactionId) {
					if ($err !== null) {
						$adminTransactionId = '';
						throw new Exception($err->getMessage(), 4);
					}
					else {
						$adminTransactionId = $result;
					}
				});
			} catch (Exception $e) {
				new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ', ' . $adminFee . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}

			if(!empty($adminTransactionId))
			{			
				$data_to_store_admin = filter_input_array(INPUT_POST);
				$data_to_store_admin = [];
				$data_to_store_admin['created_at'] = date('Y-m-d H:i:s');
				$data_to_store_admin['sender_id'] = $_SESSION['user_id'];
				$data_to_store_admin['reciver_address'] = $toAccount2;
				$data_to_store_admin['amount'] = $adminFee;
				$data_to_store_admin['coin_type'] = 'ctc';
				$data_to_store_admin['fee_in_eth'] = 0;
				$data_to_store_admin['fee_in_gcg'] = 0;
				$data_to_store_admin['status'] = 'completed';
				$data_to_store_admin['transactionId'] = $adminTransactionId;
				
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store_admin); 		
			}
			// send CTC Token to destination Address END
			
			
			// Add log records
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = $send_type;
			$data_to_send_logs['coin_type'] = 'ctc';
			$data_to_send_logs['from_id'] = $_SESSION['user_id'];
			//$data_to_send_logs['to_id'] = '';
			$data_to_send_logs['from_address'] = $ownerAccount;
			$data_to_send_logs['to_address'] = $toAccount2;
			$data_to_send_logs['amount'] = $adminFee;
			$data_to_send_logs['fee'] = '0';
			if ( !empty($adminTransactionId) ) {
				$data_to_send_logs['transactionId'] = $adminTransactionId;
			}
			$data_to_send_logs['status'] = !empty($adminTransactionId) ? 'send' : 'fail';
			$data_to_send_logs['etoken_send'] = 'N';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl2 = $db->insert('user_transactions_all', $data_to_send_logs);
			

			$_SESSION['success'] = !empty($langArr['send_success_message1']) ? $langArr['send_success_message1'] : "Transmission was successful.";
			header('location: '.$return_page);
			exit();
			
		} else {
			$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again.";
			header('location: '.$return_page);
			exit();
			
		}	
		
	}
						
	// send transactions end					

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

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>

<div id="page-wrapper">
	<div id="exchange_etp3" class="send_common">

		<?php include('./includes/flash_messages.php') ?>
		<div class="row">

			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->
				   <div id="main_content" class="panel-body">
						<div class="card">
							
							<ul class="index_token_block">
								<li class="token_block">
									<div class="a1">
										<div class="img2"><div><img src="images/logo2/<?php echo $token; ?>.png" alt="<?php echo $token; ?>" /></div></div>
										<span class="text"><?php echo $n_full_name_array[$token]; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewCoinBalance,$n_decimal_point_array[$token]); ?></span><span class="amount_t2"> <?php echo strtoupper($token); ?></span></span>
									</div>
								</li>
								<?php if($accountType!='admin' && $transfer_approved == 'C') { ?>
									<li class="token_block">
										<div class="a1">
											<div class="img2"><div><img src="images/logo2/ctc.png" alt="ctc" /></div></div>
											<span class="text"><?php echo $n_full_name_array['ctc']; ?></span>
											<span class="amount"><span class="amount_t1"><?php echo new_number_format($getCtcTokenBalance,$n_decimal_point_array['ctc']); ?></span><span class="amount_t2"> CTC</span></span>
										</div>
									</li>
								<?php } ?>	
							</ul>

							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">
								<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo strtoupper($token); ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['exchange_rate']) ? $langArr['exchange_rate'] : "Exchange Rate :"; ?> 1 <?php echo strtoupper($token); ?>  = <?php echo $getExchangeRateVal; ?> e<?php echo strtoupper($token); ?></span>
										</label>
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="number">
									</div>
									<div class="clearfix"></div>
														
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo 'e'.strtoupper($token); ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<?php if ( $transfer_approved == 'C' ) { ?><span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php echo $getTokenFeeVal; ?> CTC</span><?php } ?>
										</label>
										<input autocomplete="off" disabled required id="etoken_value" name="etoken_value" placeholder="" type="text">
									</div>
									<div class="clearfix"></div>
									
									<div class="col-md-12 btn_area">
										<input name="submit" class="btn" value="<?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>" type="submit">
									</div>
								</form>
							</div>

						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('#amount').keyup(function () {
    if($(this).val() == '') {
            $("#etoken_value").val(0);
        } else {
			var getAmt = $('#amount').val();
			var etoken_value = getAmt*<?php echo $getExchangeRateVal; ?>;
			$("#etoken_value").val(etoken_value);
        }
    });	
});
</script>

<?php include_once 'includes/footer.php'; ?>
