<?php
// Page in use

//use Nurigo\Api\Message;
//use Nurigo\Exceptions\CoolsmsException;

//require_once "./sms/bootstrap.php";
require_once "/var/www/html/wallet2/sms/bootstrap.php";

class SendMail {

	protected $n_email_from_address = 'michael@cybertronchain.com';

	// SMS 발송 키
	//protected $n_api_key = '1234';
	//protected $n_api_secret = '1234';
	protected $n_api_key = 'NCSWM2DJ81J4J5V9';
	protected $n_api_secret = 'WATOR3FYBG4MOJONQYTXDY5TPYLBPE4C';

	// SMS 발송시 보내는 전화번호. Phone number when sending SMS
	protected $n_sms_from_tel = '0234893237';


	protected function encoding_change($str)
	{
		$encode = array('ASCII','UTF-8','EUC-KR');
		$str_encode = mb_detect_encoding($str, $encode);
		if(strtoupper($str_encode) == 'EUC-KR') {
			$str = mb_convert_encoding ($str, 'UTF-8', 'EUC-KR');
		}
		return $str;
	}

	// to_email : To Email Address
	// subject : Email subject
	// Contents (Array)
	//		$contents[0] : Message
	//		$contents[1] : Message2<a href="Link">Link</a>
	public function send_email ($to_email, $subject, $contents)
	{
		$send_sms_r = '';
		$subject = $this->encoding_change($subject);
		$thanks = $_SESSION['lang'] == 'ko' ? '감사합니다.' : 'Thanks';
		$date = date("Y-m-d");

		$mailHtml = '<table align="center" width="600"  style=" background:#fff; "><tbody><tr align="center"><td><img src="http://'.$_SERVER['HTTP_HOST'].'/wallet2/images/logo3.png" /></td></tr>';
		if ( !empty($contents[0]) ) {
			$contents[0] = $this->encoding_change($contents[0]);
			$mailHtml .= '<tr align="center"><td><p style="padding:0 3%; line-height:25px; text-align: justify;">'.$contents[0].'</p></td></tr>';
		}
		if ( !empty($contents[1]) ) {
			$contents[1] = $this->encoding_change($contents[1]);
			$mailHtml .= '<tr><td align="center";><div style=" font-weight:bold; padding: 12px 35px; color: #fff; border-radius:5px; text-align:center; font-size: 14px; margin: 10px 0 20px; background: #ec552b; display: inline-block; text-decoration: none;">'.$contents[1].'</div></td></tr>';
		}
		$mailHtml .= '<tr align="center"><td><p style="padding:0 3%; line-height:25px; text-align: justify; margin:0px;">'.$thanks.' <br/>Team Support</p></td></tr></tbody></table>';
		$mailHtml .= '<table style="color:#b7bbc1;width:600px;"><tr><td style="text-align: center;"><h4>©'.$date.' All right reserved</h4></td></tr></table>';
								
		//require './sendgrid-php/vendor/autoload.php';
		require '/var/www/html/wallet2/sendgrid-php/vendor/autoload.php';
		
		$email = new \SendGrid\Mail\Mail();
		$email->setFrom($this->n_email_from_address, "CyberTron Coin");
		$email->setSubject($subject);
		$email->addTo($to_email);
		
		$email->addContent("text/html", $mailHtml);
		
		$sendgrid = new \SendGrid('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');
		
		try {
			$response = $sendgrid->send($email);
			$send_sms_r = 'Y';

		} catch (Exception $e) {
			$send_sms_r = 'F';
			$this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : 81, File : ' . $e->getFile() . ' on line ' . $e->getLine());
		}

		return $send_sms_r;

	} //

	// country : 82
	// phone : 010XXXXOOOO
	// contents : Message
	public function send_sms ($country, $phone, $contents)
	{
		$send_sms_r = '';
		$contents = $this->encoding_change($contents);
		
		if ( !empty($country) && !empty($phone) && !empty($contents) ) {
			try {
				$phone = preg_replace('/[\(\)\-\_\.~\s]/i', '', $phone);

				$rest = new Nurigo\Api\Message($this->n_api_key, $this->n_api_secret);

				$options = new stdClass(); // $options = new \stdClass();
				$options->to = $phone; // 수신번호
				$options->from = $this->n_sms_from_tel; // 발신번호
				
				$options->country = $country;
				$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
				$options->text = $contents; // 문자내용

				$result = $rest->send($options);     

				if($result->success_count == '1') {
					$send_sms_r = 'Y';
				}

			} catch(Nurigo\Exceptions\CoolsmsException $e) {
				$send_sms_r = 'F';
				$this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : 82, File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}
		} else {	
			$send_sms_r = 'F';
		}
		return $send_sms_r;
	} //


	// Log
	protected function wi_fn_logSave($log)
	{
		$logPathDir = "/var/www/html/wallet2/_log";  //로그위치 지정

		$filePath = $logPathDir."/".date("Y")."/".date("n");
		$folderName1 = date("Y"); //폴더 1 년도 생성
		$folderName2 = date("n"); //폴더 2 월 생성

		if(!is_dir($logPathDir."/".$folderName1)){
			mkdir($logPathDir."/".$folderName1, 0777);
		}
		
		if(!is_dir($logPathDir."/".$folderName1."/".$folderName2)){
			mkdir(($logPathDir."/".$folderName1."/".$folderName2), 0777);
		}
		
		$log_file = fopen($logPathDir."/".$folderName1."/".$folderName2."/".date("Ymd").".txt", "a");
		fwrite($log_file, date("Y-m-d H:i:s ").$log."\r\n");
		fclose($log_file);
	}

}
?>	

