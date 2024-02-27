<?php
// Page in use
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;
use Pachico\Magoo\Magoo as walletMasking;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:./index.php');
	 exit;
}

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$filter = walletFilter::getInstance();

$db = getDbInstance();

$walletMasking = new walletMasking();

//2021-08-06 XSS Filter by.ojt
$targetPostData = array(
    'page' => 'string',
    'type' => 'string',
    'send_type' => 'string',
    'coin_type' => 'string',
    'wallet_address' => 'string',
    'status' => 'string',
    'send_target' => 'string',
    'search_string' => 'string',
);

$filterData = $filter->postDataFilter($_GET,$targetPostData);

//기존 변수를 그대로 써야해서.... 가변 변수로 선언..
foreach ($targetPostData as $key => $value){
    if($key == 'filter_limit'){
        if(key_exists($key,$filterData)){
            $pagelimit = $filterData[$key];
        }
        else{
            $pagelimit = false;
        }

    }
    else{
        if(key_exists($key,$filterData)){
            $$key = $filterData[$key];
        }
        else{
            $$key = false ;
        }

    }
}
unset($targetPostData);
/*
$page = filter_input(INPUT_GET, 'page');
$type = filter_input(INPUT_GET, 'type');
$send_type = filter_input(INPUT_GET, 'send_type');
$coin_type = filter_input(INPUT_GET, 'coin_type');
$wallet_address = filter_input(INPUT_GET, 'wallet_address');
$status = filter_input(INPUT_GET, 'status');
$send_target = filter_input(INPUT_GET, 'send_target');
$search_string = filter_input(INPUT_GET, 'search_string');
*/
/*
page			user_transactions_all.send_type
exchange_etoken		exchange_eToken
exchange_etoken_re	exchange_eToken		CTC, TP3, MC 충전 : eCTC, eTP3, eMC로 충전할 때 발생 / eCTC, eTP3, eMC 충전할 때 발생
exchange			exchange			CTC 충전, TP3 충전 (ETH) : ETH 전송할 떄 발생
transaction_cron		exchange_r		CTC충전, TP3 충전 (ETH) : CTC / TP3 받을 때 발생

*/


if ($page == "") {
    $page = 1;
}

if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') {
	$all_field = 'Y';
} else {
	$all_field = 'N';
}

$pagelimit = ($type=='error') ? 13 : 20;
//$pagelimit = 20;
$filter_col = "id";
$order_by = "desc";

switch($type) {
	case 'eth':
		$title = !empty($langArr['sendlog_subject2']) ? $langArr['sendlog_subject2'] : 'Eth Logs'; 
		$tbl_name = 'ethsend';
		break;
	case 'us':
		$title = !empty($langArr['sendlog_subject3']) ? $langArr['sendlog_subject3'] : 'User transaction Logs';
		$tbl_name = 'user_transactions';
		break;
	case 'error':
		$title = 'Error Logs';
		$tbl_name = 'send_error_logs';
		break;
	case 'kiosk':
		$title = 'Kiosk cancel Logs';
		$tbl_name = 'kiosk_cancel_log';
		break;
	case 'login':
		$title = 'Login Device Logs';
		$tbl_name = 'login_device_logs';
		if ( !empty($search_string) ) {
			$db->where('user_id', $search_string);
		}

		break;
	default:
		
		$title = !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : 'User transaction All Logs';
		$tbl_name = 'user_transactions_all';
		if ( !empty($send_type) ) {
		    /*
			if ( $send_type == 'exchange') {
				$db->where('send_type', '%' . $send_type . '%', 'like');
				$db->where('send_type', '%exchange_etoken%', 'not like');
			}
			else if ($send_type == 'exchange_eToken' && !empty($send_target) ) {
				$db->where('send_type', $send_type);
				if ( $send_target == 'P' ) {
					$db->where('etoken_send', $send_target);
				} else if ( $send_target == 'to_token' ) {
					$db->where('to_id', NULL, 'IS NOT');
				}
			}
			else {
				$db->where('send_type', $send_type);
			}
		    */
            if ( $send_type == 'exchange') {
                $db->where('send_type',$send_type);
                $db->where('send_type', 'exchange_etoken', '!=');
            }
            else if ($send_type == 'exchange_eToken' && !empty($send_target) ) {
                $db->where('send_type', $send_type);
                if ( $send_target == 'P' ) {
                    $db->where('etoken_send', $send_target);
                } else if ( $send_target == 'to_token' ) {
                    $db->where('to_id', NULL, 'IS NOT');
                }
            }
            else {
                $db->where('send_type', $send_type);
            }
		}

		if ( !empty($coin_type) ) {
			$db->where('coin_type', $coin_type);
		}

		if ( !empty($status) ) {
			if ( $status == 'waiting') {
				$db->where('status', 'success', '!=');
				$db->where('status', 'fail', '!=');
			} else {
				$db->where('status', $status);
			}
		}

		break;
}
if ( $type == 'error' ) {
	$db->orderBy('confirmed', 'desc')->orderBy($filter_col, $order_by);
} else {
	$db->orderBy($filter_col, $order_by);
}
$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate($tbl_name, $page);
$total_pages = $db->totalPages;

$walletLogger->info('관리자 모드 > 전송 로그 > 조회 / 전송 타입 :'.$send_type.' / 필터 타입 : '.$type,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

include_once './includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';
?>
<style type="text/css">
.table-bordered { min-width: 935px; }
.table-bordered td { word-break: break-all; }
</style>

<link  rel="stylesheet" href="css/admin.css"/>
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
<div id="sendlog_list">
	<div class="row">
		 <div class="col-lg-6">
			<h1 class="page-header"><?php echo $title; ?></h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php'); ?>
	<ul class="sendlog_link">
		<li><a href="sendlog_list.php" title="<?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?>">
			<?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?> (<?php echo !empty($langArr['sendlog_subject_text1']) ? $langArr['sendlog_subject_text1'] : "After 2020-05-18"; ?>)
		</a></li>
		<li><a href="sendlog_list.php?type=eth" title="<?php echo !empty($langArr['sendlog_subject2']) ? $langArr['sendlog_subject2'] : "Eth Logs"; ?>"><?php echo !empty($langArr['sendlog_subject2']) ? $langArr['sendlog_subject2'] : "Eth Logs"; ?></a></li>
		<li><a href="sendlog_list.php?type=us" title="<?php echo !empty($langArr['sendlog_subject3']) ? $langArr['sendlog_subject3'] : "User transaction Logs"; ?>"><?php echo !empty($langArr['sendlog_subject3']) ? $langArr['sendlog_subject3'] : "User transaction Logs"; ?></a></li>
		<li><a href="sendlog_list.php?type=error" title="Error Logs">Error Logs</a></li>
		<li><a href="sendlog_list.php?type=kiosk" title="Kiosk Logs">Kiosk cancel Logs</a></li>
		<li><a href="sendlog_list.php?type=login" title="Login Logs">Login Device Logs</a></li>
	</ul>

	<?php if ( empty($type) ) { ?>
		<ul class="sendlog_link2">
			<li><a href="sendlog_list.php?send_type=exchange" title="Logs"><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?>, <?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?></a></li>
			<li><a href="sendlog_list.php?send_type=register" title="Logs"><?php echo !empty($langArr['register']) ? $langArr['register'] : "Register"; ?></a></li>
			<li><a href="sendlog_list.php?send_type=send" title="Logs"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></a></li>
			<li><a href="sendlog_list.php?send_type=exchange_eToken" title="Logs">e-Pay</a></li>
		</ul>
		<?php if ( !empty($send_type) ) { ?>
			<ul class="sendlog_link3">
				<li><a href="sendlog_list.php?send_type=<?=$send_type?>&status=success" title="Logs">Success</a></li>
				<li><a href="sendlog_list.php?send_type=<?=$send_type?>&status=fail" title="Logs">Fail</a></li>
				<li><a href="sendlog_list.php?send_type=<?=$send_type?>&status=waiting" title="Logs">Pending</a></li>
				<?php if ( $send_type == 'exchange_eToken' ) { ?>
					<li><a href="sendlog_list.php?send_type=exchange_eToken&send_target=P"><?php echo !empty($langArr['etoken_log_send_text1']) ? $langArr['etoken_log_send_text1'] : "Target"; ?>(e-Pay)</a></li>
					<li><a href="sendlog_list.php?send_type=exchange_eToken&send_target=to_token"><?php echo !empty($langArr['etoken_log_send_text1']) ? $langArr['etoken_log_send_text1'] : "Target"; ?>(Coin)</a></li>
				<?php } ?>
			</ul>
	<?php } } ?>

	 <div class="tab-content">
			<div id="user_first" class="tab-pane fade in active">
				<div class="table-responsive">
					<table class="table table-bordered admin_table_new">
						<thead>
							<?php
							if ($type == 'eth') {
								$table_title = array('User ID', 'TransactionId', 'ethmethod', 'Amount', 'Coin Type', 'To Wallet Address', 'From Wallet Address', 'Status', 'Date');
							} else if ($type == 'us') {
								$table_title = array('Coin Type', 'From ID', 'To Wallet Address', 'TransactionId', 'Amount', 'Fee in eth', 'Fee in gcg', 'Status', 'date');
							} else if ($type == 'error') {
								$table_title = array('Check', 'SendType', 'CoinType', 'UserID', 'UserEmail', 'User Wallet Address', 'MessageType', 'Message', 'Date', 'UseApproval');
							} else if ($type == 'kiosk') {
								$table_title = array('Payment result', 'Approval No', 'Status', 'Cancel request date', 'CoinType', 'Store Wallet Address', 'User Wallet Address', 'Amount', 'TransactionId', 'TransactionStatus', 'Canceled date', 'Message');
							} else if ( $type == 'login' ) {
								$table_title = array('User Id', 'User Email', 'App Name', 'Device', 'Device ID / Number', 'Message', 'IP', 'Date');
							} else {
								if ( $all_field == 'Y' ) {
									$table_title = array('Send SMS', 'Send Type', 'Coin Type', 'From ID', 'To ID', 'From Wallet Address', 'To Wallet Address', 'Amount', 'Fee', 'TransactionId', 'Status', 'Date');
								} else {
									$table_title = array('Send Type', 'Coin Type', 'From ID', 'To ID', 'From Wallet Address', 'To Wallet Address', 'Amount', 'Fee', 'TransactionId', 'Status', 'Date');
								}
								if ( $send_type == 'exchange_eToken' ) {
									array_push($table_title, 'eToken Charging');
								}
							}
								?>
								<tr>
									<?php if( $all_field == 'Y' ) { ?><th>ID</th><?php }
										foreach($table_title as $k=>$v) {
											?><th><?=$v?></th><?php
										}
									?>
								</tr>
						</thead>
						<tbody>

						<?php 

							foreach ($resultData as $row) {
								if ( isset($row) ) {
									if ($type == 'eth') {
										$txid = !empty($row['tx_id']) ? $row['tx_id'] : '';
										$url =  !empty($row['tx_id']) ? 'https://etherscan.io/tx/'.$row['tx_id'] : '';

										/*if ( !empty($row['status']) ) {
											$status_tx = $row['status'];
										} else {
											$status_tx = $wi_wallet_infos->wi_get_status($row['tx_id']);
											if ( $status_tx == 'Completed' || $status_tx == 'Failed' ) {
												$updateArr = [];
												$updateArr['status'] = $status_tx;
												$db = getDbInstance();
												$db->where("id", $row['id']);
												$last_id = $db->update('ethsend', $updateArr);
											}
										}*/
										$status_tx = $row['status'];


										?>
									
										<tr>
											<?php if( $all_field == 'Y' ) { ?><td class="align_center"><?php echo $row['id']; ?></td><?php } ?>
											<td class="align_center"><?php echo $row['user_id']; ?></td>
											<td><?php
												if ( !empty($url) ) {
													echo '<a href="'.$url.'" title="'.$txid.'" target="_blank">'.$txid.'</a>';
												}
											?></td>
											<td class="align_center"><?php echo $row['ethmethod']; ?></td>
											<td class="align_center"><?php echo $row['amount']; ?></td>
											<td class="align_center"><?php echo $row['coin_type']; ?></td>
											<td><?php echo $row['to_address']; ?></td>
											<td><?php echo $row['from_address']; ?></td>
											<td class="align_center"><?php echo $status_tx; ?></td>
											<td class="align_center"><?php echo $row['created']; ?></td>
										</tr>
									<?php
									} else if ($type == 'us') {
										$txid = !empty($row['transactionId']) ? $row['transactionId'] : '';
										$url =  !empty($row['transactionId']) ? 'https://etherscan.io/tx/'.$row['transactionId'] : '';
										?>
									
										<tr>
											<?php if( $all_field == 'Y' ) { ?><td class="align_center"><?php echo $row['id']; ?></td><?php } ?>
											<td class="align_center"><?php echo $row['coin_type']; ?></td>
											<td class="align_center"><?php echo $row['sender_id']; ?></td>
											<td><?php echo $row['reciver_address']; ?></td>
											<td><?php
												if ( !empty($url) ) {
													echo '<a href="'.$url.'" title="'.$txid.'" target="_blank">'.$txid.'</a>';
												}
											?></td>
											<td class="align_center"><?php echo $row['amount']; ?></td>
											<td class="align_center"><?php echo $row['fee_in_eth']; ?></td>
											<td class="align_center"><?php echo $row['fee_in_gcg']; ?></td>
											<td class="align_center"><?php echo $row['status']; ?></td>
											<td class="align_center"><?php echo $row['created_at']; ?></td>
										</tr>
									<?php
									} else if ($type == 'error') {
										$db = getDbInstance();
										$db->where('id', $row['user_id']);
										$rowm_infos = $db->getOne('admin_accounts');

                                        // 회원이름 조립
                                        $user_name = '';
										if ( isset($rowm_infos['auth_name']) || isset($rowm_infos['name']) ) {
											$user_name = get_user_real_name($rowm_infos['auth_name'], $rowm_infos['name'], $rowm_infos['lname']);
										}

										$message = $row['message'];
										switch($row['message']) {
											case 'ctc_not_approved':
												$message = '[CTC] No permission (Can\'t read)';
												break;
											case 'token_not_approved':
												$message = '['.strtoupper($row['coin_type']).'] No permission (Can\'t read)';
												break;
											case 'ctc_approve_result_not_success':
												$message = '[CTC] approve - Failed, Dropped, Pending';
												break;
											case 'token_approve_result_not_success':
												$message = '['.strtoupper($row['coin_type']).'] approve -  Failed, Dropped, Pending';
												break;
											case 'admin_unlock':
												$message = 'Master Wallet unlock failed';
												break;
											default:
												$message = $row['message'];
												break;
										}
										// permission (ctc_not_approved, ctc_approve_result_not_success, token_not_approved, token_approve_result_not_success) / error (admin_unlock, unlock, send)

										?>
									
										<tr>
										
											<?php if( $all_field == 'Y' ) { ?><td class="align_center"><?php echo $row['id']; ?></td><?php } ?>
											<td class="align_center">
												<?php if ( $row['confirmed'] == 'N' ) { ?>
													<a href=""  class="" data-toggle="modal" data-target="#confirmed_<?php echo $row['id'] ?>"><span class="glyphicon glyphicon-ok"></span></a>
												<?php } ?>
											</td>
											<td class="align_center"><?php echo $row['send_type']; ?></td>
											<td class="align_center"><?php echo $row['coin_type']; ?></td>
											<td class="align_center">
                                                <?php echo $row['user_id']; ?>
                                                <span class="maskingArea" data-id="<?php echo $row['user_id'] ?>" data-type="name">
                                                    <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($user_name)); ?>
                                                </span>
                                            </td>
											<td class="align_center">
                                                <?php echo (!empty($rowm_infos['email']))?$walletMasking->reset()->pushEmailMask()->pushUniversalIdMask()->getMasked(htmlspecialchars($rowm_infos['email'])):'' ?>
                                            </td>
											<td>
												<?php if ( !empty($rowm_infos['wallet_address']) ) { ?>
													<a href="admin_users.php?search_string=<?php echo urlencode($rowm_infos['wallet_address']);?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="users"><?php echo !empty($rowm_infos['wallet_address']) ? $rowm_infos['wallet_address'] : ''; ?></a>
												<?php } ?>
											</td>
											<td class="align_center"><?php echo $row['msg_type']; ?></td>
											<td><?php echo $message; ?></td>
											<td class="align_center"><?php echo $row['created_at']; ?></td>
											<td class="align_center"><a href="admin_user_approval.php?user_id=<?php echo $row['user_id']?>" title="User Approval" class="btn btn-primary"><span class="glyphicon glyphicon-chevron-right"></span></a></td>
										</tr>
										
										<!-- Update Modal-->
										 <div class="modal fade" id="confirmed_<?php echo $row['id'] ?>" role="dialog">
											<div class="modal-dialog">
											  <form action="multiprocess.php" method="POST">
											  	<input type="hidden" name="mode" value="sendlog_list_err" />
											  	<input type="hidden" name="page" value="<?=$page?>" />
											  <!-- Modal content-->
												  <div class="modal-content">
													<div class="modal-header">
													  <button type="button" class="close" data-dismiss="modal">&times;</button>
													  <h4 class="modal-title">Confirm</h4>
													</div>
													<div class="modal-body">
														<input type="hidden" name="update_id" id = "update_id" value="<?php echo $row['id'] ?>">
														<p>Are you sure you want to update this log?</p>
													</div>
													<div class="modal-footer">
														<button type="submit" class="btn btn-default pull-left">Yes</button>
														<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
													</div>
												  </div>
											  </form>
											</div>
										</div>



									<?php
									} else if ( $type == 'kiosk' ) {
										$tmp = '';
										if ( !empty($row['user_transactions_all_id']) ) {
											$db = getDbInstance();
											$db->where('id', $row['user_transactions_all_id']);
											$logInfos = $db->getOne('user_transactions_all');
											if ( !empty($logInfos) ) {
												$tmp = '<a href="https://etherscan.io/tx/'.$logInfos['transactionId'].'">'.$logInfos['status'].'</a>';
											}
										}
										?>
											<tr>
												<?php if( $all_field == 'Y' ) { ?><td class="align_center"><?php echo $row['id']; ?></td><?php } ?>
												<td class="align_center"><?php echo $tmp; ?></td>
												<td class="align_center"><?php echo $row['approval_no']; ?></td>
												<td class="align_center"><?php echo $row['cancel_status']; ?></td>
												<td class="align_center"><?php echo $row['cancel_request_at']; ?></td>
												<td class="align_center"><?php echo $row['coin_type']; ?></td>
												<td><a href="https://cybertronchain.com/wallet2/admin_users.php?search_string=<?php echo $row['from_address']; ?>" title="From User Info"><?php echo $row['from_address']; ?></a></td>
												<td><a href="https://cybertronchain.com/wallet2/admin_users.php?search_string=<?php echo $row['to_address']; ?>" title="To User Info"><?php echo $row['to_address']; ?></a></td>
												<td class="align_center"><?php echo $row['amount']; ?></td>
												<td><?php echo $row['transactionId']; ?></td>
												<td class="align_center"><?php echo $row['status']; ?></td>
												<td class="align_center"><?php echo $row['cancel_completion_at']; ?></td>
												<td><?php echo $row['msg']; ?></td>
											</tr>
										<?php

									} else if ( $type == 'login' ) {
										?>
											<tr>
												<?php if( $all_field == 'Y' ) { ?><td class="align_center"><?php echo $row['id']; ?></td><?php } ?>
												<td class="align_center"><?php echo $row['user_id']; ?></td>
												<td class="align_center">
                                                    <span class="maskingArea" data-id="<?php echo $row['user_id'] ?>" data-type="email">
                                                        <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['email'])); ?>
                                                    </span>
                                                    <a href="https://cybertronchain.com/wallet2/admin_users.php?search_string=<?php echo urlencode($row['email']); ?>" title="User Info" class="btn btn-sm btn-info">
                                                        정보 확인
                                                    </a>
                                                </td>
												<td class="align_center"><?php echo $row['app_name']; ?></td>
												<td class="align_center"><?php echo $row['device']; ?></td>
												<td><?php echo $row['devId']; ?></td>
												<td><?php echo $row['msg']; ?></td>
												<td class="align_center"><?php echo $row['ip']; ?></td>
												<td class="align_center"><?php echo $row['created_at']; ?></td>
											</tr>
										<?php
									} else {
										$txid = !empty($row['transactionId']) ? $row['transactionId'] : '';
										$url =  !empty($row['transactionId']) ? 'https://etherscan.io/tx/'.$row['transactionId'] : '';
										if ( $send_type == 'exchange_eToken' ) {
											$link = '<a href="admin_etoken_logs.php?search_string='.$row['id'].'" title="log">'.$row['amount'].'</a>';
											$etoken_send = '';
											if ( $row['etoken_send'] == 'P' ) {
												$etoken_send = !empty($langArr['etoken_log_send_text1']) ? $langArr['etoken_log_send_text1'] : "Target";
											} else if ( $row['etoken_send'] == 'Y' ) {
												$etoken_send = !empty($langArr['etoken_log_send_text2']) ? $langArr['etoken_log_send_text2'] : "Completed";
											} else {
												$etoken_send = !empty($langArr['etoken_log_send_text3']) ? $langArr['etoken_log_send_text3'] : "Not applicable";
											}
										} else {
											$link = $row['amount'];
										}
										?>
										<tr>
											<?php if( $all_field == 'Y' ) { ?><td class="align_center"><?php echo $row['id']; ?></td><td><?php echo $row['send_sms']; ?></td><?php } ?>
											<td class="align_center"><?php echo $row['send_type']; ?></td>
											<td class="align_center"><?php echo $row['coin_type']; ?></td>
											<td class="align_center"><?php echo $row['from_id']; ?></td>
											<td class="align_center"><?php echo !empty($row['to_id']) ? $row['to_id'] : ''; ?></td>
											<td><a href="admin_users.php?search_string=<?php echo urlencode($row['from_address']);?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="users"><?php echo $row['from_address']; ?></a></td>
											<td><a href="admin_users.php?search_string=<?php echo urlencode($row['to_address']);?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="users"><?php echo $row['to_address']; ?></a></td>
											<td class="align_center"><?php echo $link; ?></td>
											<td class="align_center"><?php echo $row['fee']; ?></td>
											<td><?php
												if ( !empty($url) ) {
													echo '<a href="'.$url.'" title="'.$txid.'" target="_blank">'.$txid.'</a>';
												}
											?></td>
											<td class="align_center"><?php echo $row['status']; ?></td>
											<td class="align_center"><?php echo $row['created_at']; ?></td>
											<?php if ( $send_type == 'exchange_eToken' ) {
												?><td class="align_center"><?php echo $etoken_send; ?></td><?php
											} ?>
										</tr>
								<?php
									} // if ($type)
								}
							} ?>   
						</tbody>
					</table>
				
				</div>

				<!--    Pagination links-->
				<div class="text-center">
					<?php
					$currentPage = 1;
					$get_infos = '';
					if ( isset($filterData) &&!empty($filterData) ) {
						$get_infos = $filterData;
						if (isset($filterData['page']) && !empty($filterData['page'])) {
							$currentPage = $filterData['page'];
						}
					}
					echo new_set_page_list($currentPage, '', $total_pages, $get_infos, '10'); // config/new_config.php
					?>
				</div>

			</div>
		</div>
	</div>
</div>

<?php include_once './includes/footer.php'; ?>