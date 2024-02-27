<?php
// Page in use
// GET : userId, return_page(admin_change_address_users), search, page, wallet_change_apply1
// https://cybertronchain.com/wallet2/admin_user_approval_apply.php?user_id=5137

session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);


$search_string = filter_input(INPUT_GET, 'search_string');
$page = filter_input(INPUT_GET, 'page');
$return_page = filter_input(INPUT_GET, 'return_page');
$wallet_change_apply1 = filter_input(INPUT_GET, 'wallet_change_apply1');

$return_page = !empty($return_page) ? $return_page : 'admin_change_address_users';
$page = !empty($page) ? $page : '1';
$search_string = !empty($search_string) ? $search_string : '';
$wallet_change_apply1 = !empty($wallet_change_apply1) ? $wallet_change_apply1 : 'W';

$userId = filter_input(INPUT_GET, 'user_id');
if(empty($userId)){
	header("Location:".$return_page.".php?search_string=".$search_string."&page=".$page."&wallet_change_apply1=".$wallet_change_apply1);
	exit();
}


require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
use Web3\Utils;


$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;

//$gasPriceInWei = 40000000000;
$gas = 45000;
$token_count = 3; // 5 coins
$max_eth_amount = 0.02; // max EthAmount : 3 coins -> 0.01
// gas : 더 적게?

/*
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	if ( !empty($result) ) {
		$gasPriceInWei = $result->toString();
	}
});
//echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';

$totalAmountInWei = $gasPriceInWei*$token_count*$gas;
$totalAmountInEth = $totalAmountInWei/1000000000000000000; // 1

$gasPriceInWei = "0x".dechex($gasPriceInWei);
*/












$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://ethgasstation.info/api/ethgasAPI.json?api-key=".$ethApiKey,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
	"cache-control: no-cache",
	"postman-token: bf5e409c-28bf-4abb-2670-d47bdf8f690e"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

$decodeData = json_decode($response,true);
//print_r($decodeData);
if (isset($decodeData['fast'])) {
	echo $decodeData['fast'].'<br />';
	$gasPriceInWei = $decodeData['fast'] * 100000000;
}

echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';


$totalAmountInWei = $gasPriceInWei*$token_count*$gas;
$totalAmountInEth = $totalAmountInWei/1000000000000000000; // 1

$gasPriceInWei = "0x".dechex($gasPriceInWei);





$totalAmountInEth = $totalAmountInEth>$max_eth_amount ? $max_eth_amount : $totalAmountInEth;
echo 'totalAmountInEth : '.$totalAmountInEth.'<br />';

$adminAccountWalletAddress = $n_master_wallet_address;
$adminAccountWalletPassword = $n_master_wallet_pass;

$walletLogger->info('관리자 모드 > Approve2 > 회원정보 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

$db = getDbInstance();
$db->where ("id", $userId);
$user_row = $db->getOne('admin_accounts');

if ( !empty($user_row['id']) ) {
	$admin_type = $user_row['admin_type'];
	$registerWith = $user_row['register_with'];
	$transfer_approved = $user_row['transfer_approved'];
	if ( $user_row['wallet_change_apply'] == 'Y' ||  $user_row['id'] >= 10900 ) {
		$walletAddress = $user_row['wallet_address'];
	} else {
		$walletAddress = $user_row['wallet_address_change']; //--------- (New Wallet Address)
	}
	$userDbEmail =  $user_row['email'];

	echo 'walletAddress : '.$walletAddress.'<br />';

	if ( $transfer_approved != 'C' ) {
		$db = getDbInstance();
		$db->where('id', $user_row['id']);
		$updateArr = [];
		$updateArr['transfer_approved'] = 'C';
		$updateArr['transfer_approved_appdate'] = date("Y-m-d H:i:s");
		$stat = $db->update('admin_accounts', $updateArr);

		$walletLogger->info('관리자 모드 > Approve2 > 수수료타입 변경',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
	}

	if ( $admin_type != 'admin' && $registerWith != "email") { //  && $transfer_approved == 'C'
			
		
		$eth_all_count = 0;
		$db = getDbInstance();
		$db->where ("user_id", $userId);
		$db->where ("coin_type", 'all');
		$db->where ("ethmethod", 'sendTransaction');
		$db->where ("del", 'use');
		$ethSendRow = $db->get('ethsend');
		$eth_all_count = $db->count;
		
		echo 'sendTransaction DB Count : '.$eth_all_count.'<br />';

		if ( $eth_all_count==0 ) {
			$getTxId = '';
			$fromAccount = $adminAccountWalletAddress;
			$fromAccountPassword = $adminAccountWalletPassword;
			$toAccount = $walletAddress;
			
			// unlock account
			try {
				$personal = $web3->personal;
				$personal->unlockAccount($fromAccount, $fromAccountPassword, function ($err, $unlocked) {
					if ($err !== null) {
						echo 'sendTransaction(Master) Unlock : Failed<br />';
						throw new Exception($err->getMessage(), 1);
					}
					else {
						echo 'sendTransaction(Master) Unlock : Success<br />';
					}
				});
			
			} catch (Exception $e) {
				new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}
			
			
			// send transaction
			try {
				
				//$totalAmountInEthSend = $totalAmountInEth*1000000000000000000;
				$totalAmountInEthSend = bcmul($totalAmountInEth, 1000000000000000000);
				$eth->sendTransaction([
					'from' => $fromAccount,
					'to' => $toAccount,
					//'value' => '0x5543DF729C000',
					'value' => '0x'.dechex($totalAmountInEthSend),
					// 'gas' => '0x186A0',   //100000
					'gasprice' =>$gasPriceInWei   //30000000000wei // 9 gwei 
					
				], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
					if ($err !== null) {
						echo 'sendTransaction(Master -&gt; User) Send ETH : Failed<br />';
						throw new Exception($err->getMessage(), 2);
					}
					else {
						echo 'sendTransaction(Master -&gt; User) Send ETH : Success<br />';
						$getTxId = $transaction;
					}

				});
			} catch (Exception $e) {
				new_fn_logSave( 'Message : (' . $userId . ', all) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}
			
			if(!empty($getTxId)) {
				$db = getDbInstance();
				$data_to_store = [];
				$data_to_store['user_id'] = $userId;
				$data_to_store['coin_type'] = 'all';
				$data_to_store['tx_id'] = $getTxId;
				$data_to_store['ethmethod'] = "sendTransaction";
				$data_to_store['amount'] = $totalAmountInEth;
				$data_to_store['to_address'] = $toAccount;
				$data_to_store['from_address'] = $fromAccount;
				$last_id = $db->insert('ethsend', $data_to_store);

				$walletLogger->info('관리자 모드 > Approve2 > SendTransaction',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);

			}
			

		}


		else {

			
			$i=1;
			foreach($contractAddressArr as $tokenCode=>$singleArr){
				// if($i>1){
				//	continue;
				//}
				if(empty($singleArr['contractAddress'])){
					continue;
				}	
				if(in_array($tokenCode,['krw','usdt'] ) ) {
					continue;
				}	
				$coinType = ($tokenCode=='tp3') ? 'tp' : strtolower($tokenCode);
				$updateColumnName = ($coinType=='ctc') ? 'sendapproved' : $coinType."_approved";
				$db = getDbInstance();
				$db->where ("user_id", $userId);
				$db->where ("coin_type", $coinType);
				$db->where ("ethmethod", 'approve');
				$db->where ("del", 'use');
				$ethSendRow = $db->get('ethsend'); 

				echo 'Approve ( '.$tokenCode.' ) DB Count: '.$db->count.'<br />';
				
				if($db->count==0){
					
					$contractAddress = $singleArr['contractAddress'];
					$testAbi = $singleArr['abi'];
					$approveTxId = '';
					$contract = new Contract($web3->provider, $testAbi);
					$senderAccount = $adminAccountWalletAddress;
					$ownerAccount = $walletAddress;
					$ownerAccountPassword = $userDbEmail.$n_wallet_pass_key;
					
					try {
						$personal = $web3->personal;
						$personal->unlockAccount($ownerAccount, $ownerAccountPassword, function ($err, $unlocked) {
							if ($err !== null) {
								echo 'Approve Unlock : Failed<br />';
								throw new Exception($err->getMessage(), 3);
							}
							else {
								echo 'Approve Unlock : Success<br />';
							}
							
						});
					} catch (Exception $e) {
						new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
					}
					
					
					try {
						$contract->at($contractAddress)->send('approve',$senderAccount, 5000000000000000000000000000, [
							'from' => $ownerAccount,
							'gasprice' =>$gasPriceInWei 
							//'gas' => '0x7530',   //30000
							//'gas' => '0x186A0',   //100000
							//'gas' => '0xEA60',   //60000
							
							//'gas' => '0x'.dechex(50000),
							//'gasprice' =>'0x'.dechex(20000000000) 
							
							//'gas' => '0x55F0',   //21000
							//'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
							//'gasprice' =>'0x2CB417800'    //12000000000wei // 12 gwei
							//'gasprice' =>'0xEE6B2800'    //4000000000wei // 4 gwei
							//'gasprice' =>'0x2540BE400'    //10000000000wei // 10 gwei 
						], function ($err, $result) use ($contract, $senderAccount, &$approveTxId) {
							if ($err !== null) {
								echo 'Approve Send : Failed<br />';
								throw new Exception($err->getMessage(), 4);
							}
							else {
								echo 'Approve Send : Success<br />';
								$approveTxId = $result;
							}
							
						}); 
					} catch (Exception $e) {
						new_fn_logSave( 'Message : (' . $userId . ', ' . $coinType . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
					}
					
					if(!empty($approveTxId)) {		
						$db = getDbInstance();
						$data_to_store = [];
						$data_to_store['user_id'] = $userId;
						$data_to_store['coin_type'] = $coinType;
						$data_to_store['tx_id'] = $approveTxId;
						$data_to_store['ethmethod'] = "approve";
						$data_to_store['amount'] = 0;
						$data_to_store['to_address'] = $senderAccount;
						$data_to_store['from_address'] = $ownerAccount;
						$last_id = $db->insert('ethsend', $data_to_store);	
						
						$db = getDbInstance();
						$db->where("id", $userId);
						$last_id = $db->update('admin_accounts', [$updateColumnName=>"Y"]);

						$walletLogger->info('관리자 모드 > Approve2 > approve / '.$coinType,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);

					}
					

				}
				$i++;
			} // foreach
		// approve End


		} // if
	

	} else { // if
		echo 'admin 계정 또는 email 가입자는 처리할 수 없습니다.';
	}

} // if ($user_row)


//header("Location:".$return_page.".php?search_string=".$search_string."&page=".$page."&wallet_change_apply1=".$wallet_change_apply1);


//include_once 'includes/footer.php'; 
?>