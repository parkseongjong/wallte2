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

if ($_SESSION['admin_type'] !== 'admin') {
    $_SESSION['failure'] = "You can't perform this action!";
	 header('Location:./index.php');
	 exit;
}

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$filter = walletFilter::getInstance();

$db = getDbInstance();

$walletMasking = new walletMasking();

//2021-08-06 XSS Filter by.ojt
$targetPostData = array(
    'page' => 'string',
    'type' => 'string',
    'send_type' => 'string',
    'coin_type' => 'string',
    'wallet_address' => 'string',
    'status' => 'string',
    'send_target' => 'string',
    'search_string' => 'string',
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
$type = filter_input(INPUT_GET, 'type');
$send_type = filter_input(INPUT_GET, 'send_type');
$coin_type = filter_input(INPUT_GET, 'coin_type');
$wallet_address = filter_input(INPUT_GET, 'wallet_address');
$status = filter_input(INPUT_GET, 'status');
$send_target = filter_input(INPUT_GET, 'send_target');
$search_string = filter_input(INPUT_GET, 'search_string');
*/
/*
page			user_transactions_all.send_type
exchange_etoken		exchange_eToken
exchange_etoken_re	exchange_eToken		CTC, TP3, MC 충전 : eCTC, eTP3, eMC로 충전할 때 발생 / eCTC, eTP3, eMC 충전할 때 발생
exchange			exchange			CTC 충전, TP3 충전 (ETH) : ETH 전송할 떄 발생
transaction_cron		exchange_r		CTC충전, TP3 충전 (ETH) : CTC / TP3 받을 때 발생

*/


if ($page == "") {
    $page = 1;
}

if(!empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') {
	$all_field = 'Y';
} else {
	$all_field = 'N';
}

//$pagelimit =4;
$pagelimit = 10;
$filter_col = "id";
$order_by = "desc";


		
$title = !empty($langArr['swap_list']) ? $langArr['swap_list'] : 'Swap List';
$tbl_name = 'token_conversion';

if ( $type == 'error' ) {
	$db->orderBy('confirmed', 'desc')->orderBy($filter_col, $order_by);
} else {
	$db->orderBy($filter_col, $order_by);
}

if ( !empty($status) ) {
    $db->where('from_token_tx_status', $status);
   
}
$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate($tbl_name, $page);
$total_pages = $db->totalPages;

$walletLogger->info('관리자 모드 > 전송 로그 > 조회 / 전송 타입 :'.$send_type.' / 필터 타입 : '.$type,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

include_once './includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';
?>
<style type="text/css">
.table-bordered { min-width: 935px; }
.table-bordered td { word-break: break-all; }
</style>

<link  rel="stylesheet" href="css/admin.css"/>
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
<div id="sendlog_list">
	<div class="row">
		 <div class="col-lg-6">
			<h1 class="page-header"><?php echo $title; ?></h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php'); ?>


	<ul class="sendlog_link3">
				<li><a href="swap_list.php?status=completed" title="Logs">Success</a></li>
				<li><a href="swap_list.php?status=pending" title="Logs">Pending</a></li>
			</ul>
            <?php
					$currentPage = 1;
					$get_infos = '';
					if ( isset($filterData) &&!empty($filterData) ) {
						$get_infos = $filterData;
						if (isset($filterData['page']) && !empty($filterData['page'])) {
							$currentPage = $filterData['page'];
						}
					}
			
					?>

	 <div class="tab-content">
			<div id="user_first" class="tab-pane fade in active">
				<div class="table-responsive">
					<table class="table table-bordered admin_table_new">
						<thead>
								<tr>
                                <th>Sr NO.</th>
                                <th>USER</th>
								<th>ETH TOKEN</th>
                                <th>BSC TOKEN</th>
								</tr>
						</thead>
						<tbody>

						<?php 
                            $i=(($currentPage-1)*$pagelimit) + 1;
							foreach ($resultData as $row) {
								?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $row['user_id']; ?></td>
                                    <td>
                                        <table class="table table-bordered admin_table_new" style="min-width:580px;">
                                            <tr><td>Token</td><td><?php echo $row['from_token']; ?></td></tr>
                                            <tr><td>Amount</td><td><?php echo $row['from_token_amount']; ?></td></tr>
                                            <tr><td>Tx ID</td><td><?php echo $row['from_token_tx_id']; ?></td></tr>
                                            <tr><td>Status</td><td><?php echo $row['from_token_tx_status']; ?></td></tr>
                                            <tr><td>From </td><td><?php echo $row['from_token_from_wallet_address']; ?></td></tr>
                                            <tr><td>To </td><td><?php echo $row['from_token_to_wallet_address']; ?></td></tr>
                                        </table>
                                    </td>
                                   
                                    <td>
                                        <table class="table bordered admin_table_new"  style="min-width:580px;">
                                            <tr><td>Token</td><td><?php echo $row['to_token']; ?></td></tr>
                                            <tr><td>Amount</td><td><?php echo $row['to_token_amount']; ?></td></tr>
                                            <tr><td>Tx ID</td><td><?php echo $row['to_token_tx_id']; ?></td></tr>
                                            <tr><td>Status</td><td><?php echo !empty($row['to_token_tx_id']) ? "completed" : ""; ?></td></tr>
                                            <tr><td>From </td><td><?php echo $row['to_token_from_wallet_address']; ?></td></tr>
                                            <tr><td>To </td><td><?php echo $row['to_token_to_wallet_address']; ?></td></tr>
                                        </table>
                                    </td>
                                </tr>


                                <?php
                                $i++;
                                }
                                
                                ?>
						</tbody>
					</table>
				
				</div>

				<!--    Pagination links-->
				<div class="text-center">
					<?php
					
                    
					echo new_set_page_list($currentPage, '', $total_pages, $get_infos, '10'); // config/new_config.php
					?>
				</div>

			</div>
		</div>
	</div>
</div>

<?php include_once './includes/footer.php'; ?>