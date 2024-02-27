


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



array("0xfd808b64f0a1bd194bd6959fc68d2c9419df9bc3", "23000"),
array("0x23ffc544a40526b65cfa5e727d1151fe42bf557e", "410770"),
array("0xb5a9dfafbbf7d47eec0ea21e8db7cb33514be2f5", "2160"),
array("0x8ff4b6f1c0e114bda95c9cd94c857db82415e8df", "14500"),
array("0x56a012f0216dabb52f9e83297c0ad308526d392f", "11243"),
array("0x26c1b739ff27bb2364e614934cfd430c481c07a8", "20176"),
array("0xcd715ed666c2832dbfc080ebb659ff968f799eac", "10000"),
array("0x61700d5b91dbcfab47b03aa39f8144050e85c21c", "22384"),
array("0x968a480164014a416588cf48bbf4f31ee6d1f415", "20000"),
array("0xc6a2e44d699a78860cbef5820af264aba5391fb3", "6000"),
array("0x3bda1018e597ef979b388be39ce24985765d8048", "617710"),
array("0xfcb264fddd278860e596a7edfa78671437d82a13", "280200"),
array("0x1b2e1726ac12ab632e5bcd087673b9509d9dac0b", "292382"),
array("0x31d4a7f61f3d701514aeebe6c24bbbaa7f7e80b6", "277000"),
array("0x580191b16913d43217901252d83335866dea8998", "200882"),
array("0x65f49de48a09cb59e56a0db5cbe6cd199783d9e4", "168627"),
array("0x800eff5bcc68ae512d2ce02b0a28c03fd22616d2", "160000"),
array("0x83cda0536ae312212eaf874157102f6f27098ed7", "97924"),
array("0x8c01da957cf4790063a329e51eb7d1872f84001d", "148043"),
array("0x364bc7ca6ac5d014bf056acc1646e13270d01841", "10000"),
array("0x82d094f26f3cd40f52d89e3a804c5fcb176ae454", "92000")


  );
	
/*
마켓코인 전송
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

	$contract->at($marketCoinContractAddress)->send('transfer', $toAddress, $amountToSend, [
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





