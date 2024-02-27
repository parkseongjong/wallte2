<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:index.php');
}

$filename = time().'export.csv';
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');

$file = fopen('php://output', 'w');
$headers = array('No', 'Wallet Address', 'Type', 'Amount', 'Error');
fputcsv($file,$headers);

$encode = array('ASCII','UTF-8','EUC-KR');

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$walletLogger->info('관리자 모드 > 비포인트 & E-Pay 적립 > form 등록',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'A']);

if($_FILES['userfile']['name']) {
	
	$err_msg = '';

	// File size Check
	//print_r($_FILES['userfile']);
	if ( $_FILES['userfile']['error'] > 0) {
		switch ($_FILES['userfile']['error']) {
			case '1':
			case '2':
				$err_msg = !empty($langArr['profile_img_message4']) ? $langArr['profile_img_message4'] : "File size is too large.";
				break;
			default:
				$err_msg = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred";
				break;
		}
	}
	if($_FILES['userfile']['size'] <= 0) { // fail
		$err_msg = !empty($langArr['profile_img_message1']) ? $langArr['profile_img_message1'] : "Please check the file size.";
	}
	
	// 파일 확장자 체크
	$full_filename = explode(".", $_FILES['userfile']['name']);
	$extension = $full_filename[sizeof($full_filename)-1];
	$extension= strtolower($extension);
	if ( $extension != 'csv') {
		$err_msg = !empty($langArr['addpoint_upload_err6']) ? $langArr['addpoint_upload_err6'] : "Only csv files (csv) can be uploaded.";
	}

	if ( empty($err_msg) ) {
	
		$row = 0;
		if ( ( $handle = fopen($_FILES['userfile']['tmp_name'], 'r')) !== FALSE ) {
			while ( ( $data = fgetcsv($handle, 1000, ',') ) !== false ) {
				$num = count($data);
				$row++;
				if ( $row > 1 ) {
					$walletAddress = '';
					$type = '';
					$amount = '';
					for ($c = 0; $c < $num; $c++) {
						//echo $row.' / '.$c.' : '.$data[$c].'<br />';
						if ( $c == 0 ) { // A열
							$walletAddress = $data[$c];
						} else if ( $c == 1 ) { // B열
							$type = $data[$c];
						} else if ( $c == 2 ) { // C열
							$amount = $data[$c];
						}
					} // for
					
					if ( mb_detect_encoding($walletAddress, $encode) == 'EUC-KR' ) {
						$walletAddress = mb_convert_encoding($walletAddress, "UTF-8", "EUC-KR");
					}
					if ( mb_detect_encoding($type, $encode) == 'EUC-KR' ) {
						$type = mb_convert_encoding($type, "UTF-8", "EUC-KR");
					}
					if ( mb_detect_encoding($amount, $encode) == 'EUC-KR' ) {
						$amount = mb_convert_encoding($amount, "UTF-8", "EUC-KR");
					}
					list($res, $msg) = new_set_excel_point($walletAddress, $type, $amount, $n_master_etoken_id, $n_master_etoken_wallet_address, $langArr);
					
					$walletAddress = mb_convert_encoding( $walletAddress, "EUC-KR", "UTF-8" );
					$type = mb_convert_encoding( $type, "EUC-KR", "UTF-8" );
					$amount = mb_convert_encoding( $amount, "EUC-KR", "UTF-8" );
					$msg = mb_convert_encoding( $msg, "EUC-KR", "UTF-8" );

					$arr = [];
					$arr['No'] = $row;
					$arr['Wallet Address'] = $walletAddress;
					$arr['Type'] = $type;
					$arr['Amount'] = $amount;
					$arr['Error'] = $msg;
					fputcsv($file,$arr);
					
				}
			} // while
			fclose($file);
			fclose($handle);
		} // if ($handle)
	} else {
		$err_msg = mb_convert_encoding( $err_msg, "EUC-KR", "UTF-8" );
		$arr = [];
		$arr['No'] = '';
		$arr['Wallet Address'] = '';
		$arr['Type'] = '';
		$arr['Amount'] = '';
		$arr['Error'] = $err_msg;
		fputcsv($file,$arr);
		fclose($file);
	}
}


function new_set_excel_point($wallet_address, $type, $amount, $adminId, $adminWalletAddress, $langArr) {
	
	$err = false;
	$err_msg = !empty($langArr['addpoint_upload_success']) ? $langArr['addpoint_upload_success'] : "Success";


	$allow_type = array('ectc', 'etp3', 'emc', 'ekrw');
	$special_pattern = "/[`~!@#$%^&*|\\\'\";:\/?^=^+_()<>]/"; // -  ,   .   허용
	// 한글이 포함되어 있으면 EUC-KR, 아니면 ASCII
	
	//if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $wallet_address) ) { // utf-8인 경우
	if (preg_match("/[\xA1-\xFE][\xA1-\xFE]/", $wallet_address) ) {

		$err = true;
		$err_msg = !empty($langArr['addpoint_upload_err1']) ? $langArr['addpoint_upload_err1'] : "Hangul input impossible";
		return array($err, $err_msg);
	}
	

	// 특수문자 금지
	if( preg_match($special_pattern, $wallet_address) || preg_match($special_pattern, $type) || preg_match($special_pattern, $amount) ){
		$err = true;
		$err_msg = !empty($langArr['addpoint_upload_err2']) ? $langArr['addpoint_upload_err2'] : "Special characters impossible";
		return array($err, $err_msg);
	}

	$coin_type = '';
	//if ( strtolower($type) != 'bee' ) {
	$coin_type = str_replace('-', '', $type);
	$coin_type = strtolower($coin_type);
	//}


	// type check
	if ( $coin_type != 'bee' && !in_array($coin_type, $allow_type) ) {
		$err= true;
		$err_msg = !empty($langArr['addpoint_upload_err3']) ? $langArr['addpoint_upload_err3'] : "Type is not correct";
		return array($err, $err_msg);
	}

	// amount check
	$amount = str_replace(',', '', $amount);
	if ( !is_numeric($amount) || $amount == 0 ) {
		$err = true;
		$err_msg = !empty($langArr['addpoint_upload_err4']) ? $langArr['addpoint_upload_err4'] : "Not a number";
		return array($err, $err_msg);
	}


	$db = getDbInstance();

	$db->where('wallet_address', $wallet_address);
	$userInfo = $db->getOne('admin_accounts', 'id');
	$user_id = $userInfo['id'];

	// 회원정보 체크
	if ( empty($userInfo) ) {
		$err = true;
		$err_msg = !empty($langArr['addpoint_upload_err5']) ? $langArr['addpoint_upload_err5'] : "No member information";
		return array($err, $err_msg);
	}
	
	// Bee Point 적립
	if ( $coin_type == 'bee' ) {
		$log_data = [];
		$log_data['user_id'] = $user_id;
		$log_data['user_wallet_address'] = $wallet_address;
		$log_data['points'] = $amount;
		$log_data['description'] = 'From Admin';
		$last_id = $db->insert('store_transactions', $log_data);

		
	} else {
		// E-Pay 적립
		
		
		if ( $amount > 0 ) {

			$db->where("id", $user_id);
			$updateArr = [];
			$updateArr['etoken_'.$coin_type] = $db->inc($amount);
			$last_id1 = $db->update('admin_accounts', $updateArr);
			if ( $last_id1 ) {
				$data_to_send_logs = [];
				$data_to_send_logs['user_id'] = $user_id;
				$data_to_send_logs['wallet_address'] = $wallet_address;
				$data_to_send_logs['coin_type'] = $coin_type;
				$data_to_send_logs['points'] = $amount;
				$data_to_send_logs['in_out'] = 'in';
				$data_to_send_logs['send_type'] = 'from_admin';
				$data_to_send_logs['send_user_id'] = $adminId;
				$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
				$data_to_send_logs['send_fee'] = '0';
				$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
				
				$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
			}

			
			$db->where("id", $adminId);
			$updateArr = [];
			$updateArr['etoken_'.$coin_type] = $db->dec($amount);
			$last_id2 = $db->update('admin_accounts', $updateArr);
			if ( $last_id2 ) {
				$data_to_send_logs = [];
				$data_to_send_logs['user_id'] = $adminId;
				$data_to_send_logs['wallet_address'] = $adminWalletAddress;
				$data_to_send_logs['coin_type'] = $coin_type;
				$data_to_send_logs['points'] = '-'.$amount;
				$data_to_send_logs['in_out'] = 'out';
				$data_to_send_logs['send_type'] = 'from_admin';
				$data_to_send_logs['send_user_id'] = $user_id;
				$data_to_send_logs['send_wallet_address'] = $wallet_address;
				$data_to_send_logs['send_fee'] = '0';
				$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
				
				$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
			}

		} else {
			
			$amount = str_replace('-', '', $amount);

			$db->where("id", $user_id);
			$updateArr = [];
			$updateArr['etoken_'.$coin_type] = $db->dec($amount);
			$last_id1 = $db->update('admin_accounts', $updateArr);
			if ( $last_id1 ) {
				$data_to_send_logs = [];
				$data_to_send_logs['user_id'] = $user_id;
				$data_to_send_logs['wallet_address'] = $wallet_address;
				$data_to_send_logs['coin_type'] = $coin_type;
				$data_to_send_logs['points'] = '-'.$amount;
				$data_to_send_logs['in_out'] = 'out';
				$data_to_send_logs['send_type'] = 'from_admin';
				$data_to_send_logs['send_user_id'] = $adminId;
				$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
				$data_to_send_logs['send_fee'] = '0';
				$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
				
				$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
			}

			$db->where("id", $adminId);
			$updateArr = [];
			$updateArr['etoken_'.$coin_type] = $db->inc($amount);
			$last_id2 = $db->update('admin_accounts', $updateArr);
			if ( $last_id2 ) {
				$data_to_send_logs = [];
				$data_to_send_logs['user_id'] = $adminId;
				$data_to_send_logs['wallet_address'] = $adminWalletAddress;
				$data_to_send_logs['coin_type'] = $coin_type;
				$data_to_send_logs['points'] = $amount;
				$data_to_send_logs['in_out'] = 'in';
				$data_to_send_logs['send_type'] = 'from_admin';
				$data_to_send_logs['send_user_id'] = $user_id;
				$data_to_send_logs['send_wallet_address'] = $wallet_address;
				$data_to_send_logs['send_fee'] = '0';
				$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
				
				$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
			}



		}
		
	}
	
	return array($err, $err_msg);
}

?>

