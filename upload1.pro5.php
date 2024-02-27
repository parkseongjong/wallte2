<?php
// Page in use
// sendTransaction, approve : 일괄처리(Batch processing) // ------------------ upload1.pro4와 내용동일 / 테이블만 다름 /// use1 => USED1
ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);  

require_once './config/config.php';
require_once './config/new_config.php';

/*
https://cybertronchain.com/wallet2/upload1.pro5.php?type=set
https://cybertronchain.com/wallet2/upload1.pro5.php?type=sendTransaction
https://cybertronchain.com/wallet2/upload1.pro5.php?type=approve
https://cybertronchain.com/wallet2/upload1.pro5.php?type=login
*/

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
use Web3\Utils;


$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;



$use_type = filter_input(INPUT_GET, 'use_type');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'pagelimit');
if ($pagelimit == "") {
	$pagelimit = 200;
}

if ($page == "") {
    $page = 1;
}
$filter_col = "id";
$order_by = "desc";

if ($use_type == "") {
    $use_type = "USED1";
}


$gasPriceInWei = 40000000000;
$gas = 45000;
$token_count = 3; // 5 coins
//$max_eth_amount = 0.03; // max EthAmount : 3 coins -> 0.01
// gas : 더 적게?



/*
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	if ( !empty($result) ) {
		$gasPriceInWei = $result->toString();
	}
});*/

/*
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
	//echo $decodeData['fast'].'<br />';
	$gasPriceInWei = $decodeData['fast'] * 100000000;
}

echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';
*/

$gasprice_gwei = filter_input(INPUT_GET, 'gasprice');
if ( empty($gasprice_gwei) ) {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.etherscan.io/api?module=gastracker&action=gasoracle&apikey=".$ethApiKey,
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
	if (isset($decodeData['result']['ProposeGasPrice'])) {
		//echo $decodeData['fast'].'<br />';
		$gasprice_gwei = $decodeData['result']['ProposeGasPrice'];
		$gasPriceInWei = bcmul($gasprice_gwei, 1000000000);
	}
}

$gasPriceInWei = bcmul($gasprice_gwei, 1000000000);
echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasprice_gwei). ' Gwei)<br />';


$totalAmountInWei = $gasPriceInWei*$token_count*$gas;
$totalAmountInEth = $totalAmountInWei/1000000000000000000; // 1

$gasPriceInWei = "0x".dechex($gasPriceInWei);

//$totalAmountInEth = $totalAmountInEth>$max_eth_amount ? $max_eth_amount : $totalAmountInEth;
echo 'totalAmountInEth : '.$totalAmountInEth.'<br />';

$adminAccountWalletAddress = $n_master_wallet_address;
$adminAccountWalletPassword = $n_master_wallet_pass;

//$totalAmountInEthSend = $totalAmountInEth*1000000000000000000;
$totalAmountInEthSend = bcmul($totalAmountInEth,1000000000000000000);
//echo $totalAmountInEthSend.'<br />';;


$type = $_GET['type'];


/*
// type
set : 변경완료처리
sendtransaction : sendTransaction

*/
$db = getDbInstance();
$db->where("use1", $use_type);

if ( $type == 'set' ) {
	$db->where("approved_field_set", 'N');

} else if ( $type == 'sendTransaction' ) {
	//$db->where("approved_field_set", 'Y');
	$db->where("send_transaction_set", 'N');

} else if ( $type == 'approve' ) {
	//$db->where("approved_field_set", 'Y');
	$db->where("send_transaction_set", 'Y');
	$db->where("approve_set", 'N');

} else if ( $type == 'login' ) {
	$db->where("approved_field_set", 'Y');
	//$db->where("send_transaction_set", 'Y');
	//$db->where("approve_set", 'Y');
	$db->where("login_or_not_set", 'N');
}
//$resultData = $db->get('z_user_address_list2');


if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("z_user_address_list2", $page);
$total_pages = $db->totalPages;



if ( !empty($resultData) ) {
	foreach($resultData as $k=>$row) {

		$result ='';
		echo '<br />'.$row['id'].' : <a href="https://cybertronchain.com/wallet2/admin_user_approval.php?user_id='.$row['admin_accounts_id'].'" target="_blank">'.$row['admin_accounts_id'].'</a><br />';
		//echo $row['id']. ' : '.$row['admin_accounts_id'].'<br />';
		$db = getDbInstance();
		$db->where("id", $row['admin_accounts_id']);
		$user_row = $db->getOne('admin_accounts');
		
		if ( !empty($user_row) ) {
			$user_id = $user_row['id'];
			$wallet_address = $user_row['wallet_address'];



			/*
			if (  $type == 'set' ) {
				$updateArr2 = [];

				if ( $user_row['wallet_change_apply'] != 'Y' && !empty($user_row['wallet_address_change']) && $user_row['id'] < 10900 ) {

					$updateArr2['sendapproved'] =  'N';
					$updateArr2['sendapproved_completed'] =  'N';
					$updateArr2['tp_approved'] =  'N';
					$updateArr2['tp_approved_completed'] =  'N';
					$updateArr2['usdt_approved'] =  'N';
					$updateArr2['usdt_approved_completed'] =  'N';
					$updateArr2['mc_approved'] =  'N';
					$updateArr2['mc_approved_completed'] =  'N';
					$updateArr2['krw_approved'] =  'N';
					$updateArr2['krw_approved_completed'] =  'N';

				
					$updateArr2['wallet_address'] =  $user_row['wallet_address_change'];
					$updateArr2['wallet_address_change'] =  $user_row['wallet_address'];
					$updateArr2['wallet_change_apply'] =  'Y';
					$updateArr2['transfer_approved'] =  'E';
					
					$updateArr2['pvt_key'] =  NULL;

					
					$db = getDbInstance();
					$db->where("id", $user_id);
					$last_id2 = '';
					$last_id3 = '';
					$last_id2 = $db->update('admin_accounts', $updateArr2);
					if ( !$last_id2 ) {
						$result = 'F';
					}
					
					$db = getDbInstance();
					$db->where('user_id', $user_id);
					$db->where("del", 'use');
					$row_ethsend = $db->get('ethsend');
					if ($db->count > 0) { 
						$db = getDbInstance();
						$updateArr3 = [];
						$updateArr3['del'] = 'del';
						$updateArr3['deleted_at'] = date("Y-m-d H:i:s");
						$db->where("user_id", $user_id);
						$last_id3 = $db->update('ethsend', $updateArr3);
						if ( !$last_id3 ) {
							$result = 'F';
						}
					}

					if ( $result == '' ) {
						$updateArr = [];
						$updateArr['approved_field_set'] = 'Y';
						$db->where("id", $row['id']);
						$last_id = $db->update('z_user_address_list2', $updateArr);
					}

				}

			} // if ($type=set)


			if ( $type == 'login' ) {

				$stat = '';

				if ( $user_row['login_or_not'] == 'N' ) {
					$db = getDbInstance();
					$db->where('id', $user_id);
					$updateArr = [];
					$updateArr['login_or_not'] = 'Y';
					$stat = $db->update('admin_accounts', $updateArr);
					if (!$stat) {
						$result = 'F';
					}

				}

				//if ( $result == '' ) {
				if ( $result != 'F' ) {
					$updateArr = [];
					$updateArr['login_or_not_set'] = 'Y';
					$updateArr['use1'] = 'FINISH';
					$db->where("id", $row['id']);
					$last_id = $db->update('z_user_address_list2', $updateArr);
				}


			} // if ($type=login)
			*/

			if ( $type == 'sendTransaction' ) {

				echo 'totalAmountInEth : '.$totalAmountInEth.'<br />';



				$admin_type = $user_row['admin_type'];
				$registerWith = $user_row['register_with'];
				$transfer_approved = $user_row['transfer_approved'];

				if ( $user_row['id'] >= 10900 ) {
					$walletAddress = $user_row['wallet_address'];
				} else {
					if ( $user_row['wallet_change_apply'] == 'Y' ) {
						$walletAddress = $user_row['wallet_address'];
					} else {
						$walletAddress = $user_row['wallet_address_change']; //--------- (New Wallet Address)
					}
				}
				$userDbEmail =  $user_row['email'];

				//echo 'walletAddress : '.$walletAddress.'<br />';

				if ( $admin_type != 'admin' && $registerWith != "email") { //  && $transfer_approved == 'C' // 
						
					$ownerAccount = $walletAddress;
					$ownerAccountPassword = $userDbEmail.$n_wallet_pass_key;
					$unlock = '';
					try {
						$personal = $web3->personal;
						$personal->unlockAccount($ownerAccount, $ownerAccountPassword, function ($err, $unlocked) {
							if ($err !== null) {
								echo 'sendTransaction(User) Unlock : Failed<br />';
								throw new Exception($err->getMessage(), 3);
							}
							else {
								echo 'sendTransaction(User) Unlock : Success<br />';
							}
							
						});
					} catch (Exception $e) {
						$unlock = 'failed';
						new_fn_logSave( 'Message ( ' . $user_id.', approve) : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
					}


					$eth_all_count = 0;
					$db = getDbInstance();
					$db->where ("user_id", $user_id);
					$db->where ("coin_type", 'all');
					$db->where ("ethmethod", 'sendTransaction');
					$db->where ("del", 'use');
					$ethSendRow = $db->get('ethsend');
					$eth_all_count = $db->count;
					
					echo 'sendTransaction DB Count : '.$eth_all_count.'<br />';

					if ( $eth_all_count==0 && $unlock == '') {
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
							new_fn_logSave( 'Message ('.$user_id.', Master unlock ) : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
						}
						
						
						// send transaction
						try {
							
							$eth->sendTransaction([
								'from' => $fromAccount,
								'to' => $toAccount,
								'value' => '0x'.dechex($totalAmountInEthSend),
								'gasprice' =>$gasPriceInWei
								
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
							new_fn_logSave( 'Message : (' . $user_id . ', all) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
						}
						
						if(!empty($getTxId)) {
							$db = getDbInstance();
							$data_to_store = [];
							$data_to_store['user_id'] = $user_id;
							$data_to_store['coin_type'] = 'all';
							$data_to_store['tx_id'] = $getTxId;
							$data_to_store['ethmethod'] = "sendTransaction";
							$data_to_store['amount'] = $totalAmountInEth;
							$data_to_store['to_address'] = $toAccount;
							$data_to_store['from_address'] = $fromAccount;
							$last_id = $db->insert('ethsend', $data_to_store);

							
							$updateArr = [];
							$updateArr['send_transaction_set'] = 'Y';
							$db->where("id", $row['id']);
							$last_id = $db->update('z_user_address_list2', $updateArr);

							$updateArr4 = [];
							$updateArr4['transfer_approved'] = 'C';
							$db->where("id", $user_id);
							$last_id = $db->update('admin_accounts', $updateArr4);


						}
						

					}




				}
			} // if ($type=sendTransaction)

			
			if ( $type == 'approve' ) {
					$admin_type = $user_row['admin_type'];
					$registerWith = $user_row['register_with'];
					$transfer_approved = $user_row['transfer_approved'];
					if ( $user_row['id'] >= 10900 ) {
						$walletAddress = $user_row['wallet_address'];
					} else {
						if ( $user_row['wallet_change_apply'] == 'Y' ) {
							$walletAddress = $user_row['wallet_address'];
						} else {
							$walletAddress = $user_row['wallet_address_change']; //--------- (New Wallet Address)
						}
					}
					$userDbEmail =  $user_row['email'];

					//echo 'walletAddress : '.$walletAddress.'<br />';

					if ( $admin_type != 'admin' && $registerWith != "email") { //  && $transfer_approved == 'C' // 

						$i=1;
						foreach($contractAddressArr as $tokenCode=>$singleArr) {
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
							$db->where ("user_id", $user_id);
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
									new_fn_logSave( 'Message ( ' . $user_id.', approve) : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
								}
								
								try {
									$contract->at($contractAddress)->send('approve',$senderAccount, 5000000000000000000000000000, [
										'from' => $ownerAccount,
										'gasprice' =>$gasPriceInWei
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
									new_fn_logSave( 'Message : (' . $user_id . ', ' . $coinType . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
								}
								
								if(!empty($approveTxId)) {
									$db = getDbInstance();
									$data_to_store = [];
									$data_to_store['user_id'] = $user_id;
									$data_to_store['coin_type'] = $coinType;
									$data_to_store['tx_id'] = $approveTxId;
									$data_to_store['ethmethod'] = "approve";
									$data_to_store['amount'] = 0;
									$data_to_store['to_address'] = $senderAccount;
									$data_to_store['from_address'] = $ownerAccount;
									$last_id = $db->insert('ethsend', $data_to_store);	
									
									$db = getDbInstance();
									$db->where("id", $user_id);
									$last_id = $db->update('admin_accounts', [$updateColumnName=>"Y"]);

									
									$updateArr = [];
									$updateArr['approve_set'] = 'Y';
									$db->where("id", $row['id']);
									$last_id = $db->update('z_user_address_list2', $updateArr);


								}
								

							}
							$i++;
						} // foreach
					// approve End


					} // if
				} // if ($type=approve)



		}
		
	} // foreach
} else { // if
	echo '데이터가 없습니다.';
}

?>
