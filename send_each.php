<?php

session_start();
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
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



	$newAccount = '0x54d894c5e31c74c54dd233d0daf6d1386b5bdfff';

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
//			echo 'New account is unlocked!' . PHP_EOL;
		} else {
//			echo 'New account isn\'t unlocked' . PHP_EOL;
		}
	});
	
	
	$fromAccount = $adminAccountWalletAddress;

	//$amountToSendInteger = 30;
	$amountToSendInteger = 5;
	$amountToSend = $amountToSendInteger*1000000000000000000;



	$amountToSend = dec2hex($amountToSend);
	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $testAbi);


$wallet_array = array(
"0x1732d63c359eddefc6386080fd5eb9600361f2ed",
"0x632935c18da8c2af09a31cbe8059c9a514d7a400",
"0x002775b248cd649f989eb38b3c49ac05189e84cb",
"0xfa8f00ecff938fc415d757b7f9366bb39306d009",
"0x6ba8c780f17fb5f4405fc69d25f0806691ba3ade",
"0x81b01a65e4696a539f444808d8e3c3745c3acade",
"0x3db5fd49b55dfb884790400f6d2d095fb84c7aa8",
"0xd3d8978a986487961ef7d26a286219f31f6ba4e0"
); 

$arrlength=count($wallet_array);

exit;
return;
for($x=0;$x<$arrlength;$x++)
  {
	$toAccount = $wallet_array[$x];
  echo $toAccount;
  echo "<br>";

//	$toAccount = $newAccount;
	$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
		'from' => $fromAccount,
		'gas' => '0x186A0',   //100000
		'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
		//'gas' => '0xD2F0'
	], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId,$amountToSendInteger) {
		 if ($err !== null) {
			throw $err;
		} 
		 if ($result) {
			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
			$_SESSION['success'] = $msg;
		} 

	}); 
	



	echo $transactionId;


  }
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