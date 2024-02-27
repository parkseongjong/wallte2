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
	$amountToSendInteger = 500000;
//	$amountToSend = $amountToSendInteger*1000000000000000000;
	$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);


	$amountToSend = dec2hex($amountToSend);
	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $tokenPayAbi);


$wallet_array = array(
"0xd75663b674a025e9acd422c7260f809e921c28cc",
"0x4cf767eef391781bb0b4a64eadfe40be27449345",
"0x4930ec5caaf50c8b8f2043a12d31ad788c6cafa1",
"0x3d6dfef5cdf342e300ad34b07d448d23645176e7",
"0x2d5484cd87c6f3e8a359289c34222bc26bd515aa",
"0x69455c85dfc968b8bcb51d1c7f51c90a6b926f49",
"0x9f57fd6a2bffdcd2363060ca41a9c98031a332d7",
"0xbada7047e70c6f7b89602df1339519776398a7f0",
"0x59db25d5e376115c99053f5e3004718781cc7e07",
"0x0d13fb4e776bb03243807b1793ab6d4f934a52c2",
"0x53ce76e00965a68d4bbc6833f7a2a690c7f87ab1",
"0x1ad0c15c174dc208f6de8d02c3b6710f42c39b07",
"0x3a3036974b75c81645afe6e517605e9e69e64334"

); 


$arrlength=count($wallet_array);


echo $amountToSend;


exit;
return;
for($x=0;$x<$arrlength;$x++)
  {
	$toAccount = $wallet_array[$x];
  echo $toAccount;
  echo "<br>";

//	$toAccount = $newAccount;
	$contract->at($tokenPayContractAddress)->send('transfer', $toAccount, $amountToSend, [
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