<?php

// $userId : admin_accounts.id
// $coin_type : 체크할 coin
// $send_coin_type : 발송할 coin
// TP3 발송인데, CTC approve 체크할 경우 : $coin_type='ctc', $send_coin_type='tp3'

function npro_send_approve_check($userId, $coin_type, $send_coin_type) {
	$db = getDbInstance();
	$db->where ("id", $userId);
	$userData = $db->getOne('admin_accounts');
	
	$err_code = '200';

	if ( !empty($userData) ) {

		if ( $coin_type == 'ctc' ) {
			$columnShortName = $coin_type;
			$columnName = 'sendapproved';

			$err_code_tmp_a = '621';
			$err_code_tmp_ac = '622';
			$err_log_msg = 'ctc_not_approved';
			$err_log_msg2 = 'ctc_approve_result_not_success';

		} else {
			$columnShortName = ($coin_type=='tp3') ? 'tp' : $coin_type; 
			$columnName = ($coin_type=='tp3') ? 'tp_approved' : $coin_type.'_approved'; 

			$err_code_tmp_a = '623';
			$err_code_tmp_ac = '624';
			$err_log_msg = 'token_not_approved';
			$err_log_msg2 = 'token_approve_result_not_success';

		}
		$columnNameCompleted = $columnName."_completed"; 
		$approved = $userData[$columnName];
		$approved_completed = $userData[$columnNameCompleted];
		
		
		if ( $approved == 'N' ) {
			$last_id_dts = new_set_send_err_log ('send', $send_coin_type, $userId, '', 'permission', $err_log_msg);
			$err_code = $err_code_tmp_a;
		} else {
			$tx_result = '';
			if ( $approved_completed == 'N' ) {
				$db = getDbInstance();
				$db->where ("user_id", $userId);
				$db->where ("coin_type", $columnShortName);
				$db->where ("ethmethod", 'approve');
				$db->where ("del", 'use');
				$ethSendRowFound = $db->get('ethsend');
				if ($db->count>0) {

					require_once BASE_PATH.'/lib/WalletInfos.php';
					$wi_wallet_infos = new WalletInfos();

					$tx_result = $wi_wallet_infos->get_txId_result($ethSendRowFound[0]['tx_id']);
					
					if ( $tx_result == 1 ) {
						$db = getDbInstance();
						$db->where("id", $userId);
						$updateColData = $db->update('admin_accounts', [$columnNameCompleted=>'Y']);
					} else {
						$err_code = $err_code_tmp_ac;
						$last_id_dts = new_set_send_err_log ('send', $send_coin_type, $userId, '', 'permission', $err_log_msg2);
					}
				} else {
					$err_code = $err_code_tmp_ac;
				}

			}
		}
	} else {
		$err_code = '701';
	}
	return $err_code;

} //


// 에러 발생시(try-catch 등) 파일에 저장
//		$log : 메세지(message)
function nproc_fn_logSave($e_message, $e_code, $e_file, $e_line, $err_code, $data) {
	$logPathDir = "/var/www/html/wallet2/_log";  //로그위치 지정

	$filePath = $logPathDir."/".date("Y")."/".date("n");
	$folderName1 = date("Y"); //폴더 1 년도 생성
	$folderName2 = date("n"); //폴더 2 월 생성

	if(!is_dir($logPathDir."/".$folderName1)){
		mkdir($logPathDir."/".$folderName1, 0777);
	}
	
	if(!is_dir($logPathDir."/".$folderName1."/".$folderName2)){
		mkdir(($logPathDir."/".$folderName1."/".$folderName2), 0777);
	}
	
	$log_file = fopen($logPathDir."/".$folderName1."/".$folderName2."/".date("Ymd").".txt", "a");

	// new_fn_logSave( 'Message : (' . $userId . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$log = 'Message : '.$e_message.', Code : '.$e_code.', File : '.$e_file.' on line '.$e_line;
	if ( !empty($err_code) ) {
		$log = '[ '.$err_code.' ] '.$log;
	}
	if ( !empty($data) ) {
		$log .= ' / ';
		foreach($data as $k=>$v) {
			$log .= ', '.$k.' : '.$v;
		}
	}

	fwrite($log_file, date("Y-m-d H:i:s ").$log."\r\n");
	fclose($log_file);
}



function npro_err_message ($code) {
	$msg = '';
	switch($code) {
		case '601':
			// 잔액부족 : ETH
			$msg = !empty($langArr['insufficient_eth_balance']) ? $langArr['insufficient_eth_balance'] : "Insufficient Eth Balance";
			break;

		case '602':
			// 잔액부족 : CTC 수수료
			$msg = !empty($langArr['send_message3']) ? $langArr['send_message3'] : "Insufficient CTC Fee for trasfer Token";
			break;

		case '603':
			// 잔액부족 : Coin
			$msg = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			break;

		case '611':
			// ETH 잔액조회 실패
			$msg = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
			break;

		case '612':
			// CTC 잔액조회 실패
			$msg = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
			break;

		case '613':
			// Coin 잔액조회 실패
			$msg = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
			break;

		case '621':
			// CTC 전송권한 없음 (approved)
			$msg = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
			break;

		case '622':
			// CTC 전송권한 없음 (approved_completed)
			$msg = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
			break;

		case '623':
			// Coin 전송권한 없음 (approved)
			$msg = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
			break;

		case '624':
			// Coin 전송권한 없음 (approved_completed)
			$msg = !empty($langArr['you_dont_have_permission_for_transfer']) ? $langArr['you_dont_have_permission_for_transfer'] : "You don't have permission for transfer";
			break;

		case '631':
			// 최소 전송해야 하는 금액보다 적은 금액을 보내려고 함
			break;

		case '641':
			// unlock failed
			$msg = 'Unlock Failed';
			break;

		case '661':
			// send - transfer failed
			$msg = !empty($langArr['send_err_message1']) ? $langArr['send_err_message1'] : "Unable to send.";
			break;

		case '662':
			// send - transferFrom failed
			$msg = !empty($langArr['send_err_message1']) ? $langArr['send_err_message1'] : "Unable to send.";
			break;

		case '663':
			// send - transferFrom failed (수수료 보낼 때)
			$msg = !empty($langArr['send_err_message1']) ? $langArr['send_err_message1'] : "Unable to send.";
			break;

		case '664':
			// send - sendTransaction (eth 전송시)
			$msg = !empty($langArr['send_err_message1']) ? $langArr['send_err_message1'] : "Unable to send.";
			break;

		case '681':
			// transaction hash return failed
			$msg = !empty($langArr['send_err_message1']) ? $langArr['send_err_message1'] : "Unable to send.";
			break;

		case '682':
			// transaction hash return failed (ctc fee)
			$msg = !empty($langArr['send_err_message1']) ? $langArr['send_err_message1'] : "Unable to send.";
			break;

		case '691':
			// save failed : user_transactions
			$msg = !empty($langArr['send_err_message2']) ? $langArr['send_err_message2'] : "Data can't be saved.";
			break;

		case '692':
			// save failed : user_transactions_all
			$msg = !empty($langArr['send_err_message2']) ? $langArr['send_err_message2'] : "Data can't be saved.";
			break;

		case '701':
			// 회원정보 없음
			$msg = !empty($langArr['send_err_message3']) ? $langArr['send_err_message3'] : "Member information could not be found.";
			break;
			
		case '711':
			// 본인인증 해야 이용가능
			$msg = !empty($langArr['send_auth_need']) ? $langArr['send_auth_need'] : 'Can be used after authentication. Please use after verifying your identity in [My Info].';
			break;

		case '712':
			// 전송비밀번호 설정이 필요함
			$msg = !empty($langArr['transfer_pw_message4']) ? $langArr['transfer_pw_message4'] : 'Please set payment password.';
			break;
	}
	return $msg;
} //

			
?>