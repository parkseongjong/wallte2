<?php
// Test page

//cybertronchain.com/wallet2/transfer_eth.php
	session_start();
	require_once './config/config.php';
	require('includes/web3/vendor/autoload.php');
	use Web3\Web3;
	use Web3\Contract;

	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$eth = $web3->eth;
	$personal = $web3->personal;

		
	

	//$newAccount = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';
		

	//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
	// unlock account


	$newAccount = '0xeefd4e236dfac8f3e4f76890600ac41cb2eb6286';
		

	$adminAccountWalletAddress = "0xf4a587c23316691f8798cf08e3b541551ec1ffcb";
	$adminAccountWalletPassword = "+821049138089"."ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
	// unlock account




	$personal = $web3->personal;
	$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			//return;
		}
		if ($unlocked) {
			echo 'New account is unlocked!' . PHP_EOL;
		} else {
			echo 'New account isn\'t unlocked' . PHP_EOL;
		}
	});
	
	
	$fromAccount = $adminAccountWalletAddress;
	$toAccount = $newAccount;

	
		
/*
�̴����� ����
*/

/**/
return;
exit;


//$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
echo $gasPriceInWei;
$gasPriceInWei = "0x".dechex($gasPriceInWei);




$amountToSend = 0.02;


$decimalDigit = 1000000000000000000;
	$amountToSend = $amountToSend*$decimalDigit;

	$amountToSend = dec2hex($amountToSend);
	$gas = '0x9088';
	$transactionId = '';
	echo $amountToSend;
	$fromAccount = $adminAccountWalletAddress;
	$toAccount = $newAccount;

	// send transaction

	$eth->sendTransaction([
		'from' => $fromAccount,
		'to' => $toAccount,
		'value' => '0x'.$amountToSend,
		'gasprice'=>$gasPriceInWei
	], function ($err, $result) use (&$transactionId) {
		if ($err !== null) {
			////print_r($err); die;
			//throw new Exception($err->getMessage(), 4);
			echo 'Error!';

		}
		$transactionId = $result;

	});



	/*
	// send transaction
	$eth->sendTransaction([
		'from' => $fromAccount,
		'to' => $toAccount,
		//'value' => '0x27CA57357C000'
//		'value' => '0xAA87BEE538000',
//		'value' => '0x1FF973CAFA8000',
		'value' => '0x6A94D74F430000',
		
		'gas' => '0x186A0',   //100000
		'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
		
	], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			//die;
		}
		else {
			$getTxId = $transaction;
			echo $getTxId;
		}

	});

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