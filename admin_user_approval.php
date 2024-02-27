<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}
$userId = filter_input(INPUT_GET, 'user_id');
if(empty($userId)){
	header("Location:admin_users.php");
	exit();
}

$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

//Get data from query string
$del_id = filter_input(INPUT_GET, 'del_id');

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
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

$db->where('user_id',  $userId);
//$walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 수수료 변환신청 결제 리스트 > User Approval',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
$walletLogger->info('관리자 모드 > User Approval',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
if ($order_by) {
	//$db->orderBy($filter_col, $order_by);
	$db->orderBy('del', 'asc')->orderBy($filter_col, $order_by);
}
function dd($var='') {
	ob_start();
	var_export($var);
	$result = ob_get_clean();
	die($result);
}

if ( $_SESSION['user_id'] != '5137' ) {
	$db->where('del',  'use');
}

$resultData = $db->get('ethsend');


// dd($resultData);

include_once 'includes/header.php';

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$m_email = '';
$m_waddr = '';
$m_last_login_at = '';
$eth_balance = 0;
$db = getDbInstance();
$db->where('id',  $userId);
$rowm = $db->getOne('admin_accounts');

$approve = array();
if ( !empty($rowm['id']) ) {
	$m_email = $rowm['email'];
	$m_waddr = $rowm['wallet_address'];
	$m_last_login_at = $rowm['last_login_at'];
	if ( !empty($rowm['wallet_address']) ) {
		$eth_balance = $wi_wallet_infos->wi_get_balance('2', 'eth', $rowm['wallet_address'], $contractAddressArr);
	}
	foreach($n_decimal_point_array as $t1=>$v1) {
		if ( $t1 != 'eth' ) {
			$approve[$t1] = $t1.' : ';
			if ( $t1 == 'ctc') {
				$approve[$t1] .= $rowm['sendapproved'] == 'Y' ? 'Y' : 'N';
			} else if ( $t1 == 'tp3' ) {
				$approve[$t1] .= $rowm['tp_approved'] == 'Y' ? 'Y' : 'N';
			} else {
				$approve[$t1] .= $rowm[$t1.'_approved'] == 'Y' ? 'Y' : 'N';
			}
		}
	}
}
					
?>
<link href="css/admin.css" rel="stylesheet">

<div id="page-wrapper" class="admin_list_table1">
	<div class="row">
		<div class="col-lg-6">
			<h1 class="page-header"><?php echo !empty($langArr['user_approval']) ? $langArr['user_approval'] : "User Approval"; ?></h1>
		</div>
	</div>
	<?php
	include('./includes/flash_messages.php');
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Successfully deleted</div>';
    }
    ?>
    
   <ul>
		<li>Email : <?php echo $m_email; ?></li>
		<li>Wallet Address : <?php echo !empty($m_waddr) ? '<a href="https://etherscan.io/address/'.$m_waddr.'" title="etherscan" target="_blank">'.$m_waddr.'</a>' : ''; ?></li>
		<li>Last Login date : <?php echo $m_last_login_at; ?></li>
		<li>Eth Balance : <?php echo new_number_format($eth_balance, $n_decimal_point_array['eth']); ?></li>
		<li><?php echo implode(', ', $approve); ?></li>
   </ul>

    <hr />
	

	<ul class="nav nav-tabs">
	    <li class="active"><a data-toggle="tab" href="#user_first"><?php echo !empty($langArr['user_approval']) ? $langArr['user_approval'] : "User Approval"; ?></a></li>
	    <li><a  href="admin_user_airdrop.php?user_id=<?php echo $userId; ?>">User Airdrop</a></li>
	  </ul>

    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered">
					<colgroup>
						<col width="3%" />
						<col width="5%" />
						<col width="8%" />
						<col width="24%" />
						<col width="10%" />
						<col width="15%" />
						<col width="15%" />
						<col width="10%" />
						<col width="5%" />
						<col width="5%" />
					</colgroup>
					<thead>
						<tr>
							 <th><?php echo !empty($langArr['sr_no']) ? $langArr['sr_no'] : "Sr No."; ?></th>
							 <th><?php echo !empty($langArr['token']) ? $langArr['token'] : "Token"; ?></th>
							 <th><?php echo !empty($langArr['status']) ? $langArr['status'] : "status"; ?></th>
							<th><?php echo !empty($langArr['tx_id']) ? $langArr['tx_id'] : "Tx id"; ?></th>
							<th><?php echo !empty($langArr['ethmethod']) ? $langArr['ethmethod'] : "Eth method"; ?></th>
							<th><?php echo !empty($langArr['from_address']) ? $langArr['from_address'] : "From Address"; ?></th>
							<th><?php echo !empty($langArr['to_address']) ? $langArr['to_address'] : "To address"; ?></th>
							<th>Date</th>
							<th>Deleted</th>
							<th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
						$totalCtcAmt = 0; 
					
						$i = 1;
						foreach ($resultData as $row) {
						
						// Only 'Completed' and 'Failed' are stored in the database.
						if ( !empty($row['status']) ) {
							$status = $row['status'];
						} else {
							$status = $wi_wallet_infos->wi_get_status($row['tx_id']);
							if ( $status == 'Completed' || $status == 'Failed' ) {
								$updateArr = [];
								$updateArr['status'] = $status;
								$db = getDbInstance();
								$db->where("id", $row['id']);
								$last_id = $db->update('ethsend', $updateArr);
							}
						}

						$deleted = '';
						if ( $row['del'] == 'del' ) {
							$deleted = 'Deleted';
							if ( !empty($row['deleted_at']) ) { $deleted .= '<br />('.$row['deleted_at'].')'; }
						} else {
							$deleted = 'Used';
						}
						
						?>
						
						<tr>
							<td><?php echo $i; ?> </td>
							<td><?php echo $row['coin_type']; ?></td>
							<td><?php echo $status; ?></td>
							<td><a target="_blank" href="https://etherscan.io/tx/<?php echo $row['tx_id']; ?>"><?php echo $row['tx_id']; ?></a></td>
							<td><?php echo $row['ethmethod']; ?></td>
							<td><?php echo $row['from_address']; ?></td>
							<td><?php echo $row['to_address']; ?></td>
							<td><?php echo $row['created']; ?></td>
							<td><?php echo $deleted; ?></td>
							<td>
								<?php if ( $deleted == 'Used') { ?>
									<a href="javascript:void(0);"  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>
								<?php } ?>
								
							</td>
						</tr>
							<!-- Delete Confirmation Modal-->
								 <div class="modal fade" id="confirm-delete-<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="delete_approval_tx.php" method="POST">
									  <!-- Modal content-->
										  <div class="modal-content">
											<div class="modal-header">
											  <button type="button" class="close" data-dismiss="modal">&times;</button>
											  <h4 class="modal-title">Confirm</h4>
											</div>
											<div class="modal-body">
												<input type="hidden" name="del_id" id = "del_id" value="<?php echo $row['id'] ?>">
												<input type="hidden" name="user_id" id = "user_id" value="<?php echo $userId; ?>">
												<p>Are you sure you want to delete this transaction?</p>
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

	    </div>
	</div>

</div>

<?php
include_once 'includes/footer.php'; ?>