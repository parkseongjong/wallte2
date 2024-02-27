<?php
// Page in use
require_once './config/config.php';
require_once './config/new_config.php';
require('includes/web3/vendor/autoload.php');
// https://cybertronchain.com/wallet2/transaction_cron_etoken.php
echo "Start : ".date('Y-m-d H:i:s')."\n";

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$db = getDbInstance();
$db->where("send_type", 'exchange_eToken');
$db->where("status", 'fail', '!=');
//$db->where("status", 'pending', '!=');
//$db->where("status", 'send', '!=');
$db->where("etoken_send", 'P');
$userTransactions = $db->get('user_transactions_all');

$etoken_tbl_count = 0; // 21.07.17, YMJ

if(!empty($userTransactions)){
	foreach($userTransactions as $row){
		$transcationId = $row['transactionId'];
		$ethAmount = $row['amount'];
		$recordId = $row['id'];
		$token = $row['coin_type']; // ctc, tp3, ...

		$tx_result = $wi_wallet_infos->get_txId_result($transcationId);
		
		$setting_value = 'exchange_e'.$token.'_per_'.$token;
		
		// 21.07.17, YMJ
		$db->where('user_transactions_all_id', $row['id']);
		$etoken_tbl_count = $db->getValue ("etoken_logs", "count(*)");
		
		$getExchangePrice = '';		
		//if($tx_result == "1"){
		if($tx_result == "1" && $etoken_tbl_count == 0){ // 21.07.17, YMJ
			$db = getDbInstance();
			$db->where("module_name", $setting_value);
			$getSetting = $db->get('settings');
			$getExchangePrice = $getSetting[0]['value'];

			if ( !empty($getExchangePrice) ) {
			
				$newTransactionId = '';
				$amountToSend = $ethAmount*$getExchangePrice;


				
				$db = getDbInstance();
				$db->where("id", $row['id']);
				$updateArr = [];
				$updateArr['etoken_send'] = 'Y';
				$last_id3 = $db->update('user_transactions_all', $updateArr);
				
				if ( $last_id3 ) {
				
					// token 보낸 사람에게 etoken을 보내줘야 한다
					$receiverUserId = $row['from_id'];
					$db = getDbInstance();
					$db->where("id", $receiverUserId);
					$rowm = $db->getOne('admin_accounts');
					$toUserAccount = $rowm['wallet_address'];	
					$toUserId = $rowm['id'];
				
					$db = getDbInstance();
					$db->where("id", $toUserId);
					$updateArr = [];
					$updateArr['etoken_e'.$token] = $db->inc($amountToSend);
					$last_id1 = $db->update('admin_accounts', $updateArr);

					if ( $last_id1 ) {
						$data_to_send_logs = [];
						$data_to_send_logs['user_id'] = $toUserId;
						$data_to_send_logs['wallet_address'] = $toUserAccount;
						$data_to_send_logs['coin_type'] = 'e'.$token;
						$data_to_send_logs['points'] = $amountToSend;
						$data_to_send_logs['in_out'] = 'in';
						$data_to_send_logs['send_type'] = 'to_etoken';
						$data_to_send_logs['send_user_id'] = $n_master_etoken_id;
						$data_to_send_logs['send_wallet_address'] = $n_master_etoken_wallet_address;
						$data_to_send_logs['send_fee'] = '0';
						$data_to_send_logs['user_transactions_all_id'] = $row['id'];
						$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
						
						$db = getDbInstance();
						$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
					}


					// 마스터 차감

					$db = getDbInstance();
					$db->where("id", $n_master_etoken_id);
					$updateArr = [];
					$updateArr['etoken_e'.$token] = $db->dec($amountToSend);
					$last_id2 = $db->update('admin_accounts', $updateArr);

					if ( $last_id2 ) {
						$data_to_send_logs = [];
						$data_to_send_logs['user_id'] = $n_master_etoken_id;
						$data_to_send_logs['wallet_address'] = $n_master_etoken_wallet_address;
						$data_to_send_logs['coin_type'] = 'e'.$token;
						$data_to_send_logs['points'] = '-'.$amountToSend;
						$data_to_send_logs['in_out'] = 'out';
						$data_to_send_logs['send_type'] = 'to_etoken';
						$data_to_send_logs['send_user_id'] = $toUserId;
						$data_to_send_logs['send_wallet_address'] = $toUserAccount;
						$data_to_send_logs['send_fee'] = '0';
						$data_to_send_logs['user_transactions_all_id'] = $row['id'];
						$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
						
						$db = getDbInstance();
						$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
					}

				}

				/*$db = getDbInstance();
				$db->where("id", $row['id']);
				$updateArr = [];
				$updateArr['etoken_send'] = 'Y';
				$last_id2 = $db->update('user_transactions_all', $updateArr);
				*/
				
			}

		} // if($tx_result)
		
	}
}

echo "Finish : ".date('Y-m-d H:i:s')."\n\n";

?>