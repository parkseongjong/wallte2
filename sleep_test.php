<?php 

die;
// Page in use
// eToken -> Token
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
die;
use wallet\common\Log as walletLog;
use wallet\common\Info as walletInfo;
use wallet\common\InfoWeb3 as walletInfoWeb3;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';
die;
$filter = walletFilter::getInstance();
die;
//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'amount' => 'string',
    'real_token_amount' => 'string',
);
die;

$filterData = $filter->postDataFilter($_POST,$targetPostData);
$filterDataGet = $filter->postDataFilter($_GET,['token'=>'string']);
unset($targetPostData);

//use Web3\Web3;
//use Web3\Contract;
die;
//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('e-pay -> token 변환 조회',['target_id'=>0,'action'=>'S']);

//require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new walletInfo();

$web3Instance = new walletInfoWeb3();
//$web3outter = $web3Instance->outterInit();
//$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
    //$web3 = $web3Instance->innerTempInit();
$web3 = $web3Instance->innerInit();
$eth = $web3->eth;
die;
$gasPriceInWei = 4000000000000;
//$web3outter->eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	if ($err !== null) {
		print_r($err->getMessage()); die;
		//throw new Exception($err->getMessage(), 3);
	}
	$gasPriceInWei = $result->toString();
});

die;
$gasPriceInWei = "0x".dechex($gasPriceInWei);




$adminAddress = $n_master_etp3_re_wallet_address;
$adminPass = $n_master_etp3_re_pass;
$adminId = $n_master_etp3_re_id;
$new_token = 'tp3';
$module_name = 'exchange_etp3_per_tp3';


$fromAccount = $adminAddress;
$new_token = 'tp3';
$tokenArr = $contractAddressArr[$new_token];
$tokenAbi = $tokenArr['abi'];
$tokenContractAddress = $tokenArr['contractAddress'];
$decimalDigit = $tokenArr['decimal'];

$toAccount = "0xD75663b674a025E9Acd422c7260f809E921C28cc";
$amount = 100000;
$nonce = 1550;


	//$amountToSend = $token_amount*$decimalDigit;
$amountToSend = bcmul($amount, $decimalDigit); // 201112

$amountToSend = dec2hex($amountToSend);
$amountToSend = '0x'.$amountToSend; // Must add 0x



			$personal = $web3->personal;
			
			$personal->unlockAccount($adminAddress, $adminPass, function ($err, $unlocked) {
				if ($err !== null) {
                    print_r($err->getMessage()); die;
					//throw new Exception($err->getMessage(), 3);
				}
			});

		
		

		$transactionId = '';
	
			
				//$otherTokenContract = new Contract($web3->provider, $tokenAbi);
				$otherTokenContract = $web3Instance->innerContract($web3->provider, $tokenAbi);
				$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
					'from' => $fromAccount,
					'gas' => '0x186A0',   //100000
					'gasprice'=>$gasPriceInWei,
                    'nonce'=>'0x'.dechex($nonce),
				], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId) {
					if ($err !== null) {
						print_r($err->getMessage()); die;
					}
					echo $transactionId = $result;
				});
			




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


die;

