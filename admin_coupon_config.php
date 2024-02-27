<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

use WalletLogger\Logger as walletLogger;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}

$filter = walletFilter::getInstance();

$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

//2021-08-06 XSS Filter by.ojt
$targetPostData = array(
    'page' => 'string'
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
/*
$page = filter_input(INPUT_GET, 'page');
*/
if ($page == "") {
    $page = 1;
}
$pagelimit = 10;
$filter_col = "amount";
$order_by = "asc";


if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("coupon_list", $page);
$total_pages = $db->totalPages;

$walletLogger->info('관리자 모드 > 쿠폰 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);


include_once 'includes/header.php';
?>

<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-6">
			<h1 class="page-header">Admin Coupon config</h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php'); ?>

    <a class="btn btn-success" href="add_coupon_config.php">Add Coupon</a>
    <hr>
	<div class="table-responsive">
		<table class="table table-bordered admin_table_new">
			<thead>
				<tr>
					<th><?php echo !empty($langArr['coupon_adm_text2']) ? $langArr['coupon_adm_text2'] : "Coupon name"; ?></th>
					<th><?php echo !empty($langArr['coupon_list_text8']) ? $langArr['coupon_list_text8'] : "Kind"; ?></th>
					<th>Coin</th>
					<th><?php echo !empty($langArr['coupon_adm_text3']) ? $langArr['coupon_adm_text3'] : "Amount of payment(unit: WON)"; ?></th>
					<th><?php echo !empty($langArr['coupon_adm_text4']) ? $langArr['coupon_adm_text4'] : "Coins to be received"; ?></th>
					<th><?php echo !empty($langArr['coupon_list_text7']) ? $langArr['coupon_list_text7'] : "Use or not"; ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>

				<?php
				foreach ($resultData as $row) { ?>
				
					<tr>
						<td><?php echo htmlspecialchars($row['name']) ?></td>
						<td class="align_center"><?php echo htmlspecialchars($row['kind']) ?></td>
						<td class="align_center"><?php echo htmlspecialchars($row['coin_type']) ?></td>
						<td class="align_center"><?php echo htmlspecialchars($row['amount']) ?></td>
						<td class="align_center"><?php echo htmlspecialchars($row['coin_amount']) ?></td>
						<td class="align_center"><?php echo htmlspecialchars($row['used']) ?></td>
						<td class="align_center">
							<a href="edit_coupon_config.php?id=<?php echo $row['id']; ?>&operation=edit&page=<?php echo $page; ?>" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
							<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm2-delete-<?php echo $row['id']; ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span></a>
						</td>
					</tr>
					 <div class="modal fade" id="confirm2-delete-<?php echo $row['id']; ?>" role="dialog">
						<div class="modal-dialog">
						  <form action="multiprocess.php" method="POST">
							<input type="hidden" name="mode" value="coupon_config_delete" />
							<input type="hidden" name="page" value="<?php echo $page; ?>" />
						  <!-- Modal content-->
							  <div class="modal-content">
								<div class="modal-header">
								  <button type="button" class="close" data-dismiss="modal">&times;</button>
								  <h4 class="modal-title">Confirm</h4>
								</div>
								<div class="modal-body">
									<input type="hidden" name="update_id" id = "update_id" value="<?php echo $row['id'] ?>">
									<p>Are you sure you want to delete this coupon [<?php echo $row['name']; ?>]?</p>
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
		if ( isset($filterData) &&!empty($filterData)){
			$get_infos = $filterData;
			if (isset($filterData['page']) && !empty($filterData['page'])) {
				$currentPage = $filterData['page'];
			}
		}
		echo new_set_page_list($currentPage, '', $total_pages, $get_infos, '10');
		?>

    </div>
</div>

<?php include_once 'includes/footer.php'; ?>