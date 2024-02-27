<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

// 전송 결과 화면

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

require_once 'includes/header.php'; 
	
$send_result = '';
$to_name = '';
$virtual = '';
$send_sms_r = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' )	{
	$txid = !empty($_GET['txid']) ? $_GET['txid'] : '';
	$type = !empty($_GET['type']) ? $_GET['type'] : ''; // send
	$virtual_account_tx1 = '';

	if ( empty($txid) ) { // 전송실패
		$send_result = 'F';
	} else {
		$db = getDbInstance();
		$db->where("id", $txid);
		$row = $db->get('user_transactions_all');


		// 200828
		$send_result_store = '';
		if ( !empty($row[0]['id']) && $row[0]['send_type'] == 'send' ) {
			$db = getDbInstance();
			$db->where("wallet_address", $row[0]['to_address']);
			$kiosk_row = $db->getOne('kiosk_config');

			if ( empty($row[0]['transactionId']) ) { // 전송 실패
				$send_result_store = 'F';
			} else {
				$send_result_store = 'Y';
			}
			if ( !empty($kiosk_row['name']) ) {
					$db = getDbInstance();
					$db->where("id", $row[0]['id']);
					$updateArr2 = [] ;
					$updateArr2['store_name'] = $kiosk_row['name'];
					$updateArr2['store_result'] = $send_result_store;
					$last_id2 = $db->update('user_transactions_all', $updateArr2);
			}
		}



		if ( empty($row[0]['transactionId']) ) { // 전송 실패
			$send_result = 'F';
		} else {
			if ($row[0]['send_sms'] == 'Y') { // 이미 발송함
				$send_result = 'Y';
			}
			$amount = $row[0]['amount'];
			$coin_type = $row[0]['coin_type'];
			$coin_type2 = strtoupper($coin_type);
			$link = 'https://etherscan.io/tx/'.$row[0]['transactionId'];

			// send시
			$db = getDbInstance();
            //휴면 계정 확인용 컬럼.
            $column = array(
                'A.account_type2','A.virtual_wallet_address','A.id_auth','A.transfer_passwd',
                'B.id','B.email','B.wallet_phone_email','B.register_with','B.passwd','B.passwd_new','B.passwd_salt','B.passwd_datetime',
                'B.name','B.lname','B.user_ip','B.phone','B.gender','B.dob','B.location','B.auth_phone','B.auth_name','B.auth_gender',
                'B.auth_dob','B.auth_local_code','B.n_country','B.n_phone','B.device','B.devId','B.devId2','B.devId3'
            );
            //if($_SERVER['REMOTE_ADDR'] == '112.171.120.140'){
                //2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
                //휴면 회원 쪽 조회 START
                $db->where("A.wallet_address", $row[0]['to_address']);
                $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
                $to_row = $db->getOne('admin_accounts A',$column);
                if(!$to_row){
                    $db->where("wallet_address", $row[0]['to_address']);
                    $to_row = $db->getOne('admin_accounts');
                }
                //휴면 회원 쪽 조회 END
//            }
//            else{
//                $db->where("wallet_address", $row[0]['to_address']);
//                $to_row = $db->getOne('admin_accounts');
//            }

			//if ($db->count >= 1) {
			if ( !empty($to_row['id']) ) {
				$to_name = get_user_real_name($to_row['auth_name'], $to_row['name'], $to_row['lname']);
				if ( !empty($to_row['account_type2']) && $to_row['account_type2'] != 'wallet' ) {
					$to_name = '('.$to_row['account_type2'].') '.$to_name;
				}
			} else {
				$db = getDbInstance();
                //2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
                //휴면 회원 쪽 조회 START
//                if($_SERVER['REMOTE_ADDR'] == '112.171.120.140'){
                    $db->where("A.virtual_wallet_address", $row[0]['to_address']);
                    $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
                    $to_row = $db->getOne('admin_accounts A',$column);
                    if(!$to_row){
                        $db->where("virtual_wallet_address", $row[0]['to_address']);
                        $to_row = $db->getOne('admin_accounts');
                    }
                    //휴면 회원 쪽 조회 END
//                }
//                else{
//                    $db->where("virtual_wallet_address", $row[0]['to_address']);
//                    $to_row = $db->getOne('admin_accounts');
//                }

				if ($db->count >= 1) {
					$to_name = get_user_real_name($to_row['auth_name'], $to_row['name'], $to_row['lname']);
					if ( !empty($to_row['virtual_wallet_address']) && $to_row['virtual_wallet_address'] == $row[0]['to_address'] ) {
						$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
						$to_name = $to_name.$virtual_account_tx1;
						$virtual = '1';
					}
				}
			}
			
			$db = getDbInstance();
			$db->where("id", $row[0]['from_id']);
			$from_row = $db->getOne('admin_accounts');
			$from_name = get_user_real_name($from_row['auth_name'], $from_row['name'], $from_row['lname']);

			if ( $_SESSION['lang'] == 'ko' ) {
				$view_msg = $to_name.'님께 '.new_number_format($amount, $n_decimal_point_array[$coin_type]).' '.$coin_type2.' 전송하였습니다.';
			} else {
				$view_msg = 'Sent '.new_number_format($amount, $n_decimal_point_array[$coin_type]).' '.$coin_type2.' to '.$to_name;
			}
			$date = date("Y-m-d");
			
			if ( $row[0]['send_sms'] == 'N') {
				
				// 메일/문자발송 시작
				/*$country = '';
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
							}

						} catch(CoolsmsException $e) {
							$send_sms_r = 'F';
							//echo "SMS Send Error ID : ".$row[0]['id']." / Mesage : ".$e->getMessage()."\n";
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

						} catch (Exception $e) {
							$send_sms_r = 'F';
							//echo "Email Send Error ID : ".$row[0]['id']." / Mesage : ".$e->getMessage()."\n";
						}
						
					} else {
						$send_sms_r = 'F';

					} // 
				}
				*/
				$send_sms_r = 'T';
				if ( !empty($send_sms_r) ) {
					$db = getDbInstance();
					$db->where("id", $row[0]['id']);
					$updateArr = [] ;
					$updateArr['send_sms'] =  $send_sms_r;
					$last_id = $db->update('user_transactions_all', $updateArr);
				}
				// 메일/문자발송 끝


			} // if
		}

	} // if

}

?>

<link  rel="stylesheet" href="css/send.css"/>
</head>

<body>

<div id="page-wrapper">
	
	<div id="send_result">
		<div class="row">
			<?php
			if ( $send_result == 'F') { // 전송 실패
			?>
				<div class="text1">
					<?php echo !empty($langArr['send_sms_message7']) ? $langArr['send_sms_message7'] : 'Cannot be transferred.<br />Please try again in a few minutes.'; ?>
				</div>
				<div class="btn">
					<a href="index.php" title="main"><?php echo !empty($langArr['send_sms_message6']) ? $langArr['send_sms_message6'] : 'HOME'; ?></a>
				</div>
			<?php } else { ?>
				<div class="img"><img src="images/icons/send_chk1.png" alt="send" /></div>
				<div class="text1">
					<?php echo $view_msg; ?>
				</div>
				<div class="text2">
					<?php echo !empty($langArr['send_sms_message5']) ? $langArr['send_sms_message5'] : 'It takes up to 24 hours to complete the transaction.'; ?>
				</div>
				<div class="btn">
					<a href="index.php" title="main"><?php echo !empty($langArr['send_sms_message6']) ? $langArr['send_sms_message6'] : 'HOME'; ?></a>
				</div>
			<?php } ?>

		</div>
	</div>

</div>

</body>
</html>


<?php include_once 'includes/footer.php'; ?>
