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

// $db->where('email', '%' . $search_string . '%', 'like');


// If user searches 
if($search_string) {
    /*
    if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){
        $db->orWhere('convert(name using utf8)', '%' . $search_string . '%', 'like');
    } else {
        //$db->orWhere('wallet_address', $search_string);
        $db->orWhere('email', '%' . $search_string . '%', 'like');
        $db->orWhere('phone', '%' . $search_string . '%', 'like');
        $db->orWhere('wallet_address', '%' . $search_string . '%', 'like');
        $db->orWhere('name', '%' . $search_string . '%', 'like');
    }
    */

	if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){
		$db->orWhere('convert(name using utf8)',$search_string);
	} else {
		//$db->orWhere('wallet_address', $search_string);
		$db->orWhere('email',$search_string);
		$db->orWhere('phone',$search_string);
		$db->orWhere('wallet_address',$search_string);
		$db->orWhere('name',$search_string);
	}


    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 상점 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 상점 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
if ( !empty($date1) ) {
	$db->where('created_at', $date1.' 00:00:00', '>=');
}
if ( !empty($date2) ) {
	$db->where('created_at', $date2.' 23:59:59', '<=');
}

$db->where('admin_type',  'store');
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
$resultData = $db->arraybuilder()->paginate("admin_accounts", $page);
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

$db = getDbInstance();
$db->where('use_yn', 'wait');
$store_wallet_count = $db->getValue('store_wallet', 'count(*)');

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
<!--	<a class="btn btn-success" href="admin_users_export2.php?date1=--><?php //echo !empty($date1) ? $date1 : ''; ?><!--&date2=--><?php //echo !empty($date2) ? $date2 : ''; ?><!--&admin_type=store">Download CSV File(new) : Store</a>-->
	<hr>

    <!-- 등록 된 사용자 서브 메뉴 START-->
    <?php include_once WALLET_PATH.'/includes/adminUsersMenu.php'; ?>
    <!-- 등록 된 사용자 서브 메뉴 END-->

    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<p><?php echo !empty($langArr['store_wallet_remaining_quantity']) ? $langArr['store_wallet_remaining_quantity'] : "Remaining quantity available for allocating seller virtual address"; ?> : <?php echo $store_wallet_count; ?></p>

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
							<th><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></th>
							<th>Wallet Address</th>
							<?php // <th style="width:150px;">KYC</th> ?>
							<th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
						$totalCtcAmt = 0; 
					   $getPage = (isset($filterData['page']) && $filterData['page']>0) ? $filterData['page'] : 1 ;
						$i = (($getPage*$pagelimit)-$pagelimit)+1;
						foreach ($resultData as $row) {
							
							$getbalances = [];
							$getbalances_vir = [];

							if(!empty($row['wallet_address'])  && strlen($row['wallet_address']) > 10 ) {
								$getbalances = $wi_wallet_infos->wi_get_balance('', 'all', $row['wallet_address'], $contractAddressArr);
							}

							if(!empty($row['virtual_wallet_address'])  && strlen($row['virtual_wallet_address']) > 10 ) {
								$getbalances_vir = $wi_wallet_infos->wi_get_balance('', 'all', $row['virtual_wallet_address'], $contractAddressArr);
							}
							
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
                                <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="name">
                                    <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['lname']).htmlspecialchars($row['name'])); ?>
                                </span>
                                <?php if ( !empty($id_auth) ) { echo $id_auth; } ?><br />

								<strong><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?>:</strong> <?php echo htmlspecialchars($row['created_at']) ?><br />
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
							<td><?php echo !empty($getbalances['ctc']) ? new_number_format($getbalances['ctc'], $n_decimal_point_array['ctc']) : 0; echo !empty($getbalances_vir['ctc']) ? '<br />'.new_number_format($getbalances_vir['ctc'], $n_decimal_point_array['ctc']) : 0; ?></td>
							<td><?php echo !empty($getbalances['tp3']) ? new_number_format($getbalances['tp3'], $n_decimal_point_array['tp3']) : 0; echo !empty($getbalances_vir['tp3']) ? '<br />'.new_number_format($getbalances_vir['tp3'], $n_decimal_point_array['tp3']) : 0; ?></td>
							<td><?php echo !empty($getbalances['usdt']) ? new_number_format($getbalances['usdt'], $n_decimal_point_array['usdt']) : 0; echo !empty($getbalances_vir['usdt']) ? '<br />'.new_number_format($getbalances_vir['usdt'], $n_decimal_point_array['usdt']) : 0; ?></td>
							<td><?php echo !empty($getbalances['mc']) ? new_number_format($getbalances['mc'], $n_decimal_point_array['mc']) : 0; echo !empty($getbalances_vir['mc']) ? '<br />'.new_number_format($getbalances_vir['mc'], $n_decimal_point_array['mc']) : 0; ?></td>
							<td><?php echo !empty($getbalances['krw']) ? new_number_format($getbalances['krw'], $n_decimal_point_array['krw']) : 0; echo !empty($getbalances_vir['krw']) ? '<br />'.new_number_format($getbalances_vir['krw'], $n_decimal_point_array['krw']) : 0; ?></td>
							<td><?php echo !empty($getbalances['eth']) ? new_number_format($getbalances['eth'], $n_decimal_point_array['eth']) : 0; echo !empty($getbalances_vir['eth']) ? '<br />'.new_number_format($getbalances_vir['eth'], $n_decimal_point_array['eth']) : 0; ?></td>
							<td><?php echo $pointSum ?> </td>				
							<td class="td_cls2">
								<?php
								$addr = new_get_waddr_arrs($row);
								foreach($addr as $key=>$addr2) {
									if ( !empty($addr2) ) {
										echo '('.$key.') '.$addr2.'<br />';
									}
								}

								//echo htmlspecialchars($row['wallet_address']);
								//echo !empty($getbalances_vir) ? '<br />(Virtual) '.htmlspecialchars($row['virtual_wallet_address']) : '';
								?>
							</td>
							<td>
							<a href="admin_user_approval.php?user_id=<?php echo $row['id']?>" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span></a>
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