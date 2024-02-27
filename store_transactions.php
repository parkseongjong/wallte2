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
//require('includes/web3/vendor/autoload.php');
//use Web3\Web3;
//use Web3\Contract;



//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}

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
    'search_type' => 'string'
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
$search_type = filter_input(INPUT_GET, 'search_type');
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

if ( $search_type == '' ) {
	$search_type = 'text';
}

// If user searches 
if ($search_string) {
	if ( $search_type == 'text' ) {
	    //$db->orwhere('store_wallet_address', '%' . $search_string . '%', 'like');
		//$db->where('user_wallet_address', '%' . $search_string . '%', 'like');
		$db->where('user_wallet_address',$search_string);
	}
	else if ( $search_type == 'user_id' ) {
		$db->where('user_id', $search_string);
	}
    $walletLogger->info('관리자 모드 > 매장 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 매장 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("store_transactions", $page);
$total_pages = $db->totalPages;

include_once 'includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';
?>
<link  rel="stylesheet" href="css/admin.css"/>
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header">Store Transactions</h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>

	<h4><a href="store_transactions_userlist.php" title="member list"><?php echo !empty($langArr['admin_store_transactions_userlist']) ? $langArr['admin_store_transactions_userlist'] : "List of Bee points quantity by member"; ?></a></h4>
	
	<?php if ( $search_type == 'text' ) { ?>
		<div class="well text-center filter-form">
			<form class="form form-inline" action="">
				
				<label for="input_search" >Search</label>
				<input type="text" placeholder="WalletAddress" class="form-control" id="input_search"  name="search_string" value="<?php echo $search_string; ?>">
				
				<input type="submit" value="Go" class="btn btn-primary">
				<br /><span><?php echo !empty($langArr['admin_store_transactions_userlist_text1']) ? $langArr['admin_store_transactions_userlist_text1'] : "When searching for an address, please enter the address used when points were paid."; ?></span>

			</form>
		</div>
    <?php } ?>
   
    <hr>
	<!--<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a>-->
	<div class="table-responsive">
    <table class="table table-bordered admin_table_new">
        <thead>
            <tr>
                <th class="header">#</th>
                <th>User</th>
				<th>Info</th>
 				<th><?php echo !empty($langArr['points']) ? $langArr['points'] : "Cashback Points"; ?></th>
 				<th>KRW</th>
				<th><?php echo !empty($langArr['ctc_amount']) ? $langArr['ctc_amount'] : "Ctc Amount"; ?></th>
				<th><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?></th>
                
            </tr>
        </thead>
        <tbody>

            <?php 
            
            $totalCtcAmt = 0; 
           
            foreach ($resultData as $row) : ?>
                
				<?php
				$db = getDbInstance();
				$db->where("id", $row['user_id']);
				$getUserDetails = $db->getOne('admin_accounts');
				
				
				$db = getDbInstance();
				$db->where("id", $row['store_id']);
				$getStoreDetails = $db->getOne('stores');

				$pay = '';
				if ( stristr($row['description'], 'coupon_result : ') == true ) {
					$pay = 'coupon';
				}
				if ( $pay == 'coupon' ) {
					$description = !empty($langArr['coupon_fee_title']) ? $langArr['coupon_fee_title'] : "Purchase fee coupon";
				} else {
					$description = htmlspecialchars($row['description']);
				}
				?>

				<tr>
					<td class="align_center"><?php echo $row['id'] ?></td>
					<td class="align_center">
                        <span class="maskingArea" data-id="<?php echo $getUserDetails['id'] ?>" data-type="name"><?php echo $walletMasking->reset()->pushNameMask()->getMasked($getUserDetails['lname'].$getUserDetails['name']); ?></span>
                    </td>
					<td>
						<strong>Store Name:</strong> <?php echo htmlspecialchars($getStoreDetails['store_name']) ?><br>
						<strong>Store Wallet Address</strong> <?php  echo $row['store_wallet_address'];  ?><br>
						<strong>User Wallet Address:</strong> <?php echo $row['user_wallet_address']; ?><br>
						<strong>Purchase Tax ID:</strong> <?php echo $row['tx_id']; ?><br>
						<strong>Description:</strong><?php echo $description; ?>
					</td>
					<td><?php echo number_format($row['points'],2); ?></td>
					<td>₩<?php echo number_format($row['krw'],2); ?></td>
					<td><?php echo number_format($row['amount'],2); ?></td>
					<td class="align_center"><?php echo htmlspecialchars($row['created_at']) ?></td>
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
            <?php endforeach; ?>   
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


<?php include_once 'includes/footer.php'; ?>