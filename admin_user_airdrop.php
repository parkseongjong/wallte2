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

$walletLogger->info('관리자 모드 > User Airdrop',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

//Get data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$del_id = filter_input(INPUT_GET, 'del_id');

$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
if($pagelimit == "") {
	$pagelimit = 10;
}
if ($page == "") {
    $page = 1;
}
// If filter types are not selected we show latest added data first
$filter_col = "id";
$order_by = "desc";

function dd($var='') {
	ob_start();
	var_export($var);
	$result = ob_get_clean();
	die($result);
}

$db->where('send_type',  'register');
$db->where('to_id',  $userId);
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}
$resultData = $db->get('user_transactions_all');



$db = getDbInstance();
$db->where('user_id',  $userId);
$db->where('send_type',  'airdrop');
$resultData2 = $db->get('etoken_logs');

include_once 'includes/header.php';

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$m_email = '';
$m_waddr = '';
$ctc_balance = 0;
$tp3_balance = 0;
$db = getDbInstance();
$db->where('id',  $userId);
$rowm = $db->getOne('admin_accounts');

if ( !empty($rowm['id']) ) {
	$m_email = $rowm['email'];
	$m_waddr = $rowm['wallet_address'];
	if ( !empty($rowm['wallet_address']) ) {
		$ctc_balance = $wi_wallet_infos->wi_get_balance('2', 'ctc', $rowm['wallet_address'], $contractAddressArr);
		$tp3_balance = $wi_wallet_infos->wi_get_balance('2', 'tp3', $rowm['wallet_address'], $contractAddressArr);
	}

	$tctc_balance = $rowm['etoken_ectc'];
	$etpc_balance = $rowm['etoken_etp3'];

}
					
?>
<link href="css/admin.css" rel="stylesheet">

<div id="page-wrapper" class="admin_list_table1">
	<div class="row">
		<div class="col-lg-6">
			<h1 class="page-header">User Airdrop</h1>
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
		<li>CTC Balance : <?php echo new_number_format($ctc_balance, $n_decimal_point_array['ctc']); ?></li>
		<li>TP3 Balance : <?php echo new_number_format($tp3_balance, $n_decimal_point_array['tp3']); ?></li>
		
		<li>eCTC Balance : <?php echo new_number_format($tctc_balance, $n_decimal_point_array2['ectc']); ?></li>
		<li>eTP3 Balance : <?php echo new_number_format($etpc_balance, $n_decimal_point_array2['etp3']); ?></li>
   </ul>

	<hr />
	
	<ul class="nav nav-tabs">
		<li><a  href="admin_user_approval.php?user_id=<?php echo $userId; ?>"><?php echo !empty($langArr['user_approval']) ? $langArr['user_approval'] : "User Approval"; ?></a></li>
		<li class="active"><a data-toggle="tab" href="#user_first">User Airdrop</a></li>
	  </ul>

	<div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered">
					<colgroup>
						<col width="23%" />
						<col width="6%" />
						<col width="8%" />
						<col width="5%" />
						<col width="39%" />
						<col width="7%" />
						<col width="12%" />
					</colgroup>
					<thead>
						<tr>
							 <th>From Address</th>
							 <th>Token</th>
							 <th>Amount</th>
							<th>Fee</th>
							<th>Transaction</th>
							<th>Status</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>

					<?php 
						if ( !empty($resultData) ) {
							foreach ($resultData as $row) {
							?>
							<tr>
								<td><?php echo $row['from_address']; ?></td>
								<td><?php echo strtoupper($row['coin_type']); ?></td>
								<td><?php echo new_number_format($row['amount'], $n_decimal_point_array[$row['coin_type']]); ?></td>
								<td><?php echo $row['fee']; ?></td>
								<td><a target="_blank" href="https://etherscan.io/tx/<?php echo $row['transactionId']; ?>"><?php echo $row['transactionId']; ?></a></td>
								<td><?php echo $row['status']; ?></td>
								<td><?php echo $row['created_at']; ?></td>
							</tr>
							<?php }
						} ?>   
					<?php 
						if ( !empty($resultData2) ) {
							foreach ($resultData2 as $row2) {
							?>
							<tr>
								<td><?php echo $row2['send_wallet_address']; ?></td>
								<td><?php echo lcfirst(strtoupper($row2['coin_type'])); ?></td>
								<td><?php echo new_number_format($row2['points'], $n_decimal_point_array2[$row2['coin_type']]); ?></td>
								<td></td>
								<td></td>
								<td></td>
								<td><?php echo $row2['created_at']; ?></td>
							</tr>
							<?php }
						} ?>   
					</tbody>
				</table>
			
			</div>

		</div>
	</div>
</div>

<?php
include_once 'includes/footer.php'; ?>