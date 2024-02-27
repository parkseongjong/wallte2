<?php
// Page in use
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once 'includes/header.php';

use Pachico\Magoo\Magoo as walletMasking;
use wallet\common\Log as walletLog;

require __DIR__ .'/vendor/autoload.php';

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('매장 목록 조회',['target_id'=>0,'action'=>'S']);

$db = getDbInstance();
$select = array('id', 'store_name','store_wallet_address','created_at');

$walletMasking = new walletMasking();

$db->orderby("store_region", "asc");

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

if ($filter_col == "") {
	$filter_col = "id";
}

if ($order_by == "") {
	$order_by = "desc";
}


$db->pageLimit = $pagelimit;
//$query = $db->where('store_region = "'.$name.'"')->arraybuilder()->paginate("stores", $page, $select);
$query = $db->arraybuilder()->paginate("stores", $page, $select);
$total_pages = $db->totalPages;


?>

<link href="css/lists.css?ver=1.4" rel="stylesheet">

<div id="page-wrapper">
	<div id="all_stores2">
		<div class="row">
			 <div class="col-lg-6">
					<h1 class="page-header"> <?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "All Stores"; ?></h1>
				</div>
			</div>
		</div>
		

		<?php include('./includes/flash_messages.php') ?>

		<div class="expand-collapse">		

			<table class="table table-bordered user_table_new">
				<thead>
					<tr>
						<th width="25%"><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?></th>
						<th width="50%">Wallet Address</th>
						<th width="25%"><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($query as $row) { ?>					
					<tr>
						<td class="align_center">
                            <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['store_name'])); ?>
                            <?php //echo htmlspecialchars($row['store_name']) ?>
                        </td>
						<td class="word_fixed"><?php echo htmlspecialchars($row['store_wallet_address']) ?></td>
						<td class="align_center"><?php echo htmlspecialchars($row['created_at']) ?></td>
					</tr>
					<?php } ?>   
				</tbody>
			</table>	

				
			<!-- Pagination links-->
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

	

<?php include_once 'includes/footer.php'; ?>