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



	$newAccount = '0x1d815d7b54ecd8e5d43e2692cf9d6ee311bbb3f6';

	$adminAccountWalletAddress = "0x1d815d7b54ecd8e5d43e2692cf9d6ee311bbb3f6";
	$adminAccountWalletPassword = "+821064321017ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
	// unlock account

	$personal = $web3->personal;
	$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ($unlocked) {
//			echo 'New account is unlocked!' . PHP_EOL;
		} else {
//			echo 'New account isn\'t unlocked' . PHP_EOL;
		}
	});
	
	
	$fromAccount = $adminAccountWalletAddress;

	//$amountToSendInteger = 30;
	$amountToSendInteger = 25500;
	$toAccount = "0x4fb2f56912444d3951ea3fcae8aedfe9da5c115d";





//ctc 전송
return;
exit;


//	$amountToSend = $amountToSendInteger*1000000000000000000;
//	$amountToSend = dec2hex($amountToSend);

	$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);
	$amountToSend1 = dec2hex($amountToSend);
	
	$amountToSend = '0x';
	$amountToSend .= $amountToSend1;





	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $testAbi);






  


//	$toAccount = $newAccount;
	$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
		'from' => $fromAccount,

/*
		'gas' => '0x186A0',   //100000
		'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
*/
//		'gas' => '0x186A0',   //100000
//		'gas' => '0x7A120',   //300000
		'gas' => '0x4C4B40',   //5000000
	
		'gasprice' =>'0x1DCD65000'    //30000000000 // 30 gwei


		//'gas' => '0xD2F0'
	], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger) {
		

		 if ($err !== null) {
			throw $err;
		} 
		 if ($result) {
			//$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
			//$_SESSION['success'] = $msg;

			

		} 

		$transactionId = $result;

	}); 
	

echo $transactionId ;

	



	// send 50 token to new register users end
		


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