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

//2021-08-06 XSS Filter by.ojt
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

// add fields eToken
$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip','pvt_key','id_auth','login_or_not','auth_name','transfer_approved','wallet_address_change','wallet_change_apply', 'devId', 'etoken_ectc', 'etoken_etp3', 'etoken_emc', 'etoken_ekrw');

// $db->where('email', '%' . $search_string . '%', 'like');


// If user searches 
if($search_string) {
    /*
	if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){ // utf-8인 경우
		$db->orWhere('convert(name using utf8)', '%' . $search_string . '%', 'like');
		$db->orWhere('convert(auth_name using utf8)', '%' . $search_string . '%', 'like');
	} else {
		//$db->orWhere('wallet_address', $search_string);
		$db->orWhere('email', '%' . $search_string . '%', 'like');
		$db->orWhere('phone', '%' . $search_string . '%', 'like');
		$db->orWhere('wallet_address', '%' . $search_string . '%', 'like');
		$db->orWhere('virtual_wallet_address', '%' . $search_string . '%', 'like');
		$db->orWhere('wallet_address_change', '%' . $search_string . '%', 'like');
		$db->orWhere('name', '%' . $search_string . '%', 'like'); 
		$db->orWhere('auth_name', '%' . $search_string . '%', 'like'); 
	}
    */

	if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){ // utf-8인 경우
		$db->orWhere('convert(name using utf8)',$search_string);
		$db->orWhere('convert(auth_name using utf8)',$search_string);
	} else {
		//$db->orWhere('wallet_address', $search_string);
		$db->orWhere('email',$search_string);
		$db->orWhere('phone',$search_string);
		$db->orWhere('wallet_address',$search_string);
		$db->orWhere('virtual_wallet_address',$search_string);
		$db->orWhere('wallet_address_change',$search_string);
		$db->orWhere('name',$search_string);
		$db->orWhere('auth_name',$search_string);
	}

    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > Coin IBT 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > Coin IBT 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


$db->where('account_type2',  $con_exchange_type_value);
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("admin_accounts", $page);
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

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header"><?php echo !empty($langArr['registered_users']) ? $langArr['registered_users'] : "Registered Users"; ?></h1>
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>

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
			
            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>
    <!--   Filter section end-->

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
							<th>CTC</th>
							<th>TP3</th>
							<th>USDT</th>
							<th>MC</th>
							<th>KRW</th>
							<th>ETH</th>
							<th>Wallet Address</th>
							<th>Logs</th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
					   $getPage = (isset($filterData['page']) && $filterData['page']>0) ? $filterData['page'] : 1 ;
						$i = (($getPage*$pagelimit)-$pagelimit)+1;
						foreach ($resultData as $row) {
							
							$userCtc2Amt = 0;
							$userTokenPayAmt = 0;
							$userUsdtAmt = 0;
							$userMcAmt = 0;
							$userKrwAmt = 0;
							$userEthAmt = 0;

							if ( !empty($row['wallet_address']) && strlen($row['wallet_address']) > 10 ) {
								$userCtc2Amt = $wi_wallet_infos->wi_get_balance('2', 'ctc', $row['wallet_address'], $contractAddressArr);
								$userTokenPayAmt = $wi_wallet_infos->wi_get_balance('2', 'tp3', $row['wallet_address'], $contractAddressArr);
								$userUsdtAmt = $wi_wallet_infos->wi_get_balance('2', 'usdt', $row['wallet_address'], $contractAddressArr);
								$userMcAmt = $wi_wallet_infos->wi_get_balance('2', 'mc', $row['wallet_address'], $contractAddressArr);
								$userKrwAmt = $wi_wallet_infos->wi_get_balance('2', 'krw', $row['wallet_address'], $contractAddressArr);
								$userEthAmt = $wi_wallet_infos->wi_get_balance('2', 'eth', $row['wallet_address'], $contractAddressArr);
							}
							$user_name = '';
							$user_name = get_user_real_name($row['auth_name'], htmlspecialchars($row['name']), htmlspecialchars($row['lname']));
							
							?>
						
						<tr>
							<td rowspan="2"><?php echo $i; ?> </td>
							<td rowspan="2" class="td_cls1">
								<strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong>
                                <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name"><?php echo $walletMasking->reset()->pushNameMask()->getMasked($user_name); ?></span>
                                <?php if ( $row['account_type2'] == $con_exchange_type_value) echo ' ['.$con_exchange_type_value.']'; ?><br />
								<strong><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?>:</strong> <?php echo htmlspecialchars($row['created_at']) ?><br />
								<strong><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?>:</strong>
                                <?php if(!empty($row['external_phone'])): ?>
                                    <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="externalPhone"><?php echo $walletMasking->reset()->pushPhoneMask('other')->getMasked($row['external_phone']); ?></span>
							    <?php endif; ?>
                            </td>
							<td><?php echo new_number_format($userCtc2Amt, $n_decimal_point_array['ctc']); ?></td>
							<td><?php echo new_number_format($userTokenPayAmt, $n_decimal_point_array['tp3']); ?></td>
							<td><?php echo new_number_format($userUsdtAmt, $n_decimal_point_array['usdt']); ?></td>
							<td><?php echo new_number_format($userMcAmt, $n_decimal_point_array['mc']); ?></td>
							<td><?php echo new_number_format($userKrwAmt, $n_decimal_point_array['krw']); ?></td>
							<td><?php echo new_number_format($userEthAmt, $n_decimal_point_array['eth']); ?></td>			
							<td rowspan="2" class="td_cls2">
								<?php
									echo htmlspecialchars($row['wallet_address']);
								?>
							</td>
							<td rowspan="2">
								<a href="admin_etoken_logs.php?search_type=uid&search_string=<?php echo $row['id'] ?>" title="E-Pay"  class="btn btn-primary">E-Pay</a><br />
							</td>
						</tr>
                        <tr>
                            <td><a href="etoken_adm.php?token=ectc&user_id=<?php echo $row['id']; ?>&user_type="><?php if (isset($row['etoken_ectc'])) { echo number_format($row['etoken_ectc'],2); } ?></a>
							</td>
                            <td><a href="etoken_adm.php?token=etp3&user_id=<?php echo $row['id']; ?>&user_type="><?php if (isset($row['etoken_etp3'])) { echo number_format($row['etoken_etp3'],2); } ?></a>
								<?php if ( !empty($row['virtual_wallet_address']) ) { ?><br /><a href="etoken_adm.php?token=etp3&user_id=<?php echo $row['id']; ?>&user_type=virtual">store</a><?php } ?>
							</td>
                            <td><a href="etoken_adm.php?token=eusdt&user_id=<?php echo $row['id']; ?>&user_type="><?php if (isset($row['etoken_eusdt'])) { echo number_format($row['etoken_eusdt'],2); } ?></a></td>
                            <td><a href="etoken_adm.php?token=emc&user_id=<?php echo $row['id']; ?>&user_type="><?php if (isset($row['etoken_emc'])) { echo number_format($row['etoken_emc'],2); } ?></a></td>
                            <td><a href="etoken_adm.php?token=ekrw&user_id=<?php echo $row['id']; ?>&user_type="><?php if (isset($row['etoken_ekrw'])) { echo number_format($row['etoken_ekrw'],2); } ?></a></td>
                            <td><a href="etoken_adm.php?token=eeth&user_id=<?php echo $row['id']; ?>&user_type="><?php if (isset($row['etoken_eeth'])) { echo number_format($row['etoken_eeth'],2); } ?></a></td>
						</tr>

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
include_once 'includes/footer.php'; ?>