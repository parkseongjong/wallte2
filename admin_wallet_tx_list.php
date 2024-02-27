<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}
require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$filter = walletFilter::getInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

//$db = getDbInstance();

//2021-08-06 XSS Filter by.ojt
$targetPostData = array(
    'g_coin' => 'string',
    'pagelimit' => 'string',
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
$g_coin = filter_input(INPUT_GET, 'g_coin');
$pagelimit = filter_input(INPUT_GET, 'pagelimit');
$page = filter_input(INPUT_GET, 'page');
*/
$walletLogger->info('관리자 모드 > 잔액 조회 (List) / 종류 : '.$g_coin,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

if ( $g_coin == '' ) {
	$g_coin = 'ctc';
}
if ($pagelimit == "") {
    $pagelimit = 20;
}
if ($page == "") {
    $page = 1;
}
$contractAddress = $contractAddressArr[$g_coin]['contractAddress'];

$getRecords = array();
$curl = curl_init();

$ethUrl = "http://api.etherscan.io/api?module=account&action=txlist&address=".$contractAddress."&page=".$page."&offset=".$pagelimit."&sort=desc&apikey=".$ethApiKey; // CTC 거래금액 표시안됨, to가 contract주소가 나와옴
//$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$contractAddress."&page=".$page."&offset=".$pagelimit."&sort=desc&apikey=".$ethApiKey; //  etherscan에서 볼 수 없는 목록이 있음
curl_setopt_array($curl, array(
  CURLOPT_URL => $ethUrl,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 3000,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
	"cache-control: no-cache",
	"postman-token: 89d13eeb-278c-730c-b720-b521c178b500"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
$getResultDecode = json_decode($response,true);
$getRecords = $getResultDecode['result']; 

$url1_count = 0;
if ( !empty($getRecords) ) {
	$url1_count = count($getRecords);
}

function check1($type, $value) {
	$name = '';
	$m_id = '';
	if ( $type == 'address' ) {

		$db = getDbInstance();
		$db->orwhere ("wallet_address", $value);
		$db->orwhere ("wallet_address_change", $value);
		$db->orwhere ("virtual_wallet_address", $value);
		$userData = $db->getOne('admin_accounts');
		if ( !empty($userData) ) {
			//$m_id = $userData['id'];
			$m_id = get_user_real_name($userData['auth_name'], $userData['name'], $userData['lname']);
		}

	} else if ( $type == 'tx' ) {
		$db = getDbInstance();
		$db->where('transactionId', $value);
		$txData = $db->getOne('user_transactions_all');
		if ( !empty($txData) ) {
			$m_id = $txData['send_type'];
		}
	}
	return $m_id;
}




include_once 'includes/header.php';
?>

<link href="css/admin.css" rel="stylesheet">

<link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
		<h1 class="page-header">List</h1>
	</div>
</div>
 <?php include('./includes/flash_messages.php') ?>

    <!--    Begin filter section-->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
			
			
            <label for="g_coin" >Coin</label>
			<select name="g_coin" id="g_coin">
			<?php
			foreach($contractAddressArr as $key=>$cr) {
				if ( $key == 'eth' ) continue;
				?><option value="<?php echo $key; ?>" <?php if ( $g_coin == $key ) echo "selected"; ?>><?php echo $key; ?></option><?php
			}
			?>
			</select>

			<label for="pagelimit">Offset</label>
			<input type="number" name="pagelimit" min="10" max="2000" value="<?php if ( !empty($pagelimit) ) echo $pagelimit; ?>" />

            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>
    <!--   Filter section end-->

    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>전송구분</th>
							<th>Transaction Hash</th>
							<th>From Address</th>
							<th>From Member</th>
							<th>To Address</th>
							<th>To Member</th>
							<th>Date</th>
						</tr>
					</thead>
					<tbody>

					<?php 

					if(!empty($getRecords)) {
						$getTime = '';
						foreach($getRecords as $getRecordSingle) {

							$from_address = '';
							$to_address = '';
							$tx_hash = '';

							$from_address = $getRecordSingle['from'];
							$to_address = $getRecordSingle['to'];
							$tx_hash = $getRecordSingle['hash'];

							$to_address_tmp = $getRecordSingle['input'];
							$to_address_tmp = substr($to_address_tmp, 34, 40);
							$to_address = '0x'.$to_address_tmp;
							
							$from_e = '';
							$to_e = '';
							$tx_e = '';

							$from_e = check1('address', $from_address);
							$to_e = check1('address', $to_address);
							$tx_e = check1('tx', $tx_hash);
							$getDate = date("Y-m-d H:i:s",$getRecordSingle['timeStamp']);

							?>
							<tr>
								<td><?php if ( $tx_e != '' ) { echo $tx_e; } ?></td>
								<td><a href="https://etherscan.io/tx/<?php echo $tx_hash;?>" title="etherscan" target="_blank"><?php echo $tx_hash; ?></a></td>
								<td><a href="https://etherscan.io/address/<?php echo $from_address; ?>" title="etherscan" target="_blank"><?php echo $from_address; ?></a></td>
								<td>
									<?php if ( $from_e ) { ?>
										<a href="admin_users.php?search_string=<?php echo $from_address; ?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="member list"><?php echo $from_e; ?></a>
									<?php } ?>
								</td>
								<td><a href="https://etherscan.io/address/<?php echo $to_address; ?>" title="etherscan" target="_blank"><?php echo $to_address; ?></a></td>
								<td>
									<?php if ( $to_e ) { ?>
										<a href="admin_users.php?search_string=<?php echo $to_address; ?>&filter_col=id&order_by=Asc&filter_limit=10&date1=&date2=" title="member list"><?php echo $to_e; ?></a>
									<?php } ?>
								</td>
								<td><?php echo $getDate; ?></td>
							</tr>
							<?php
						} // foreach
					}
					?>
					</tbody>
				</table>
			
			</div>
			
			<ul class="pagination">
			<?php
			$first_page_url = "admin_wallet_tx_list.php?g_coin=".$g_coin."&pagelimit=".$pagelimit."&page=1";
			$before_page_url = "admin_wallet_tx_list.php?g_coin=".$g_coin."&pagelimit=".$pagelimit."&page=".($page-1);
			$next_page_url = "admin_wallet_tx_list.php?g_coin=".$g_coin."&pagelimit=".$pagelimit."&page=".($page+1);
			if ( $page > 1 ) { // Before
				?>
				<li class="page-item"><a class="page-link" href="<?php echo $first_page_url; ?>" title="1">First</a></li>
				<li class="page-item"><a class="page-link" href="<?php echo $before_page_url; ?>" title="<?php echo ($page-1); ?>">Previous</a></li><?php
			}
			?><li class="page-item"><a class="page-link" href="<?php echo $next_page_url; ?>" title="<?php echo ($page+1); ?>">Next</a></li>
			</ul>

	    </div>
	</div>

</div>

<?php
include_once 'includes/footer.php'; ?>

<?php

//$ethUrl = "http://api.etherscan.io/api?module=account&action=txlist&address=".$walletAddress."&page=".$page."&offset=".$offset."&sort=desc&apikey=".$ethApiKey; // CTC 거래금액 표시안됨, to가 contract주소가 나와서 안됨.

/*
result=>0=>
blockNumber	"11464771"
timeStamp	"1608130310"
hash	"0x2f59e5980f5d88d16d43c2964d98112a51275848f6746718db572c1ebc059784"
nonce	"215"
blockHash	"0x1e886cbef93ddb6ec5ccf14419e025906fab66559df62b4f83900a7ba9b2bc20"
transactionIndex	"231"
from	"0x1da4a1759ed3e2d59d4ae4303eaf5d408fbb24c6"
to	"0x00b7db6b4431e345eee5cc23d21e8dbc1d5cada3"
value	"0"
gas	"100000"
gasPrice	"173000000000"
isError	"0"
txreceipt_status	"1"
input	"0xa9059cbb000000000000000000000000c15ad5fab35c7b7345dbcb28a0b2b062d217b2680000000000000000000000000000000000000000000000015cc96a6a3a99d051"
contractAddress	""
cumulativeGasUsed	"11201546"
gasUsed	"37074"
confirmations	"3199"
*/



//$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$walletAddress."&page=".$page."&offset=".$offset."&sort=desc&apikey=".$ethApiKey; // 조회가능!!! / 그런데 etherscan에서 볼 수 없는 목록이 있네?
/*
{"blockNumber":"11464771",
"timeStamp":"1608130310",
"hash":"0x2f59e5980f5d88d16d43c2964d98112a51275848f6746718db572c1ebc059784"
"nonce":"215"
"blockHash":"0x1e886cbef93ddb6ec5ccf14419e025906fab66559df62b4f83900a7ba9b2bc20"
"from":"0x1da4a1759ed3e2d59d4ae4303eaf5d408fbb24c6"
"contractAddress":"0x00b7db6b4431e345eee5cc23d21e8dbc1d5cada3"
"to":"0xc15ad5fab35c7b7345dbcb28a0b2b062d217b268"
"value":"25132736200000000081"
"tokenName":"CyberTronChain"
"tokenSymbol":"CTC"
"tokenDecimal":"18"
"transactionIndex":"231"
"gas":"100000"
"gasPrice":"173000000000"
"gasUsed":"37074"
"cumulativeGasUsed":"11201546"
"input":"deprecated"
"confirmations":"3389"},
*/
?>