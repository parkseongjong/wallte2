<?php
// Test Page
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
//require_once 'includes/auth_validate.php';

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;


$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;


require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

//$gasPriceInWei = 40000000000;
$gasPriceInWei = $wi_wallet_infos->get_gas_price('fast');
echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';

$gasPriceInWei = "0x".dechex($gasPriceInWei);



// https://cybertronchain.com/wallet2/kiosk_cancel.php
/* ó�� ����

1. �ŷ��� ������ ������ Ȯ�� : wallet.kiosk_cancel_log.user_transactions_all_id => wallet.user_transactions_all���� ��ȸ => transactionId�� ������ ������ Ȯ��
2. vixber �ּҿ� �ܾ� Ȯ�� ( ȯ���� �ݾ׺��� ���� TP3 ���� �Ǵ� ETH==0 : ���� )
3. vixber �ּ� unlock
4. vixber -> user : send
5. log save

*/

$db = getDbInstance();
$db->where("cancel_status", 'Request');
$resultData = $db->get('kiosk_cancel_log');
if ( $db->count > 0 && !empty($resultData) ) {
	foreach ($resultData as $row) {

		$cancel_status = '';
		$transactionId = '';
		$msg = '';


		$amountToSend = $row['amount'];
		$toAccount = $row['to_address']; // user
		$fromAccount = $row['from_address']; // vixber
		$token = $row['coin_type'];

		$tokenArr = $contractAddressArr[$token];
		$tokenAbi = $tokenArr['abi'];
		$tokenContractAddress = $tokenArr['contractAddress'];
		$decimalDigit = $tokenArr['decimal'];
		$functionName = "balanceOf";

		echo '<br />Amount : '.$amountToSend.'<br />';
		echo 'fromAccount(vixber) : '.$fromAccount.'<br />';
		echo 'toAccount(user) : '.$toAccount.'<br />';
		echo 'token : '.$token.'<br />';
		

		// 1. �ŷ��� ������ ������ Ȯ��
		// user_transactions_all transaction�� ������ ������ Ȯ���ؾ� �Ѵ�
		$status = '';
		if ( !empty($row['user_transactions_all_id']) ) {
			$db = getDbInstance();
			$db->where("id", $row['user_transactions_all_id']);
			$logData = $db->getOne('user_transactions_all');

			if ( !empty($logData['transactionId']) ) {
				$status = $wi_wallet_infos->wi_get_status($logData['transactionId']);
				
				if ( $status == 'Failed' ) {
					$cancel_status = 'Cancel';
					$msg = 'Failed payment transaction';
				}
			} else {
				$cancel_status = 'Cancel';
				$msg = 'Failed payment transaction';
			}

		} else { // error
			$cancel_status = 'Cancel';
			$msg = 'Unable to check payment transaction details';
		}
		echo 'status : '.$status.'<br />';

		if ( $msg == '' ) {
			if ( $status == 'Completed' ) { // ������ �Ǹ� ����

				// 2. vixber �ּҿ� �ܾ� Ȯ��
				// From get balance
				$getEthbalance = 0;
				$getEthbalance = $wi_wallet_infos->wi_get_balance('2', 'eth', $fromAccount, $contractAddressArr);
				
				$getCoinBalance = 0;
				$getCoinBalance = $wi_wallet_infos->wi_get_balance('2', $token, $fromAccount, $contractAddressArr);
				
				echo 'ETH Balance : '.$getEthbalance.'<br />';
				echo $token.' Balance : '.$getCoinBalance.'<br />';

				// �ܾ׺��� - ����
				if ( $getCoinBalance < $amountToSend || $getEthbalance == 0) {
					$msg = 'Insufficient balance';

				} else {

				
					// 3. vixber �ּ� unlock
					// Get User 'Email' for from address unlock
					$db = getDbInstance();
					$db->where("wallet_address", $fromAccount);
					$userData = $db->getOne('admin_accounts');
					$fromAccountPass = '';
					if ( !empty($userData['id']) && !empty($userData['email']) && stristr($userData['name'], 'vixber') == true ) {
						$fromAccountPass = $userData['email'].$n_wallet_pass_key;
						
						// From account unlock
						$personal = $web3->personal;
							try {
							$personal->unlockAccount($fromAccount, $fromAccountPass, function ($err, $unlocked) {
								if ($err !== null) {
									throw new Exception($err->getMessage(), 1);
								}
								if ($unlocked) {
									echo 'From Address Unlock : Success';
								} else {
									$msg = 'Failed unlock';
									echo 'From Address Unlock : Failed';
								}
							});
						} catch (Exception $e) {
							$msg = 'Failed unlock';
							echo 'From Address Unlock : Failed';
						}
						

						// amount
						$amountToSend = $amountToSend*$decimalDigit;
						
						$amountToSend = dec2hex($amountToSend);
						$amountToSend = '0x'.$amountToSend; // Must add 0x
						//$gas = '0x9088';
						
						
						/*
						// send
						if ( $msg == '' ) {
							try {
								$otherTokenContract = new Contract($web3->provider, $tokenAbi);
								$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
									'from' => $fromAccount,
									'gas' => '0x186A0',   //100000
									'gasprice'=>$gasPriceInWei
								], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId) {
									if ($err !== null) {
										throw new Exception($err->getMessage(), 2);
									} 
									if ($result) {
										$transactionId = $result;
									}
								});
							} catch (Exception $e) {
								$msg = 'Failed send';
							}
						} // if ($msg)

						if ( !empty($transactionId) ) {
							$cancel_status = 'Completion';

						} else {
							$msg = 'Failed send';
						}
						*/

					} else {
						$msg = 'User information inquiry failed';
					} // if ($userData)

				} // if (�ܾ�Ȯ��)

			} else {
				$msg = 'Pending payment transaction';
			} // $status
		} // $msg
		
		echo '<br />transactionId : '.$transactionId.'<br />';
		echo 'status : '.$cancel_status.'<br />';
		echo 'msg : '.$msg.'<br />';
		
		if ( !empty($cancel_status) || !empty($msg) ) {
			$db = getDbInstance();
			$db->where("id", $row['id']);
			$updateArr = [];
			if ( !empty($transactionId) ) {
				$updateArr['transactionId'] = $transactionId;
			}
			$updateArr['cancel_status'] = $cancel_status;
			$updateArr['cancel_completion_at'] = date("Y-m-d H:i:s");
			if ( !empty($msg) ) {
				$updateArr['msg'] = $msg;
			}
			$db = getDbInstance();
			$last_id = $db->update('kiosk_cancel_log', $updateArr);
		}
		


	} // foreach
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
