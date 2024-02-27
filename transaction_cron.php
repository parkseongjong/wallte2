<?php
// Page in use
require_once './config/config.php';
require_once './config/new_config.php';
require('includes/web3/vendor/autoload.php');

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;

echo "Start : ".date('Y-m-d H:i:s')."\n";

$db = getDbInstance();
$db->where("status", 'pending');
$userTransactions = $db->get('user_transactions');

if(!empty($userTransactions)){
	foreach($userTransactions as $userTransaction){
		$transcationId = $userTransaction['transactionId'];
		$ethAmount = $userTransaction['amount'];
		$recordId = $userTransaction['id'];

		$token = $userTransaction['coin_type'];
		
		$decimal = $contractAddressArr[$token]['decimal'];
		$testAbi = $contractAddressArr[$token]['abi'];
		$contractAddress = $contractAddressArr[$token]['contractAddress'];

		$contract = new Contract($web3->provider, $testAbi);
		switch($token) {
			case 'tp3':
				$adminAccountWalletAddress = $n_master_wallet_address_exc_out_tp3;
				$adminAccountWalletPassword = $n_master_wallet_pass_exc_out_tp3;
				$adminAccountWalletId = $n_master_id_exc_out_tp3;
				$setting_value = 'exchange_rate_tp3';
				break;
			default: // ctc
				$adminAccountWalletAddress = $n_master_wallet_address_exc_out;
				$adminAccountWalletPassword = $n_master_wallet_pass_exc_out;
				$adminAccountWalletId = $n_master_id_exc_out;
				$setting_value = 'exchange_rate';
				break;
		}

		// check status 
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash=".$transcationId."&apikey=".$ethApiKey,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"postman-token: 8b1efa98-e4d4-9221-cded-86fb915c3780"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		$jsonDecode = json_decode($response,true);
        //error_log('cron test raw:'.print_r($response,true),0);
		$transactionStatus = $jsonDecode['result']['status'];
		if(!empty($jsonDecode['result']['status']) && $jsonDecode['result']['status'] == "1"){
			
			$db = getDbInstance();
			$db->where("module_name", $setting_value);
			$getSetting = $db->get('settings');
			$getExchangePrice = $getSetting[0]['value'];
			
			$newTransactionId = '';
			$amountToSend = $ethAmount*$getExchangePrice;
			$amountToSend = round($amountToSend,8);
			$receiverUserId = $userTransaction['sender_id'];
			$db = getDbInstance();
			$db->where("id", $receiverUserId);
			$row = $db->getOne('admin_accounts');
			$firstName = get_user_real_name($row['auth_name'], $row['name'], $row['lname']);
			$toUserAccount = $row['wallet_address'];	
			$registerWith = $row['register_with'];	
			$userEmail = $row['email'];
			$toUserId = $row['id'];
			

			// send To User Account
			$actualAmountToSendWithoutDecimal = $amountToSend;
			$actualAmountToSendWithoutDecimal = round($actualAmountToSendWithoutDecimal,8);
			
			echo $ethAmount." x ".$getExchangePrice." = ".$amountToSend." ".$token."\n";

			$amountToSend = bcmul ($amountToSend, $decimal);
			$amountToSend1 = dec2hex($amountToSend);
			
			$amountToSend = '0x';
			$amountToSend .= $amountToSend1;

			
			// unlock admin account
			$personal = $web3->personal;
			$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
				
			});
			
			//$gasPriceInWei = 40000000000;
			$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
				$gasPriceInWei = $result->toString();
			});
			//echo 'gas : '.$gasPriceInWei.'<br />';
			$gasPriceInWei = "0x".dechex($gasPriceInWei);	

			//echo $ethAmount." / ".$amountToSend." / ".$getExchangePrice."\n";
			echo "From ".$adminAccountWalletAddress." To ".$toUserAccount."\n";
			
			$contract->at($contractAddress)->send('transfer', $toUserAccount, $amountToSend, [
					'from' => $adminAccountWalletAddress,
					'gas' => '0x186A0',   //100000
					'gasprice'=>$gasPriceInWei
					//'gas' => '0x186A0',   //100000
					//'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
				], function ($err, $result) use ($contract,&$newTransactionId) {
						if ($err !== null) {
							//continue;
							echo 'Error:  ' . $err->getMessage()."\n";
						}
						$newTransactionId =$result; 
				});
			
			
			// Add log records (2020-05-21 16:56, YMJ)
			$data_to_send_logs = [];
			$data_to_send_logs['send_type'] = 'exchange_r';
			$data_to_send_logs['coin_type'] = $token;
			$data_to_send_logs['from_id'] = $adminAccountWalletId;
			if ( !empty($toUserId) ) {
				$data_to_send_logs['to_id'] = $toUserId;
			}
			$data_to_send_logs['from_address'] = $adminAccountWalletAddress;
			$data_to_send_logs['to_address'] = $toUserAccount;
			$data_to_send_logs['amount'] = $actualAmountToSendWithoutDecimal;
			$data_to_send_logs['fee'] =0;
			if ( !empty($newTransactionId) ) {
				$data_to_send_logs['transactionId'] = $newTransactionId;
			}
			$data_to_send_logs['status'] = !empty($newTransactionId) ? 'send' : 'fail';
			$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

			$db = getDbInstance();
			$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);


			if(!empty($newTransactionId)){

				echo $newTransactionId."\n";
				
				//$data_to_store = filter_input_array(INPUT_POST);
				$data_to_store = [];
				$data_to_store['created_at'] = date('Y-m-d H:i:s');
				$data_to_store['coin_type'] = $token;
				$data_to_store['sender_id'] = $adminAccountWalletId;
				$data_to_store['reciver_address'] = $toUserAccount;
				$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
				$data_to_store['status'] = 'completed';
				$data_to_store['fee_in_eth'] = 0;
				$data_to_store['fee_in_gcg'] = 0;
				$data_to_store['transactionId'] = $newTransactionId;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store);
				$date = date("Y");
				// update record 
				$db = getDbInstance();
				$db->where("id", $recordId);
				$last_id = $db->update('user_transactions', ['status'=>"completed"]);	
				
				
				// send alert
			
				$tmp_amount = number_format((float)$actualAmountToSendWithoutDecimal,8);
				$tmp_amount = rtrim($tmp_amount, 0);
				$tmp_amount = rtrim($tmp_amount, '.');
				$alertMsg = $tmp_amount." ".strtoupper($token)." Token added to your account";

				if($registerWith=="phone"){
					$phone2 = '';
					$country = '';

					if ( !empty($row['n_country']) && $row['n_country'] == '82' && !empty($row['n_phone']) ) {
						$country = $row['n_country'];
						$phone2 = $row['n_phone'];
						$phone3 = preg_replace('/[\(\)\-\_\.~\s]/i', '', $phone2);
						
						$rest = new Message($n_api_key, $n_api_secret);

						$options = new stdClass();
						$options->to = $phone3; // 수신번호
						$options->from = $n_sms_from_tel; // 발신번호
						
						$options->country = $country;
						$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
						$options->text = $alertMsg; // 문자내용
						
						$result = $rest->send($options);     

						if($result->success_count == '1')
						{
							//echo 'success';
						}
						else
						{
							//echo 'Send SMS fail';
						}

					}
					// send sms end
					
				}
				else {
					
					 $verifyLink = "http://".$_SERVER['HTTP_HOST']."/login.php";
					 $mailHtml = '<table style="background:#f6f6f6; width:100%; height: 100vh;">
							<tr>
								<td>
								<table align="center" width="600"  style=" background:#fff; ">
								<tbody>
								<tr align="center" > 
									<td>
										<img src="http://'.$_SERVER['HTTP_HOST'].'/images/logo/'.$token.'.png" />
									</td>
								</tr>	
									
								  <tr><td><h4 style="text-align: left; padding-left: 16px; margin:0px;">Hi '.$firstName.',</h4></td></tr>
								  <tr align="center"><td><p style="padding:0 3%; line-height:25px; text-align: justify;">Congratulation </p></td></tr>
								  <tr align="center"><td><p style="padding:0 3%; line-height:25px; text-align: justify;">'.$alertMsg.'</p></td></tr>
								  
									<tr>
										  <td align="center"><div style=" font-weight:bold; padding: 12px 35px; color: #fff; border-radius:5px; text-align:center; font-size: 14px; margin: 10px 0 20px; background: #ec552b; display: inline-block; text-decoration: none;">Click To Login: <a href="'.$verifyLink.'">'.$verifyLink.'</a></div></td>
									</tr>
								  
								  <tr align="center"><td><p style="padding:0 3%; line-height:25px; text-align: justify; margin:0px;">Thanks, <br/>Team Support</p></td></tr>
							</tbody>
							</table>
							
							<table align="center" width="600"  style=" background:#f3f5f7; color:#b7bbc1 ">
								<tr><td><h4>©'.$date.' All right reserved</h4></td></tr>  
							</table>
						</td></tr></table>
						';
					 
						require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)

						$email = new \SendGrid\Mail\Mail();
						$email->setFrom($n_email_from_address, "CyberTron Coin");
						$email->setSubject($alertMsg);
						$email->addTo($userEmail);//$email_id;

						$email->addContent("text/html", $mailHtml);

						$sendgrid = new \SendGrid('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');
						$response = $sendgrid->send($email);
						
				}
			}
		}
		
	}
}


echo "Finish : ".date('Y-m-d H:i:s')."\n\n";

function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}
?>