<?php
// Page in use -> Not used
// 7,17,27,37,47,57 * * * * sendlog_list.pro2.php -O ->> /var/www/html/wallet2/_cron3.log
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";
// https://cybertronchain.com/wallet2/sendlog_list.pro3.php
/*require_once 'includes/auth_validate.php';

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:./index.php');
	 exit;
}

$return_url = 'sendlog_list.php';
*/

echo "Start : ".date('Y-m-d H:i:s')."\n";

$db = getDbInstance();
$db->where('send_type', 'register');
$db->where('send_sms', 'N');
$db->where('id', '11026', '>');
//$db->where('id', '1482'); // success

$page = 1;
$db->orderBy('rand()', 'asc');
$db->pageLimit = 20;
$infos = $db->arraybuilder()->paginate("user_transactions_all", $page);
//$infos = $db->get('user_transactions_all');
$send_sms_count = 0;

if (!empty($infos)) {
	foreach($infos as $row) {
		$status_r = '';
		$virtual = '';
		$send_sms_r = '';
		if ( !empty($row['transactionId']) ) {
			$status_r = check_eth_result($row['transactionId'], $ethApiKey);
			//echo $row['id'].' / '.$row['transactionId'].' : '.$status_r.'<br />';
			$receiver_id = $row['to_id'];
			
			if ( $status_r == 'F' ) { // error
				$status_r = '';
			} else {
				
				if ( $status_r == 'success') {
					// 문자발송 시작
					$db = getDbInstance();
					$db->where("id", $receiver_id);
					$to_row = $db->getOne('admin_accounts');
					if ( !empty($to_row['id']) ) {
						$to_name = get_user_real_name($to_row['auth_name'], $to_row['name'], $to_row['lname']);
						
						$amount = $row['amount'];
						$coin_type = $row['coin_type'];
						$coin_type2 = strtoupper($coin_type);

						$country = '';
						$phone = '';
						$alert_msg = '';
						
						$airdrop_send_message1 = ' 님의 지갑에 ';
						$airdrop_send_message2 = '가 무료지급 되었습니다.';
						$alert_msg = 'CyberTChain '.$to_name.$airdrop_send_message1.new_number_format($amount, $n_decimal_point_array[$coin_type]).$coin_type2.$airdrop_send_message2;
						// 100 TP3 was paid free of charge into 이름's Wallet
						// 이름 님의 지갑에 100 TP3가 무료지급 되었습니다.
						
						if ( $to_row['register_with'] == 'phone' || ($to_row['id_auth'] == 'Y' && !empty($to_row['auth_phone']) ) ) { // 핸드폰가입자 혹은 본인인증한 경우
							if ( $to_row['id_auth'] == 'Y' ) { // 본인인증한 경우
								if ( !empty($to_row['n_country']) ) {
									$country = $to_row['n_country'];
								} else{
									$country = '82';
								}
								$phone = $to_row['auth_phone'];
							} else {
								$country = $to_row['n_country'];
								$phone = $to_row['n_phone'];
							}
							
							if ( !empty($country) && !empty($phone) ) {
								try {
									$phone3 = preg_replace('/[\(\)\-\_\.~\s]/i', '', $phone);

									$rest = new Message($n_api_key, $n_api_secret);

									$options = new stdClass();
									$options->to = $phone3; // 수신번호
									$options->from = $n_sms_from_tel; // 발신번호
									
									$options->country = $country;
									$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
									$options->text = $alert_msg; // 문자내용

									$result = $rest->send($options);     

									if($result->success_count == '1') {
										$send_sms_r = 'Y';
										$send_sms_count = $send_sms_count + 1;
									}

								} catch(CoolsmsException $e) {
									$send_sms_r = 'F';
									echo "SMS Send Error ID : ".$row['id']." / Mesage : ".$e->getMessage()."\n";
								}
								
							} else {	
								$send_sms_r = 'F';
							}
						}
					}
					// 문자발송 종료

				} else if ($status_r == 'fail') {
					$send_sms_r = 'D';
				}
			}

		} else { // txid 없는경우
			$status_r = 'fail';
			$send_sms_r = 'D';
		}
						
		if ( !empty($status_r) ) {				
			$db = getDbInstance();
			$db->where("id", $row['id']);
			$updateArr = [] ;
			$updateArr['status'] =  $status_r;
			if ( !empty($send_sms_r) ) {
				$updateArr['send_sms'] =  $send_sms_r;
			}
			$last_id = $db->update('user_transactions_all', $updateArr);

			//if ( $status_r == 'fail' ) {
				// airdrop 재시도
			//}
		}

	} // foreach

	echo "Send/Total Count : ".$send_sms_count."/".count($infos)."\n";
}
echo "Finish : ".date('Y-m-d H:i:s')."\n\n";

function check_eth_result($txhash, $ethApiKey) {
	$result = '';
	$result2 = '';
	$status = '';
	$eurl = 'https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash='.$txhash.'&apikey='.$ethApiKey;
		
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

	if ( !empty($err) ) {
		return 'F';
	}
	
	$result = isset($getResultDecode['result']['status']) ? $getResultDecode['result']['status'] : '';

	if ( $result == '1') {
		$status = 'success';
	} else if ( $result == '0') {
		$status = 'fail';
	}
	return $status;
}

?>
