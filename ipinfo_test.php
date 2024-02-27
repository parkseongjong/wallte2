<?php
// https://cybertronchain.com/wallet2/ipinfo_test.php-

session_start();
require_once './config/config.php';
require_once './config/new_config.php';
/*
$txid = isset($_GET['txid']) ? $_GET['txid'] : '';

if ( !empty($txid) ) {
	require_once BASE_PATH.'/lib/WalletInfos.php';
	$wi_wallet_infos = new WalletInfos();
	$transcationId = '0xbc6be89defaf0a9a4708fdccf2c3161c5272d04d25142c76026f3868f9082be8';

	$tx_result = $wi_wallet_infos->get_txId_result($transcationId);

	echo $tx_result;
} else {
	echo 'please enter txid';
}
exit();



//require_once './config/proc_config.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

//$walletLogger->info('관리자 모드 > Approve2',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
//$walletLogger->info('관리자 모드 > Approve2',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userId,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);


exit();

require_once './config/config_exchange.php';
*/
require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

require_once './lib/WalletProcess.php';
$wi_wallet_process = new WalletProcess();

$walletAddress= (isset($_GET['walletAddress']) && !empty($_GET['walletAddress']) ) ? $_GET['walletAddress'] : '0x48decf9d1410ac78406fa0a85b3a9c4c4f8bd6d7';

echo 'Addr : '.$walletAddress.'<br />';
echo 'Etherscan : <a href="https://etherscan.io/address/'.$walletAddress.'" title="etherscan" target="_blank">Etherscan</a><br />';
$getbalances = $wi_wallet_infos->wi_get_balance('', 'all', $walletAddress, $contractAddressArr);
print_r($getbalances);
	
//$getbalances2 = $wi_wallet_process->wi_get_balance('ctc', $walletAddress, $contractAddressArr);
//print_r($getbalances2);

exit();

$price = ex_get_coin_price_one('KRW', 'BTC');

//$price2 = 1/$price;
$price2 = bcdiv(1, $price, 18);
//$price2 = round($price2, 2);
echo $price;
echo '<Br />';
echo $price2;

exit();



echo '200 : '.is_numeric(200).'<br />';
echo '0 : '.is_numeric(0).'<br />';
echo '1.0 : '.is_numeric(1.0).'<br />';
echo '0.5 : '.is_numeric(0.5).'<br />';
echo '\'200\' : '.is_numeric('200').'<br />';
echo '\'200.001\' : '.is_numeric('200.001').'<br />';
echo 'kkk : '.is_numeric('kkk').'<br />';
exit;
//$e = npro_send_approve_check('5137', 'mc', 'mc');
//echo $e;
$err_code = 200;
$userId = '5137';
$token = 'ctc';
nproc_fn_logSave('Test Message', '111', 'ipinfo_test.php', '12', $err_code, $userId, $token, array('toto'=>'abc', 'bb'=>'cream'));
/*
require_once './lib/WalletProcess.php';
$wi_wallet_process = new WalletProcess();
$walletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';
$token = 'ctc';
$getNewBalance = $wi_wallet_process->wi_get_balance($token, $walletAddress, $contractAddressArr);
echo $getNewBalance;
*/
exit;

?>
<script src="js/jquery.min.js" type="text/javascript"></script> 



<table>
<thead>
	<tr>
		<th>ID</th>
		<th>Wallet Address</th>
		<th>eCTC</th>
		<th>eCTC(sum)</th>
		<th>eCTC(sum-가상)</th>
		<th>eTP3</th>
		<th>eTP3(sum)</th>
		<th>eTP3(sum-가상)</th>
		<th>eMC</th>
		<th>eMC(sum)</th>
		<th>eMC(sum-가상)</th>
		<th>eKRW</th>
		<th>eKRW(sum)</th>
		<th>eKRW(sum-가상)</th>
	</tr>
</thead>
<tbody>
<?php
$db = getDbInstance();
$db->where("virtual_wallet_address", '', '!=');
$db->where("id", '5137', '!=');
$resultData = $db->get('admin_accounts');
if ( $db->count > 0 ) {
	foreach ($resultData as $k1=>$row) {
		?><tr>
			<td><?php echo $row['id']; ?></td>
			<td><?php echo $row['wallet_address']; ?></td><?php
		
		foreach($n_decimal_point_array2 as $k2=>$v2) {
			?><td><?php echo $row['etoken_'.$k2]; ?></td><?php


			$db = getDbInstance();
			$db->where("send_wallet_address", $row['wallet_address']);
			$db->where('coin_type', $k2);
			$point = 0;
			$row_point = $db->getOne('etoken_logs', 'sum(points) as points');
			if ( !empty($row_point['points']) ) {
				$point = $row_point['points'];
				if ( $point  != 0 ) {
					$point = $point * -1;
				}
			}

			$db = getDbInstance();
			$db->where("send_wallet_address", $row['virtual_wallet_address']);
			$db->where('coin_type', $k2);
			$point2 = 0;
			$row_point = $db->getOne('etoken_logs', 'sum(points) as points');
			if ( !empty($row_point['points']) ) {
				$point2 = $row_point['points'];
				if ( $point2  != 0 ) {
					$point2 = $point2 * -1;
				}
			}

			
			?>
				<td><?php echo $point; ?><?php if ( $k2 == 'etp3' && $point2 != 0 ) { echo '///'; } ?></td>
				<td><?php echo $point2; ?></td>
			<?php


			if ( $k2 == 'etp3' && $point2 != 0 ) {
				$updateArr = [] ;
				$db = getDbInstance();
				$db->where("id", $row['id']);
				$updateArr['etoken_'.$k2] =  $point;
				//$last_id = $db->update('admin_accounts', $updateArr);
			}






		} // foreach
		?></tr><?php
	} // foreach
} // if

?>
</tbody>
</table>
<?php



return;
exit;


/*
if ( !empty($_POST['dev_id']) && empty($row[0]['devId'])  &&  !empty($_POST['dev_use']) && $_POST['dev_use'] == 'Y' ) {
$db = getDbInstance();
$db->where("id", $userId, '!=');
$db->where('devId', $_POST['dev_id']);
$dev_count = $db->getValue('admin_accounts', 'count(*)');
if ( $dev_count > 0 ) {
	$_SESSION['login_failure'] = !empty($langArr['login_device_id_message4']) ? $langArr['login_device_id_message4'] : 'Only one ID can be registered on one device.'; // 하나의 장치에 1개의 아이디만 등록할 수 있습니다.
	header('Location:login.php');
	exit;
}
}

*/
//ini_set('memory_limit', -1);
//echo memory_get_usage();
echo '<br />';



/*

// blocked IP Code, 20.10.20
// $userip = new_getUserIpAddr();
$blocked_ip_count = 0;
$db = getDbInstance();
$db->where("ip_name", $userip);
$blocked_ip_count = $db->getValue('blocked_ips', 'count(*)');
if ($blocked_ip_count > 0) { 
header('location: login.php');
exit();
}
*/




$last_login_at = '2020-10-11 15:34:23';
$last_login_date_tmp = explode(' ', $last_login_at);
$last_login_date = $last_login_date_tmp[0];
echo $last_login_date.'/';

echo '<br /><br />';
$phone2 = '(10)222-1_2.3~4 5.e(';
$phone3 = preg_replace('/[\(\)\-\_\.~\s]/i', '', $phone2);
echo $phone2.'<br />'.$phone3.'<br />';

return;
exit;

/*
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
if ( !empty($result) ) {
	$gasPriceInWei = $result->toString();
}
});
echo $gasPriceInWei.'<br />';

$gasPriceInWei = 40000000000;
$gasPriceInWei = $wi_wallet_infos->get_gas_price('average1');
echo 'gasPriceInWei(average1) : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';


$gasPriceInWei = $wi_wallet_infos->get_gas_price('fast');
echo 'gasPriceInWei(fast) : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';

$gasPriceInWei = $wi_wallet_infos->get_gas_price('average');
echo 'gasPriceInWei(average) : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';

$gasPriceInWei = $wi_wallet_infos->get_gas_price('fastest');
echo 'gasPriceInWei(fastest) : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';
*/


//$percent = 60;
//$gasPriceInGwei_per = $gasPriceInGwei / $percent;
//echo '1 CTC = '.$percent.' Gwei<br />';
//echo 'Fee : '. number_format($gasPriceInGwei_per, 1).' CTC<br />';



//$tmp = $wi_wallet_infos->get_txId_result('0x74ecceac6a46cde81e5c977909c0f98bf6eea2eb54c9d813dece0e2ae29e6667');
//echo $tmp;

//$ctc_balance = $wi_wallet_infos->wi_get_balance('1', 'ctc', '0xf4a587c23316691f8798cf08e3b541551ec1ffcb', $contractAddressArr);



//	$db = getDbInstance();
//	$get_airdrop = $db->where("module_name", 'send_free_etp3')->getValue('settings', 'value');
//	echo $get_airdrop;

/*
require_once BASE_PATH.'/lib/SendMail.php';
$wi_send_mail = new SendMail();


$to_email = 'dngngnzz@naver.com';
$subject = 'subject';
$contents = array('contents', 'link<a href="google.com">google.com</a>');
//$result = $wi_send_mail->send_email($to_email, $subject, $contents);
//echo $result;
$country = '82';
$phone = '01049138089';
$contents = 'SMS내용123abc';

$result = $wi_send_mail->send_sms($country, $phone, $contents);
echo $result;
*/

//echo $wi_wallet_infos->wi_get_status('0x1e7538644124b6790e221010a419166c564fa400996054b1135084c68a3702fc');
$ctc_balance = -1;
//$ctc_balance = new_number_format($ctc_balance, $n_decimal_point_array['ctc']);
//echo $ctc_balance;

//echo 'session-lang : '.$_SESSION['lang'].' / ';
//echo $langArr['member_auth_finish'];
//print_r($langArr);



$gasPriceInWei = 40000000000;

$curl = curl_init();
$url1 = 'https://ethgasstation.info/api/ethgasAPI.json?api-key='.$ethApiKey;

echo '<a href="https://etherscan.io/gastracker" target="_blank">https://etherscan.io/gastracker</a><br />';
echo '<a href="'.$url1.'" target="_blank">'.$url1.'</a><br />';


curl_setopt_array($curl, array(
CURLOPT_URL => "https://ethgasstation.info/api/ethgasAPI.json?api-key=".$ethApiKey,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "GET",
CURLOPT_HTTPHEADER => array(
"cache-control: no-cache",
"postman-token: bf5e409c-28bf-4abb-2670-d47bdf8f690e"
),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

$decodeData = json_decode($response,true);
//print_r($decodeData);
if (isset($decodeData['fast'])) {
echo $decodeData['fast'].'<br />';
$gasPriceInWei = $decodeData['fast'] * 100000000;
}

echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';

/*

$db = getDbInstance();
$db->where("result", 'N');
$resultData = $db->get('temp_etoken_list');


if ( !empty($resultData) ) {
foreach($resultData as $k=>$row) {

	$user_id = $row['user_id'];
	$user_wallet_address1 = $row['wallet_address'];
	$token = $row['etoken'];
	$etoken_amount = $row['points'];
	$adminId = $n_master_etoken_id;
	$adminWalletAddress = $n_master_etoken_wallet_address;
	echo $user_id.'<br />';

	
	$db = getDbInstance();
	$db->where("id", $user_id);
	$updateArr = [];
	$updateArr['etoken_'.$token] = $db->inc($etoken_amount);
	$last_id1 = $db->update('admin_accounts', $updateArr);
	if ( $last_id1 ) {
		$data_to_send_logs = [];
		$data_to_send_logs['user_id'] = $user_id;
		$data_to_send_logs['wallet_address'] = $user_wallet_address1;
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['points'] = $etoken_amount;
		$data_to_send_logs['in_out'] = 'in';
		$data_to_send_logs['send_type'] = 'from_admin';
		$data_to_send_logs['send_user_id'] = $adminId;
		$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
		$data_to_send_logs['send_fee'] = '0';
		$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
		
		$db = getDbInstance();
		$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
	}

	$db = getDbInstance();
	$db->where("id", $adminId);
	$updateArr = [];
	$updateArr['etoken_'.$token] = $db->dec($etoken_amount);
	$last_id2 = $db->update('admin_accounts', $updateArr);
	if ( $last_id2 ) {
		$data_to_send_logs = [];
		$data_to_send_logs['user_id'] = $adminId;
		$data_to_send_logs['wallet_address'] = $adminWalletAddress;
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['points'] = '-'.$etoken_amount;
		$data_to_send_logs['in_out'] = 'out';
		$data_to_send_logs['send_type'] = 'from_admin';
		$data_to_send_logs['send_user_id'] = $user_id;
		$data_to_send_logs['send_wallet_address'] = $user_wallet_address1;
		$data_to_send_logs['send_fee'] = '0';
		$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
		
		$db = getDbInstance();
		$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
	}
	

	$updateArr = [] ;
	$db = getDbInstance();
	$db->where("id", $row['id']);
	$updateArr['result'] = 'Y';
	$last_id = $db->update('temp_etoken_list', $updateArr);



}
}
*/



//$walletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';
//$rr1 = $wi_wallet_infos->wi_get_balance('', 'all', $walletAddress, $contractAddressArr);
//print_r($rr1);

/*
//$token = 'etp3'; $totalAmt = 10; $getTokenFeeVal = 1;
//$token = 'etp3'; $totalAmt = 10; $getTokenFeeVal = 0;
$token = 'ectc'; $totalAmt = 10; $getTokenFeeVal = 1;
//$token = 'ectc'; $totalAmt = 10; $getTokenFeeVal = 0;

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
if ( $token == 'ectc' ) {
$updateArr['etoken_ectc'] = 'etoken_ectc - '.$totalAmt;
}
$last_id = $db->update('admin_accounts', $updateArr);
//$last_id = $db->rawQuery("update admin_accounts set etoken_ectc = etoken_ectc - ".$totalAmt." - ".$getTokenFeeVal." WHERE id=", $_SESSION['user_id']);

$db = getDbInstance();
$db->where("id", '5137');
$updateArr = array (
//	'etoken_ectc' => $db->inc(10)
// 차감 : $db->dec
);
//'etoken_ectc' => $db->dec(10)
//'etoken_ectc' => $db->inc(10)

$last_id = $db->update('admin_accounts', $updateArr);
*/

function kisa_ip_chk(){
// https://후이즈검색.한국/kor/openkey/keyCre.do
$ip = getUserIpAddr();
//$ip = '2600:1012:b01e:acad:8ca8:69ad:e5b1:2d5c';
$key = "2020032517154809084222";
$url ="http://whois.kisa.or.kr/openapi/ipascc.jsp?query=".$ip."&key=".$key."&answer=json";
$ch = curl_init();

curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_NOSIGNAL, 1);
//curl_setopt($ch,CURLOPT_POST, 1); //Method를 POST. 없으면 GET
$data = curl_exec($ch);
$curl_errno = curl_errno($ch);
$curl_error = curl_error($ch);
curl_close($ch);
$decodeJsonData = json_decode($data, true);
return $decodeJsonData['whois']['countryCode'];
}

function ipinfo_ip_chk() { // 수량 체크 테스트용. whois 대신 사용 가능한지 check (2020.05.14, YMJ)
// https://ipinfo.io/
//$access_token = 'd5b65ce795f734'; // 무료 version key (50,000건)
$access_token = '7c984c718aef66'; // 무료 version key (50,000건)
//$access_token = '6ad007f53defcc';
$ip_address = getUserIpAddr();
//$ip_address = '2600:1012:b01e:acad:8ca8:69ad:e5b1:2d5c';
$country = '';

//$url = "https://ipinfo.io/{$ip_address}?token=".$access_token;
//$details = json_decode(@file_get_contents($url));
//if ( !empty($details->country) ) {
//	return $details->country;
//}
$url = "https://ipinfo.io/{$ip_address}/country?token=".$access_token;
//try {
	$country = @file_get_contents($url);
	//if ( empty($country) ) {
	//}
//} catch (Exception $e) {
//}
return $country; // 국내 : KR
}

//echo 'WHOIS OpenAPI : '.kisa_ip_chk().'<br />';

//$ip_kor = trim(ipinfo_ip_chk());
//echo 'IPinfo : /'.$ip_kor.'/<br />';


/*

{ "ip": "109.169.23.83", "city": "Maidenhead", "region": "England", "country": "GB", "loc": "51.5228,-0.7199", "org": "AS20860 IOMART CLOUD SERVICES LIMITED", "postal": "SL6", "timezone": "Europe/London" }
{ "ip": "178.162.205.226", "city": "Frankfurt am Main", "region": "Hesse", "country": "DE", "loc": "50.1025,8.6299", "org": "AS28753 Leaseweb Deutschland GmbH", "postal": "60326", "timezone": "Europe/Berlin" }

*/
function getUserIpAddr()
{
if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	//ip from share internet
	$ip = $_SERVER['HTTP_CLIENT_IP'];
}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
	//ip pass from proxy
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}else{
	$ip = $_SERVER['REMOTE_ADDR'];
}
return $ip;
}

//////////////////////////////////
/*

// etherscan api
$ethApiKey = 'ehtkey';

$tx = '0x1f5c5172ec0407f02661f864c6e0c216d86ac9fe7d679c47544e023d7aeee8e3'; // 조회불가
// getstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 
// gettxreceiptstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [status] => ) ) 
// proxy(eth_getTransactionByHash) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => ) 
// proxy(eth_getTransactionReceipt) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => ) 


//$tx = '0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a'; // fail
// getstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 1 [errDescription] => Reverted ) ) 
// gettxreceiptstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [status] => 0 ) ) 
// proxy(eth_getTransactionByHash) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x42e15dd6f02fbb7b382b0dd30fa614e13907ce961a71478ade53426a92074a38 [blockNumber] => 0x97dbf3 [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gas] => 0x186a0 [gasPrice] => 0x6fc23ac00 [hash] => 0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a [input] => 0x23b872dd000000000000000000000000f4a587c23316691f8798cf08e3b541551ec1ffcb00000000000000000000000006978f9023a79138376b722db285da08bd068ad3000000000000000000000000000000000000000000000000016345785d8a0000 [nonce] => 0x3af2 [to] => address [transactionIndex] => 0x26 [value] => 0x0 [v] => 0x25 [r] => 0x762c2ae26e6c16c57e70b5784cc19581b03f94c6591320d84378b3fab2aefdec [s] => 0x75be12fc409129996ff52e51688f77e7126b5ad6b601ca21470f7a638c2157d7 ) ) 
// proxy(eth_getTransactionReceipt) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x42e15dd6f02fbb7b382b0dd30fa614e13907ce961a71478ade53426a92074a38 [blockNumber] => 0x97dbf3 [contractAddress] => [cumulativeGasUsed] => 0x148e0c [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gasUsed] => 0x933d [logs] => Array ( ) [logsBloom] => 0x00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000 [status] => 0x0 [to] => address [transactionHash] => 0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a [transactionIndex] => 0x26 ) ) 


//$tx = '0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e'; // success
// getstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [isError] => 0 [errDescription] => ) ) 
// gettxreceiptstatus : Array ( [status] => 1 [message] => OK [result] => Array ( [status] => 1 ) ) 
// proxy(eth_getTransactionByHash) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [blockNumber] => 0x97de8c [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gas] => 0x186a0 [gasPrice] => 0x6fc23ac00 [hash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [input] => 0x23b872dd000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d0000000000000000000000000000000000000000000003cfc82e37e9a7400000 [nonce] => 0x3afa [to] => address [transactionIndex] => 0x19 [value] => 0x0 [v] => 0x26 [r] => 0x45f916d6d67037bf2ce2b37ef0954106cb9238b3501a6276fa7bf3220c58a920 [s] => 0x3f7755dc96c079acb0f89fd03d8ee059fbb0488e30ff8a2733aecfccd2f6cce6 ) ) 
// proxy(eth_getTransactionReceipt) : Array ( [jsonrpc] => 2.0 [id] => 1 [result] => Array ( [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [blockNumber] => 0x97de8c [contractAddress] => [cumulativeGasUsed] => 0x127b57 [from] => 0xcea66e2f92e8511765bc1e2a247c352a7c84e895 [gasUsed] => 0x8e7b [logs] => Array ( [0] => Array ( [address] => address [topics] => Array ( [0] => 0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef [1] => 0x000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d [2] => 0x000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d ) [data] => 0x0000000000000000000000000000000000000000000003cfc82e37e9a7400000 [blockNumber] => 0x97de8c [transactionHash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [transactionIndex] => 0x19 [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [logIndex] => 0x15 [removed] => ) [1] => Array ( [address] => address [topics] => Array ( [0] => 0x8c5be1e5ebec7d5bd14f71427d1e84f3dd0314c0f7b2291e5b200ac8c7c3b925 [1] => 0x000000000000000000000000b6c01773211968ee3a73e24cbea8a00d722fef4d [2] => 0x000000000000000000000000cea66e2f92e8511765bc1e2a247c352a7c84e895 ) [data] => 0x00000000000000000000000000000000000000001027e0179ff5669fe7e80000 [blockNumber] => 0x97de8c [transactionHash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [transactionIndex] => 0x19 [blockHash] => 0x2f447d0c4bf4ca096b752f5ad45ac80dc0199991011d2341be7effed753beccd [logIndex] => 0x16 [removed] => ) ) [logsBloom] => 0x00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000002000000280000000000004000000000000008010000000000000000000000000000001000000000000000000000000000000000800000000000000000000000000010000000000000000000000000000000000000000000000010000000000000000000000000020000000000000000000000000000000000000000000000000000000000000000000002000000000000000000000100000000000000000000000000000000000010000000002000000000000000000000000000000000000000000000000000 [status] => 0x1 [to] => address [transactionHash] => 0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e [transactionIndex] => 0x19 ) ) 

//$eurl = 'https://api.etherscan.io/api?module=transaction&action=getstatus&txhash='.$tx.'&apikey='.$ethApiKey;
$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$tx.'&apikey='.$ethApiKey; // status가 1인 경우에만 성공
//$eurl = 'https://api.etherscan.io/api?module=proxy&action=eth_getTransactionByHash&txhash='.$tx.'&apikey='.$ethApiKey;
//$eurl = 'https://api.etherscan.io/api?module=proxy&action=eth_getTransactionReceipt&txhash='.$tx.'&apikey='.$ethApiKey;

//https://api.etherscan.io/api?module=transaction&action=getstatus&txhash=0x1f5c5172ec0407f02661f864c6e0c216d86ac9fe7d679c47544e023d7aeee8e3&apikey=ehtkey

function check_eth_result($txhash, $ethApiKey) {
$result = '';
$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ethApiKey; // status가 1인 경우에만 성공
	
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => $eurl,
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

$result = !empty($getResultDecode['result']['status']) ? $getResultDecode['result']['status'] : '';
return $result;
}

$tx = '0x1f5c5172ec0407f02661f864c6e0c216d86ac9fe7d679c47544e023d7aeee8e3'; // 조회불가
echo $tx.' : '.check_eth_result($tx, $ethApiKey);
echo '<br />';
$tx = '0x09b100b01144c0fd1ceb1f5afca9043d8e5f2d61965b234f9dbd3b5cfb83312a'; // fail
echo $tx.' : '.check_eth_result($tx, $ethApiKey);
echo '<br />';
$tx = '0x1988e8340373447ed9ca07549598b3adb782070b8668376b4c3db17ce392278e'; // success
echo $tx.' : '.check_eth_result($tx, $ethApiKey);
*/


?>	

