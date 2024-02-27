<?php
// Page in use
// 수수료 변환신청 결제한사람 목록
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
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}
require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$filter = walletFilter::getInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$db = getDbInstance();

$walletMasking = new walletMasking();

//2021-08-05 XSS Filter by.ojt
$targetPostData = array(
    'search_string' => 'string',
    'del_id' => 'string',
    'filter_col' => 'string',
    'order_by' => 'string',
    'page' => 'string',
    'filter_limit' => 'string',
    'gstatus_f' => 'string'
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

//Get data from query string
/*
$search_string = filter_input(INPUT_GET, 'search_string');
$del_id = filter_input(INPUT_GET, 'del_id');

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
$gstatus_f = filter_input(INPUT_GET, 'gstatus_f');
*/
if($pagelimit == "") {
	$pagelimit = 10;
}
if ($page == "") {
    $page = 1;
}
// If filter types are not selected we show latest added data first
if ($filter_col == "") {
    $filter_col = "id";
}
if ($order_by == "") {
    $order_by = "desc";
}


// If user searches 
if($search_string) {
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 수수료 변환신청 결제 리스트 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 수수료 변환신청 결제 리스트 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}

if ( $gstatus_f == '' ) {
	$gstatus_f = '0';
}

if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}
$db->where('coupon_kind', 'fee_change');
if ( $gstatus_f != 'A' ) {
	$db->where('gstatus', $gstatus_f); // 결제완료건만 보여줌
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("coupon_result", $page); // , $select
$total_pages = $db->totalPages;



// get columns for order filter
foreach ($resultData as $value) {
    foreach ($value as $col_name => $col_value) {
        $filter_options[$col_name] = $col_name;
    }
    //execute only once
    break;
}


include_once 'includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';
?>

<link href="css/admin.css" rel="stylesheet">
<link rel="stylesheet" href="css/lists.css" />

<link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 


<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header"><?php echo !empty($langArr['registered_users']) ? $langArr['registered_users'] : "Registered Users"; ?></h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>
	
    <!--    Begin filter section-->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <!--<label for="input_search" >Search</label>
            <input type="text" placeholder="Email, Phone, WalletAddress, Name" class="form-control" id="input_search"  name="search_string" value="<?php echo $search_string; ?>">-->
            <label for ="input_order">Order By</label>
            <select name="filter_col" class="form-control">

                <?php
				if ( isset($filter_options) ) {
					foreach ($filter_options as $option) {
						($filter_col === $option) ? $selected = "selected" : $selected = "";
						echo ' <option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
					}
				}
                ?>

            </select>

            <select name="order_by" class="form-control" id="input_order">

                <option value="Asc" <?php
                if ($order_by == 'Asc') {
                    echo "selected";
                }
                ?> >Asc</option>
                <option value="Desc" <?php
                if ($order_by == 'Desc') {
                    echo "selected";
                }
                ?>>Desc</option>
            </select>
			
			<label for ="filter_limit">Limit</label>
			<select name="filter_limit" id="filter_limit" class="form-control">
				<option <?php if ($pagelimit == 10) { echo "selected"; } ?> value="10">10</option>
				<option <?php if ($pagelimit == 20) { echo "selected"; } ?> value="20">20</option>
				<option <?php if ($pagelimit == 50) { echo "selected"; } ?>  value="50">50</option>
			</select>

			<select name="gstatus_f">
				<option <?php if ( $gstatus_f == 'A' ) { echo "selected"; } ?> value="A">전체</option>
				<option <?php if ( $gstatus_f == '0' ) { echo "selected"; } ?> value="0">결제완료</option>
				<option <?php if ( $gstatus_f == '1' ) { echo "selected"; } ?> value="1">입금전</option>
			</select>

            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>

    <!-- 등록 된 사용자 서브 메뉴 START-->
    <?php include_once WALLET_PATH.'/includes/adminUsersMenu.php'; ?>
    <!-- 등록 된 사용자 서브 메뉴 END-->
	
    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered admin_table_new">
					<thead>
						<tr>
							 <th><?php echo !empty($langArr['sr_no']) ? $langArr['sr_no'] : "Sr No."; ?></th>
							<th><?php echo !empty($langArr['user_info']) ? $langArr['user_info'] : "User Info"; ?></th>
							<th><?php echo !empty($langArr['email_phone']) ? $langArr['email_phone'] : "Email / Phone"; ?></th>
							<th>CTC</th>
							<th>TP3</th>
							<th>USDT</th>
							<th>MC</th>
							<th>KRW</th>
							<th>ETH</th>
							<th>Wallet Address</th>
							<th>주소변경여부</th>
							<th><?php echo !empty($langArr['coupon_list_btn2']) ? $langArr['coupon_list_btn2'] : "Payment"; ?></th>
							<th>Message</th>
							<th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
					   $getPage = (isset($filterData['page']) && $filterData['page']>0) ? $filterData['page'] : 1 ;
						$i = (($getPage*$pagelimit)-$pagelimit)+1;
						foreach ($resultData as $row_list) {

							$db = getDbInstance();
							$db->where('id', $row_list['user_id']);
							$row = $db->getOne('admin_accounts');
							
							$userCtcAmt = 0;
							$userCtc2Amt = 0;
							$userTokenPayAmt = 0;
							$userUsdtAmt = 0;
							$userMcAmt = 0;
							$userKrwAmt = 0;
							$userEthAmt = 0;

							if ( !empty($row['wallet_address']) && strlen($row['wallet_address']) > 10 ) {
								//$userCtcAmt = getMyCTCbalance($row['wallet_address'], $n_connect_ip, $n_connect_port);
								$userCtc2Amt = getMyCTC2balance($row['wallet_address'], $testAbi, $contractAddress, $n_connect_ip, $n_connect_port);
								$userTokenPayAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$tokenPayContractAddress,1000000000000000000, $n_connect_ip, $n_connect_port);
								$userUsdtAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$usdtContractAddress,1000000, $n_connect_ip, $n_connect_port);
								$userMcAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$marketCoinContractAddress,1000000, $n_connect_ip, $n_connect_port);
								$userKrwAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$koreanWonContractAddress,1000000, $n_connect_ip, $n_connect_port);
								$userEthAmt = getMyETHBalance($row['wallet_address'], $n_connect_ip, $n_connect_port);
							}

							$userCtc2Amt2 = 0;
							$userTokenPayAmt2 = 0;
							$userUsdtAmt2 = 0;
							$userMcAmt2 = 0;
							$userKrwAmt2 = 0;
							$userEthAmt2 = 0;

							if ( !empty($row['wallet_address_change']) && strlen($row['wallet_address_change']) > 10 ) {
								$userCtc2Amt2 = getMyCTC2balance($row['wallet_address_change'], $testAbi, $contractAddress, $n_connect_ip, $n_connect_port);
								$userTokenPayAmt2 = getMyTokenBalance($row['wallet_address_change'],$tokenPayAbi,$tokenPayContractAddress,1000000000000000000, $n_connect_ip, $n_connect_port);
								$userUsdtAmt2 = getMyTokenBalance($row['wallet_address_change'],$tokenPayAbi,$usdtContractAddress,1000000, $n_connect_ip, $n_connect_port);
								$userMcAmt2 = getMyTokenBalance($row['wallet_address_change'],$tokenPayAbi,$marketCoinContractAddress,1000000, $n_connect_ip, $n_connect_port);
								$userKrwAmt2 = getMyTokenBalance($row['wallet_address_change'],$tokenPayAbi,$koreanWonContractAddress,1000000, $n_connect_ip, $n_connect_port);
								$userEthAmt2 = getMyETHBalance($row['wallet_address_change'], $n_connect_ip, $n_connect_port);
								
								$userCtc2Amt2 = new_number_format($userCtc2Amt2, $n_decimal_point_array['ctc']);
								$userTokenPayAmt2 = new_number_format($userTokenPayAmt2, $n_decimal_point_array['tp3']);
								$userUsdtAmt2 = new_number_format($userUsdtAmt2, $n_decimal_point_array['usdt']);
								$userMcAmt2 = new_number_format($userMcAmt2, $n_decimal_point_array['mc']);
								$userKrwAmt2 = new_number_format($userKrwAmt2, $n_decimal_point_array['krw']);
								$userEthAmt2 = new_number_format($userEthAmt2, $n_decimal_point_array['eth']);
							}


							$db = getDbInstance();
							$db->where("user_id", $row['id']);
							$pointSum = $db->getValue("store_transactions", "sum(points)");
							$pointSum = ($pointSum == NULL ? '0.0000000' : $pointSum);
							$pointSum = rtrim($pointSum, 0);
							$pointSum = rtrim($pointSum, '.');

							$id_auth = '';
							if ( !empty($row['id_auth']) && $row['id_auth'] == 'Y') {
								$id_auth = !empty($langArr['member_auth_finish']) ? $langArr['member_auth_finish'] : ' (Personal identification completed)';
							}

							$user_name = '';
							$user_name = get_user_real_name($row['auth_name'], htmlspecialchars($row['name']), htmlspecialchars($row['lname']));
							
							$transfer_approved = $row['transfer_approved']=='C' ? 'CTC' : 'ETH';

							$wallet_change_apply = '';
							if ( $row['id'] >= 10900 ) {
								$wallet_change_apply = '해당없음';
							} else {
								switch($row['wallet_change_apply']) {
									case 'N':
										$wallet_change_apply = '미신청';
										break;
									case 'W':
										$wallet_change_apply = '신청완료';
										break;
									case 'Y':
										$wallet_change_apply = '변경완료';
										break;

								}
							}
							?>
						
						<tr>
							<td><?php echo $i; ?> </td>
							<td class="td_cls1">
								<strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong>
                                <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name"><?php echo $walletMasking->reset()->pushNameMask()->getMasked($user_name); ?></span>
                                <?php if ( !empty($id_auth) ) { echo $id_auth; } ?><br />
								<strong><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?>:</strong> <?php echo htmlspecialchars($row['created_at']) ?><br />
								<strong>Last Login: </strong><?php echo htmlspecialchars($row['last_login_at']); ?><br />
								<strong>Login:</strong>
								<?php
								if ( $row['login_or_not'] == 'N' ) {
									echo !empty($langArr['admin_users_text2']) ? $langArr['admin_users_text2'] : 'Impossible';
								} else {
									echo !empty($langArr['admin_users_text1']) ? $langArr['admin_users_text1'] : 'Possible';
								}
								?>
								 / <strong>Fee: </strong><?php echo $transfer_approved; ?>
							</td>


							<td>
                                <?php echo ($row['register_with']=='email') ? htmlspecialchars($row['email']).'<br />' : "" ?>
                                <?php if($row['phone']): ?>
                                    <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="phone">
                                        <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['phone'])); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
							<!--<td><?php echo new_number_format($userCtcAmt, $n_decimal_point_array['ctc']); ?></td>-->
							<td><?php echo new_number_format($userCtc2Amt, $n_decimal_point_array['ctc']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?=$userCtc2Amt2?><?php } ?></td>
							<td><?php echo new_number_format($userTokenPayAmt, $n_decimal_point_array['tp3']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?=$userTokenPayAmt2?><?php } ?></td>
							<td><?php echo new_number_format($userUsdtAmt, $n_decimal_point_array['usdt']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?=$userUsdtAmt2?><?php } ?></td>
							<td><?php echo new_number_format($userMcAmt, $n_decimal_point_array['mc']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?=$userMcAmt2?><?php } ?></td>
							<td><?php echo new_number_format($userKrwAmt, $n_decimal_point_array['krw']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?=$userKrwAmt2?><?php } ?></td>
							<td><?php echo new_number_format($userEthAmt, $n_decimal_point_array['eth']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?=$userEthAmt2?><?php } ?></td>
							<td class="td_cls2">
								<?php
								$addr = new_get_waddr_arrs($row);
								foreach($addr as $key=>$addr2) {
									if ( !empty($addr2) ) {
										echo '('.$key.') '.$addr2.'<br />';
									}
								}
								?>
							</td>
							<td><?php echo $wallet_change_apply; ?></td>
							<td>
								<?php

								$db = getDbInstance();
								$db->where('order_num',  $row_list['payment_id']);
								$row2 = $db->getOne('kcp_order');
								echo '['.new_pay_type_change($_SESSION['lang'], $row2['order_pay']).']<br />';


								$gstatus = ''; // 결제완료정보
								if($row_list['gstatus']!="0"){
									if ( $row2['gva_date'] >= date("Y-m-d H:i:s") ) {
										echo !empty($langArr['coupon_list_text3']) ? $langArr['coupon_list_text3'] : "It is before deposit.";
										?>
										<br /><span><?=$row2['gbank_name']?></span> (<?=$row2['gaccount']?>)
										<br /><?php echo !empty($langArr['coupon_list_text4']) ? $langArr['coupon_list_text4'] : "Depositor"; ?> : <?=$row2['gdepositor']?>
										<br /><?php echo !empty($langArr['coupon_list_text5']) ? $langArr['coupon_list_text5'] : "Deposit amount"; ?> : <span class="virtual_account_font1"><?=number_format($row2['order_amount'],0)?></span> <?php echo !empty($langArr['krw_unit']) ? $langArr['krw_unit'] : "WON"; ?>
										<br /><?php echo !empty($langArr['coupon_list_text6']) ? $langArr['coupon_list_text6'] : "Deposit deadline"; ?> : <span class="virtual_account_font2"><?=$row2['gva_date']?></span>

										<?php
									} else {
										echo !empty($langArr['coupon_list_text13']) ? $langArr['coupon_list_text13'] : "The deposit deadline has passed.";
										?>
										<br /><?php echo !empty($langArr['coupon_list_text6']) ? $langArr['coupon_list_text6'] : "Deposit deadline"; ?> : <span class="virtual_account_font2"><?=$row2['gva_date']?></span>
										<?php
									}
								} else {
									$gstatus = 'Finish';
								}


								?>

							</td>
							<td>
								<?php
								if ( !empty($row_list['etc']) ) {
									?><a href="edit_fee_payment.php?coupon_result_id=<?php echo $row_list['id']; ?>&operation=edit" title="edit Message"><?php echo stripslashes($row_list['etc']); ?></a><?php
								} else {
									?><a href="edit_fee_payment.php?coupon_result_id=<?php echo $row_list['id']; ?>&operation=edit" title="edit Message"><?php echo !empty($langArr['admin_feepayment_message_set1']) ? $langArr['admin_feepayment_message_set1'] : "Add message"; ?></a><?php
								} ?>
							</td>
							<td>
								<a href="admin_user_approval.php?user_id=<?php echo $row['id']?>" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span></a>
							<?php
							if($row['login_or_not']=="Y") { ?>
								<a href=""  class="btn btn-primary" data-toggle="modal" data-target="#login_or_not_change_<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-remove"></span></a>
							<?php } else { ?>
								<a href=""  class="btn btn-primary" data-toggle="modal" data-target="#login_or_not_change_<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-ok"></span></a>
							<?php } ?>
							<br />
							<?php
							if ( $gstatus == 'Finish' && $row['transfer_approved'] != 'C' ) {
								if($row['wallet_change_apply']!="Y" && $row['id'] < 10900 ) { ?>
									<a href=""  class="btn btn-primary" data-toggle="modal" data-target="#change_address_<?php echo $row['id'] ?>" style="margin-right: 8px;">주소변경완료처리</a>
								<?php } ?>
								<a href="admin_user_approval_apply.php?user_id=<?php echo $row['id']?>&return_page=admin_users_fee_payment&page=<?php echo $page; ?>&search_string=<?php echo urlencode($search_string); ?>&gstatus_f=<?php echo urlencode($gstatus_f); ?>" class="btn btn-primary" target="_blank">Approve2</a><br />
								<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#transfer_approved_<?php echo $row['id'] ?>" style="margin-right: 8px;"><?php echo !empty($langArr['admin_fee_btn']) ? $langArr['admin_fee_btn'] : "Conversion complete"; ?></a>
							<?php } ?>


							</td>
						</tr>
			
							<!-- Update Login or Not Modal-->
								 <div class="modal fade" id="login_or_not_change_<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="multiprocess.php" method="POST">
									  	<input type="hidden" name="mode" value="admin_users_loginornot" />
										<input type="hidden" name="return_page" value="admin_users_fee_payment" />
										<input type="hidden" name="queries" value="<?php echo !empty($filterData) ? http_build_query($filterData) : ''; ?>" />
									  <!-- Modal content-->
										  <div class="modal-content">
											<div class="modal-header">
											  <button type="button" class="close" data-dismiss="modal">&times;</button>
											  <h4 class="modal-title">Confirm</h4>
											</div>
											<div class="modal-body">
												<input type="hidden" name="update_id" id = "update_id" value="<?php echo $row['id'] ?>">
												<p>Are you sure you want to update this user?</p>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-default pull-left">Yes</button>
												<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
											</div>
										  </div>
									  </form>
									</div>
								</div>


			
							<!-- Update Login or Not Modal-->
								 <div class="modal fade" id="change_address_<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="multiprocess.php" method="POST">
									  	<input type="hidden" name="mode" value="change_address_set" />
										<input type="hidden" name="return_page" value="admin_users_fee_payment" />
										<input type="hidden" name="search_string" value="<?php echo $search_string; ?>" />
										<input type="hidden" name="queries" value="<?php echo !empty($filterData) ? http_build_query($filterData) : ''; ?>" />
									  <!-- Modal content-->
										  <div class="modal-content">
											<div class="modal-header">
											  <button type="button" class="close" data-dismiss="modal">&times;</button>
											  <h4 class="modal-title">Confirm</h4>
											</div>
											<div class="modal-body">
												<input type="hidden" name="update_id" id = "update_id" value="<?php echo $row['id'] ?>">
												<p><?=$user_name?> / <?=$row['email']?> : <br />
												사용자 권한 초기화, User Approval 삭제처리<br />
												변경된 지갑 주소로 적용됩니다.(기존 지갑 주소는 사용자가 볼 수 없는 곳에 보관)<br /><br />												
												변경되지 않는 항목 : 수수료 CTC/ETH 사용여부, 로그인 허용여부<br />
												</p>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-default pull-left">Yes</button>
												<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
											</div>
										  </div>
									  </form>
									</div>
								</div>

							<!-- Update Login or Not Modal-->
								 <div class="modal fade" id="transfer_approved_<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="multiprocess.php" method="POST">
									  	<input type="hidden" name="mode" value="transfer_approved" />
										<input type="hidden" name="return_page" value="admin_users_fee_payment" />
										<input type="hidden" name="search_string" value="<?php echo $search_string; ?>" />
										<input type="hidden" name="queries" value="<?php echo !empty($filterData) ? http_build_query($filterData) : ''; ?>" />
										<input type="hidden" name="transfer_fee_type_change" value="Y" />
									  <!-- Modal content-->
										  <div class="modal-content">
											<div class="modal-header">
											  <button type="button" class="close" data-dismiss="modal">&times;</button>
											  <h4 class="modal-title">Approval of fees</h4>
											</div>
											<div class="modal-body">
												<input type="hidden" name="update_id" id = "update_id" value="<?php echo $row['id'] ?>">
												<p>
													<?=$user_name?>, <?=htmlspecialchars($row['email'])?><br />
													<?php echo !empty($langArr['admin_fee_message']) ? $langArr['admin_fee_message'] : "Would you like to convert your users to be able to pay their fees in CTC?"; ?>
												</p>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-default pull-left">Yes</button>
												<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
											</div>
										  </div>
									  </form>
									</div>
								</div>

						<?php $i++; } ?>   
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

<?php

function getMyCTCbalance($address, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$getBalance 	= 0;
	$coinBalance 	= 0;
	$EthCoinBalance	= 0;

	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
		
	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
	$contractAddress = 'address';
	
	
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		
		$coinBalance1 = $coinBalance/1000000000000000000;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	
 

function getMyETHBalance($walletAddress, $n_connect_ip, $n_connect_port) {
	
	$getBalance = 0;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$eth = $web3->eth;
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});
	return $getBalance/1000000000000000000;
}

function getMyCTC2balance($address, $testAbi, $contractAddress, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$coinBalance 	= 0;
	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		$coinBalance1 = $coinBalance/1000000000000000000;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	
 


function getMyTokenBalance($address,$testAbi,$contractAddress,$setDecimal, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$coinBalance 	= 0;
	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		
		$coinBalance1 = $coinBalance/$setDecimal;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	

include_once 'includes/footer.php'; ?>