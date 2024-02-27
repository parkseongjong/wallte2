<?php 
// Page in use
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

$update_id = filter_input(INPUT_POST, 'update_id');
$mode = filter_input(INPUT_POST, 'mode');
$page = filter_input(INPUT_POST, 'page');
$page_str = (!empty($page)) ? '&page='.$page : '';

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);
$db = getDbInstance();

if($_SESSION['admin_type']!='admin'){
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}
if ($update_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    switch($mode) {
		case 'sendlog_list_err':
			$db = getDbInstance();
			$db->where('id', $update_id);
			$updateArr = [];
			$updateArr['confirmed'] = 'Y';
			$stat = $db->update('send_error_logs', $updateArr);
			if ($stat) {
				$_SESSION['info'] = "Update successfully!";
                $walletLogger->info('sendlog_list_err 처리',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
				header('location: sendlog_list.php?type=error'.$page_str);
				exit;
			}
			break;

		case 'admin_users_loginornot':
			if ( isset($_POST['return_page']) && !empty($_POST['return_page']) ) {
				$return_page = $_POST['return_page'];
			} else {
				$return_page = 'admin_users';
			}
			$db->where('id', $update_id);
			$row = $db->getOne("admin_accounts");
			if ( $row['login_or_not'] == 'Y' ) {
				$login_or_not = 'N';
			} else {
				$login_or_not = 'Y';
			}

			$db = getDbInstance();
			$db->where('id', $update_id);
			$updateArr = [];
			$updateArr['login_or_not'] = $login_or_not;
			$stat = $db->update('admin_accounts', $updateArr);
			if ($stat) {
				$_SESSION['info'] = "User Update successfully!";
				if($login_or_not == 'Y'){
                    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 로그인 허용 처리',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
				}
				else{
                    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 로그인 불가 처리',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
				}
				
			}
			if ( isset($_POST['queries']) && !empty($_POST['queries']) ) {
				header('location: '.$return_page.'.php?'.$_POST['queries']);
			} else {
				header('location: '.$return_page.'.php');
			}
			exit;

			break;

		case 'transfer_approved':
			if ( isset($_POST['return_page']) && !empty($_POST['return_page']) ) {
				$return_page = $_POST['return_page'];
			} else {
				$return_page = 'admin_fee_list';
			}

			$db = getDbInstance();
			$db->where('id', $update_id);
			$row = $db->getOne("admin_accounts");
			if ( $row['transfer_approved'] == 'W' ) {
				$transfer_approved = 'C';
				$db = getDbInstance();
				$db->where('id', $update_id);
				$updateArr = [];
				$updateArr['transfer_approved'] = $transfer_approved;
				$updateArr['transfer_approved_appdate'] = date("Y-m-d H:i:s");

				if ( isset($_POST['transfer_fee_type_change']) && $_POST['transfer_fee_type_change'] == 'Y' && $row['transfer_fee_type'] != 'B' ) {
					$updateArr['transfer_fee_type'] = 'B';
				}

				$stat = $db->update('admin_accounts', $updateArr);
				if ($stat) {
					$_SESSION['info'] = "User Update successfully!";
				} else {
					$_SESSION['info'] = "Failed";
				}
			}
            $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > change(변환 완료)',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
			if ( isset($_POST['queries']) && !empty($_POST['queries']) ) {
				header('location: '.$return_page.'.php?'.$_POST['queries']);
			} else {
				header('location: '.$return_page.'.php');
			}
			//header('location: admin_fee_list.php');
			exit;

			break;

		case 'change_address_set':
			if ( isset($_POST['return_page']) && !empty($_POST['return_page']) ) {
				$return_page = $_POST['return_page'];
			} else {
				$return_page = 'admin_change_address_users';
			}

			
			if ( isset($_POST['search_string']) && !empty($_POST['search_string']) ) {
				$search_string = $_POST['search_string'];
			} else {
				$search_string = '';
			}


			$db = getDbInstance();
			$db->where('id', $update_id);
			$row = $db->getOne("admin_accounts");

			if ( !empty($row['id']) ) {
				$updateArr2 = [];
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

				if ( $row['wallet_change_apply'] != 'Y' && !empty($row['wallet_address_change']) ) {
					$updateArr2['wallet_address'] =  $row['wallet_address_change'];
					$updateArr2['wallet_address_change'] =  $row['wallet_address'];
					$updateArr2['wallet_change_apply'] =  'Y';
					$updateArr2['pvt_key'] =  NULL;
				}

				$result ='';
				$db = getDbInstance();
				$db->where("id", $update_id);
				$last_id2 = $db->update('admin_accounts', $updateArr2);
				if ( $last_id2 ) {
					$result .= 'Account : Updated';
				} else {
					$result .= 'Account : Failed';
				}
				


				$db = getDbInstance();
				$db->where('user_id', $update_id);
				$db->where("del", 'use');
				$row_ethsend = $db->get('ethsend');
				if ($db->count > 0) { 
					$db = getDbInstance();
					$updateArr3 = [];
					$updateArr3['del'] = 'del';
					$updateArr3['deleted_at'] = date("Y-m-d H:i:s");
					$db->where("user_id", $update_id);
					$last_id3 = $db->update('ethsend', $updateArr3);

					if ( $last_id3 ) {
						$result .= ' / User Approval : Updated';
					} else {
						$result .= ' / User Approval : Failed';
					}
				} else {
					$result .= ' / User Approval : No data!';
				}
			}

			if ( !empty($result) ) {
				$_SESSION['info'] = $result;
			}
            $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 주소변경 신청 목록 > 주소변경 신청 목록 > 변경완료처리1',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
			if ( isset($_POST['queries']) && !empty($_POST['queries']) ) {
				header('location: '.$return_page.'.php?'.$_POST['queries']);
			} else {
				header('location: '.$return_page.'.php');
			}
			//header('location: '.$return_page.'.php?search_string='.$search_string);
			exit;

			break;

		case 'coupon_config_delete':

			$db->where('id', $update_id);
			$stat = $db->delete('coupon_list');
			if ($stat) {
				$_SESSION['info'] = "Coupon deleted successfully!";
                $walletLogger->info('관리자 모드 > 쿠폰 목록 > 제거 처리 / 고유 ID : '.$update_id,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'D']);
				header('location: admin_coupon_config.php');
				exit;
			}

			break;

		case 'transfer_fee_type_change':

			$return_page = 'admin_users_fee_type1';

			$db = getDbInstance();
			$db->where('id', $update_id);
			$updateArr = [];
			$updateArr['transfer_fee_type'] = $_POST['transfer_fee_type'];
			$stat = $db->update('admin_accounts', $updateArr);
			if ($stat) {
				$_SESSION['info'] = "User Update successfully!";
                $walletLogger->info('transfer_fee_type_change 처리',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$update_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
			}
			if ( isset($_POST['queries']) && !empty($_POST['queries']) ) {
				header('location: '.$return_page.'.php?'.$_POST['queries']);
			} else {
				header('location: '.$return_page.'.php');
			}
			exit;
			
			break;

	} // switch
}