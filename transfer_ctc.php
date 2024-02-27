<?php
// Test page

//cybertronchain.com/wallet2/transfer_ctc.php
	session_start();
	require_once './config/config.php';
	require('includes/web3/vendor/autoload.php');
	use Web3\Web3;
	use Web3\Contract;

	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$eth = $web3->eth;
	$personal = $web3->personal;

	require_once './config/new_config.php';
/*
	$email = '+821049138089';

        // create walletAddress if not exists start
        $personal = $web3->personal;
        if(empty($walletAddress)){
            $walletAddress = '';
            try {
                $personal->newAccount($email.$n_wallet_pass_key, function ($err, $account) use (&$walletAddress) { // $n_wallet_pass_key : config/new_config.php
                    if ($err !== null) {
                        //echo 'Error: ' . $err->getMessage();
                        throw new Exception($err->getMessage(), 1);
                    }
                    else {
                        $walletAddress = $account;
                    }
                });
            } catch (Throwable $e) {
                new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            } catch (Exception $e) {
                new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            }


        }
		echo $walletAddress;
        // create walletAddress if not exists end

*/
//exit;
/*
//회사주소
	$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";

*/
	$adminAccountWalletAddress = "0x8e4ccefa42dc4124be5b942daab9057a10203636";
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
	
	exit;
	return;
	$fromAccount = $adminAccountWalletAddress;

	$gas = '0x9088';
	$transactionId = '';



	$contract = new Contract($web3->provider, $tokenPayAbi);




//$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
echo $gasPriceInWei;
$gasPriceInWei = "0x".dechex($gasPriceInWei);


	
/*
ctc 전송
*/
return;
exit;




	$toAddress = "0x1125a7156dc34ABC463E35Bc7703B3287c41FD60";


	$toAmount = 0;


	echo $toAddress;
	echo "<br>";

	$amountToSendInteger = $toAmount;
//	$amountToSend = $amountToSendInteger*1000000000000000000;


	$amountToSend = bcmul ($amountToSendInteger, 1000000000000000000);
	$amountToSend1 = dec2hex($amountToSend);
	
	$amountToSend = '0x';
	$amountToSend .= $amountToSend1;


	$contract->at($contractAddress)->send('transfer', $toAddress, $amountToSend, [
		'from' => $fromAccount,
		'gas' => '0x186A0',   //100000
		'gasprice' =>$gasPriceInWei    //30000000000 // 30 gwei
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







