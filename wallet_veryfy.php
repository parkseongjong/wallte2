


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

$tp3list = array
  (

array("0x7c507ad16918d32be5b6a5539d1d7a74170c9aba", ""),
array("0x16cb9400b850ca613c796bfbb5ad83ca5f26d541", ""),
array("0x6756fdf7fad370af1385907383928ff89ffb3e42", ""),
array("0x7df648ed91173d73e88b54c2e0ed81da7f696554", ""),
array("0x3fc709f6070328204ee84a25ee44230c16c2ee95", ""),
array("0xa60165e62587c3821a7f8c3218d322effb4ae3d3", ""),
array("0xc17560e7212070b867c9fc49ff841f14691ab325", ""),
array("0x088107c623a21cf8496aea9e6f46f9f0cd3de46c", ""),
array("0x303eb8efa41da1cac3584cf7bf062b33b5af522e", ""),
array("0xc1659bd3f50b86dc6c458dc0c45cccd58e7339b6", ""),
array("0x10384609a8e27955a8fc75beb416999798397e98", ""),
array("0xa8c4ad1249f4e4c283ea512e29267b255d3e33df", ""),
array("0x620f6956230df8c56d6d1efd6d01b8a8257bb4c8", ""),
array("0x2bdaf728e060941da1a03f526b74b689f0825d6d", ""),
array("0x503886e5e52fcff759765a43d00092bcd28b9389", ""),
array("0xabcf1a2db95bee43ca542d5aa43cafe6775642b1", ""),
array("0x15fb0ce8957a7d1da971289c8083c172009ad691", ""),
array("0x115f357c7c9634cb66e5b0cc7d75c50c298edf9c", ""),
array("0xc6eb015fe6ecb1cbd7ac71c449539ca8a6895d90", ""),
array("0xf161acd2c8bd8fae35be0846d80aac07db310ea7", ""),
array("0xceba8be05db3c450fae8b54e7676070507273533", ""),
array("0xe44dd5e6c321181290d6cc02a7c3105e39b0c60f", ""),
array("0xde64e5e4fc629241823cb9b90a186a2a7392fc21", ""),
array("0xfbb2af34c6a9219f0bddb76c975db4537c28ff42", ""),
array("0x981a46b42d86f23eb2998ede344ff28eff6a569e", ""),
array("0x3ad4cc5272740459dbdc81ebe20265c06c47130a", ""),
array("0x325497cdd238c4717652742a291af1c396c2ec6a", ""),
array("0x70e31ed3fa31b5fb791a4af25e68e9b997f8b8ba", ""),
array("0x19d8b9e87f2a470ce053f55eadacc2897a2a5c94", ""),
array("0x53096a31613cfabc49b6e7dc34895484a9a22cfa", ""),
array("0x54a61aea116735fceabadfe0b348be2edba5c465", ""),
array("0xa2c31f9a045c4e503709b3e6ff96bf5391aa3428", ""),
array("0x94559a4fc2cc5c4d023a4fffe3422bec77bdbfff", ""),
array("0x9328fc10595dc550de54d9c7cfd0a8b5608bcb91", ""),
array("0xd785e4323ea5d2814b866cc2c514a23a44171724", ""),
array("0xcd9e44280b92d5c3a68d0037019d97d71423364f", ""),
array("0x11b0cef35874d2d4b963853eb9edb3e615e26b90", ""),
array("0xda35fdc96a7fe3dac12ee95264ff760b2a745e62", ""),
array("0x7dda3b9c2326916f85fc9e329b42db50c8c7f50a", ""),
array("0xb361aae48da57823889cf90ad4d0dab69af3a73f", ""),
array("0x6a893545cead985e0c1d5d200d877cc25e9107f0", ""),
array("0x1536b3c3569bd85a6e771cd28a4d4fcd84ebf39a", ""),
array("0x3f85a9de9f4db61fab3f0bc3267bd0d3df9eaf11", ""),
array("0x7858125e875dd4c7fe6848838a57f346e096d65e", ""),
array("0x8f3439325ee4bca3b41419757c593690b8747175", ""),
array("0xa263de4fdca7216ef9c11e35344df36649404b2c", ""),
array("0x37d5e263144c1b79819721323113bce22ea5df28", ""),
array("0xb664582db4946af5cda4c6392a85bcffa198596d", ""),
array("0xddf019638e1d5b0f2360de631e7df728be98245f", ""),
array("0x6068a18ede686270f1e6cac7489f3cc008fa4929", ""),
array("0x202e1b77f2c3263c94ff308548f7c2c4bf4d18b1", ""),
array("0xdafe4c889668a033a4da9a4d834314d140bb78d0", ""),
array("0x5c918dd4a59037590d1ca8249f2ddbaaa5fb355d", ""),
array("0xa19769ccbeb0794142345265dc75dc1a48c1752d", ""),
array("0x017c4389381405b904dd0e6169df46dfe1ad323d", ""),
array("0x781a7cfe8b43502722ed46c711e3e0670619aabe", ""),
array("0xa05a953c0b0b8c432e43c02b64378798bef4bc82", ""),
array("0xa091131ca84483d99da09ea27d77b324952dbfc9", ""),
array("0x88a5a5c585a4dd64fb4228119d76b141d1549d73", ""),
array("0xad038cb47fc2a1a0390a68bc1bd049bcc035e44e", ""),
array("0xf8df9fe8653592a0344519aacb8d78c11572ab56", ""),
array("0x968a480164014a416588cf48bbf4f31ee6d1f415", ""),
array("0xf2555b284d1e30e51f1edbc7c09c88caeab7db96", ""),
array("0xb8dd6aadf092c1f1c1bb53d515d576628adcf397", ""),
array("0x223dac800060940ddf6060b9e8ab617b50cff85c", ""),
array("0x85f896fb5a2b4fa4a5c7e060fc65099cb979c479", ""),
array("0x8e312fa199587bf0fa875689cfd661cc63ee264c", ""),
array("0xeb7a364985e9502219ba81ad00e200346b7871fa", ""),
array("0x52f6efe125947d28374880a7176e7a5c5ebe6ffc", ""),
array("0xdf7ab7efd0fb21634bbdcf2c9e5c3b35a0d9f711", ""),
array("0xd4bedb789c263321e9d21f9d08a2664cbe2f1b24", ""),
array("0xade494368ac45f8ece4d5cb3f9228f11567158b8", ""),
array("0xa600e15c45495ae5def18730dd6e89833df9cc82", ""),
array("0x26dea842527ac1205a8d3d45b205e04ccfa62b3e", ""),
array("0x54d894c5e31c74c54dd233d0daf6d1386b5bdfff", ""),
array("0x7e55e2bbdef92c7a3170dc8378c4e10cae90ed6a", ""),
array("0xcc74c198c0c20f3a2c26558e69bbd21cb365d25d", ""),
array("0x39246f5a499ccc229161e33845b051d6e9722997", ""),
array("0xdf0d3c3b53008d4eced9b9b853b8b311a14ee70c", ""),
array("0x51734636516b3efa6561552e96783a7eed1ec79d", ""),
array("0xc59f26a7cc1e9e8a3626ce823dbdc75802827ce5", ""),
array("0x81b1b32c372d6724f6af7f3dbb7fcb5fd4f79153", ""),
array("0xfd808b64f0a1bd194bd6959fc68d2c9419df9bc3", ""),
array("0x75a5f21029c1baafd64f6aaeb5054b665c5817a2", ""),
array("0x081f9e97ba6dc3454a63984e23c2430ec231f881", ""),
array("0x23d2ed4c1c2657b98b75aaffd9e29cc49be0eacc", ""),
array("0xf8cf5143c68492cc197985f7cfb9062d8831a150", "")

  );

return;
exit;


$arrlength=count($tp3list);

$toAddress ="";
$toPhone ="";
for($x=0;$x<$arrlength;$x++)
  {

	$toAddress = $tp3list[$x][0];


	$toPhone = $tp3list[$x][1];




	$adminAccountWalletAddress = $toAddress;
	$adminAccountWalletPassword = $toPhone."ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";


	// unlock account

$err1 = "1";
	$personal = $web3->personal;
	$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
		if ($err !== null) {
//			echo 'Error: ' . $err->getMessage();

//$adminAccountWalletAddress = $err;
echo "err <br>";

//			return;
		}
		if ($unlocked) {
//			echo 'New account is unlocked!' . PHP_EOL;

echo "pass <br>";

		} else {
//			echo 'New account isn\'t unlocked' . PHP_EOL;
		}


	});



  }

exit;
return;

exit;
return;	
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





array("0x0b1c19915a1f01cdd1bb4f57cf3b19eeddbd0e29", 36320),
array("0x6a56efed0793ae59d32da456386c4393ae23b1c0", 572379)






  );
	
/*
tp3 ����
*/

exit;
return;



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
	$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);

	$amountToSend = dec2hex($amountToSend);

/**/

	$contract->at($tokenPayContractAddress)->send('transfer', $toAddress, $amountToSend, [
		'from' => $fromAccount,
		'gas' => '0x186A0',   //100000
		'gasprice' =>'0x6FC23AC00'    //30000000000 // 30 gwei
		//'gas' => '0xD2F0'
	], function ($err, $result) use ($contract, $fromAccount, $toAddress,$transactionId,$amountToSendInteger) {
		 if ($err !== null) {
			throw $err;
		} 
		 if ($result) {
/*
			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";

			$_SESSION['success'] = $msg;

*/

		} 

	}); 
	



	echo $transactionId;
	echo "<br>";

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





