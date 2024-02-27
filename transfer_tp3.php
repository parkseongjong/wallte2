<?php
// Test page

//cybertronchain.com/wallet2/transfer_tp3.php
	session_start();
	require_once './config/config.php';
	require_once './config/new_config.php';
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


	$adminAccountWalletAddress = '0x06978f9023a79138376b722db285da08bd068ad3';
	$adminAccountWalletPassword = '+821049138089'.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
*/

	$adminAccountWalletAddress = "0xf4a587c23316691f8798cf08e3b541551ec1ffcb";
	$adminAccountWalletPassword = "+821049138089ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";


	// unlock account

	$personal = $web3->personal;
	$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ($unlocked) {
			echo 'New account is unlocked!' . PHP_EOL;
		} else {
			echo 'New account isn\'t unlocked' . PHP_EOL;
		}
	});
	
	
	$fromAccount = $adminAccountWalletAddress;

	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $tokenPayAbi);



	
/*
tp3 ����
*/
return;
exit;



//$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
echo $gasPriceInWei;
$gasPriceInWei = "0x".dechex($gasPriceInWei);






	$toAddress = "0xeefd4e236dfac8f3e4f76890600ac41cb2eb6286";


	$toAmount = 50;


	echo $toAddress;
	echo "<br>";

	$amountToSendInteger = $toAmount;
	$amountToSend = $amountToSendInteger*1000000000000000000;


	//$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);
	echo '1 : '.$amountToSend1.'<br />';
	$amountToSend1 = dec2hex($amountToSend);
	echo '1 : '.$amountToSend1.'<br />';
	
	$amountToSend = '0x';
	$amountToSend .= $amountToSend1;

return;
exit;
	try {



/*



			$amountToSend = $amountToSend*$decimalDigit;

		$amountToSend = dec2hex($amountToSend);
		$amountToSend = '0x'.$amountToSend; // Must add 0x
		$gas = '0x9088';
		$transactionId = '';
		
		try {
			$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
				'from' => $fromAccount,
				'gas' => '0x186A0',   //100000
				'gasprice'=>$gasPriceInWei
				//'gasprice' =>'0x4A817C800'    //20000000000wei // 20 gwei 
                //'gasprice' =>'0x826299e00'    //35000000000wei // 35 gwei 
			], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId, &$langArr) {
				if ($err !== null) {
					throw new Exception($err->getMessage(), 5);
					//throw $err;
				} 
				if ($result) {
					$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					//$_SESSION['success'] = $msg;
				}
				$transactionId = $result;
				
			});

*/





			//$senderAccount = $n_master_wallet_address;
		//$contract->at($tokenPayContractAddress)->send('transferFrom', $fromAccount, $toAddress, $amountToSend, [
		//	'from' => $senderAccount,

		
		$contract->at($tokenPayContractAddress)->send('transfer', $toAddress, $amountToSend, [
			'from' => $fromAccount,
	
			//'gas' => '0x186A0',   //100000
			//'gasprice' =>'0x1DCD65000'    //30000000000 // 30 gwei

			'gas' => '0x9088',   //100000
			'gasprice' =>$gasPriceInWei    //30000000000 // 30 gwei
			//'gas' => '0xD2F0'
		], function ($err, $result) use ($contract, $fromAccount, $toAddress,$transactionId,$amountToSendInteger) {
			 if ($err !== null) {
				throw $err;
				echo 'Error!';
			} 
			 if ($result) {
	//			$msg = $langArr['transaction_has_made'].":) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
	//			$_SESSION['success'] = $msg;
			} 

			echo $result;
		}); 
		


		/*
		$contract->at($tokenPayContractAddress)->send('transferFrom',$fromAccount, $toAddress, $amountToSend, [
			'from' => $fromAccount,
			// 'gas' => '0x186A0',   //100000
			//'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei 
			//'gas' => '0x186A0',   //100000
			//'gasprice' =>'0x6FC23AC00'    //30000000000 // 9 gwei
		], function ($err, $result) use ($contract, $fromAccount, $toAddress, &$transactionId) {
			if ($err !== null) {
				//throw new Exception($err->getMessage(), 7);
				//print_r($err); die;
				//$transactionId = '';
			}
			else {
				$transactionId = $result;
			}
		});
		echo $transactionId;
		*/




	} catch (Exception $e) {
		echo 'fail';
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







