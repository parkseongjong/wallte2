<?php
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

$newAccount = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';
$db = getDbInstance();

return false;
exit;

//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
				//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
				$adminAccountWalletAddress = $n_master_wallet_address_cta;
				$adminAccountWalletPassword = $n_master_wallet_pass_cta;
				$adminAccountWalletId = $n_master_id_cta;

				// unlock account

				// Add (2020-05-22, YMJ)
				$getSendFreeCtc = $db->where("module_name", 'send_free_ctc')->getOne('settings');
				if ( !empty($getSendFreeCtc['value']) ) {
					$getSendFreeCtcVal = $getSendFreeCtc['value'];
				}
				if ( empty($getSendFreeCtcVal) ) {
					$getSendFreeCtcVal = 2;
				}

				$personal = $web3->personal;
				try {
					$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
						if ($err !== null) {
							echo 'Error: ' . $err->getMessage();
							new_fn_logSave('Error (CTC airdrop unlock) : '. $err->getMessage() . ', File : ' . $_SERVER['SCRIPT_FILENAME']);
							return;
						}
						if ($unlocked) {
							//echo 'New account is unlocked!' . PHP_EOL;
						} else {
							//echo 'New account isn\'t unlocked' . PHP_EOL;
						}
					});
				} catch (Exception $e) {
					new_fn_logSave('Exception Error (CTC airdrop unlock) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				}
				
				
				$fromAccount = $adminAccountWalletAddress;
				$toAccount = $newAccount;
				//$amountToSendInteger = 30;
				//$amountToSendInteger = 3; // 5 -> 3 (2020.05.12 14:58, YMJ)
				$amountToSendInteger = $getSendFreeCtcVal; // (2020-05-22, YMJ)
				$amountToSend = $amountToSendInteger*1000000000000000000;

				$amountToSend = dec2hex($amountToSend);
				$gas = '0x9088';
				$transactionId = '';
				$txid = '';
				/*
				try {
					$contract = new Contract($web3->provider, $testAbi);
					$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
						'from' => $fromAccount,
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
						//'gas' => '0xD2F0'
					], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger, &$txid, &$adminAccountWalletId) {
						if ($err !== null) {
							new_fn_logSave('Error (CTC airdrop) : '. $err->getMessage() . ', File : ' . $_SERVER['SCRIPT_FILENAME']);
							//	throw $err;
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
							//$last_id = $db->insert('user_transactions', $data_to_store);
							
							
						}  
						else {
							//$_SESSION['failure'] = "Unable to send Token ! Try Again";
						}
					}); 
				} catch (Exception $e) {
					new_fn_logSave('Exception Error (CTC airdrop) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				}
				*/
				/*
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
				
				// send transaction
				*/


				/// send free tp3 to user  start

				
				$adminAccountWalletAddress = $n_master_wallet_address_tpa;
				$adminAccountWalletPassword = $n_master_wallet_pass_tpa;
				$adminAccountWalletId = $n_master_id_tpa;

			
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
							echo 'Error: ' . $err->getMessage();
							new_fn_logSave('Error (TP3 airdrop unlock) : '. $err->getMessage() . ', File : ' . $_SERVER['SCRIPT_FILENAME']);
							return;
						}
						if ($unlocked) {
							//echo 'New account is unlocked!' . PHP_EOL;
						} else {
							//echo 'New account isn\'t unlocked' . PHP_EOL;
						}
					});
				} catch (Exception $e) {
					new_fn_logSave('Exception Error (TP3 airdrop unlock) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
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
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
						//'gas' => '0xD2F0'
					], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger, &$txid, &$adminAccountWalletId) {
						if ($err !== null) {
							new_fn_logSave('Error (TP3 airdrop) : '. $err->getMessage() . ', File : ' . $_SERVER['SCRIPT_FILENAME']);
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
							$data_to_store['coin_type'] = "tp3";
							$data_to_store['transactionId'] = $transactionId;
							
							//print_r($data_to_store);die;
							$db = getDbInstance();
							//$last_id = $db->insert('user_transactions', $data_to_store);
							
							
						}  
						else {
							//$_SESSION['failure'] = "Unable to send Token ! Try Again";
						}
					});
				} catch (Exception $e) {
					new_fn_logSave('Exception Error (TP3 airdrop) : ' . $e->getMessage() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
				}
				/*
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
				