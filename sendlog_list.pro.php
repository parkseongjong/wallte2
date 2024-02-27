<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

/*require_once 'includes/auth_validate.php';

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:./index.php');
	 exit;
}

$return_url = 'sendlog_list.php';
*/

echo "Start : ".date('Y-m-d H:i:s')."\n";

$db = getDbInstance();
$db->orwhere('status', 'send');
$db->orwhere('status', 'pending');

$page = 1;
$db->orderBy('rand()', 'asc');
$db->pageLimit = 50; // 35 : 1 minutes
$infos = $db->arraybuilder()->paginate("user_transactions_all", $page); // , $select
//$infos = $db->get('user_transactions_all');
$send_sms_count = 0;

$be_date = date('Y-m-d H:i:s', strtotime ('-60 days'));

$column = array(
	'A.account_type2','A.virtual_wallet_address','A.id_auth','A.transfer_passwd','A.email_verify',
	'B.id','B.email','B.wallet_phone_email','B.register_with','B.passwd','B.passwd_new','B.passwd_salt','B.passwd_datetime',
	'B.name','B.lname','B.user_ip','B.phone','B.gender','B.dob','B.location','B.auth_phone','B.auth_name','B.auth_gender',
	'B.auth_dob','B.auth_local_code','B.n_country','B.n_phone','B.device','B.devId','B.devId2','B.devId3'
);

if (!empty($infos)) {
	foreach($infos as $row) {
		$status_r = '';
		$virtual = '';
		$send_sms_r = '';
		$status_r = check_eth_result($row['transactionId'], $ethApiKey);
		//echo $row['id'].' / '.$row['transactionId'].' : '.$status_r.'<br />';

		// fail 중 최근 3일 이내의 경우에는 실패처리 안함
		// pending 리턴값이 동일 URL(같은 txhash값)확인하는데도 서로 다른 결과를 보여줌
		if ( $status_r == 'none' && $row['created_at'] > $be_date ) { // fail 중 최근 30일 이내의 경우에는 실패처리 안함
			$status_r = 'F';
		}


		if ( $status_r != 'F' ) { // F=조회실패
			if ( $status_r == 'none' || $status_r == 'fail') { // none = 3일 지난건 실패처리
				$status_r = 'fail';
				$send_sms_r = 'D';
			} else {

				// 문자/메일발송 시작
				if ( $row['send_sms'] == 'T' && $status_r == 'success') {
					$db = getDbInstance();
					//휴면 계정 ... cron.. 확인 할 방법이 필요함....

//					if($_SERVER['REMOTE_ADDR'] == '112.171.120.140') {
						//2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
						//휴면 회원 쪽 조회 START
						$db->where("A.wallet_address", $row['to_address']);
						$db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
						$to_row = $db->getOne('admin_accounts A',$column);
						if(!$row){
							$db->where("wallet_address", $row['to_address']);
							$to_row = $db->getOne('admin_accounts');
						}
						//휴면 회원 쪽 조회 END
//					}
//					else{
//						$db->where("wallet_address", $row['to_address']);
//						$to_row = $db->getOne('admin_accounts');
//					}
					//if ($db->count >= 1) {
					if ( !empty($to_row['id']) ) {
						$to_name = get_user_real_name($to_row['auth_name'], $to_row['name'], $to_row['lname']);
					}
					else {
						$db = getDbInstance();

						//if($_SERVER['REMOTE_ADDR'] == '112.171.120.140') {
							//2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
							//휴면 회원 쪽 조회 START
							$db->where("A.virtual_wallet_address", $row['to_address']);
							$db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
							$to_row = $db->getOne('admin_accounts A',$column);
							if(!$row){
								$db->where("virtual_wallet_address", $row['to_address']);
								$to_row = $db->getOne('admin_accounts');
							}
							//휴면 회원 쪽 조회 END
						//}
						//else{
						//	$db->where("virtual_wallet_address", $row['to_address']);
						//	$to_row = $db->getOne('admin_accounts');
						//}

						if ($db->count >= 1) {
							$to_name = get_user_real_name($to_row['auth_name'], $to_row['name'], $to_row['lname']);
							if ( !empty($to_row['virtual_wallet_address']) && $to_row['virtual_wallet_address'] == $row['to_address'] ) {
								$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
								$to_name = $to_name.$virtual_account_tx1;
								$virtual = '1';
							}
						}
					}
					
					$db = getDbInstance();
					$db->where("id", $row['from_id']);
					$from_row = $db->getOne('admin_accounts');
					$from_name = get_user_real_name($from_row['auth_name'], $from_row['name'], $from_row['lname']);



					$amount = $row['amount'];
					$coin_type = $row['coin_type'];
					$coin_type2 = strtoupper($coin_type);
					$link = 'https://etherscan.io/tx/'.$row['transactionId'];

					$country = '';
					$phone = '';

					$send_sms_message4 = !empty($langArr['send_sms_message4']) ? $langArr['send_sms_message4'] : 'Check on Etherscan : ';
					$thanks = !empty($langArr['thanks']) ? $langArr['thanks'] : 'Thanks';
					$send_sms_message3 = !empty($langArr['send_sms_message3']) ? $langArr['send_sms_message3'] : 'CyberTronChain : Coin has been sent.';
					$alert_msg = '';
					if ( $virtual == '' ) {
						$send_sms_message1 = !empty($langArr['send_sms_message1']) ? $langArr['send_sms_message1'] : ' sent ';
						$alert_msg = $from_name.$send_sms_message1.new_number_format($amount, $n_decimal_point_array[$coin_type]).$coin_type2;
						$alert_msg .= isset($langArr['send_sms_message2']) ? $langArr['send_sms_message2'] : '';
					} else {
						$send_sms_vertual_message1= !empty($langArr['send_sms_vertual_message1']) ? $langArr['send_sms_vertual_message1'] : " sent ";
						$send_sms_vertual_message2 = !empty($langArr['send_sms_vertual_message2']) ? $langArr['send_sms_vertual_message2'] : " for the purchase of goods.";
						$alert_msg = $from_name.$send_sms_vertual_message1.new_number_format($amount, $n_decimal_point_array[$coin_type]).$coin_type2.$send_sms_vertual_message2;
					}
					$date = date("Y-m-d");
							
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
						
					} else {

						$email_address = $to_row['email'];
						
						if ( !empty($email_address) && $to_row['email_verify'] == 'Y') {

							$mailHtml = '<table align="center" width="600"  style=" background:#fff; ">
								<tbody>
									<tr align="center" > 
										<td><img src="http://'.$_SERVER['HTTP_HOST'].'/wallet2/images/logo3.png" /></td>
									</tr>
									<tr align="center">
										<td><p style="padding:0 3%; line-height:25px; text-align: justify;">'.$alert_msg.'</p></td>
									</tr>
									<tr>
										<td align="center";><div style=" font-weight:bold; padding: 12px 35px; color: #fff; border-radius:5px; text-align:center; font-size: 14px; margin: 10px 0 20px; background: #ec552b; display: inline-block; text-decoration: none;">'.$send_sms_message4.'<a href="'.$link.'">'.$link.'</a></div></td>
									</tr>
									<tr align="center">
										<td><p style="padding:0 3%; line-height:25px; text-align: justify; margin:0px;">'.$thanks.' <br/>Team Support</p></td>
									</tr>
								</tbody>
							</table>
							<table style="color:#b7bbc1;width:600px;">
								<tr><td style="text-align: center;"><h4>©'.$date.' All right reserved</h4></td></tr>
							</table>
							';
							
							require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)
							
							$email = new \SendGrid\Mail\Mail();
							$email->setFrom($n_email_from_address, "CyberTron Coin");
							$email->setSubject($send_sms_message3);
							$email->addTo($email_address);
							
							$email->addContent("text/html", $mailHtml);
							
							$sendgrid = new \SendGrid('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');
							
							try {
								$response = $sendgrid->send($email);
								$send_sms_r = 'Y';
								$send_sms_count = $send_sms_count + 1;

							} catch (Exception $e) {
								$send_sms_r = 'F';
								echo "Email Send Error ID : ".$row['id']." / Mesage : ".$e->getMessage()."\n";
							}
							
						} else {
							$send_sms_r = 'F';

						} // 
					}



				}
				// 문자/메일발송 종료
			}

			$db = getDbInstance();
			$db->where("id", $row['id']);
			$updateArr = [] ;
			$updateArr['status'] =  $status_r;
			if ( !empty($send_sms_r) ) {
				$updateArr['send_sms'] =  $send_sms_r;
			}
			$last_id = $db->update('user_transactions_all', $updateArr);

		} // if

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
	} else {
		//$result2 = check_eth_result2($txhash, $ethApiKey);
		//$status = $result2;
		$status = 'pending';
	}
	return $status;
}


function check_eth_result2($txhash, $ethApiKey) {
	$result = '';
	$eurl = 'https://api.etherscan.io/api?module=proxy&action=eth_getTransactionByHash&txhash='.$txhash.'&apikey='.$ethApiKey;
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

	if ( !empty($getResultDecode['result']) && empty($getResultDecode['result']['blockNumber']) ) {
		$result = 'pending';

	// 20.06.23 13:09 주석
	// 성공했는데도 실패라고 표시되는 이유가 check_eth_result에서 '' 리턴받고(pending중) check_eth_result2 검사하려는데 그 잠깐 사이에 success한 경우 blockNumber값이 생기기 때문에 success인데도 fail로 실패처리되는게 아닌가 싶다. 
	// 따라서 none 표시하고 fail이 확실할 때까지 검사하도록 하도록 수정 -> success인데 fail로 표시되는건이 있는지 확인해볼 것
	//} else if ( !empty($getResultDecode['result']) && !empty($getResultDecode['result']['blockNumber']) ) {
	//	$result = 'fail';
	
	} else {
		$result = 'none';
	}
	return $result;
}


?>
