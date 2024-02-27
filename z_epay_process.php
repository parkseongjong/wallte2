<?php
/// TMP Page
// TABLE : z_user_epay_process
// e-Pay 일괄 지급 처리를 위한 페이지

// https://cybertronchain.com/wallet2/z_epay_process.php

require_once './config/config.php';
require_once './config/new_config.php';


$db = getDbInstance();
$db->where('epay_send', 'N');
$resultData = $db->get('z_user_epay_process');

$adminId = $n_master_etoken_id;
$adminWalletAddress = $n_master_etoken_wallet_address;

if ( $db->count > 0 ) {
	foreach ($resultData as $row ) {
		
		$db = getDbInstance();
		$db->where('id', $row['user_id']);
		$db->where('wallet_address', $row['wallet_address']);
		$userData = $db->getOne('admin_accounts');
		if ( !empty($userData) ) {
			$user_id = $userData['id'];
			$user_wallet_address1 = $userData['wallet_address'];
			$token = $row['coin_type'];
			$etoken_amount = $row['amount'];

			
			$db = getDbInstance();
			$db->where("id", $user_id);
			$updateArr = [];
			$updateArr['etoken_'.$token] = $db->inc($etoken_amount);
			$last_id1 = $db->update('admin_accounts', $updateArr);
			if ( $last_id1 ) {
				$data_to_send_logs = [];
				$data_to_send_logs['user_id'] = $user_id;
				$data_to_send_logs['wallet_address'] = $user_wallet_address1;
				$data_to_send_logs['coin_type'] = $token;
				$data_to_send_logs['points'] = $etoken_amount;
				$data_to_send_logs['in_out'] = 'in';
				$data_to_send_logs['send_type'] = 'from_admin';
				$data_to_send_logs['send_user_id'] = $adminId;
				$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
				$data_to_send_logs['send_fee'] = '0';
				$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
				
				$db = getDbInstance();
				$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
				
				if ( $last_id_sl ) {
					$db = getDbInstance();
					$db->where("id", $row['id']);
					$updateArr = [];
					$updateArr['epay_send'] = 'Y';
					$updateArr['etoken_logs_id'] = $last_id_sl;
					$last_id1 = $db->update('z_user_epay_process', $updateArr);
				}

			}

			$db = getDbInstance();
			$db->where("id", $adminId);
			$updateArr = [];
			$updateArr['etoken_'.$token] = $db->dec($etoken_amount);
			$last_id2 = $db->update('admin_accounts', $updateArr);
			if ( $last_id2 ) {
				$data_to_send_logs = [];
				$data_to_send_logs['user_id'] = $adminId;
				$data_to_send_logs['wallet_address'] = $adminWalletAddress;
				$data_to_send_logs['coin_type'] = $token;
				$data_to_send_logs['points'] = '-'.$etoken_amount;
				$data_to_send_logs['in_out'] = 'out';
				$data_to_send_logs['send_type'] = 'from_admin';
				$data_to_send_logs['send_user_id'] = $user_id;
				$data_to_send_logs['send_wallet_address'] = $user_wallet_address1;
				$data_to_send_logs['send_fee'] = '0';
				$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
				
				$db = getDbInstance();
				$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
			}
			




		} // if










	} // foreach
} // if
