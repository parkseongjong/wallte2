<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use wallet\common\Log as walletLog;

require __DIR__ .'/vendor/autoload.php';

//2021-08-12 LOG 기능 추가 By.OJT
$log = new walletLog();
$log->info('쿠폰 구매 목록 조회',['target_id'=>0,'action'=>'S']);
//$walletLogger->info('',['admin_id'=>$walletLoggerUtil->getUserSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

$page = filter_input(INPUT_GET, 'page');
$search = filter_input(INPUT_GET, 'search');

$db = getDbInstance();
$db->where('user_id',  $_SESSION['user_id']);
$db->where('coupon_kind', 'fee');

$pagelimit = 10;

if ($page == "") {
    $page = 1;
}
$filter_col = "id";
$order_by = "desc";

if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("coupon_result", $page);
$total_pages = $db->totalPages;

include_once 'includes/header.php';

?>
<link  rel="stylesheet" href="css/coupon.css?ver=2.1.5"/>
<link href="css/lists.css?ver=1.0" rel="stylesheet">

<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-6">
			<h1 class="page-header">LIST</h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php') ?>


    <div class="tab-content">
		<div id="coupon_list" class="coupon_common ">

			<div class="payment_btn">
				
			
				<?php
				$app_version = '';
				$app_name = 'wallet';
				$app_id = []; // 마켓 URL
				$app_device = ''; // android, ios
				$app_id['android'] = 'market://details?id=com.cybertronchain.wallet2';
				$app_id['ios'] = 'itms-apps://itunes.apple.com/kr/app/apple-store/id1527694686';
				if ( isset($_SESSION['app_version']) && !empty($_SESSION['app_version']) ) {
					$app_version = $_SESSION['app_version'];
				}
				if ( isset($_SESSION['app_name']) && !empty($_SESSION['app_name']) ) {
					$app_name = $_SESSION['app_name'];
					if ( $app_name == 'barrybarries' ) {
						$app_id['android'] = 'market://details?id=com.cybertronchain.barrybarries';
						$app_id['ios'] = 'itms-apps://itunes.apple.com/kr/app/apple-store/id1537941110';
					}
				}

				$payment_ok = ''; // 결제가능여부
				if ( stristr($_SERVER['HTTP_USER_AGENT'], 'android-web-view') == TRUE ) {
					$app_device = 'android';
					//if ( ( $app_name == 'wallet' && $app_version >= $payment_android_wallet ) || ($app_name == 'barrybarries' && $app_version >= $payment_android_barry) ) { // 결제가능
						$payment_ok = 'Y';
					//} else { // 업데이트 필요함
					//	$payment_ok = 'Update';
					//}
				} else if ( stristr($_SERVER['HTTP_USER_AGENT'], 'ios-web-view') == TRUE ) {
					$app_device = 'ios';
					//if ( ( $app_name == 'wallet' && $app_version >= $payment_ios_wallet ) || ($app_name == 'barrybarries' && $app_version >= $payment_ios_barry) ) { // 결제가능
						$payment_ok = 'Y';
					//} else { // 업데이트 필요함
					//	$payment_ok = 'Update';
					//}
					//$payment_ok = 'Wait';
				} else { // 결제불가 단말기
					$payment_ok = 'No';
					
				}

				//$payment_ok = 'Wait_checking'; // ----------------------------

				if ( $payment_ok == 'Y' ) {
					?><a href="coupon_buy.php" class="btn btn-primary"><?php echo !empty($langArr['coupon_buy_button1']) ? $langArr['coupon_buy_button1'] : "coupon payment"; ?></a><?php
				} else if ($payment_ok == 'Update' ) {
					?><a class="warning" href="<?php echo $app_id[$app_device]; ?>" title="app"><?php echo !empty($langArr['coupon_payment_update_app1']) ? $langArr['coupon_payment_update_app1'] : "The latest version of the app is required to make payments. Click "; ?><span><?php echo !empty($langArr['coupon_payment_update_app2']) ? $langArr['coupon_payment_update_app2'] : "here"; ?></span><?php echo !empty($langArr['coupon_payment_update_app3']) ? $langArr['coupon_payment_update_app3'] : " to update."; echo !empty($langArr['coupon_payment_update_app4']) ? $langArr['coupon_payment_update_app4'] : " If it is the latest version, please log out and log in again."; ?></a><?php
				} else if ($payment_ok == 'No' ) {
					?><span class="warning"><?php echo !empty($langArr['coupon_payment_app_not_supported']) ? $langArr['coupon_payment_app_not_supported'] : "This device does not support payment service."; ?></span><?php
				} else if ( $payment_ok == 'Wait' ) {
					?><span class="warning"><?php echo !empty($langArr['coupon_payment_app_waiting']) ? $langArr['coupon_payment_app_waiting'] : "We are preparing to update the iOS app."; ?></span><?php
				} else if ( $payment_ok == 'Wait_checking' ) {
					?><span class="warning"><?php echo !empty($langArr['coupon_checked_waiting']) ? $langArr['coupon_checked_waiting'] : "We apologize for the inconvenience. Please note that the service is under maintenance, and payment service is not available during the maintenance period. Thank you."; ?></span><?php
				}
				?>
			</div>

			<table class="table table-bordered user_table_new">
				<thead>
					<tr>
						<th style="min-width: 70px;"><?php echo !empty($langArr['coupon_list_text8']) ? $langArr['coupon_list_text8'] : "Kind"; ?></th>
						<th><?php echo !empty($langArr['coupon_list_text1']) ? $langArr['coupon_list_text1'] : "Purchase date"; ?> / <?php echo !empty($langArr['coupon_list_text2']) ? $langArr['coupon_list_text2'] : "Usage date"; ?></th>
						<th><?php echo !empty($langArr['coupon_list_text7']) ? $langArr['coupon_list_text7'] : "Use or not"; ?></th>
						<th><?php echo !empty($langArr['coupon_payment_receipt1']) ? $langArr['coupon_payment_receipt1'] : "Receipt"; ?></th>
					</tr>
				</thead>
				<tbody>

					<?php
					if ( !empty($resultData) ) {
						foreach ($resultData as $row) {

							$db = getDbInstance();
							$db->where('id',  $row['coupon_id']);
							$coupon_info = $db->getOne('coupon_list');
							
							$db = getDbInstance();
							$db->where('order_num',  $row['payment_id']);
							$row2 = $db->getOne('kcp_order');
							

							if ( $row['status'] == 'pending' || $row['status'] ==' available' ) {
								$status = 'wait';
							} else { // used, canceled
								$status = 'used';
							}
							$c_id = $row['id'];
							$e_coin = $coupon_info['coin_type'];
							
							$amount_won = 0;
							$etoken_amount = 0;

							$amount_won = $coupon_info['amount'];
							$etoken_amount = $coupon_info['coin_amount'];
							//$etoken_amount = new_coupon_ex_rate($coupon_info['coin_type'], 'e_coin'); // 1원당 가격
							//$etoken_amount = floor($etoken_amount * $amount_won);
							// 1원당 몇 eCTC인가가 필요함! 
							
							?>
							<tr>
								<td style="width:100px;"  class="align_center"><span class="coupon_name_box1"><?php echo number_format($coupon_info['amount']); ?></span><br><span class="coupon_name_box2"><?php echo new_pay_type_change($_SESSION['lang'], $row['pay_title']); ?></span></td>

								<td>
									<?php
										//가상계좌가 0 이라며예전루틴 		
										//7777777777777777777777777777777777 가상계좌로 분기
										if($row['gstatus']=="0"){
											echo !empty($langArr['coupon_list_text1']) ? $langArr['coupon_list_text1'] : "Purchase date";
											echo ' : '.substr($row['created_at'], 0, 16).'<br />';
											echo !empty($langArr['coupon_list_text2']) ? $langArr['coupon_list_text2'] : "Usage date";
											echo ' : ';
											if ( $status == 'used' ) { echo substr($row['used_at'], 0, 16); }
										

										//가상계좌가 1 이라며예전루틴 		
										//7777777777777777777777777777777777 가상계좌로 분기	
										}else{ // 입금전
											if ( $row2['gva_date'] >= date("Y-m-d H:i:s") ) {
												echo !empty($langArr['coupon_list_text3']) ? $langArr['coupon_list_text3'] : "It is before deposit.";
											} else {
												echo !empty($langArr['coupon_list_text13']) ? $langArr['coupon_list_text13'] : "The deposit deadline has passed.";
											}
										
										//가상계좌가 0 이라며예전루틴 		
										//7777777777777777777777777777777777 가상계좌로 분기 end 
										}
														
									?>										 
								</td>
								<!--	<td><?php echo new_coupon_status_change($row['status']); ?></td>  -->
								

								<td>
									<?php
										if($row['gstatus']=="0"){ // 가상계좌가 아닐 경우
											if ( $row['status'] == 'available' ) {
												if ( !empty($row['deadline_use']) ) {
													if ( $row['deadline_use'] >= date("Y-m-d H:i:s") ) {
														?><a href="javascript:;" class="btn btn-primary" data-toggle="modal" data-target="#coupon_use_<?php echo $c_id; ?>"><?php echo !empty($langArr['coupon_list_btn1']) ? $langArr['coupon_list_btn1'] : "Use"; ?></a><br /><?php
														echo !empty($langArr['coupon_list_text10']) ? $langArr['coupon_list_text10'] : "Deadline of use";
														echo ' : '.$row['deadline_use'];
													} else { // 사용기한 초과
														echo !empty($langArr['coupon_list_text11']) ? $langArr['coupon_list_text11'] : "Expiration date exceeded";
													}
												} else { // 확인중입니다
													echo !empty($langArr['coupon_list_text12']) ? $langArr['coupon_list_text12'] : "Checking";
												}
											}
											if ( !empty($row['coin_amount']) && $status == 'used' ) {
												echo !empty($langArr['etoken_log_send_text2']) ? $langArr['etoken_log_send_text2'] : "Completed"; 
												echo " : ".number_format($row['coin_amount']).' '.$n_epay_name_array[$coupon_info['coin_type']];
											}
										}else{ // 가상계좌일 경우
											?>
											<div class="virtual_account_font">
												<span class="virtual_account_box"><?php echo new_pay_type_change($_SESSION['lang'], '가상계좌'); ?></span>
												<br /><span class="virtual_account_font"><?=$row2['gbank_name']?></span> (<?=$row2['gaccount']?>)
												<br /><?php echo !empty($langArr['coupon_list_text4']) ? $langArr['coupon_list_text4'] : "Depositor"; ?> : <?=$row2['gdepositor']?>
												<br /><?php echo !empty($langArr['coupon_list_text5']) ? $langArr['coupon_list_text5'] : "Deposit amount"; ?> : <span class="virtual_account_font1"><?=number_format($row2['order_amount'],0)?></span> <?php echo !empty($langArr['krw_unit']) ? $langArr['krw_unit'] : "WON"; ?>
												<br /><?php echo !empty($langArr['coupon_list_text6']) ? $langArr['coupon_list_text6'] : "Deposit deadline"; ?> : <span class="virtual_account_font2"><?=$row2['gva_date']?></span>
											</div>
											<?php
										}
														
										?>								
										
								</td>
								<?php
								$receipt_url = '';
								$receipt_url_lang = '';
								if ( $_SESSION['lang'] == 'en' ) {
									$receipt_url_lang = '_eng';
								}
								switch($row2['order_pay']) {
									case '카드결제':
										$receipt_url = 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=card_bill'.$receipt_url_lang.'&tno='.$row2['tno'].'&order_no='.$row2['order_num'].'&trade_mony='.$row2['order_amount'];
										$width = 470;
										$height = 815;
										break;
									case '계좌이체':
										$receipt_url = 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=acnt_bill'.$receipt_url_lang.'&tno='.$row2['tno'].'&order_no='.$row2['order_num'].'&trade_mony='.$row2['order_amount'];
										$width = 470;
										$height = 695;
										break;
									case '가상계좌':
										$receipt_url = 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=vcnt_bill'.$receipt_url_lang.'&tno='.$row2['tno'].'&order_no='.$row2['order_num'].'&trade_mony='.$row2['order_amount'];
										$width = 470;
										$height = 695;
										break;
								}
								?>
								<td class="align_center"><a href="javascript:;" onclick="window.open('<?php echo $receipt_url; ?>','receipt','width=<?php echo $width; ?>,height=<?php echo $height; ?>');" class="btn_small receipt_btn" ><?php echo !empty($langArr['confirm']) ? $langArr['confirm'] : "Confirm"; ?></a></td>
							</tr>
							<div class="modal fade" id="coupon_use_<?php echo $c_id; ?>" role="dialog">
								<div class="modal-dialog">
									<form action="multi.pro.php" method="POST">
										<input type="hidden" name="mode" value="coupon_payment1" />
										<input type="hidden" name="cid" value="<?php echo $c_id; ?>" />
										<input type="hidden" name="page" value="<?php echo $page; ?>" />
										<input type="hidden" name="search" value="<?php echo $search; ?>" />
										<!-- Modal content-->
										<div class="modal-content">
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal">&times;</button>
												<h4 class="modal-title">Confirm</h4>
											</div>
											<div class="modal-body">
												<p><?php echo !empty($langArr['coupon_modal_text1']) ? $langArr['coupon_modal_text1'] : "The quantity to receive is "; ?><?php echo $etoken_amount.' '.$n_epay_name_array[$e_coin]; ?><?php echo !empty($langArr['coupon_modal_text2']) ? $langArr['coupon_modal_text2'] : ". Would you like to use it?"; ?></p>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-default pull-left"><?php echo !empty($langArr['confirm_btn_yes']) ? $langArr['confirm_btn_yes'] : "Yes"; ?></button>
												<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo !empty($langArr['confirm_btn_no']) ? $langArr['confirm_btn_no'] : "No"; ?></button>
											</div>
										</div>
									</form>
								</div>
							</div>
						<?php
						} // foreach
					} // if
					?>

				</tbody>
			</table>

			<!-- Pagination links-->
			<div class="text-center">
				<?php
				$currentPage = 1;
				$get_infos = '';
				if ( isset($_GET) &&!empty($_GET) ) {
					$get_infos = $_GET;
					if (isset($_GET['page']) && !empty($_GET['page'])) {
						$currentPage = $_GET['page'];
					}
				}
				echo new_set_page_list($currentPage, '', $total_pages, $get_infos, '10');
				?>
			</div>

	    </div>
	</div>
</div>
<?php
include_once 'includes/footer.php'; ?>
