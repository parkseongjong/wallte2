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
// select the columns
$select = array('id', 'store_name','store_region','store_address','store_phone','store_wallet_address','store_description','created_at');

// If user searches 
if ($search_string) {
    //$db->where('email', '%' . $search_string . '%', 'like');
    $db->where('email',$search_string);
    $walletLogger->info('관리자 모드 > 매장 관리 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 매장 관리 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


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
$resultData = $db->arraybuilder()->paginate("stores", $page, $select);
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
<link  rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header">Admin Stores</h1>
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
    <a class="btn btn-success" href="add_store.php">Add Store</a>
    <hr>
	<div class="table-responsive">
    <table class="table table-bordered admin_table_new">
        <thead>
            <tr>
	            <th width="10%">Name</th>
	            <th width="5%">Region</th>
	            <th width="20%">Store Address</th>
	            <th width="10%">Phone</th>
				<th width="20%">Wallet Address</th>
				<th width="10%">Description</th>
				<th width="10%">Created</th>
				<th width="10%">Actions</th>
            </tr>
        </thead>
        <tbody>

            <?php
            foreach ($resultData as $row) { ?>
				<tr>
					<td class="align_center">
                        <span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="store"><?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['store_name'])); ?></span>
                    </td>
					<td class="align_center"><?php echo htmlspecialchars($row['store_region']) ?></td>
					<td><?php echo htmlspecialchars($row['store_address']) ?></td>
                    <td><span class="maskingArea" data-id="<?php echo $row['id'] ?>" data-type="storePhone"><?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['store_phone'])); ?></span></td>
					<td><?php echo htmlspecialchars($row['store_wallet_address']) ?></td>
					<td><?php echo htmlspecialchars($row['store_description']) ?></td>
					<td class="align_center"><?php echo htmlspecialchars($row['created_at']) ?></td>
					<td class="align_center">
						<a href="edit_store.php?id=<?php echo $row['id']?>&operation=edit" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
						<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>					
					</td>
				</tr>
		
                <!-- Delete Confirmation Modal-->
                     <div class="modal fade" id="confirm-delete-<?php echo $row['id'] ?>" role="dialog">
                        <div class="modal-dialog">
                          <form action="delete_store.php" method="POST">
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
            <?php } ?>   
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