<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

use wallet\common\Log as walletLog;

require __DIR__ .'/vendor/autoload.php';

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('매장 거래 조회',['target_id'=>0,'action'=>'S']);

$db = getDbInstance();
//Get data from query string
$search_string = filter_input(INPUT_GET, 'search_string');
$del_id = filter_input(INPUT_GET, 'del_id');

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
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

// If user searches 
if ($search_string) {
    $db->where('email', '%' . $search_string . '%', 'like');
}

$db->where("user_id", $_SESSION['user_id']);
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}


$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("store_transactions", $page);
$total_pages = $db->totalPages;

include_once 'includes/header.php';
?>
<link href="css/lists.css?ver=1.4" rel="stylesheet">

<div id="page-wrapper">
	<div id="store_transactions_user">
		<div class="row">
			 <div class="col-lg-6">
					<h1 class="page-header"><?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?></h1>
				</div>
			</div>
		</div>

	 <?php include('./includes/flash_messages.php') ?>
		
		<!--<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a>-->
		<div class="table-responsive">
		<table class="table table-bordered user_table_new">
			<thead>
				<tr>
					<th width="40%"><?php echo !empty($langArr['beepoint_title1']) ? $langArr['beepoint_title1'] : "Payment information"; ?></th>
					<th width="20%"><?php echo !empty($langArr['points']) ? $langArr['points'] : "Cashback Points"; ?></th>
					<th width="20%"><?php echo !empty($langArr['beepoint_title2']) ? $langArr['beepoint_title2'] : "Amount of payment"; ?></th>
					<th width="20%"><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?></th>
				</tr>
			</thead>
			<tbody>

				<?php 
				
				$totalCtcAmt = 0; 
			   
				foreach ($resultData as $row) : ?>
					
					<?php 
					$pay = '';
					if ( stristr($row['description'], 'coupon_result : ') == true ) {
						$pay = 'coupon';
					}

						//$db = getDbInstance();
						//$db->where("id", $row['user_id']);
						//$getUserDetails = $db->getOne('admin_accounts');
						
						
						$db = getDbInstance();
						$db->where("id", $row['store_id']);
						$getStoreDetails = $db->getOne('stores');

					?>
						
				
				<tr>
					<td class="word_fixed">
						<?php
						if ( !empty($getStoreDetails['store_name']) ) {
							echo '['.$getStoreDetails['store_name'].']';
						}
						if ( !empty($row['store_wallet_address']) ) {
							echo $row['store_wallet_address'];
						}
						if ( $pay == 'coupon' ) {
							echo !empty($langArr['coupon_fee_title']) ? $langArr['coupon_fee_title'] : "Purchase fee coupon";
						}
						?>
					</td>
					<td class="align_center"><?php echo new_number_format2($row['points']); ?></td>
					<td>
						<?php
						if ( !empty($row['amount']) ) {
							echo !empty($langArr['amount2']) ? $langArr['amount2'] : "Amount";
							echo ' : '.new_number_format($row['amount'], $n_decimal_point_array['ctc']);
						}
						if ($pay == 'coupon' ) {
							echo '<br />';
							echo !empty($langArr['coupon_payment_text5']) ? $langArr['coupon_payment_text5'] : "Amount of payment";
							echo ' : '.number_format($row['krw']);
							echo !empty($langArr['krw_unit']) ? $langArr['krw_unit'] : "WON";
						} ?>
					</td>				
					<td class="align_center"><?php echo htmlspecialchars($row['created_at']) ?></td>
					

				</tr>
				

				<?php endforeach; ?>   
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


<?php include_once 'includes/footer.php'; ?>