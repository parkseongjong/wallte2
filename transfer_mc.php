


<?php
//cybertronchain.com/wallet2/transfer_mc.php
	session_start();
	require_once './config/config.php';
	require('includes/web3/vendor/autoload.php');
	use Web3\Web3;
	use Web3\Contract;

	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$eth = $web3->eth;
	$personal = $web3->personal;



/*
//ȸ���ּ�
	$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
*/

	$adminAccountWalletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';
	$adminAccountWalletPassword = '+821049138089'.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';

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

	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $tokenPayAbi);



	
/*
mc ����
*/
return;
exit;


	$toAddress = "0x06978f9023a79138376b722db285da08bd068ad3";


	$toAmount = 1;


	echo $toAddress;
	echo "<br>";

	$amountToSendInteger = $toAmount;
//	$amountToSend = $amountToSendInteger*1000000;


	$amountToSend = bcmul ($amountToSendInteger, 1000000);
	echo $amountToSend.'<br />';
	$amountToSend1 = dec2hex($amountToSend);
	echo $amountToSend1.'<br />';
	
	$amountToSend = '0x';
	$amountToSend .= $amountToSend1;

	echo $amountToSend.'<br />';
;
	try {
		$contract->at($marketCoinContractAddress)->send('transfer', $toAddress, $amountToSend, [
			'from' => $fromAccount,
	/*
			'gas' => '0x186A0',   //100000
			'gasprice' =>'0x1DCD65000'    //30000000000 // 30 gwei
	*/
			'gas' => '0x186A0',   //100000
			'gasprice' =>'0x4A817C800'    //30000000000 // 30 gwei
		/*	'gasprice' =>'0x4A817C800'    //20000000000wei // 20 gwei */
			//'gas' => '0xD2F0'
		], function ($err, $result) use ($contract, $fromAccount, $toAddress,$transactionId,$amountToSendInteger) {
			 if ($err !== null) {
				throw $err;
			} 
			 if ($result) {
	//			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
	//			$_SESSION['success'] = $msg;
			} 

			echo $result;
		}); 
	} catch (Exception $e) {
		echo $e->getcode().' : '.$e->getMessage();
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







