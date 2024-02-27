<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$mode = $_POST['mode'];
	

	switch($mode) {

		case 'login_set_dev_id':
			$email = $_POST['email'];
			$dev_id = $_POST['dev_id'];
			$pw = $_POST['pw'];
			
			$result = 'n';
			if ( !empty($dev_id) ) {
				$db = getDbInstance();
				$db->where ("admin_type", 'admin', '!=');
				$db->where ("email", $email);
				$db->where ("passwd", md5($pw));
				$row = $db->get('admin_accounts');
				if ($db->count > 0 && empty($row[0]['devId']) ) {
					$result = 'y';
				}
			}

			$subject = !empty($langArr['login_device_id_message1']) ? $langArr['login_device_id_message1'] : 'Would you like to register this machine?\r\nAfter device registration, you cannot log in from other devices.';
			echo json_encode(array('result'=>$result, 'subject'=>$subject));

			break;
		
		// 20.10.12
		case 'login_set_dev_id2':
			$email = $_POST['email'];
			$dev_id = $_POST['dev_id'];
			$dev_id2 = $_POST['dev_id2'];
			$dev_id3 = $_POST['dev_id3'];
			$pw = $_POST['pw'];
			$subject = '';
			$result = 'n';
			//if ( !empty($dev_id) ) {
			if ( !empty($dev_id) || !empty($dev_id2) || !empty($dev_id3) ) {
				$db = getDbInstance();
				$db->where ("admin_type", 'admin', '!=');
				$db->where ("email", $email);
				$db->where ("passwd", md5($pw));
				$row = $db->get('admin_accounts');
				if ($db->count > 0 ) {
					/*
					if ( empty($row[0]['device']) && empty($row[0]['devId']) ) { // 처음 등록
						$result = 'y';
						$subject = !empty($langArr['login_device_id_message1']) ? $langArr['login_device_id_message1'] : 'Would you like to register this machine?\r\nAfter device registration, you cannot log in from other devices.';
					} else if ( empty($row[0]['device']) && !empty($row[0]['devId']) ) { // 기존 devId 등록한 사람 : 시스템 업데이트
						$result = 'y';
						$subject = !empty($langArr['login_device_id_message5']) ? $langArr['login_device_id_message5'] : 'The system has been updated.';
					}
					*/
					
					// 20.11.04
					//if ( empty($row[0]['device']) ) {
					if ( empty($row[0]['devId']) || empty($row[0]['devId2']) ) {
						if ( empty($row[0]['devId']) && empty($row[0]['devId2']) ) { // 처음 등록 //  && empty($row[0]['devId3'])
							$result = 'y';
							$subject = !empty($langArr['login_device_id_message1']) ? $langArr['login_device_id_message1'] : 'Would you like to register this machine?\r\nAfter device registration, you cannot log in from other devices.';
						//} else { // 기존 devId 등록한 사람 : 시스템 업데이트
						} else if ( ( !empty($dev_id) && empty($row[0]['devId']) ) || ( !empty($dev_id2) && empty($row[0]['devId2']) ) ) {
							$result = 'y';
							$subject = !empty($langArr['login_device_id_message5']) ? $langArr['login_device_id_message5'] : 'The system has been updated.';
						}
					}
					
				}
			}

			
			echo json_encode(array('result'=>$result, 'subject'=>$subject));

			break;

		case 'coupon_payment1':


			
			// 20.11.05, YMJ
			// multi.pro.php 페이지로 옮길 소스
			// 사용자가 [사용하기] 누르면 처리하는 부분

			$coupon_id = $_POST['cid'];
			$page = $_POST['page'];
			$search = $_POST['search'];

			// eMoney 지급 시작


			//////////////////////////
			$adminId = $n_master_etoken_id;
			$adminWalletAddress = $n_master_etoken_wallet_address;

			$send_type = 'coupon';

			$db = getDbInstance();
			$db->where('id',  $coupon_id);
			$result1 = $db->getOne('coupon_result');
			if ( !empty($result1) ) {
				
				// coupon info
				$db = getDbInstance();
				$db->where('id',  $result1['coupon_id']);
				$list1 = $db->getOne('coupon_list');

				// 사용자 정보 조회
				$db = getDbInstance();
				$db->where('id',  $result1['user_id']);
				$member = $db->getOne('admin_accounts');

				if ( !empty($list1)  && !empty($member) ) {
					$user_id = $result1['user_id'];
					$user_wallet_address1 = $member['wallet_address'];
					$token = $list1['coin_type']; // ectc
					
					// $etoken_amount 계산 (사용자에게 지급할 량)
					$etoken_amount = 0;
					$etoken_amount = $list1['coin_amount'];
					//$etoken_amount = new_coupon_ex_rate($token, 'e_coin');
					//$etoken_amount = floor($etoken_amount * $list1['amount']);



					$updateArr = [];
					$db = getDbInstance();
					$db->where("id", $user_id);
					$updateArr['etoken_'.$token] = $db->inc($etoken_amount);
					if ( $member['transfer_fee_type'] != 'B' ) { // 201126
						$updateArr['transfer_fee_type'] = 'B';
					}
					$last_id1 = $db->update('admin_accounts', $updateArr);

					if ( $last_id1 ) {
						$data_to_send_logs = [];
						$data_to_send_logs['user_id'] = $user_id;
						$data_to_send_logs['wallet_address'] = $user_wallet_address1;
						$data_to_send_logs['coin_type'] = $token;
						$data_to_send_logs['points'] = $etoken_amount;
						$data_to_send_logs['in_out'] = 'in';
						$data_to_send_logs['send_type'] = $send_type;
						$data_to_send_logs['send_user_id'] = $adminId;
						$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
						$data_to_send_logs['send_fee'] = '0';
						$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
						
						$db = getDbInstance();
						$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
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
						$data_to_send_logs['send_type'] = $send_type;
						$data_to_send_logs['send_user_id'] = $user_id;
						$data_to_send_logs['send_wallet_address'] = $user_wallet_address1;
						$data_to_send_logs['send_fee'] = '0';
						$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
						
						$db = getDbInstance();
						$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
					}

					// Beepoint 
					$points = $list1['amount']*20/100;
					$newSaveArr = [];
					$newSaveArr['user_id'] = $user_id;
					$newSaveArr['user_wallet_address'] = $user_wallet_address1;
					$newSaveArr['points'] = $points;
					$newSaveArr['amount'] = $etoken_amount;
					$newSaveArr['krw'] = $list1['amount'];
					$newSaveArr['description'] = 'coupon_result : '.$coupon_id;
					$newSaveArr['created_at'] = date("Y-m-d H:i:s");

					$db = getDbInstance();
					$last_id = $db->insert('store_transactions', $newSaveArr); 		



				} // if ($list1)

			} // if
			// eMoney 지급 종료


			// coupon_result TABLE에 처리결과 저장 시작

			$updateArr = [];
			$db = getDbInstance();
			$db->where("id", $coupon_id);

			$updateArr['coin_logs_id'] = $last_id_sl;
			$updateArr['status'] = 'used';
			$updateArr['coin_amount'] = $etoken_amount;
			$updateArr['used_at'] = date("Y-m-d H:i:s");
			$updateArr['deadline_use'] = NULL;
			$last_id3 = $db->update('coupon_result', $updateArr);


			// coupon_result TABLE에 처리결과 저장 종료
			$_SESSION['success'] = 'success';
			// $_SESSION['failure'] = !empty($langArr['send_auth_need']) ? $langArr['send_auth_need'] : 'Can be used after authentication. Please use after verifying your identity in [My Info].';
			header('location: coupon_list.php?page='.$page.'&search='.urlencode($search));
			
			break;

		// 21.02.15
		case 'coin_won_change1':
			$won_price = $_POST['won'];

			$new_price = new_coin_price_change_1won('CTC', $won_price, '');
			echo json_encode(array('result'=>'y', 'price'=>$new_price));
			
			break;
			

	} // switch
}
?>