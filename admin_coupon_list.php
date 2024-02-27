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
//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}
//require_once BASE_PATH.'/lib/WalletInfos.php';
//$wi_wallet_infos = new WalletInfos();

$filter = walletFilter::getInstance();

//2021-08-06 XSS Filter by.ojt
$targetPostData = array(
    'page' => 'string',
    'search_id' => 'string'
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
//$filter_col = filter_input(INPUT_GET, 'filter_col');
//$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
//$pagelimit = filter_input(INPUT_GET, 'filter_limit');
$search_id = filter_input(INPUT_GET, 'search_id');
*/
$db = getDbInstance();

$walletMasking = new walletMasking();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$walletLogger->info('관리자 모드 > 쿠폰 로그 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

//$db->where('user_id',  $_SESSION['user_id']);

$pagelimit = 10;

if ($page == "") {
    $page = 1;
}
$filter_col = "id";
$order_by = "desc";


if ( $search_id ) {
	$db->where('user_id', $search_id);
}

if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("coupon_result", $page);
$total_pages = $db->totalPages;



include_once 'includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';

?>
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-6">
			<h1 class="page-header">LIST</h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php') ?>


    <div class="tab-content">
		<div id="coupon_list" class="send_common ">

			<table class="table table-bordered admin_table_new">
				<thead>
					<tr>
						<th>회원정보</th>
						<th>쿠폰정보</th>
						<th>결제방법</th>
						<th>결제일시</th>
						<th>상태</th>
						<th>지급정보</th>
						<th>가상계좌</th>
						<th>영수증</th>
					</tr>
				</thead>
				<tbody>

					<?php // DB조회-루프 시작
					if ( !empty($resultData) ) {
						foreach ($resultData as $row) {

							$db = getDbInstance();
							$db->where('id',  $row['user_id']);
							$member_info = $db->getOne('admin_accounts');
							$member_name = get_user_real_name($member_info['auth_name'], $member_info['name'], $member_info['lname']);

							$db = getDbInstance();
							$db->where('id',  $row['coupon_id']);
							$coupon_info = $db->getOne('coupon_list');
							
							$db = getDbInstance();
							$db->where('order_num',  $row['payment_id']);
							$row2 = $db->getOne('kcp_order');
							
							$c_id = $row['id'];
							$e_coin = $coupon_info['coin_type'];


							
							$kind_text = '';
							if ( $coupon_info['kind'] == 'fee' ) {
								$kind_text = !empty($langArr['coupon_list_type1']) ? $langArr['coupon_list_type1'] : "Fees Coupon";
							} else if ( $coupon_info['kind'] == 'fee_change' ) {
								$kind_text = !empty($langArr['coupon_list_type2']) ? $langArr['coupon_list_type2'] : "CTC Fees Conversion";
							}

							
							?>
							<tr>

							
								<td>
                                    <span class="maskingArea" data-id="<?php echo $member_info['id'] ?>" data-type="email">
                                        <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($member_info['email'])); ?>
                                    </span>
                                    <br/>
                                    <span class="maskingArea" data-id="<?php echo $member_info['id'] ?>" data-type="name">
                                        <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($member_name)); ?>
                                    </span>
                                    <a href="admin_users.php?search_string=<?php echo $member_info['wallet_address']; ?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" class="btn btn-sm btn-info">
                                        정보 확인
                                    </a>
                                </td>
								<td><?php echo $kind_text; ?><br /><?php echo number_format($coupon_info['amount']); ?></td>
								<td class="align_center"><?php echo new_pay_type_change($_SESSION['lang'], $row['pay_title']); ?></td>
								<td class="align_center"><?php echo $row['created_at']; ?></td>
								<td>
									<?php
									if ( $row['gstatus'] == '1' ) {
									
										if ( $row2['gva_date'] >= date("Y-m-d H:i:s") ) {
											echo !empty($langArr['coupon_list_text3']) ? $langArr['coupon_list_text3'] : "It is before deposit.";
										} else {
											echo !empty($langArr['coupon_list_text13']) ? $langArr['coupon_list_text13'] : "The deposit deadline has passed.";
										}
									} else {
										if ( $row['coupon_kind'] == 'fee' ) {
											echo '[';
											echo !empty($langArr['coupon_status_'.$row['status']]) ? $langArr['coupon_status_'.$row['status']] : $row['status'];
											echo ']<br />';
										
											if ( $row['status'] == 'available' ) {
												if ( !empty($row['deadline_use']) ) {
													if ( $row['deadline_use'] >= date("Y-m-d H:i:s") ) {
														echo !empty($langArr['coupon_list_text10']) ? $langArr['coupon_list_text10'] : "Deadline of use";
														echo ' : '.$row['deadline_use'];
													} else { // 사용기한 초과
														echo !empty($langArr['coupon_list_text11']) ? $langArr['coupon_list_text11'] : "Expiration date exceeded";
														echo ' : '.$row['deadline_use'];
													}
												} else { // 확인중입니다
													echo !empty($langArr['coupon_list_text12']) ? $langArr['coupon_list_text12'] : "Checking";
													echo '(사용기한이 없어 관리자의 확인 후 사용기한을 채워줘야 사용이 가능합니다.)';
												}
											}
										}


										//echo new_coupon_status_change($row['status']);

									}
									?>
								</td>
								<td><?php if ( !empty($row['coin_amount']) ) { echo number_format($row['coin_amount']).' '.$n_epay_name_array[$coupon_info['coin_type']]; } ?><br /><?php if ( !empty($row['used_at']) ) { echo $row['used_at']; } ?></td>
								<td>
									<?php
									if ( $row['gstatus'] == '1' ) { ?>
										<?=$row2['gbank_name']?> (<?=$row2['gaccount']?>)
										<br /><?php echo !empty($langArr['coupon_list_text4']) ? $langArr['coupon_list_text4'] : "Depositor"; ?> : <?=$row2['gdepositor']?>
										<br /><?php echo !empty($langArr['coupon_list_text5']) ? $langArr['coupon_list_text5'] : "Deposit amount"; ?> : <?=number_format($row2['order_amount'],0)?> <?php echo !empty($langArr['krw_unit']) ? $langArr['krw_unit'] : "WON"; ?></span>
										<br /><?php echo !empty($langArr['coupon_list_text6']) ? $langArr['coupon_list_text6'] : "Deposit deadline"; ?> : <?=$row2['gva_date']?>
									<?php } ?>
								</td>
								<td class="align_center">
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
									<a href="javascript:;" onclick="window.open('<?php echo $receipt_url; ?>','receipt','width=<?php echo $width; ?>,height=<?php echo $height; ?>');" class="btn_small" style="border:1px solid #DDDDDD; padding: 5px;"><?php echo !empty($langArr['confirm']) ? $langArr['confirm'] : "Confirm"; ?></a>
								</td>
							</tr>

						<?php // 루프 종료 
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
<?php
include_once 'includes/footer.php'; ?>
