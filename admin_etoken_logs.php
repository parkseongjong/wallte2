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
	 header('Location:index.php');
}

$filter = walletFilter::getInstance();

//2021-08-05 XSS Filter by.ojt
$targetPostData = array(
    'page' => 'string',
    'search_string' => 'string',
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
/*
$page = filter_input(INPUT_GET, 'page');
$search_string = filter_input(INPUT_GET, 'search_string');
$search_type = filter_input(INPUT_GET, 'search_type');
*/

if ($page == "") {
    $page = 1;
}

$pagelimit = 20;
$filter_col = "id";
$order_by = "desc";


$db = getDbInstance();

$walletMasking = new walletMasking();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

if ( $search_string ) {
	if ( empty($search_type) ) {
		$db->where('user_transactions_all_id', $search_string);
	} else if ( $search_type == 'uid' ) {
		$db->where('user_id', $search_string);
	}
}
$db->orderBy($filter_col, $order_by);

$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("etoken_logs", $page);
$total_pages = $db->totalPages;

$db = getDbInstance();
$pointSum = $db->getValue("etoken_logs", "sum(points)");

if($search_type == 'uid'){
    $walletLogger->info('관리자 모드 > e-PAY > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$search_string,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}
else{
    if($search_string){
        $walletLogger->info('관리자 모드 > e-PAY > 조회/트랜젝션 ID:'.$search_string,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
    }
    else{
        $walletLogger->info('관리자 모드 > e-PAY > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
    }
}

include_once 'includes/header.php';
include_once WALLET_PATH.'/includes/adminAssets.php';
?>
<link  rel="stylesheet" href="css/admin.css"/>
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-6">
				<h1 class="page-header">E-Pay</h1>
			</div>
	</div>
 <?php include('./includes/flash_messages.php') ?>
    <hr>
	<div class="table-responsive">
		<p><a href="sendlog_list.php?send_type=exchange_eToken" title="log">E-Pay Transaction Log</a></p>
		<p>Sum : <?=$pointSum?></p>
		<?php
		if ( !empty($search_type) && $search_type == 'uid' ) {

			
			$db = getDbInstance();
			$db->where("id", $search_string);
			$userInfos = $db->getOne('admin_accounts');

			$user_email = $userInfos['email'];
			$user_name = get_user_real_name($userInfos['auth_name'], $userInfos['name'], $userInfos['lname']);
			$admin_type = $userInfos['admin_type'];

			?>
			<span>Send Type</span>
			<ul>
				<li>to_etoken : Coin -&gt; e-Coin</li>
				<li>to_token : e-Coin -&gt; Coin</li>
				<li>from_admin : 관리자가 지급</li>
				<li>barry : barrybarries 결제</li>
				<li>값 없음 : 사용자간 전송</li>
			</ul>

			<?php foreach($n_full_name_array2 as $k1=>$v1) {
				echo lcfirst(strtoupper($k1)).' : '.new_number_format($userInfos['etoken_'.$k1], $n_decimal_point_array2[$k1]).'<br />';
			}

		} ?>

		<table class="table table-bordered admin_table_new">
			<thead>
				<tr>
					<th>User</th>
					<th>Coin Type</th>
					<th>Send Type</th>
					<th>Points</th>
					<th>Date</th>
					<th>Infos</th>                
				</tr>
			</thead>
			<tbody>

				<?php
				foreach ($resultData as $row) : 
					if ( $search_type != 'uid' ) {
						$db = getDbInstance();
						$db->where("id", $row['user_id']);
						$getUserDetails = $db->getOne('admin_accounts');
						$user_email = $getUserDetails['email'];
						$user_name = get_user_real_name($getUserDetails['auth_name'], $getUserDetails['name'], $getUserDetails['lname']);
						
						if ( !empty($getUserDetails['account_type2']) && $getUserDetails['account_type2'] != 'wallet' ) {
							$user_name = $user_name.' ('.$getUserDetails['account_type2'].')';
						}

						$admin_type = $getUserDetails['admin_type'];
					}

				
					$db = getDbInstance();
					$db->where("id", $row['send_user_id']);
					$getUserDetails2 = $db->getOne('admin_accounts');
					$u_name2 = $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars(get_user_real_name($getUserDetails2['auth_name'], $getUserDetails2['name'], $getUserDetails2['lname'])));
					$u_name2Type = false;
					if ( !empty($getUserDetails2['account_type2']) && $getUserDetails2['account_type2'] != 'wallet' ) {
						$u_name2 = $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($u_name2));
                        $u_name2Type = $getUserDetails2['account_type2'];
					}

					
					$virtual = '';
					if ( $row['send_wallet_address'] == $getUserDetails2['virtual_wallet_address'] ) {
						$virtual = '(가상주소)';
					}
					if ( $row['in_out'] == 'in' ) {
						$type = '[From]';
					} else {
						$type = '[To]';
					}
					$type .= ' <a href="admin_users.php?search_string='.urlencode($row['send_wallet_address']).'&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="infos">'.$row['send_wallet_address'].'</a>';
					?>

					<tr>
						<td>
							ID :
                            <span class="maskingArea" data-id="<?php echo $row['user_id'] ?>" data-type="email">
                                <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($user_email)); ?>
                            </span>
                            <a href="admin_users.php?search_string=<?=urlencode($user_email)?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="User Info" class="btn btn-sm btn-info">정보 확인</a>
                            <br />
							Name(<?=$admin_type?>) :
                            <span class="maskingArea" data-id="<?php echo $row['user_id'] ?>" data-type="name">
                                <?php echo $walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($user_name)); ?>
                            </span>
						</td>
						<td class="align_center"><?php echo lcfirst(strtoupper($row['coin_type'])); ?></td>
						<td class="align_center">
							<?php echo $row['send_type'];?>
						</td>
						<td class="align_center"><?php echo $row['points'] ?></td>
						<td class="align_center"><?php echo $row['created_at'] ?></td>
						<!--<td><?=$type?></td>-->
						<td>
							<?php echo $type.$virtual; ?>
                            <br />
                            <span class="maskingArea" data-id="<?php echo $getUserDetails2['id'] ?>" data-type="name">
                                <?php echo $u_name2; ?>
                            </span>
                            <?php echo ($u_name2Type)?'('.$u_name2Type.')':false ?>
                            <br />
                            <span class="maskingArea" data-id="<?php echo $getUserDetails2['id'] ?>" data-type="email">
                                <?php echo $walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($getUserDetails2['email'])); ?>
                            </span>
							<?php
								if ( !empty($row['user_transactions_all_id']) ) { // 21.07.17, YMJ
									echo '<br />'.$row['user_transactions_all_id'];
								}
							?>

						</td>
					</tr>

				<?php endforeach; ?>   
			</tbody>
		</table>
	
	</div>
	
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