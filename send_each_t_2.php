


<?php


require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://127.0.0.1:8545/');
$eth = $web3->eth;
	$personal = $web3->personal;




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


array("0x07aae6e9e024dbe9f5f1492171952187c0f133d4", "63947"),
array("0x130a592c328eea865719f3427c6c3fccfb05d947", "192000"),
array("0xe6059c94a2626400f1264232339fe3fdc34124fb", "67400"),
array("0x8025733ba6f4d953aa980724f92cb130c4f4baf0", "736260"),
array("0x10384609a8e27955a8fc75beb416999798397e98", "745974"),
array("0x94c8090a1703aa65806860564447dfdb2d26235a", "200000"),
array("0x695396302004d8d1c3525b8eb90d57d06310c022", "500860"),
array("0x6b912db06a72254c685ba7391a09a30522595e97", "582600"),
array("0xd5aa37b23f064e5d7d5447a386040577390bb16f", "300000"),
array("0xd5aa37b23f064e5d7d5447a386040577390bb16f", "209521"),
array("0xd5aa37b23f064e5d7d5447a386040577390bb16f", "24810"),
array("0xf95d3d6f70cee18d53bbbb77dbd9dee0436498a3", "41601"),
array("0xebff38523e716b29f0a4fcb21114d64b8219d0e0", "100"),
array("0xe27625bd67cda9dcbfa9910edc5e35120545c761", "260661"),
array("0x8103d87ed66a7f9207cf7ddc523b7b53a4e60993", "434609"),
array("0xcd9fa897aaeb77a4494fa37baa7318fa5ef55b6a", "62740"),
array("0x84599cffae49a3e80cae8e98edec35f638e7e19a", "35000"),
array("0x90a9d12246bee91d1b3cfc0507d59fb04010c896", "272"),
array("0x90a9d12246bee91d1b3cfc0507d59fb04010c896", "190"),
array("0xbd29589b7592ef1451d4fbb217001942fcb00624", "148426"),
array("0x4eaaf526680b4e0f33464f45e4b8fae1ab91a51e", "507000"),
array("0xc9fa5d484e7952db49f79275502e196b10f2ddbf", "172643"),
array("0xf25ee38fd243cb8ffcb722ae22c415ffaa435e24", "14400"),
array("0xaec23a0ed28b3c314ff180d71bdd7a64ef99a79d", "488700"),
array("0x19970103e5131ac62c2f3795185438dd2738a4fe", "42880"),
array("0x3df2de460bbc06f30644ca76f5881e7b50cf7715", "676500"),
array("0x3df2de460bbc06f30644ca76f5881e7b50cf7715", "22000")

  );
	
/*
tp3 전송
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
	$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);

	$amountToSend1 = dec2hex($amountToSend);
	
	$amountToSend = '0x';
	$amountToSend .= $amountToSend1;




	$contract->at($tokenPayContractAddress)->send('transfer', $toAddress, $amountToSend, [
		'from' => $fromAccount,
//		'gas' => '0x186A0',   //100000
//		'gas' => '0x7A120',   //300000
		'gas' => '0x4C4B40',   //5000000
	
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





