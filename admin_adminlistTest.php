<?php
//테스트 페이지 입니다. 20210805 반영 완료
exit();
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

$walletMasking = new walletMasking();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$db = getDbInstance();

//2021-08-05 XSS Filter by.ojt
$targetPostData = array(
    'search_string' => 'string',
    'del_id' => 'string',
    'filter_col' => 'string',
    'order_by' => 'string',
    'page' => 'string',
    'filter_limit' => 'string'
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
*/
if($pagelimit == "") {
    $pagelimit = 20;
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
$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip','id_auth');

// $db->where('email', '%' . $search_string . '%', 'like');

$db->Where('admin_type',  'admin');
// If user searches
if ($search_string) {
    /*
        $db->orWhere('email', '%' . $search_string . '%', 'like');
        $db->orWhere('phone', '%' . $search_string . '%', 'like');
        $db->orWhere('wallet_address', '%' . $search_string . '%', 'like');
        $db->orWhere('name', '%' . $search_string . '%', 'like');
    */

    $db->orWhere('email',$search_string);
    $db->orWhere('phone',$search_string);
    $db->orWhere('wallet_address',$search_string);
    $db->orWhere('name',$search_string);

    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 관리자 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 조회 > 관리자 목록',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


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

    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-6">
                <h1 class="page-header"><?php echo !empty($langArr['registered_admins']) ? $langArr['registered_admins'] : "Registered Admins"; ?></h1>
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

                <label for ="input_order">Limit</label>
                <select name="filter_limit" class="form-control">
                    <option <?php if ($pagelimit == 20) { echo "selected"; } ?> value="20">20</option>
                    <option <?php if ($pagelimit == 50) { echo "selected"; } ?>  value="50">50</option>
                    <!-- 				<option <?php if ($pagelimit == 500) { echo "selected"; } ?>  value="500">500</option> -->
                    <!-- 				<option <?php if ($pagelimit == 1000000000000) { echo "selected"; } ?>  value="1000000000000">Show All</option> -->
                </select>
                <input type="submit" value="Go" class="btn btn-primary">

            </form>
        </div>
        <!--   Filter section end-->
        <hr>
        <!-- 2021.06.28 엑셀 다운로드 기능 비활성화 by. OJT -->
        <!--	<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a> | -->
        <!--	<a class="btn btn-success" href="admin_users_export2.php?admin_type=admin">Download CSV File(new) : Admin</a>-->
        <hr>

        <ul class="nav nav-tabs">
            <li ><a href="admin_users.php"><?php echo !empty($langArr['user_list']) ? $langArr['user_list'] : "User List"; ?></a></li>
            <li class="active"><a data-toggle="tab" href="#user_first"><?php echo !empty($langArr['admin_list']) ? $langArr['admin_list'] : "Admin List"; ?></a></li>
            <li><a  href="admin_stores.php"><?php echo !empty($langArr['store_list']) ? $langArr['store_list'] : "Store List"; ?></a></li>
            <li><a  href="admin_user_exchange.php"><?php echo $con_exchange_type_value; ?></a></li>
            <li><a  href="admin_ctc_not_approved.php"><?php echo !empty($langArr['ctc_not_approved_users']) ? $langArr['ctc_not_approved_users'] : "Ctc Not Approved Users"; ?></a></li>
            <li><a  href="admin_fee_list.php"><?php echo !empty($langArr['change_fee_admin_tab_name']) ? $langArr['change_fee_admin_tab_name'] : "Fee conversion application list"; ?></a></li>
            <li><a  href="admin_change_address_users.php"><?php echo !empty($langArr['change_address_text1']) ? $langArr['change_address_text1'] : "address change application list"; ?></a></li>
            <li><a  href="admin_users_fee_type1.php"><?php echo !empty($langArr['admin_users_fee_type1_title']) ? $langArr['admin_users_fee_type1_title'] : "List with separate fees"; ?></a></li>
            <li><a  href="admin_users_fee_payment.php"><?php echo !empty($langArr['coupon_payment_admin_list']) ? $langArr['coupon_payment_admin_list'] : "Fee conversion payment list"; ?></a></li>
            <li><a  href="admin_users_epay.php"><?php echo !empty($langArr['admin_user_epay_list']) ? $langArr['admin_user_epay_list'] : "E-Pay Users List"; ?></a></li>
        </ul>

        <div class="tab-content">
            <div id="user_first" class="tab-pane fade in active">
                <div class="table-responsive">
                    <table class="table table-bordered admin_table_new">
                        <thead>
                        <tr>
                            <th><?php echo !empty($langArr['user_info']) ? $langArr['user_info'] : "User Info"; ?></th>
                            <th><?php echo !empty($langArr['email_phone']) ? $langArr['email_phone'] : "Email / Phone"; ?></th>
                            <th>CTC(Old)</th>
                            <th>CTC</th>
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

                        foreach ($resultData as $row) {

                            $userCtcAmt = 0;
                            $userCtc2Amt = 0;
                            $userEthAmt = 0;

                            if(!empty($row['wallet_address'])) {
                                $userCtcAmt = getMyCTCbalance($row['wallet_address'], $n_connect_ip, $n_connect_port);
                                $userCtc2Amt = $wi_wallet_infos->wi_get_balance('2', 'ctc', $row['wallet_address'], $contractAddressArr);
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
                                <td class="td_cls1">
                                    <strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong>
                                    <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name">
                                    <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['lname']).htmlspecialchars($row['name'])); ?>
                                </span>
                                    <?php if ( !empty($id_auth) ) { echo $id_auth; } ?>
                                </td>
                                <td>
                                    <?php if($row['email'] && $row['register_with']=='email'): ?>
                                        <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="email">
                                        <?php echo $walletMasking->reset()->pushEmailMask()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['email'])) ?>
                                        <br/>
                                    </span>
                                    <?php endif; ?>
                                    <?php if($row['phone']): ?>
                                        <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="phone">
                                        <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['phone'])); ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo new_number_format($userCtcAmt, $n_decimal_point_array['ctc']); ?> </td>
                                <td><?php echo new_number_format($userCtc2Amt, $n_decimal_point_array['ctc']); ?> </td>
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
                                    <a href="edit_users.php?admin_user_id=<?php echo $row['id']?>&operation=edit" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
                                    <?php if($row['email_verify']=="N") { ?>
                                        <!--<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>-->
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
                        <?php } ?>
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
    $coinBalance 	= 0;
    $walletAddress = $address;
    $web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');

    $testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';

    $contractAddress = 'address';

    $functionName = "balanceOf";
    $contract = new Contract($web3->provider, $testAbi);
    try {
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