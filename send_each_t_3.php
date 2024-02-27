<?php

	session_start();
	require_once './config/config.php';
	require('includes/web3/vendor/autoload.php');
	use Web3\Web3;
	use Web3\Contract;

	$web3 = new Web3('http://127.0.0.1:8545/');
	$eth = $web3->eth;
	$personal = $web3->personal;

		
	

	$newAccount = '0xfd469315a1046581e90e88e6d9838f83e266c35a';
		

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

	
		
/*
이더리움 전송
*/


	return;
	exit;

	
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

/*
	$amountToSend = 0.001;
	if(!empty($getTxId)) {
		$db = getDbInstance();
		$data_to_store = [];
		$data_to_store['user_id'] = 'DirectSend';
		$data_to_store['coin_type'] = 'eth';
		$data_to_store['tx_id'] = $getTxId;
		$data_to_store['ethmethod'] = "sendTransaction";
		$data_to_store['amount'] = $amountToSend;
		$data_to_store['to_address'] = $toAccount;
		$data_to_store['from_address'] = $fromAccount;
		$last_id = $db->insert('ethsend', $data_to_store);
		//die;
	}
*/
	

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