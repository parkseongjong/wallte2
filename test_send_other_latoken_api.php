<?php 
// Page in use
//session_start();
header('Content-Type: application/json');

define('WALLET_BASE_PATH', '/var/www/html/wallet2');
// https://cybertronchain.com/wallet2/test_send_other_latoken_api.php
require_once WALLET_BASE_PATH.'/config/config.php';
require_once WALLET_BASE_PATH.'/config/new_config.php';
require_once WALLET_BASE_PATH.'/config/proc_config.php';
//require_once './includes/auth_validate.php';

require(WALLET_BASE_PATH.'/includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

require_once WALLET_BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

require_once WALLET_BASE_PATH.'/lib/WalletProcess.php';
$wi_wallet_process = new WalletProcess();

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$eth = $web3->eth;


$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);


// ===========================================================
$userId = $_POST['userId'];
$amount = $_POST['amount'];
//$userId = '5137';
$token = 'usdt';

$transfer_passwd_check = 'N';
// Y -> check, N -> not check

// 지갑에 버튼 심을 때 거기에서 체크하면 되기 때문에 여기서는 할 필요 없음
$id_auth_check = 'N';
// Y -> check, N -> not check


$toAccount = '0xeefd4e236dfac8f3e4f76890600ac41cb2eb6286';
//$amount = '1';
$amountToSend = $amount;

$err_code = '';
$ok_json = array('code'=>200,'error'=>false, 'msg'=>'Success');

// ===========================================================

$db = getDbInstance();
$db->where("id", $userId);
$userData = $db->getOne('admin_accounts');
//$checkApproved = $userData['usdt_approved'];	
$accountType = $userData['admin_type'];
$walletAddress = $userData['wallet_address'];
$transfer_approved = $userData['transfer_approved'];

$fromAccount = $walletAddress;
$fromAccountPass = $userData['email'].$n_wallet_pass_key;

$adminAddress =	$n_master_wallet_address;
$adminPassword =	$n_master_wallet_pass;


if ( $id_auth_check == 'Y' ) {
	$user_id_auth = 'N';
	if ( !empty($userData['id_auth']) && $userData['id_auth'] == 'Y' ) {
		$user_id_auth = 'Y';
	}
	$ip_kor = '';
	$ip_kor = trim(new_ipinfo_ip_chk('2'));
	if ($ip_kor == 'KR' && $user_id_auth != 'Y') {
		$err_code = '711';
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}
}
if ( $transfer_passwd_check == 'Y' && empty($userData['transfer_passwd']) ) {
	$err_code = '712';
	jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
}

$getNewBalance = 0;
$getNewCoinBalance = 0 ;

$tokenArr = $contractAddressArr[$token];
$tokenAbi = $tokenArr['abi'];
$tokenContractAddress = $tokenArr['contractAddress'];
$decimalDigit = $tokenArr['decimal'];

// ETH 잔액 조회
$getNewBalance = $wi_wallet_process->wi_get_balance('eth', $walletAddress, $contractAddressArr);
if ( $getNewBalance == -1 ) {
	$err_code = '611';
	jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
}

// Coin 잔액 조회
$getNewCoinBalance = $wi_wallet_process->wi_get_balance($token, $walletAddress, $contractAddressArr);
if ( $getNewCoinBalance == -1 ) {
	$err_code = '613';
	jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
}


$db = getDbInstance();
$getTokenFee = $db->where("module_name", 'send_token_fee')->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];

// 전송권한 체크 시작
if ( ($accountType=='user' || $accountType=='store') && $transfer_approved == 'C' ) {
	$err_code = npro_send_approve_check($userId, 'ctc', $token);
	if ( $err_code != '200' ) {
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}
	$err_code = npro_send_approve_check($userId, $token, $token);
	if ( $err_code != '200' ) {
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}
}
// 전송권한 체크 종료


// unlock account
try {
	$personal = $web3->personal;
	$personal->unlockAccount($adminAddress, $adminPassword, function ($err, $unlocked) {
		if ($err !== null) {
			throw new Exception($err->getMessage(), 3);
		}
		if (!$unlocked) {
			throw new Exception($err->getMessage(), 3);
		}
	});
} catch (Exception $e) {
	$err_code = '641';
	nproc_fn_logSave($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $err_code, array('User'=>$userId, 'Coin'=>$token));
	jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
}



if ($accountType=='admin' || $transfer_approved != 'C') {

	if($getNewCoinBalance < trim($amount)){
		$err_code = '603';
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}


	//if($getNewBalance < 0.008){
	//	$err_msg = !empty($langArr['insufficient_eth_balance']) ? $langArr['insufficient_eth_balance'] : "Insufficient Eth Balance";
	//	header('location: send_other.php?token='.$token);
	//	exit();
	//}
	
	$personal = $web3->personal;
	try {
		$personal->unlockAccount($fromAccount, $fromAccountPass, function ($err, $unlocked) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 4);
			}
			if ( !$unlocked) {
				throw new Exception($err->getMessage(), 4);
			}
		});
	
	} catch (Exception $e) {
		$err_code = '641';
		nproc_fn_logSave($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $err_code, array('User'=>$userId, 'Coin'=>$token));
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}

		

	//$amountToSend = $amountToSend*$decimalDigit; // 201112
	$amountToSend = bcmul($amountToSend,$decimalDigit);

	$amountToSend = dec2hex($amountToSend);
	$amountToSend = '0x'.$amountToSend; // Must add 0x
	$gas = '0x9088';
	$transactionId = '';
	
	/*
	try {
		$otherTokenContract = new Contract($web3->provider, $tokenAbi);
		$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
			'from' => $fromAccount,
			'gas' => '0x186A0',   //100000
			'gasprice'=>$gasPriceInWei
		], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 5);
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
		$err_code = '661';

		$last_id_dts = new_set_send_err_log ('send', $token, $userId, $toAccount, 'error', 'send'.$send_error_msg);
		nproc_fn_logSave($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $err_code, array('User'=>$userId, 'Coin'=>$token));
		
		//if ( !empty($send_error_msg) ) {
		//	$err_msg = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : "The balance is insufficient.";
		//} else {
		//	$err_msg = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
		//}
	}
	*/
	
	if(!empty($transactionId)){
		$last_id = new_set_user_transactions($token, $userId, $toAccount, $amount, 0, 0, 'completed', $transactionId);
	} else {
		$err_code = '681';
	}


	
	$status = !empty($transactionId) ? 'send' : 'fail';
	$last_id_sl = new_set_user_transactions_all('send', $token, $userId, '', $fromAccount, $toAccount, $amount, 0, $transactionId, $status, '', '', '', '');
	
	if ( $err_code == '661' || $err_code == '681' ) {
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}

}
else {
	
	

	//$feePercent = $getTokenFeeVal;
	$adminFee = $getTokenFeeVal;
	$adminFee = number_format((float)$adminFee,2);
	//$actualAmountToSend = $amountToSend-$adminFee;
	$actualAmountToSend = $amountToSend;
	$actualAmountToSendWithoutDecimal = $actualAmountToSend;
	//$actualAmountToSend = $actualAmountToSend*$decimalDigit; // 201112
	$actualAmountToSend = bcmul($actualAmountToSend, $decimalDigit);
	
	
	 
	$getCtcTokenBalance = 0 ;
	$getCtcTokenBalance = $wi_wallet_process->wi_get_balance('ctc', $walletAddress, $contractAddressArr);
	if ( $getCtcTokenBalance == -1 ) {
		$err_code = '612';
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}

		
	if($getCtcTokenBalance < $adminFee){
		$err_code = '602';
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}
	if($getNewCoinBalance < trim($amount)){
		$err_code = '603';
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
	}
	
	
	//echo $adminFee; die;
	$actualAmountToSend = dec2hex($actualAmountToSend);
	$actualAmountToSend = '0x'.$actualAmountToSend; // Must add 0x
	$gas = '0x9088';
	$transactionId = '';
	
	//$senderAccount = $fromAccount;
	$senderAccount = $n_master_wallet_address;
	$ownerAccount = $walletAddress;
	$nonce = "";
	$eth->getTransactionCount($senderAccount,'pending', function ($err, $getNonce) use (&$nonce) {
		if ($err !== null) {
			$nonce = "";
		} else {
			$nonce = $getNonce->toString();
			$nonce = (int)$nonce+1;
		}
	});
	
	/*
	try {
		// send CTC Token to destination Address
		$otherTokenContract = new Contract($web3->provider, $tokenAbi);
		//$contract->at($contractAddress)->send('transfer',$toAccount, $actualAmountToSend, [
		$otherTokenContract->at($tokenContractAddress)->send('transferFrom',$ownerAccount, $toAccount, $actualAmountToSend, [
			'from' => $senderAccount,
			'nonce' => '0x'.dechex($nonce),
			'gasprice'=>$gasPriceInWei
		], function ($err, $result) use ($contract, $ownerAccount, $toAccount, &$transactionId) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 7);
			} else {
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
		$last_id_dts = new_set_send_err_log ('send', $token, $userId, $toAccount, 'error', 'send'.$send_error_msg);
		$err_code = '662';
		nproc_fn_logSave($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $err_code, array('User'=>$userId, 'Coin'=>$token));
		
	}*/
	
	
	// Add log records (2020-05-18, YMJ)
	$status = !empty($transactionId) ? 'send' : 'fail';
	$last_id_sl = new_set_user_transactions_all('send', $token, $userId, '', $ownerAccount, $toAccount, $actualAmountToSendWithoutDecimal, $adminFee, $transactionId, $status, '', '', '', '');


	if (!empty($transactionId)) {
		
		$last_id = new_set_user_transactions($token, $userId, $toAccount, $actualAmountToSendWithoutDecimal, 0, $adminFee, 'completed', $transactionId);
	
		// send CTC Token to destination Address START
		
		$adminTransactionId = '';
		
		//$adminFeeInDecimal = $adminFee*1000000000000000000;
		$adminFeeInDecimal = bcmul($adminFee,1000000000000000000); // 201112

		$adminFeeInDecimal = dec2hex($adminFeeInDecimal);
		$adminFeeInDecimal = '0x'.$adminFeeInDecimal; // Must add 0x
		//$contract->at($contractAddress)->send('transfer', $n_master_wallet_address, $adminFeeInDecimal, [
		$senderAccount = $n_master_wallet_address;
		$toAccount = $n_master_wallet_address;

		$toAccount2 = $n_master_wallet_address_fee;
		 // 200810			
		/*try {
			$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount2, $adminFeeInDecimal, [
				'from' => $senderAccount,
				'gas' => '0x'.dechex(100000),   //100000
				'gasprice'=>$gasPriceInWei
			], function ($err, $result) use ($contract, $ownerAccount,  &$adminTransactionId) {
				if ($err !== null) {
					$adminTransactionId = '';
					throw new Exception($err->getMessage(), 8);
				}
				else {
					$adminTransactionId = $result;
				}
			});
		} catch (Exception $e) {
			nproc_fn_logSave($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $err_code, array('User'=>$userId, 'Coin'=>$token, 'adminFee'=>$adminFee));
			$err_code = '663';

		}*/

		if (!empty($adminTransactionId)) {			
			$last_id = new_set_user_transactions('ctc', $userId, $toAccount2, $adminFee, 0, 0, 'completed', $adminTransactionId);
		} else {
			$err_code = '682';
		}
		// send CTC Token to destination Address END
		
		$status = !empty($adminTransactionId) ? 'send' : 'fail';
		$last_id_sl2 = new_set_user_transactions_all('send', 'ctc', $userId, '', $ownerAccount, $toAccount2, $adminFee, 0, $adminTransactionId, $status, '', '', '', '');

		if ( $err_code == '662' || $err_code == '663' || $err_code == '682' ) {
			jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
		}
		
	} else {
		$err_code = '681';
		jsonReturn(array('code'=>$err_code,'error'=>true,'msg'=>npro_err_message($err_code)));
		
	}	
	
}
					
// send transactions end					


/*

	echo 'ETH Balance : '.$getNewBalance.'<br />';
	echo $token.' Balance : '.$getNewCoinBalance.'<br />';
	echo 'Fee : '.$getTokenFeeVal.'<br />';
	echo 'User Type : '.$accountType.', Fee Type : '.$transfer_approved.'<br />';
	echo 'Error : '.$err_code. ' '.npro_err_message($err_code);
	exit;
	
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

function jsonReturn($arr='') {
    if (empty($arr)) {
        $arr = array('code'=>'99','msg'=>'Error');
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    } else {
        if (is_array($arr)) {
            echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('code'=>'99','msg'=>$arr), JSON_UNESCAPED_UNICODE);
        }
    }
    //logWrite($arr);
    exit();
}
?>
