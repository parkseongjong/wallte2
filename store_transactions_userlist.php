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
	$pagelimit = 20;
}
if ($page == "") {
    $page = 1;
}
// If filter types are not selected we show latest added data first
if ($filter_col == "") {
    $filter_col = "points";
}
if ($order_by == "") {
    $order_by = "Desc";
}

// If user searches 
if ($search_string) {
    //$db->orwhere('store_wallet_address', '%' . $search_string . '%', 'like');
    //$db->where('user_wallet_address', '%' . $search_string . '%', 'like');
    $db->where('user_wallet_address',$search_string);
    $walletLogger->info('관리자 모드 > 회원 별 비포인트 목록 > 검색 /검색어:'.$search_string.'/검색 조건:'.$filter_col,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    $walletLogger->info('관리자 모드 > 회원 별 비포인트 목록 > 사용자 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


$db->groupBy('user_id');
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("store_transactions", $page, 'user_id, sum(points) as points');
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

    <?php
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Successfully deleted</div>';
    }
    ?>


	<div class="well text-center filter-form">
        <form class="form form-inline" action="">
		
			<label for="input_search" >Search : </label>
			<input type="text" placeholder="WalletAddress" class="form-control" id="input_search"  name="search_string" value="<?php echo $search_string; ?>">
		   
			<label for="input_order" >Bee points : </label>
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
			

			<input type="submit" value="Go" class="btn btn-primary">
			<br /><span><?php echo !empty($langArr['admin_store_transactions_userlist_text1']) ? $langArr['admin_store_transactions_userlist_text1'] : "When searching for an address, please enter the address used when points were paid."; ?></span>

		</form>
	</div>
    
   
    <hr>
	<!--<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a>-->
	<div class="table-responsive">
    <table class="table table-bordered admin_table_new">
        <thead>
            <tr>
                <th>User</th>
				<th>Bee point</th>
				<th>Log</th>
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

				$user_name = '';
				$user_name = get_user_real_name($getUserDetails['auth_name'], htmlspecialchars($getUserDetails['name']), htmlspecialchars($getUserDetails['lname']));

				$wallet_address = '';
				$new_address = '';
				if ( $getUserDetails['id'] >= 10900 ) {
					$wallet_address .= htmlspecialchars($getUserDetails['wallet_address']);
					$new_address = $getUserDetails['wallet_address'];
				} else {
					if ( $getUserDetails['wallet_change_apply'] == 'Y' ) {
						$wallet_address .= '(New) '.htmlspecialchars($getUserDetails['wallet_address']);
						$new_address = $getUserDetails['wallet_address'];
						if ( !empty($getUserDetails['wallet_address_change']) ) {
							$wallet_address .= '<br />(Old) '.htmlspecialchars($getUserDetails['wallet_address_change']);
						}
					} else {
						$wallet_address .= '(Old) '.htmlspecialchars($getUserDetails['wallet_address']);
						if ( !empty($getUserDetails['wallet_address_change']) ) {
							$wallet_address .= '<br />(New) '.htmlspecialchars($getUserDetails['wallet_address_change']);
							$new_address = $getUserDetails['wallet_address_change'];
						}
					}
				}
				if ( !empty($getUserDetails['virtual_wallet_address']) ) {
					$wallet_address .= '<br />(Virtual) '.$getUserDetails['virtual_wallet_address'];
				}
				/*
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
				}*/
				?>
				<tr>
					<td>
                        <span class="maskingArea" data-id="<?php echo $getUserDetails['id'] ?>" data-type="name">
                            <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($user_name)); ?>
                        </span>
						<a href="admin_users.php?search_string=<?php echo urlencode($new_address); ?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="user info" class="btn btn-sm btn-info">
                            정보 확인
                        </a>
                        <br />
						<?php echo $wallet_address; ?>
					</td>
					<td><?php echo $row['points']; ?></td>
					<td class="align_center"><a href="store_transactions.php?search_type=user_id&search_string=<?php echo urlencode($getUserDetails['id']); ?>" title="bee points logs">Logs</a></td>
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