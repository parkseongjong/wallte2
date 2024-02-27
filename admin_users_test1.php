<?php
// Test Page
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

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

$db = getDbInstance();
//Get data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$del_id = filter_input(INPUT_GET, 'del_id');

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');

$date1 = filter_input(INPUT_GET, 'date1');
$date2 = filter_input(INPUT_GET, 'date2');

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
	if (preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $search_string)){ // utf-8ÀÎ °æ¿ì
		$db->orWhere('convert(name using utf8)', '%' . $search_string . '%', 'like');
		$db->orWhere('convert(auth_name using utf8)', '%' . $search_string . '%', 'like');
	} else {
		$db->orWhere('email', '%' . $search_string . '%', 'like');
		$db->orWhere('phone', '%' . $search_string . '%', 'like');
		$db->orWhere('wallet_address', '%' . $search_string . '%', 'like');
		$db->orWhere('wallet_address_change', '%' . $search_string . '%', 'like');
		$db->orWhere('name', '%' . $search_string . '%', 'like'); 
		$db->orWhere('auth_name', '%' . $search_string . '%', 'like'); 
	}
}
if ( !empty($date1) ) {
	$db->where('created_at', $date1.' 00:00:00', '>=');
}
if ( !empty($date2) ) {
	$db->where('created_at', $date2.' 23:59:59', '<=');
}

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


include_once 'includes/header.php';
?>

<link href="css/admin.css" rel="stylesheet">

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
			<input type="text" name="date1" readonly value="<?php echo !empty($_GET['date1']) ? $_GET['date1'] : ''; ?>" placeholder="" class="form-control" id = "date1"> ~ 
			<input type="text" name="date2" readonly value="<?php echo !empty($_GET['date2']) ? $_GET['date2'] : ''; ?>" placeholder="" class="form-control" id = "date2">




            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>
    <!--   Filter section end-->
    <hr>
	<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a> | 
	<a class="btn btn-success" href="admin_users_export2.php?date1=<?php echo !empty($_GET['date1']) ? $_GET['date1'] : ''; ?>&date2=<?php echo !empty($_GET['date2']) ? $_GET['date2'] : ''; ?>&admin_type=user">Download CSV File(new) : User</a>
	<hr>

	<ul class="nav nav-tabs">
	    <li class="active"><a data-toggle="tab" href="#user_first"><?php echo !empty($langArr['user_list']) ? $langArr['user_list'] : "User List"; ?></a></li>
	    <li><a  href="admin_adminlist.php"><?php echo !empty($langArr['admin_list']) ? $langArr['admin_list'] : "Admin List"; ?></a></li>
	    <li><a  href="admin_stores.php"><?php echo !empty($langArr['store_list']) ? $langArr['store_list'] : "Store List"; ?></a></li>
	    <li><a  href="admin_ctc_not_approved.php"><?php echo !empty($langArr['ctc_not_approved_users']) ? $langArr['ctc_not_approved_users'] : "Ctc Not Approved Users"; ?></a></li>
	    <li><a  href="admin_fee_list.php"><?php echo !empty($langArr['change_fee_admin_tab_name']) ? $langArr['change_fee_admin_tab_name'] : "Fee conversion application list"; ?></a></li>
	    <li><a  href="admin_change_address_users.php"><?php echo !empty($langArr['change_address_text1']) ? $langArr['change_address_text1'] : "address change application list"; ?></a></li>
	  </ul>

    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							 <th><?php echo !empty($langArr['sr_no']) ? $langArr['sr_no'] : "Sr No."; ?></th>
							<th><?php echo !empty($langArr['user_info']) ? $langArr['user_info'] : "User Info"; ?></th>
							<th><?php echo !empty($langArr['email_phone']) ? $langArr['email_phone'] : "Email / Phone"; ?></th>
							<th><?php echo !empty($langArr['ctc_balance']) ? $langArr['ctc_balance'] : "CTC Balance"; ?>(New)</th>
							<th><?php echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "TP Balance"; ?></th>
							<th><?php echo !empty($langArr['usdt_balance']) ? $langArr['usdt_balance'] : "USDT Balance"; ?></th>
							<th><?php echo !empty($langArr['mc_balance']) ? $langArr['mc_balance'] : "MC Balance"; ?></th>
							<th><?php echo !empty($langArr['krw_balance']) ? $langArr['krw_balance'] : "KRW Balance"; ?></th>
							<th><?php echo !empty($langArr['eth_balance']) ? $langArr['eth_balance'] : "ETH Balance"; ?></th>
							<th><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></th>
							<th><?php echo !empty($langArr['wallet_address']) ? $langArr['wallet_address'] : "Wallet Address"; ?></th>
							<th>pvt key</th>
							<th>dev id</th>
							<?php // <th style="width:150px;">KYC</th> ?>
							<th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
					   $getPage = (isset($_GET['page']) && $_GET['page']>0) ? $_GET['page'] : 1 ;
						$i = (($getPage*$pagelimit)-$pagelimit)+1;
						foreach ($resultData as $row) {
							
							$userCtcAmt = 0;

							if ( !empty($row['wallet_address']) && strlen($row['wallet_address']) > 10 ) {
								$getbalances = $wi_wallet_infos->wi_get_balance('', 'all', $row['wallet_address'], $contractAddressArr);
							}
							if ( !empty($row['wallet_address_change']) && strlen($row['wallet_address_change']) > 10 ) {
								$getbalances2 = $wi_wallet_infos->wi_get_balance('', 'all', $row['wallet_address_change'], $contractAddressArr);		
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
							?>
						
						<tr>
							<td rowspan="2"><?php echo $i; ?> </td>
							<td rowspan="2" class="td_cls1">
								<strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong> <?php echo $user_name; ?><?php if ( !empty($id_auth) ) { echo $id_auth; } ?><br />
								<strong><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?>:</strong> <?php echo htmlspecialchars($row['created_at']) ?><br />
								<strong>IP:</strong> <?php echo htmlspecialchars($row['user_ip']) ?><br />
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


							<td rowspan="2"><?php echo ($row['register_with']=='email') ? htmlspecialchars($row['email']).'<br />' : "" ?> <?php echo htmlspecialchars($row['phone']) ?></td>
							<td><?php echo new_number_format($getbalances['ctc'], $n_decimal_point_array['ctc']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($getbalances2['ctc'], $n_decimal_point_array['ctc']); ?><?php } ?></td>
							<td><?php echo new_number_format($getbalances['tp3'], $n_decimal_point_array['tp3']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($getbalances2['tp3'], $n_decimal_point_array['tp3']); ?><?php } ?></td>
							<td><?php echo new_number_format($getbalances['usdt'], $n_decimal_point_array['usdt']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($getbalances2['usdt'], $n_decimal_point_array['usdt']); ?><?php } ?></td>
							<td><?php echo new_number_format($getbalances['mc'], $n_decimal_point_array['mc']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($getbalances2['mc'], $n_decimal_point_array['mc']); ?><?php } ?></td>
							<td><?php echo new_number_format($getbalances['krw'], $n_decimal_point_array['krw']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($getbalances2['krw'], $n_decimal_point_array['krw']); ?><?php } ?></td>
							<td rowspan="2"><?php echo new_number_format($getbalances['eth'], $n_decimal_point_array['eth']); ?><?php if ( !empty($row['wallet_address_change']) ) { ?><br /><?php echo new_number_format($getbalances2['eth'], $n_decimal_point_array['eth']); ?><?php } ?></td>
							<td rowspan="2"><?php echo $pointSum ?> </td>				
							<td rowspan="2" class="td_cls2">
									<?php
									if ( $row['id'] >= 10900 ) {
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
								?>
							</td>
							<td rowspan="2"><?php echo $row['pvt_key']; ?></td>
							<td rowspan="2"><?php echo $row['devId']; ?>
                                <?php if (trim($row['devId']!='')) { ?>
                                <a href="javascript:;" class="btn btn-danger delete_btn btn-sm" data-toggle="modal" data-target="#confirm-reset-<?php echo $row['id'] ?>"><span class="glyphicon glyphicon-remove">devId</span></a>
                                <?php } ?>
                            </td>
							<td rowspan="2">
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
								<a href="admin_etoken_logs.php?search_type=uid&search_string=<?php echo $row['id'] ?>" class="btn btn-primary">eToken Logs</a>
							</td>
						</tr>
                        <tr>
                            <td><?php echo new_number_format($row['etoken_ectc'], $n_decimal_point_array2['ectc']); ?></td>
                            <td><?php echo new_number_format($row['etoken_etp3'], $n_decimal_point_array2['etp3']); ?></td>
                            <td></td>
                            <td><?php echo new_number_format($row['etoken_emc'], $n_decimal_point_array2['emc']); ?></td>
                            <td><?php echo new_number_format($row['etoken_ekrw'], $n_decimal_point_array2['ekrw']); ?></td>
						</tr>
							<!-- Reset Device ID Modal-->
								 <div class="modal fade" id="confirm-reset-<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="reset_device_id_user.php" method="POST">
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
										<input type="hidden" name="queries" value="<?php echo !empty($_GET) ? http_build_query($_GET) : ''; ?>" />
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
				if ( isset($_GET) &&!empty($_GET) ) {
					$get_infos = $_GET;
					if (isset($_GET['page']) && !empty($_GET['page'])) {
						$currentPage = $_GET['page'];
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