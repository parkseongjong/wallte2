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
	$toAccount = '';

	
		
/*
이더리움 전송
*/







$tp3list = array
  (




array("0x1d815d7b54ecd8e5d43e2692cf9d6ee311bbb3f6","1") 


/*
array("0x6607bef3f5c9c0815036aab37e33c4f3f8567cbf","10000"), 
array("0xbc96c26c8646f447f2a3b4a0e5ae04821f0d7487","10000"), 
array("0x20506d32e50079f74c1881b342e66399ed1c2821","10000"), 
array("0x10a0cb406b7a2efab33a1eb5c73a83fe89161174","10000"), 
array("0x16cb9400b850ca613c796bfbb5ad83ca5f26d541","10000"), 
array("0x604d8093618940d2e4544d709dc87387c390ecf3","10000"), 
array("0xdc8712f4710da55d850e75333e499ec2954511e4","10000"), 
array("0x812e6eddf6360f3a12d1b604e0eac5f1c5dff3d0","10000"), 
array("0x2fc7769ef716f2a5e1bb37c65bfc3eec7b64de30","10000"), 
array("0x076bf33c6a8fe941574652c352d4f30cb7463e77","10000"), 
array("0x195edac70c77c0cb8bc217f463c0ace25ee624cb","10000"), 
array("0xf388ed3213fbce801d50eda3fc1f20562478d6e3","10000"), 
array("0xbd9ace2c87aa4f299a855bbcddc68068f8f239bb","10000"), 
array("0x785381f21c479d015e52d8b0a0edd2f829d19f0a","10000"), 
array("0x0e6de2dca9c3f62cc1da275c9d97d6dab38f4746","10000"),
array("0xbe06c3ae2e05b89ae505dc065fbbd40165cf6c71","10000"), 
array("0x8c178147fbc4d24051bcef037839b5b62e574500","10000"), 
array("0xbfda43d43db386d8a63b70798099d770ca1be928","10000"), 
array("0xbb79e4892391a6a3a587ec1e6aac96ce219114e4","10000"), 
array("0x1b61f71239a6a45c85ec538a77cf0a77850d08c5","10000"), 
array("0xa402ada472e13515338101f532b50eb09c745144","10000"), 
array("0x05faadd3229ca7bc1900104660d9d821e6259919","10000"), 
array("0x0d384828e2baa10807e27930076e373f88652e20","10000"), 
array("0xcd71b48003573782b569453f4de56152160a55cb","10000"), 
array("0x4207a1d716809b6efc9520af280f542309067619","10000"), 
array("0x6fd8cb21c28a90fa2903a9fb5633f0df875db481","10000"),
array("0xb2f377943d90c9ad6e37531e53cfd63f953402ea","10000"), 
array("0x2f9b4eb31a787fa6d40c55b1fd3b6b701e45cc38","10000"), 
array("0x053fc980269a7b0e2c88e96438f5cd3371986bf6","10000"), 
array("0x088cec09b5ae450fc4db361eb4e9e5ef684d6fb6","10000"), 
array("0xd8b9e7616792068d3afc22c3191eb09281c815bc","10000"), 
array("0xad56392f1b2a6e13a196019a74cef3efb7d3a245","10000"), 
array("0x686345d74b273c01d0d4458008bb9e691cf361fd","10000"), 
array("0x8edbf020fad877f9765eda4f05e520866fa6d404","10000"), 
array("0x08e73a6bebefeb2560c1ec436f88c842d3169d0a","10000"), 
array("0xaecab74f98e2c61c74ce8d044e8fa4e11e0da691","10000"), 
array("0xfe01a17e8645735db6d1e76f082df7ff7dba1c50","10000"), 
array("0x8a81297f7efb99209b91c703aa0acc358d529a66","10000"), 
array("0x064a063ce465e1f281226f71a812768dcfd7924e","10000"), 
array("0x34f6b94f32767d47bef3eba793184e37f4223df4","10000"), 
array("0xcf9f18720c425d088e31a24edcdc0d66e633b4ad","10000"), 
array("0xf4bf5fbebcd1a3b04e17b9503167b4f0acf6c0d4","10000"), 
array("0xc47fdd7abbfe940d015561ff9c8e006170ae0437","10000"), 
array("0x20ab3214d02b50ec56f4b55f6a22fc040394149b","10000"), 
array("0x7537df460256c086f16548d7fe27117331493811","10000"), 
array("0x6678409b468bf1dfe677386898d57428813b697b","10000"), 
array("0xa9594021e8f35365d28522853208aad235391b9f","10000"), 
array("0x00d90fecb9ff40dfef376d9bc3975b1eee3bf84d","10000"), 
array("0x828864057050f6367e53d120bb72a592d1ad50d7","10000"), 
array("0x6a893545cead985e0c1d5d200d877cc25e9107f0","10000"), 
array("0x9119b50e0d591c22e12add80a3065edffd959a0b","10000"), 
array("0xb1d18d3c638bebd5cf6928982f308c7bee2c671b","10000"), 
array("0xd2c03c0306980861608c90ac1801a44bbb4d6395","10000"), 
array("0x8d998b3460103589579b5a44cac7b6f622272a67","10000"), 
array("0x35db650eb39cd38358e007fd6ed06e3c7e292603","10000"), 
array("0x77227595345fecfac8ccf3cd073b75f9bf3a0754","10000"), 
array("0xc948f64cbaa4ed5e606bf976a8e7923297fed259","10000"), 
array("0xd06719eea34f5d75f99b63a5be45d3df64e3780b","10000"), 
array("0x9fb1b25250b50ba5175dd0a8964abc78d0099109","10000"), 
array("0x2d12a0860778aee19a5fb065a7efbd6d89025f66","10000"), 
array("0x0bd36bdf2a230b0bb06a9687995c8b9250fa2bb6","10000"), 
array("0xbca1a1b06918e4b8bd2922ea6c505158ff10876d","10000"), 
array("0xdee1e83ea0ee8624b0884c748bd498c14ad30270","10000"), 
array("0x88ad80c7c9b4bafd4940eb8ef320e8690ff1fb7a","10000"), 
array("0x3bda1018e597ef979b388be39ce24985765d8048","10000"), 
array("0x5214b3705f7f22fc5f38741dd937656676a03f99","10000"), 
array("0x6b13d36894e3d49eeddf480859feab888809662d","10000"), 
array("0x4996eac3b20ba610ec3d4b9a7a7ab3e3177a6bf2","10000") 
*/

  );
	
//이더 전송 
return;
exit;

$arrlength=count($tp3list);

for($x=0;$x<$arrlength;$x++)
  {


	$toAccount = $tp3list[$x][0];

	
	// send transaction
	$eth->sendTransaction([
		'from' => $fromAccount,
		'to' => $toAccount,
		//'value' => '0x27CA57357C000'
		'value' => '0AA87BEE538000',
		'gas' => '0x186A0',   //100000
		'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
		

	], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			//die;
		}
		else {
			$getTxId = $transaction;
			echo $transaction;
		}

	});

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