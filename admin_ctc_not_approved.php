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

$db = getDbInstance();

$walletMasking = new walletMasking();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

//2021-08-05 XSS Filter by.ojt
$targetPostData = array(
    'search_string' => 'string',
    'del_id' => 'string',
    'filter_col' => 'string',
    'order_by' => 'string',
    'page' => 'string',
    'filter_limit' => 'string',
    'date1' => 'string',
    'date2' => 'string'
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

$date1 = filter_input(INPUT_GET, 'date1');
$date2 = filter_input(INPUT_GET, 'date2');
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
// select the columns
$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip','pvt_key','id_auth','login_or_not');

// $db->where('email', '%' . $search_string . '%', 'like');


// If user searches 
if($search_string) {
    /*
    if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){
        $db->orWhere('convert(name using utf8)', '%' . $search_string . '%', 'like');
        $db->orWhere('convert(auth_name using utf8)', '%' . $search_string . '%', 'like');
    } else {
        $db->orWhere('wallet_address', $search_string);
        $db->orWhere('email', '%' . $search_string . '%', 'like');
        $db->orWhere('phone', '%' . $search_string . '%', 'like');
        $db->orWhere('wallet_address', '%' . $search_string . '%', 'like');
        $db->orWhere('name', '%' . $search_string . '%', 'like');
        $db->orWhere('auth_name', '%' . $search_string . '%', 'like');
    }
    */
	if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){
		$db->orWhere('convert(name using utf8)',$search_string);
		$db->orWhere('convert(auth_name using utf8)',$search_string);
	} else {
		$db->orWhere('wallet_address', $search_string);
		$db->orWhere('email',$search_string);
		$db->orWhere('phone',$search_string);
		$db->orWhere('wallet_address',$search_string);
		$db->orWhere('name',$search_string);
		$db->orWhere('auth_name',$search_string);
	}
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > CTC Not Approved Users > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > CTC Not Approved Users > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
if ( !empty($date1) ) {
	$db->where('created_at', $date1.' 00:00:00', '>=');
}
if ( !empty($date2) ) {
	$db->where('created_at', $date2.' 23:59:59', '<=');
}

$db->where('sendapproved',  'Y');
$db->where('register_with',  'phone');
$db->where('admin_type',  'user');
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}
function dd($var='') {
	ob_start();
	var_export($var);
	$result = ob_get_clean();
	die($result);
}


$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("admin_accounts", $page, $select);
$total_pages = $db->totalPages;

// dd($resultData);


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
<script>
$(document).ready(function(){
	$('#date1').datepicker({format: "yyyy-mm-dd"});
	$('#date2').datepicker({format: "yyyy-mm-dd"});
});
</script>

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header"><?php echo !empty($langArr['ctc_not_approved_users']) ? $langArr['ctc_not_approved_users'] : "CTC Not Approved Users"; ?></h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>

    <?php
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Successfully deleted</div>';
    }
    ?>
    
    <!--    Begin filter section-->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for="input_search" >Search</label>
            <input type="text" placeholder="Email, Phone, WalletAddress, Name" class="form-control" id="input_search"  name="search_string" value="<?php echo $search_string; ?>">
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
<!-- 				<option <?php if ($pagelimit == 500) { echo "selected"; } ?>  value="500">500</option> -->
<!-- 				<option <?php if ($pagelimit == 1000000000000) { echo "selected"; } ?>  value="1000000000000">Show All</option> -->
			</select>

			
			<label for ="date1">Registered Date</label>
			<input type="text" name="date1" readonly value="<?php echo !empty($date1) ? $date1 : ''; ?>" placeholder="" class="form-control" id = "date1"> ~
			<input type="text" name="date2" readonly value="<?php echo !empty($date2) ? $date2 : ''; ?>" placeholder="" class="form-control" id = "date2">




            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>
    <!--   Filter section end-->
    <hr>
	 <!--<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a> | 
	<a class="btn btn-success" href="admin_users_export2.php?date1=<?php //echo !empty($date1) ? $date1 : ''; ?>&date2=<?php //echo !empty($date2) ? $date2 : ''; ?>&admin_type=user">Download CSV File(new) : User</a>-->
	<hr>

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
							<th>CTC(Old)</th>
							<th>CTC</th>
							<th>TP3</th>
							<th>USDT</th>
							<th>MC</th>
							<th>KRW</th>
							<th>ETH</th>
							<th><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></th>
							<th>Wallet Address</th>
							<?php // <th style="width:150px;">KYC</th> ?>
							<th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
						$totalCtcAmt = 0; 
					   $getPage = (isset($_GET['page']) && $_GET['page']>0) ? $_GET['page'] : 1 ;
						$i = (($getPage*$pagelimit)-$pagelimit)+1;
						foreach ($resultData as $row) {
							
							$userCtcAmt = 0;
							$userCtc2Amt = 0;
							$userTokenPayAmt = 0;
							$userUsdtAmt = 0;
							$userMcAmt = 0;
							$userKrwAmt = 0;
							$userEthAmt = 0;

							if ( !empty($row['wallet_address']) && strlen($row['wallet_address']) > 10 ) {
								$userCtcAmt = getMyCTCbalance($row['wallet_address'], $n_connect_ip, $n_connect_port);												
								$userCtc2Amt = $wi_wallet_infos->wi_get_balance('2', 'ctc', $row['wallet_address'], $contractAddressArr);
								$userTokenPayAmt = $wi_wallet_infos->wi_get_balance('2', 'tp3', $row['wallet_address'], $contractAddressArr);
								$userUsdtAmt = $wi_wallet_infos->wi_get_balance('2', 'usdt', $row['wallet_address'], $contractAddressArr);
								$userMcAmt = $wi_wallet_infos->wi_get_balance('2', 'mc', $row['wallet_address'], $contractAddressArr);
								$userKrwAmt = $wi_wallet_infos->wi_get_balance('2', 'krw', $row['wallet_address'], $contractAddressArr);
								$userEthAmt = $wi_wallet_infos->wi_get_balance('2', 'eth', $row['wallet_address'], $contractAddressArr);

							}

							$totalCtcAmt = $totalCtcAmt+$userCtcAmt;
							
							$pointSum = new_get_bee_point($row['id']);

							$id_auth = '';
							if ( !empty($row['id_auth']) && $row['id_auth'] == 'Y') {
								$id_auth = !empty($langArr['member_auth_finish']) ? $langArr['member_auth_finish'] : ' (Personal identification completed)';
							}
							?>
						
						<tr>
							<td><?php echo $i; ?> </td>
							<td class="td_cls1">
								<strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong>
                                <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name"><?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['lname']).htmlspecialchars($row['name'])); ?></span>
                                <?php if ( !empty($id_auth) ) { echo $id_auth; } ?><br />
								<strong><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?>:</strong> <?php echo htmlspecialchars($row['created_at']) ?><br />
								<strong>IP:</strong>
                                <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="ip">
                                        <?php echo $walletMasking->reset()->pushIpMask()->getMasked(htmlspecialchars($row['user_ip'])); ?>
                                </span><br />
								<strong>Login:</strong> 
								<?php
								if ( $row['login_or_not'] == 'N' ) {
									echo !empty($langArr['admin_users_text2']) ? $langArr['admin_users_text2'] : 'Impossible';
								} else {
									echo !empty($langArr['admin_users_text1']) ? $langArr['admin_users_text1'] : 'Possible';
								}
								?>
							</td>
							<td>
                                <?php if($row['phone']): ?>
                                    <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="phone">
                                        <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['phone'])); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
							<td><?php echo new_number_format($userCtcAmt, $n_decimal_point_array['ctc']); ?> </td>
							<td><?php echo new_number_format($userCtc2Amt, $n_decimal_point_array['ctc']); ?> </td>
							<td><?php echo new_number_format($userTokenPayAmt, $n_decimal_point_array['tp3']); ?> </td>
							<td><?php echo new_number_format($userUsdtAmt, $n_decimal_point_array['usdt']); ?> </td>
							<td><?php echo new_number_format($userMcAmt, $n_decimal_point_array['mc']); ?> </td>
							<td><?php echo new_number_format($userKrwAmt, $n_decimal_point_array['krw']); ?> </td>
							<td><?php echo new_number_format($userEthAmt, $n_decimal_point_array['eth']); ?> </td>
							<td><?php echo $pointSum ?> </td>				
							<td class="td_cls2"><?php echo htmlspecialchars($row['wallet_address']); ?></td>
							<?php /*<td>
								<strong>PAN NO : </strong><?php echo htmlspecialchars($row['pan_no']) ?><br/>
								<strong>BANK A/C NO : </strong><?php echo htmlspecialchars($row['bank_ac_no']) ?><br/>
								<strong>IFSC CODE : </strong><?php echo htmlspecialchars($row['ifsc_code']) ?><br/>
								<strong>BANK NAME : </strong> <?php echo htmlspecialchars($row['bank_name']) ?><br/>
							</td>*/ ?>
							<td>
								<a href="admin_user_approval.php?user_id=<?php echo $row['id']?>" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span></a>
							   <a href="edit_users.php?admin_user_id=<?php echo $row['id']?>&operation=edit" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
							<?php if($row['email_verify']=="N") { ?>
								<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>
							<?php }
							if($row['login_or_not']=="Y") { ?>
								<a href=""  class="btn btn-primary" data-toggle="modal" data-target="#login_or_not_change_<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-remove"></span></a>
							<?php } else { ?>
								<a href=""  class="btn btn-primary" data-toggle="modal" data-target="#login_or_not_change_<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-ok"></span></a>
							<?php } ?>
								
							</td>
						</tr>
							<!-- Delete Confirmation Modal-->
								 <div class="modal fade" id="confirm-delete-<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="delete_user.php" method="POST">
									  <!-- Modal content-->
										  <div class="modal-content">
											<div class="modal-header">
											  <button type="button" class="close" data-dismiss="modal">&times;</button>
											  <h4 class="modal-title">Confirm</h4>
											</div>
											<div class="modal-body">
												<input type="hidden" name="del_id" id = "del_id" value="<?php echo $row['id'] ?>">
												<p>Are you sure you want to delete this user?</p>
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
								 <div class="modal fade" id="login_or_not_change_<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
                                        <form action="multiprocess.php" method="POST">
                                            <input type="hidden" name="mode" value="admin_users_loginornot" />
                                            <input type="hidden" name="return_page" value="admin_ctc_not_approved" />
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
 





include_once 'includes/footer.php'; ?>