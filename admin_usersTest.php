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

//2021-08-03 XSS Filter by.ojt
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
//$search_string = filter_input(INPUT_GET, 'search_string');
//$del_id = filter_input(INPUT_GET, 'del_id');
//
//$filter_col = filter_input(INPUT_GET, 'filter_col');
//$order_by = filter_input(INPUT_GET, 'order_by');
//$page = filter_input(INPUT_GET, 'page');
//$pagelimit = filter_input(INPUT_GET, 'filter_limit');
//
//$date1 = filter_input(INPUT_GET, 'date1');
//$date2 = filter_input(INPUT_GET, 'date2');

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
//$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip','pvt_key','id_auth','login_or_not','auth_name','transfer_approved','wallet_address_change','wallet_change_apply', 'devId');

// add fields eToken
$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip','pvt_key','id_auth','login_or_not','auth_name','transfer_approved','wallet_address_change','wallet_change_apply', 'devId', 'etoken_ectc', 'etoken_etp3', 'etoken_emc', 'etoken_ekrw');

// $db->where('email', '%' . $search_string . '%', 'like');


// If user searches
if($search_string) {
    /*
    if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){ // utf-8인 경우
        $db->orWhere('convert(name using utf8)', '%' . $search_string . '%', 'like');
        $db->orWhere('convert(auth_name using utf8)', '%' . $search_string . '%', 'like');
    }
    else {
        //$db->orWhere('wallet_address', $search_string);
        $db->orWhere('email', '%' . $search_string . '%', 'like');
        $db->orWhere('phone', '%' . $search_string . '%', 'like');
        $db->orWhere('wallet_address', '%' . $search_string . '%', 'like');
        $db->orWhere('virtual_wallet_address', '%' . $search_string . '%', 'like');
        $db->orWhere('wallet_address_change', '%' . $search_string . '%', 'like');
        $db->orWhere('name', '%' . $search_string . '%', 'like');
        $db->orWhere('auth_name', '%' . $search_string . '%', 'like');
        $db->orWhere('auth_phone', '%' . $search_string . '%', 'like');
        $db->orWhere('external_phone', '%' . $search_string . '%', 'like');
    }
    */
    if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){ // utf-8인 경우
        $db->orWhere('convert(name using utf8)', $search_string);
        $db->orWhere('convert(auth_name using utf8)', $search_string);
    }
    else {
        //$db->orWhere('wallet_address', $search_string);
        $db->orWhere('email',$search_string);
        $db->orWhere('phone',$search_string);
        $db->orWhere('wallet_address',$search_string);
        $db->orWhere('virtual_wallet_address',$search_string);
        $db->orWhere('wallet_address_change',$search_string);
        $db->orWhere('name',$search_string);
        $db->orWhere('auth_name',$search_string);
        $db->orWhere('auth_phone',$search_string);
        $db->orWhere('external_phone',$search_string);
    }
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
if ( !empty($date1) ) {
    $db->where('created_at', $date1.' 00:00:00', '>=');
}
if ( !empty($date2) ) {
    $db->where('created_at', $date2.' 23:59:59', '<=');
}

//$db->where('admin_type',  'user');
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
$resultData = $db->arraybuilder()->paginate("admin_accounts", (int)$page);
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
                <h1 class="page-header"><?php echo !empty($langArr['registered_users']) ? $langArr['registered_users'] : "Registered Users"; ?></h1>
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
        <!-- 2021.06.28 엑셀 다운로드 기능 비활성화 by. OJT -->
        <!--	<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a> | -->
        <!--	<a class="btn btn-success" href="admin_users_export2.php?date1=--><?php //echo !empty($date1) ? $date1 : ''; ?><!--&date2=--><?php //echo !empty($date2) ? $date2 : ''; ?><!--&admin_type=user">Download CSV File(new) : User</a>-->
        <hr>

        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#user_first"><?php echo !empty($langArr['user_list']) ? $langArr['user_list'] : "User List"; ?></a></li>
            <li><a  href="admin_adminlist.php"><?php echo !empty($langArr['admin_list']) ? $langArr['admin_list'] : "Admin List"; ?></a></li>
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
                            <th><?php echo !empty($langArr['sr_no']) ? $langArr['sr_no'] : "Sr No."; ?></th>
                            <th><?php echo !empty($langArr['user_info']) ? $langArr['user_info'] : "User Info"; ?></th>
                            <th><?php echo !empty($langArr['email_phone']) ? $langArr['email_phone'] : "Email / Phone"; ?></th>
                            <th>CTC(Old)<!--<?php echo !empty($langArr['ctc_balance']) ? $langArr['ctc_balance'] : "CTC Balance"; ?>--></th>
                            <th>CTC<!--<?php echo !empty($langArr['ctc_balance']) ? $langArr['ctc_balance'] : "CTC Balance"; ?>(New)--></th>
                            <th>TP3<!--<?php echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "TP Balance"; ?>--></th>
                            <th>USDT<!--<?php echo !empty($langArr['usdt_balance']) ? $langArr['usdt_balance'] : "USDT Balance"; ?>--></th>
                            <th>MC<!--<?php echo !empty($langArr['mc_balance']) ? $langArr['mc_balance'] : "MC Balance"; ?>--></th>
                            <th>KRW<!--<?php echo !empty($langArr['krw_balance']) ? $langArr['krw_balance'] : "KRW Balance"; ?>--></th>
                            <th>ETH<!--<?php echo !empty($langArr['eth_balance']) ? $langArr['eth_balance'] : "ETH Balance"; ?>--></th>
                            <th><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></th>
                            <th>Wallet Address<!--<?php echo !empty($langArr['wallet_address']) ? $langArr['wallet_address'] : "Wallet Address"; ?>--></th>
                            <th>pvt key</th>
                            <th>Logs</th>
                            <th>Device ID/Number</th>
                            <?php // <th style="width:150px;">KYC</th> ?>
                            <th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php

                        $totalCtcAmt = 0;
                        //$getPage = (isset($_GET['page']) && $_GET['page']>0) ? $_GET['page'] : 1 ;
                        $getPage = $page;
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
                                $userCtc2Amt = getMyCTC2balance($row['wallet_address'], $testAbi, $contractAddress, $n_connect_ip, $n_connect_port);
                                $userTokenPayAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$tokenPayContractAddress,1000000000000000000, $n_connect_ip, $n_connect_port);
                                $userUsdtAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$usdtContractAddress,1000000, $n_connect_ip, $n_connect_port);
                                $userMcAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$marketCoinContractAddress,1000000, $n_connect_ip, $n_connect_port);
                                $userKrwAmt = getMyTokenBalance($row['wallet_address'],$tokenPayAbi,$koreanWonContractAddress,1000000, $n_connect_ip, $n_connect_port);
                                $userEthAmt = getMyETHBalance($row['wallet_address'], $n_connect_ip, $n_connect_port);
                                //$userCtc2Amt = $wi_wallet_infos->wi_get_balance('2', 'ctc', $row['wallet_address'], $contractAddressArr);
                                //$userTokenPayAmt = $wi_wallet_infos->wi_get_balance('2', 'tp3', $row['wallet_address'], $contractAddressArr);
                                //$userUsdtAmt = $wi_wallet_infos->wi_get_balance('2', 'usdt', $row['wallet_address'], $contractAddressArr);
                                //$userMcAmt = $wi_wallet_infos->wi_get_balance('2', 'mc', $row['wallet_address'], $contractAddressArr);
                                //$userKrwAmt = $wi_wallet_infos->wi_get_balance('2', 'krw', $row['wallet_address'], $contractAddressArr);
                                //$userEthAmt = $wi_wallet_infos->wi_get_balance('2', 'eth', $row['wallet_address'], $contractAddressArr);
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
                            }


                            $totalCtcAmt = $totalCtcAmt+$userCtcAmt;

                            //$db = getDbInstance();
                            //$db->where("user_id", $row['id']);
                            //$pointSum = $db->getValue("store_transactions", "sum(points)");
                            //$pointSum = ($pointSum == NULL ? '0.0000000' : $pointSum);
                            //$pointSum = rtrim($pointSum, 0);
                            //$pointSum = rtrim($pointSum, '.');

                            $pointSum = new_get_bee_point($row['id']);

                            //$id_auth = '';
                            //if ( !empty($row['id_auth']) && $row['id_auth'] == 'Y') {
                            //	$id_auth = !empty($langArr['member_auth_finish']) ? $langArr['member_auth_finish'] : ' (Personal identification completed)';
                            //}

                            $user_name = '';
                            $user_name = get_user_real_name($row['auth_name'], htmlspecialchars($row['name']), htmlspecialchars($row['lname']));

                            $transfer_approved = $row['transfer_approved']=='C' ? 'CTC' : 'ETH';
                            ?>

                            <tr>
                                <td rowspan="2"><?php echo $i; ?> </td>
                                <td rowspan="2" class="td_cls1">
                                    <strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong>
                                    <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name"><?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['lname']).htmlspecialchars($row['name'])); ?></span>
                                    <?php if ( $row['account_type2'] == $con_exchange_type_value) echo ' ['.$con_exchange_type_value.']'; ?><br />
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
                                    / <strong>Fee: </strong><?php echo $transfer_approved; ?>
                                </td>


                                <td rowspan="2">
                                    <?php if ( $row['account_type2'] == $con_exchange_type_value ): ?>
                                        <?php if(!empty($row['external_phone'])): ?>
                                            <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="externalPhone"><?php echo $walletMasking->reset()->pushPhoneMask('other')->getMasked($row['external_phone']); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
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
                                        <?php if($row['id_auth'] == 'Y'): ?>
                                            <br />
                                            (<?php echo !empty($langArr['personal_identification']) ? $langArr['personal_identification'] : 'Personal identification'; ?>)
                                            <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name">
                                            <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['auth_name'])); ?>
                                        </span>
                                            <br/>
                                            <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="authPhone">
                                            <?php echo $walletMasking->reset()->pushPhoneMask('other')->getMasked(htmlspecialchars($row['auth_phone'])); ?>
                                        </span>
                                        <?php endif; ?>
                                    <?php endif;?>
                                    <br /><a href="admin_sms_form.php?user_id=<?php echo $row['id']; ?>&type=sms" title="SMS Send">SMS Send</a><br /><a href="admin_sms_form.php?user_id=<?php echo $row['id']; ?>&type=push" title="Push Send">Push Send</a>
                                </td>
                                <td rowspan="2"><?php echo new_number_format($userCtcAmt, $n_decimal_point_array['ctc']); ?></td>
                                <td>
                                    <a href="token_adm.php?token=ctc&user_id=<?php echo $row['id']; ?>" target="_blank"><?php echo new_number_format($userCtc2Amt, $n_decimal_point_array['ctc']); ?></a>
                                    <?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($userCtc2Amt2, $n_decimal_point_array['ctc']); ?><?php } ?>
                                </td>
                                <td>
                                    <a href="token_adm.php?token=tp3&user_id=<?php echo $row['id']; ?>" target="_blank"><?php echo new_number_format($userTokenPayAmt, $n_decimal_point_array['tp3']); ?></a>
                                    <?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($userTokenPayAmt2, $n_decimal_point_array['tp3']); ?><?php } ?>
                                </td>
                                <td>
                                    <a href="token_adm.php?token=usdt&user_id=<?php echo $row['id']; ?>" target="_blank"><?php echo new_number_format($userUsdtAmt, $n_decimal_point_array['usdt']); ?></a>
                                    <?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($userUsdtAmt2, $n_decimal_point_array['usdt']); ?><?php } ?>
                                </td>
                                <td>
                                    <a href="token_adm.php?token=mc&user_id=<?php echo $row['id']; ?>" target="_blank"><?php echo new_number_format($userMcAmt, $n_decimal_point_array['mc']); ?></a>
                                    <?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($userMcAmt2, $n_decimal_point_array['mc']); ?><?php } ?>
                                </td>
                                <td>
                                    <a href="token_adm.php?token=krw&user_id=<?php echo $row['id']; ?>" target="_blank"><?php echo new_number_format($userKrwAmt, $n_decimal_point_array['krw']); ?></a>
                                    <?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($userKrwAmt2, $n_decimal_point_array['krw']); ?><?php } ?>
                                </td>
                                <td>
                                    <a href="token_adm.php?token=eth&user_id=<?php echo $row['id']; ?>" target="_blank"><?php echo new_number_format($userEthAmt, $n_decimal_point_array['eth']); ?></a>
                                    <?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($userEthAmt2, $n_decimal_point_array['eth']); ?><?php } ?>
                                </td>
                                <td rowspan="2"><?php echo $pointSum ?> </td>
                                <td rowspan="2" class="td_cls2">
                                    <?php
                                    /*if ( $row['id'] >= 10900 ) {
                                        echo htmlspecialchars($row['wallet_address']);
                                    } else {
                                        if ( $row['wallet_change_apply'] == 'Y' ) {
                                            echo '(New) '.htmlspecialchars($row['wallet_address']);
                                            if ( !empty($row['wallet_address_change']) ) {
                                                echo '<br />(Old) '.htmlspecialchars($row['wallet_address_change']);
                                            }
                                        } else {
                                            echo '(Old) '.htmlspecialchars($row['wallet_address']);
                                            if ( !empty($row['wallet_address_change']) ) {
                                                echo '<br />(New) '.htmlspecialchars($row['wallet_address_change']);
                                            }
                                        }
                                    }
                                    if ( !empty($row['virtual_wallet_address']) ) {
                                        echo '<br />(Virtual) '.$row['virtual_wallet_address'];
                                    }*/
                                    $addr = new_get_waddr_arrs($row);
                                    foreach($addr as $key=>$addr2) {
                                        if ( !empty($addr2) ) {
                                            echo '('.$key.') '.$addr2.'<br />';
                                        }
                                    }
                                    ?>
                                </td>
                                <td rowspan="2"><?php echo $row['pvt_key']; ?></td>
                                <td rowspan="2">
                                    <a href="sendlog_list.php?type=login&search_string=<?php echo $row['id'] ?>" title="login" class="btn btn-primary">login device</a><br />
                                    <a href="admin_etoken_logs.php?search_type=uid&search_string=<?php echo $row['id'] ?>" title="E-Pay"  class="btn btn-primary">E-Pay</a><br />
                                    <a href="admin_coupon_list.php?search_id=<?php echo $row['id']; ?>" title="payment"  class="btn btn-primary">Payment</a><br />
                                    <a href="store_transactions.php?search_type=user_id&search_string=<?php echo urlencode($row['id']); ?>" title="bee points logs"  class="btn btn-primary">bee points</a>
                                </td>
                                <td rowspan="2"><?php echo $row['device'].'<br />(C)'.$row['devId'].'<br />(B)'.$row['devId2']; ?>
                                    <?php if (trim($row['devId']!='') || trim($row['devId2']!='') || trim($row['devId3']!='') ) { ?>
                                        <a href="javascript:;" class="btn btn-danger delete_btn btn-sm" data-toggle="modal" data-target="#confirm-reset-<?php echo $row['id'] ?>"><span class="glyphicon glyphicon-remove">devId</span></a>
                                    <?php } ?>
                                </td>
                                <?php /*<td>
								<strong>PAN NO : </strong><?php echo htmlspecialchars($row['pan_no']) ?><br/>
								<strong>BANK A/C NO : </strong><?php echo htmlspecialchars($row['bank_ac_no']) ?><br/>
								<strong>IFSC CODE : </strong><?php echo htmlspecialchars($row['ifsc_code']) ?><br/>
								<strong>BANK NAME : </strong> <?php echo htmlspecialchars($row['bank_name']) ?><br/>
							</td>*/ ?>
                                <td rowspan="2">
                                    <a href="admin_user_approval.php?user_id=<?php echo $row['id']?>" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span></a>
                                    <a href="edit_users.php?admin_user_id=<?php echo $row['id']?>&operation=edit<?php if ( !empty($filterData) ) { echo '&'.http_build_query(http_build_query($filterData)); } ?>" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
                                    <?php if($row['email_verify']=="N") { ?>
                                        <!-- 2021.06.28 회원 삭제 기능 비활성화 -->
                                        <!--								<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete---><?php //echo $row['id'] ?><!--" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>-->
                                    <?php }
                                    if($row['login_or_not']=="Y") { ?>
                                        <a href=""  class="btn btn-primary" data-toggle="modal" data-target="#login_or_not_change_<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-remove"></span></a>
                                    <?php } else { ?>
                                        <a href=""  class="btn btn-primary" data-toggle="modal" data-target="#login_or_not_change_<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-ok"></span></a>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="etoken_adm.php?token=ectc&user_id=<?php echo $row['id']; ?>&user_type=" target="_blank"><?php if (isset($row['etoken_ectc'])) { echo number_format($row['etoken_ectc'],2); } ?></a></td>
                                <td><a href="etoken_adm.php?token=etp3&user_id=<?php echo $row['id']; ?>&user_type=" target="_blank"><?php if (isset($row['etoken_etp3'])) { echo number_format($row['etoken_etp3'],2); } ?></a>
                                    <?php if ( !empty($row['virtual_wallet_address']) ) { ?><br /><a href="etoken_adm.php?token=etp3&user_id=<?php echo $row['id']; ?>&user_type=virtual" target="_blank">store</a><?php } ?>
                                </td>
                                <td><a href="etoken_adm.php?token=eusdt&user_id=<?php echo $row['id']; ?>&user_type=" target="_blank"><?php if (isset($row['etoken_eusdt'])) { echo number_format($row['etoken_eusdt'],2); } ?></a></td>
                                <td><a href="etoken_adm.php?token=emc&user_id=<?php echo $row['id']; ?>&user_type=" target="_blank"><?php if (isset($row['etoken_emc'])) { echo number_format($row['etoken_emc'],2); } ?></a></td>
                                <td><a href="etoken_adm.php?token=ekrw&user_id=<?php echo $row['id']; ?>&user_type=" target="_blank"><?php if (isset($row['etoken_ekrw'])) { echo number_format($row['etoken_ekrw'],2); } ?></a></td>
                                <td><a href="etoken_adm.php?token=eeth&user_id=<?php echo $row['id']; ?>&user_type=" target="_blank"><?php if (isset($row['etoken_eeth'])) { echo number_format($row['etoken_eeth'],2); } ?></a></td>
                            </tr>
                            <!-- Reset Device ID Modal-->
                            <div class="modal fade" id="confirm-reset-<?php echo $row['id'] ?>" role="dialog">
                                <div class="modal-dialog">
                                    <form action="reset_device_id_user.php" method="POST">
                                        <input type="hidden" name="queries" value="<?php echo !empty($filterData) ? http_build_query($filterData) : ''; ?>" />
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Confirm</h4>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="reset_id" id="reset_id" value="<?php echo $row['id'] ?>">
                                                <p>Reset this member's device information?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-default pull-left">Yes</button>
                                                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

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
                                        <input type="hidden" name="return_page" value="admin_users" />
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