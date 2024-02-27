


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




//회사주소
	$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";

/*
	$adminAccountWalletAddress = '0x9cdb4eaad0c85c0df2ad0f8ff6904b7f72f8177e';
	$adminAccountWalletPassword = '+821082947633'.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
*/


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
//	$amountToSendInteger = 500000;
//	$amountToSend = $amountToSendInteger*1000000000000000000;



//	$amountToSend = dec2hex($amountToSend);
	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $tokenPayAbi);


$tp3list = array
  (









array("0xf4a587c23316691f8798cf08e3b541551ec1ffcb", "10")





  );
	
/*
krw 전송
*/

return;
exit;


$arrlength=count($tp3list);

for($x=0;$x<$arrlength;$x++)
  {

	$toAddress = $tp3list[$x][0];
	echo $toAddress;
	echo "<br>";

	$toAmount = $tp3list[$x][1];
//	echo $toAmount;
//	echo "<br>";

	$amountToSendInteger = $toAmount;
//	$amountToSend = $amountToSendInteger*1000000000000000000;
//	$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);
	$amountToSend = bcmul ($amountToSendInteger, 1000000);

	$amountToSend1 = dec2hex($amountToSend);
	
	$amountToSend = '0x';
	$amountToSend .= $amountToSend1;


/*
09C40 40000
C350 50000
11170 70000
13880 80000
186A0 100000


*/

	$contract->at($koreanWonContractAddress)->send('transfer', $toAddress, $amountToSend, [
		'from' => $fromAccount,
		'gas' => '0x186A0',   //100000
		'gasprice' =>'0x1DCD65000'    //30000000000 // 30 gwei
		//'gas' => '0xD2F0'
	], function ($err, $result) use ($contract, $fromAccount, $toAddress,$transactionId,$amountToSendInteger) {
		 if ($err !== null) {
			throw $err;
		} 
		 if ($result) {

//			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
//			$_SESSION['success'] = $msg;



		} 

	}); 
	
/*
	echo $amountToSend;
	echo "<br>";
	echo $amountToSend1;


	echo "<br>";
	echo $amountToSendInteger;
	echo $transactionId;
	echo "<br>";
*/
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





<?php
/*

$tp3list = array
  (
  array("0xfd2a9cee4ac79bc0370f27d2628245282a2d9ba1", '14805154'),
  array("0xd8fb6cc81e2090f7376ab9b8b24cbbb88c9b719a", '11446758'),
  array("0x479896f340ec5a0eb980106fe885f8321da2a1bb", '10178572'),
  array("0x2186c3331d93ee586a71edeb8002ea3e8ade776c", '7922180')
  );


$arrlength=count($tp3list);

for($x=0;$x<$arrlength;$x++)
  {
	$toAddress = $tp3list[$x][0];
	echo $toAddress;
	echo "<br>";

	$toAccount = $tp3list[$x][1];
	echo $toAccount;
	echo "<br>";

  }

*/

?>





