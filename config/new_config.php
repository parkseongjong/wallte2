<?php
// Page in use

$bsc_master_wallet = "0xE17363E3b7c1b47a7A3c2dE5673fE1d4b703c8e1";
$bsc_master_pvt_key = "0bcb750afc3400a90c67c2aed69a21f176a4107a95b941e009dccd1c517b6e03";

$n_wallet_pass_key = 'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM';
$n_master_email = 'michael@cybertronchain.com';

$gasPriceInWei = 4000000000000;

$n_master_id = 45;
// CybertronChain
//$n_master_wallet_address = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
//$n_master_wallet_pass = $n_master_email.$n_wallet_pass_key;
$n_master_wallet_address = "0x1125a7156dc34ABC463E35Bc7703B3287c41FD60";
$n_master_wallet_pass = '@@ajsl2020@@'.$n_wallet_pass_key;



// CTC airdrop Master Wallet Infos
// CybertronChain2
$n_master_id_cta = 43;
$n_master_wallet_address_cta = "0xebE75b6272746340E31E356b6C42953CB3Ba336E";
$n_master_wallet_pass_cta = $n_master_email.$n_wallet_pass_key;

// TP3 airdrop Master Wallet Infos
// CybertronChain1
$n_master_id_tpa = 44;
$n_master_wallet_address_tpa = "0x35c937aBC9F48E01EFff1B8f2D3D38E3332cf110";
$n_master_wallet_pass_tpa = $n_master_email.$n_wallet_pass_key;



// CTC fee receiving address
// CybertronChain5
$n_master_id_fee = 40;
$n_master_wallet_address_fee = "0xB124556aCb6703cbF9b1244A18B72091734025c4";
$n_master_wallet_pass_fee = $n_master_email.$n_wallet_pass_key;
// 변경시 아래 파일도 변경해야 함 : /apis/barry/config/config_barry.php


// IN
// exchange Master Wallet Infos : CTC
// CybertronChain3
$n_master_id_exc = 42;
$n_master_wallet_address_exc = "0xCb0A1aE92ECe84ffC0310A6e958A854A3baccf28";
$n_master_wallet_pass_exc = $n_master_email.$n_wallet_pass_key;
// This password is wrong

// exchange Master Wallet Infos : TP3
// CybertronChain4
$n_master_id_exc_tp3 = 41;
$n_master_wallet_address_exc_tp3 = "0xbcB0A3F1c377cF4898AC9A67731098E64bD9bB2d";
$n_master_wallet_pass_exc_tp3 = $n_master_email.$n_wallet_pass_key;
// This password is wrong


// Out - transaction_cron
// exchange Master Wallet Infos : CTC
// CybertronChain6
$n_master_id_exc_out = 39;
$n_master_wallet_address_exc_out = "0x1da4a1759ed3e2d59d4ae4303eaf5d408fbb24c6"; // 0x70304eEC09499e11D4311fE2a430b2044545d8Ad
$n_master_wallet_pass_exc_out = 'exhieisme1352hie@'.$n_wallet_pass_key;


// exchange Master Wallet Infos : TP3
// CybertronChain7
$n_master_id_exc_out_tp3 = 38;
$n_master_wallet_address_exc_out_tp3 = "0x233a562005ff31c1999253ff28048f4bb01d1887"; // 0x87250a8D8d7bD706Ff3Da7E9d3C646Ed3a1B484A";
$n_master_wallet_pass_exc_out_tp3 = 'b57e8seivmk192iehkij@'.$n_wallet_pass_key;






// eToken

// transaction_cron_etoken, airdrop
// 사용자가 충전 요청하면 누가 보내줄 것인가
// 수정시 : /apis/config/config_coinibt.php
$n_master_etoken_id = $n_master_id;
$n_master_etoken_wallet_address = $n_master_wallet_address;

// exchange_ectc
// Exhange CTC -> eCTC Address
// CTC를 어느 주소로 받을 것인가
//$n_master_ectc_id = $n_master_id;
$n_master_ectc_wallet_address = '0x1da4a1759ed3e2d59d4ae4303eaf5d408fbb24c6';

// exchange_etp3
// Exhange TP3 -> eTP3 Address
// Tp3를 어느 주소로 받을것인가
//$n_master_etp3_id = $n_master_id;
$n_master_etp3_wallet_address = '0x233a562005ff31c1999253ff28048f4bb01d1887';


// exchange_etoken.php  : exchange_etp3.php 대체
// Token을 어느 주소로 받을 것인가
$n_master_etoken_receive_address = array(
	'tp3' => '0x233a562005ff31c1999253ff28048f4bb01d1887',
	'krw' => '0x233a562005ff31c1999253ff28048f4bb01d1887',
	'mc' => '0x233a562005ff31c1999253ff28048f4bb01d1887',
	'usdt' => '0x233a562005ff31c1999253ff28048f4bb01d1887',
	'eth' => '0x233a562005ff31c1999253ff28048f4bb01d1887'
);

// eCTC -> CTC : 보내줄 주소. CTC & ETH 있어야 함
$n_master_ectc_re_id = $n_master_id_exc_out;
$n_master_ectc_re_wallet_address = $n_master_wallet_address_exc_out;
$n_master_ectc_re_pass = $n_master_wallet_pass_exc_out;

// eTP3 -> TP3 : 보내줄 주소. TP3 & ETH 있어야 함
$n_master_etp3_re_id = $n_master_id_exc_out_tp3;
$n_master_etp3_re_wallet_address = $n_master_wallet_address_exc_out_tp3;
$n_master_etp3_re_pass = $n_master_wallet_pass_exc_out_tp3;

// eMC -> MC : 보내줄 주소. MC & ETH 있어야 함
$n_master_emc_re_id = $n_master_id_exc_out_tp3;
$n_master_emc_re_wallet_address = $n_master_wallet_address_exc_out_tp3;
$n_master_emc_re_pass = $n_master_wallet_pass_exc_out_tp3;

// eUSDT -> USDT : 보내줄 주소. USDT & ETH 있어야 함
$n_master_eusdt_re_id = $n_master_id_exc_out_tp3;
$n_master_eusdt_re_wallet_address = $n_master_wallet_address_exc_out_tp3;
$n_master_eusdt_re_pass = $n_master_wallet_pass_exc_out_tp3;

// eETH -> ETH : 보내줄 주소. ETH 있어야 함
$n_master_eeth_re_id = $n_master_id_exc_out_tp3;
$n_master_eeth_re_wallet_address = $n_master_wallet_address_exc_out_tp3;
$n_master_eeth_re_pass = $n_master_wallet_pass_exc_out_tp3;

// eTP3 전송시 수수료 eCTC 받을 곳 : send_etoken.php
$n_master_etoken_ctc_fee_id = $n_master_id_fee;
$n_master_etoken_ctc_fee_wallet_address = $n_master_wallet_address_fee;
// 변경시 아래 파일도 변경해야 함 : /apis/barry/config/config_barry.php


// 결제 가능한 앱 버전 (2020-11-20 기준 결제모듈 앱에서 이상없이 되도록 적용한 최소 버전)
$payment_android_wallet = '2.7';
$payment_android_barry = '1.2';
$payment_ios_wallet = '1.4';
$payment_ios_barry = '1.1';

/*

exchange ctc (in ETH )
0xCb0A1aE92ECe84ffC0310A6e958A854A3baccf28

exchange tp3 (in ETH)
0xbcB0A3F1c377cF4898AC9A67731098E64bD9bB2d

exchange tp3 (out TP3)
0x87250a8D8d7bD706Ff3Da7E9d3C646Ed3a1B484A

exchange ctc (out CTC)
0x70304eEC09499e11D4311fE2a430b2044545d8Ad
*/



/*
// CTC airdrop Master Wallet Infos
$n_master_id_cta = 45;
$n_master_wallet_address_cta = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$n_master_wallet_pass_cta = $n_master_email.$n_wallet_pass_key;

// TP3 airdrop Master Wallet Infos
$n_master_id_tpa = 45;
$n_master_wallet_address_tpa = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$n_master_wallet_pass_tpa = $n_master_email.$n_wallet_pass_key;
*/


 // Changed it to set it at once on that page : config/new_config.php
// tongkni.co.kr
// *** If you change your IP, change the following file as well : lib/WalletInfos.php, WalletProcess.php
$n_connect_ip= '195.201.168.34';
$n_connect_port = 8545;

// SMS 발송시 보내는 전화번호. Phone number when sending SMS
$n_sms_from_tel = '0234893237';
// 변경시 아래 파일도 변경해야 함 : lib/SendMail.php


// 메일 발송시 보내는 메일주소. E-mail address sent when sending mail
$n_email_from_address = 'michael@cybertronchain.com';
// 변경시 아래 파일도 변경해야 함 : lib/SendMail.php


// SMS 발송 키
//$n_api_key = '1234';
//$n_api_secret = '1234';
$n_api_key = 'NCSWM2DJ81J4J5V9';
$n_api_secret = 'WATOR3FYBG4MOJONQYTXDY5TPYLBPE4C';
// 변경시 아래 파일도 변경해야 함 : lib/SendMail.php, lib/common/Push.php, /var/www/ctc/wallet/cron/common.php


// 마지막 전송 후 몇 분을 기다려야 전송할 수 있는지 설정. Set how long to wait after sending the last transmission
// 3분이 지나야 다시 보낼 수 있다. It can be sent again after 1 minutes.
$n_send_re_time = 1; // minutes
// 3 minutes -> 1 minute (20.09.03)

// login 페이지 하단에 표시하는 버전 정보. Version information displayed at the bottom of the login page
$n_version = 'ver 2.0';

// 전송시 별도로 설정한 비밀번호를 입력해야 전송할 수 있다. When sending, you must enter a password that you set separately to send.
// profile 페이지에서 설정 가능. Can be set in the profile page
// 전송password 자릿수. Number of digits for transmission password
$n_transfer_pw_length = 6;

// transfer password 실패시 1일 최대 몇번까지 허용할 것인가 설정. Set how many times a day is allowed when the transfer password fails
// 10번  이상 틀리면 그날은 전송불가. If you are wrong more than 10 times, you cannot send token that day.
$n_transfer_pw_count = 10;
// 변경시 아래 파일도 변경해야 함 : /apis/barry/config/config_barry.php

// 소수점 자릿수 설정. Decimal Place Setting
// 잔액 표시할 때 최대 몇자리까지 표시할 것인지 결정함. Decide how many digits to display when displaying balances
$n_decimal_point_array = array(
	'ctc' => 8,
	'tp3' => 8,
	'eth' => 8,
	'usdt' => 8,
	'mc' => 8,
	'krw' => 8,
	'ctc7' => 8,
    'ctctm' => 8
);
$n_full_name_array = array(
	'ctc' => 'CyberTronChain',
	'tp3' => 'TokenPlay',
	'eth' => 'Ethereum',
	'usdt' => 'Tether USD',
	'mc' => 'MarketCoin',
	'krw' => 'Korean Won',
	'ctc7' => 'Ctc7',
    'ctctm' => 'Ctc TM'
);
$n_full_name_array2 = array(
	'ectc' => 'E-CyberTronChain',
	'etp3' => 'E-TokenPlay',
	'emc' => 'E-MarketCoin',
	'ekrw' => 'E-Korean Won',
	'eusdt' => 'E-Tether USD',
	'eeth' => 'E-Ethereum'
);

// 변경시 다음 파일도 변경 : cybertronchain.kr/wallet/config/func.php
$n_decimal_point_array2 = array(
	'ectc' => 8,
	'etp3' => 8,
	'emc' => 8,
	'ekrw' => 8,
	'eusdt' => 8,
	'eeth' => 8
);

// 변경시 다음 파일도 변경 : cybertronchain.kr/wallet/config/func.php
$n_epay_name_array = array(
	'ectc' => 'E-CTC',
	'etp3' => 'E-TP3',
	'emc' => 'E-MC',
	'ekrw' => 'E-KRW',
	'eusdt' => 'E-USDT',
	'eeth' => 'E-ETH'
);

// apis/walletapp/wallet.php
$new_walletapp_coin_list = array('ctc', 'tp3', 'mc', 'krw', 'usdt', 'eth');
$new_walletapp_epay_list = array('ectc', 'etp3', 'emc', 'ekrw', 'eusdt', 'eeth');


//coinibt : admin_accounts.account_type2
// config/config_exchange.php와 같아야 함
// /apis/config/config_coinibt.php와 같아야 함
$con_exchange_type_value = 'CoinIBT';


// coupon_list.kind=fee_change
//$coupon_fee_change_id = 8;


$n_profile_uploaddir = 'userfiles/';

function e_pay_name_change($name) {
	$result = '';
	switch($name) {
		case 'ectc': $result = 'e-CTC'; break;
		case 'etp3': $result = 'e-TP3'; break;
		case 'emc' : $result = 'e-MC'; break;
		case 'ekrw': $result = 'e-KRW'; break;
		case 'eusdt': $result = 'e-USDT'; break;
		case 'eeth': $result = 'e-ETH'; break;
		default: $result = $name; break;
	}
	return $result;
}
function e_pay_name_change2($name) {
	$result = '';
	switch($name) {
		case 'ectc': $result = 'E-CTC'; break;
		case 'etp3': $result = 'E-TP3'; break;
		case 'emc' : $result = 'E-MC'; break;
		case 'ekrw': $result = 'E-KRW'; break;
		case 'eusdt': $result = 'E-USDT'; break;
		case 'eeth': $result = 'E-ETH'; break;
		default: $result = $name; break;
	}
	return $result;
}

function get_user_real_name($auth_name, $name, $lname)
{
	$user_name = '';

	if ( !empty($auth_name) ) { // 본인인증 완료한 경우 실명 표시, Real name indication when self-certification is complete
		$user_name = $auth_name;
	} else if ( !empty($name) ) { // 사용자 입력한 이름, Show user-populated names
		$user_name = $name;
		if ( !empty($lname) ) {
			$user_name = $lname.$name;
		}
	}
	$user_name = $user_name != '' ? $user_name : '';
	return $user_name;
}




// 에러 발생시(try-catch 등) 파일에 저장
//		$log : 메세지(message)
function new_fn_logSave($log)
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


// IP 확인
// return : IP Address
function new_getUserIpAddr()
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


// Check country code by IP
// WHOIS OpenAPI
// return : 국가코드(KR, DE, ...)
function new_kisa_ip_chk(){

	$ip = new_getUserIpAddr();
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


// ipinfo.io
// return : 국가코드(KR, DE, ...)
function new_ipinfo_ip_chk($key) { // 수량 체크 테스트용. whois 대신 사용 가능한지 check (2020.05.14, YMJ)
	if ($key == '1') {
		$access_token = 'd5b65ce795f734'; // 무료 version key (50,000건)
	} else {
		$access_token = '7c984c718aef66'; // 무료 version key (50,000건)
	}
	$ip_address = new_getUserIpAddr();
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

function new_number_format($value, $leng) {
	$result = '';
	$result = number_format($value, $leng);
	$result = rtrim($result, 0);
	$result = rtrim($result, '.');
	return $result;
}
function new_number_format2($value) {
	$result = '';
	$result = rtrim($value, 0);
	$result = rtrim($result, '.');
	return $result;
}



// Profile Image : Generate file name
function new_profile_set_filename($uploaddir) {
	$filename = date("YmdHis").'_'.rand(100000,999999);
	if ( is_file($uploaddir . $filename) ) {
		$filename = new_profile_set_filename($uploaddir);
	}
	return $filename;
} //

// Profile Image : File compression
// image/gif
// image/jpeg
// image/pjpeg
function new_profile_compress_image($tmp_file, $new_file, $quality) {
	$info = getimagesize($tmp_file);
	if ($info['mime'] == 'image/jpeg') {
		$image = imagecreatefromjpeg($tmp_file);
	} else if ($info['mime'] == 'image/gif') {
		$image = imagecreatefromgif($tmp_file);
	} else if ($info['mime'] == 'image/png') {
		$image = imagecreatefrompng($tmp_file);
	}
	imagejpeg($image, $new_file, $quality);
	return $new_file;
} //


// $currentPage : 현재 페이지번호, now page
// $link_page : 이동할 페이지 주소 : page link, OOO.php
// $total_pages : $db->totalPages;
function new_set_page_list($currentPage, $link_page, $total_pages, $get_infos, $showRecordPerPage = 10)
{
	$page_html = '';
	if(isset($currentPage) && !empty($currentPage)){
		$currentPage = $currentPage;
	}else{
		$currentPage = 1;
	}
	
	$http_query = '';
	if ( !empty($link_page) ) {
		$http_query = $link_page;
	}
	if (!empty($get_infos)) {
		unset($_GET['page']);
		unset($get_infos['page']);
		$http_query .= "?" . http_build_query($get_infos) . '&';
	} else {
		$http_query .= "?";
	}

	$startFrom = ($currentPage * $showRecordPerPage) - $showRecordPerPage;
	$lastPage = $total_pages;
	$firstPage = 1;
	$nextPage = $currentPage + 1;
	$previousPage = $currentPage - 1;

	$startPage = $currentPage - 5;
	$endPage = $currentPage + 5;
	if ($startPage < $firstPage) $startPage = $firstPage;
	if ($endPage > $lastPage) $endPage = $lastPage;
	
	$page_html = '<ul class="pagination">';
	if ( $currentPage != $firstPage ) {
		$page_html .= '<li class="page-item"><a class="page-link" href="'.$http_query.'page='.$firstPage.'" tabindex="-1" aria-label="Previous"><span aria-hidden="true">First</span></a></li>';
	}
	//if ( $currentPage >= 2 ) {
	//	$page_html .= '<li class="page-item"><a class="page-link" href="'.$http_query.'page='.$previousPage.'">'.$previousPage.'</a></li>';
	//}
	for ($i=$startPage; $i<=$endPage; $i++) { 
		$active = $currentPage==$i ? ' active' : '';
		$page_html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.$http_query.'page='.$i.'">'.$i.'</a></li>';
	}
	//$page_html .= '<li class="page-item active"><a class="page-link" href="'.$http_query.'page='.$currentPage.'">'.$currentPage.'</a></li>';
	 if ($total_pages > 1 && $currentPage != $lastPage) {
		// $page_html .= '<li class="page-item"><a class="page-link" href="'.$http_query.'page='.$nextPage.'">'.$nextPage.'</a></li>';
		 $page_html .= '<li class="page-item"><a class="page-link" href="'.$http_query.'page='.$lastPage.'" aria-label="Next"><span aria-hidden="true">Last</span></a></li>';
	 }
	 return $page_html;
}

function newSetPageList($currentPage, $link_page, $total_pages, $showRecordPerPage = 10)
{
    $page_html = '';
    if(isset($currentPage) && !empty($currentPage)){
        $currentPage = $currentPage;
    }else{
        $currentPage = 1;
    }
    $http_query = WALLET_URL.'/';
    $http_query .= $link_page;

    $startFrom = ($currentPage * $showRecordPerPage) - $showRecordPerPage;
    $lastPage = $total_pages;
    $firstPage = 1;
    $nextPage = $currentPage + 1;
    $previousPage = $currentPage - 1;

    $startPage = $currentPage - 5;
    $endPage = $currentPage + 5;
    if ($startPage < $firstPage) $startPage = $firstPage;
    if ($endPage > $lastPage) $endPage = $lastPage;

    $page_html = '<ul class="pagination">';
    if ( $currentPage != $firstPage ) {
        $page_html .= '<li class="page-item"><a class="page-link" href="'.$http_query.'/'.$firstPage.'" tabindex="-1" aria-label="Previous"><span aria-hidden="true">First</span></a></li>';
    }
    for ($i=$startPage; $i<=$endPage; $i++) {
        $active = $currentPage==$i ? ' active' : '';
        $page_html .= '<li class="page-item'.$active.'"><a class="page-link" href="'.$http_query.'/'.$i.'">'.$i.'</a></li>';
    }
    if ($total_pages > 1 && $currentPage != $lastPage) {
        $page_html .= '<li class="page-item"><a class="page-link" href="'.$http_query.'/'.$lastPage.'" aria-label="Next"><span aria-hidden="true">Last</span></a></li>';
    }
    return $page_html;
}


function new_get_device($devId) {
	$device = '';
	if ( !empty($devId) ) {
		$device = 'android';
		if(stristr($devId, '-') == TRUE){ 
			$device = 'ios';
		}
	}
	return $device;
}
 // ------------------------------------------------------




$push_message_array = array(
	'1' => 'OOO 주문시 주문서 미작성으로 반려되어서 코인이 전송되었습니다.',
	'2' => 'OOO 주문시 주문서와 금액불일치로 반려되어서 코인이 전송되었습니다.',
	'3' => 'OOO 주문시 한정구매수량 초과로 반려되어서 코인이 전송되었습니다.',
	'4' => 'OOO 주문시 정확하지 않은 주소지로 반려되어서 코인이 전송되었습니다.'
);
function sendPushText($product, $type) {
	$message = '';
	switch($type) {
		case '1':
			$message = array(
				"en" => "When ordering ".$product.", the coin was sent because the order was not filled out and was rejected.",
				"ko" => $product." 주문시 주문서 미작성으로 반려되어서 코인이 전송되었습니다."
			);
			break;
		case '2':
			$message = array(
				"en" => "When ordering ".$product.", the coin was sent because it was rejected due to a mismatch between the order and the amount.",
				"ko" => $product." 주문시 주문서와 금액불일치로 반려되어서 코인이 전송되었습니다."
			);
			break;
		case '3':
			$message = array(
				"en" => "When ordering ".$product.", the coin was sent because the limited purchase amount was exceeded.",
				"ko" => $product." 주문시 한정구매수량 초과로 반려되어서 코인이 전송되었습니다."
			);
		case '4':
			$message = array(
				"en" => "When ordering ".$product.", the coin was sent because it's an incorrect address.",
				"ko" => $product." 주문시 정확하지 않은 주소지로 반려되어서 코인이 전송되었습니다."
			);
			break;
	}
	return $message;
}


// $content : 배열 ($title과 같은 형식으로 작성)
// $player_id : 푸시 발송대상, 1개만 가능
function sendPushMessage($content, $player_id){
	$title = array(
		"en" => 'CTC Wallet',
		"ko" => 'CTC Wallet'
	);
	$fields = array(
		'app_id' => "43f16767-973b-42bb-b157-df95b8ae19cd",
		'include_player_ids' => array($player_id),
		'data' => array("title" => "wallet", "message" => "Hello"),
		'contents' => $content,
		'headings' => $title,
		'small_icon' => "@mipmap/ic_launcher_round",
		'large_icon' => "@mipmap/ic_launcher"
	);
	
	$fields = json_encode($fields);
	
	$log_message = '';
	$log_message .= "JSON sent:\n";
	$log_message .= $fields;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$response = curl_exec($ch);
	curl_close($ch);
	
	$return["allresponses"] = $response;
	$return = json_encode($return);
	$log_message .= "\nJSON received:\n";
	$log_message .= $return."\n\n";
	new_fn_logSave_Push($log_message);

	return $response;
}

// Push 발생시 파일에 저장
//		$log : 메세지(message)
function new_fn_logSave_Push($log)
{
	$logPathDir = "/var/www/html/wallet2/_log_message";  //로그위치 지정

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




// $site : wallet / barrybarries
// $title1, $content1 : 배열
// $player_id : 푸시 발송대상, 1개만 가능
function sendPushMessage2($site, $title1, $content1, $player_id){
	$title = array(
		"en" => $title1['en'],
		"ko" => $title1['ko']
	);
	$content = array(
		"en" => $content1['en'],
		"ko" => $content1['ko']
	);
	
	$app_id = "43f16767-973b-42bb-b157-df95b8ae19cd";
	if ( $site == 'barrybarries' ) {
		$app_id = "3b79bd55-c0a8-4fb4-904c-6de7268293ff";
	}
	
	$fields = array(
		'app_id' => $app_id,
		'include_player_ids' => array($player_id),
		'data' => array("title" => $title1['en'], "message" => "Hello"),
		'contents' => $content,
		'headings' => $title,
		'small_icon' => "@mipmap/ic_launcher_round",
		'large_icon' => "@mipmap/ic_launcher"
	);
	
	$fields = json_encode($fields);
	
	$log_message = '';
	$log_message .= "JSON sent:\n";
	$log_message .= $fields;
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$response = curl_exec($ch);
	curl_close($ch);
	
	$return["allresponses"] = $response;
	$return = json_encode($return);
	$log_message .= "\nJSON received:\n";
	$log_message .= $return."\n\n";
	new_fn_logSave_Push($log_message);

	return $response;
}





// -------------------------------------------------------------------------------------------


// e_coin  : ectc, etp3, emc, ekrw
// type : won (1eCTC가 몇원인가)
// type : e_coin (1원이 몇 eCTC인가)

// return : 1eCTC가 몇원인가 / 1원이 몇 eCTC인가
function new_coupon_ex_rate($e_coin, $type) {
	
	$coin = substr($e_coin, 1); // ctc

	if ( $e_coin != 'ectc' && $e_coin != 'etp3' && $e_coin != 'emc' ) {
		return false;
	}
	
	$etoken_amount = 0;
	
	$db = getDbInstance();
	$get_ex_rate = $db->where("module_name", 'krw_per_'.$coin.'_kiosk')->getOne('settings');
	$get_ex_rateVal = $get_ex_rate['value'];	
	// 1CTC = ?원 => 1CTC = 605원
	
	$db = getDbInstance();
	$get_ex_rate2 = $db->where("module_name", 'exchange_'.$e_coin.'_per_'.$coin)->getOne('settings');
	$get_ex_rateVal2 = $get_ex_rate2['value'];	
	// 1CTC = ?1eCTC => 1CTC = 1eCTC
	// 현재 $get_ex_rateVal2 값은 1 => 1CTC = 1eCTC
	// ( 만약 $get_ex_rateVal2 값이 2라면, 1CTC=2eCTC가 됩니다.)
	
	// 1CTC = $get_ex_rateVal 원
	// 1CTC = $get_ex_rateVal2 eCTC
	// $get_ex_rateVal2 eCTC = $get_ex_rateVal 원
	
	if ( $type == 'e_coin' ) {
		// 1원 = $get_ex_rateVal2 eCTC / $get_ex_rateVal 원
		$etoken_amount = $get_ex_rateVal2 / $get_ex_rateVal;
	} else if ( $type == 'won' ) {
		// 1 eCTC = $get_ex_rateVal 원 / $get_ex_rateVal2 eCTC
		$etoken_amount = $get_ex_rateVal / $get_ex_rateVal2;
	}
	
	
	return $etoken_amount;

}

//function new_coupon_number_format($val) {
//	$val = number_format($val, 2);
//	return $val;
//}
function new_coupon_status_change($key) {
	$arr = array(
		'pending' => '결제완료',
		'available' => '사용가능',
		'used' => '사용완료',
		'canceled' => '취소완료'
	);
	if ( !empty($key) ) {
		return $arr[$key];
	} else {
		return $arr;
	}
}


function new_pay_type_change($lang, $val) {
	$ret = $val;
	if ( $lang == 'en' ) {
		if ( $val == '카드결제' ) {
			$ret = 'Card';
		} else if ($val == '가상계좌' ) {
			$ret = 'Virtual Account';
		} else if ($val == '계좌이체' ) {
			$ret = 'Bank Transfer';
		}
	}
	return $ret;
}


// ---------------------- 2021-01-06 (아직 적용 전)
function new_set_send_err_log ($send_type, $coin_type, $user_id, $to_address, $msg_type, $message) {

	$data_to_sendlog = [];
	$data_to_sendlog['send_type'] = $send_type;
	$data_to_sendlog['coin_type'] = $coin_type;
	$data_to_sendlog['user_id'] = $user_id;
	if ( !empty($to_address) ) { $data_to_sendlog['to_address'] = $to_address; }
	$data_to_sendlog['msg_type'] = $msg_type;
	$data_to_sendlog['message'] = $message;
	$db = getDbInstance();
	$last_id = $db->insert('send_error_logs', $data_to_sendlog);
	return $last_id;

}


function new_set_user_transactions ($coin_type, $sender_id, $reciver_address, $amount, $fee_in_eth, $fee_in_gcg, $status, $transactionId ) {
	$data_to_store = [];
	if ( !empty($coin_type) ) {
		$data_to_store['coin_type'] = $coin_type;
	}
	$data_to_store['sender_id'] = $sender_id;
	$data_to_store['reciver_address'] = $reciver_address;
	$data_to_store['amount'] = $amount;
	$data_to_store['fee_in_eth'] = $fee_in_eth;
	$data_to_store['fee_in_gcg'] = !empty($fee_in_gcg) ? $fee_in_gcg : 0;
	if ( !empty($status) ) {
		$data_to_store['status'] = $status;
	}
	$data_to_store['transactionId'] = $transactionId;
	$data_to_store['created_at'] = date('Y-m-d H:i:s');
	
	$db = getDbInstance();
	$last_id = $db->insert('user_transactions', $data_to_store);
	return $last_id;
}

function new_set_user_transactions_all ($send_type, $coin_type, $from_id, $to_id, $from_address, $to_address, $amount, $fee, $transactionId, $status, $send_sms, $store_name, $store_result, $etoken_send) {
	$data_to_send_logs = [];
	$data_to_send_logs['send_type'] = $send_type;
	$data_to_send_logs['coin_type'] = $coin_type;
	$data_to_send_logs['from_id'] = $from_id;
	if ( !empty($to_id) ) {
		$data_to_send_logs['to_id'] = $to_id;
	}
	$data_to_send_logs['from_address'] = $from_address;
	$data_to_send_logs['to_address'] = $to_address;
	$data_to_send_logs['amount'] = $amount;
	$data_to_send_logs['fee'] = $fee;
	if ( !empty($transactionId) ) {
		$data_to_send_logs['transactionId'] = $transactionId;
	}
	if ( empty($status) ) {
		$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
	} else {
		$data_to_send_logs['status'] = $status;
	}
	if ( !empty($send_sms) ) {
		$data_to_send_logs['send_sms'] = $send_sms;
	}
	if ( !empty($store_name) ) {
		$data_to_send_logs['store_name'] = $store_name;
	}
	if ( !empty($store_result) ) {
		$data_to_send_logs['store_result'] = $store_result;
	}
	if ( !empty($etoken_send) ) {
		$data_to_send_logs['etoken_send'] = $etoken_send;
	}
	$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

	$db = getDbInstance();
	$last_id = $db->insert('user_transactions_all', $data_to_send_logs);
	return $last_id;

}



// 21.02.09
// 입력한 금액(단위: 원)이 몇 코인인지 계산하는 부분 - API(베리베리)에서 별도로 관리하는 부분을 여기로 옮김
// apis/barry/apis
// $coin_type2 : epay/coin
function new_coin_price_change_won($coin_type, $won, $coin_type2) {
	$coin_type = strtolower($coin_type);

	list($module_name, $module_name2) = new_coin_settings_module_name($coin_type);
	
	$coin = 0;
	if ( is_numeric($won) && $won > 0 && !empty($module_name) ) {
		$db = getDbInstance();
		$db->where('module_name', $module_name);
		$coinData = $db->getOne('settings');
		
		if ( !empty($coinData) ) {
			$coin = bcdiv($won, $coinData['value'], 16); // 1 Won = ? Coin

			$e_coin_rate = 1;
			// 1 Coin당 몇 E-Pay 인지
			if ( $coin_type2 == 'epay' ) {
				$db = getDbInstance();
				$db->where('module_name', $module_name2);
				$row2 = $db->getOne('settings');
				if (!empty($row2) && !empty($row2['value'])) {
					$e_coin_rate = $row2['value'];
				}
				if ( $e_coin_rate != 1 ) { // 1:1 비율일 때는 할 필요 없으므로
					$coin = bcmul($coin, $e_coin_rate, 16);
				}
			}
			$coin = new_floor($coin, 0);
		}
	}
	return $coin;
}

function new_coin_price_change_won_allowEctc($coin_type, $won, $coin_type2) {
    $coin_type = strtolower($coin_type);

    list($module_name, $module_name2) = new_coin_settings_module_name($coin_type);
    $coin = 0;
    if ( is_numeric($won) && $won > 0 && !empty($module_name) ) {
        $db = getDbInstance();
        $db->where('module_name', $module_name);
        $coinData = $db->getOne('settings');
        if ( !empty($coinData) ) {
            $coin = bcdiv($won, $coinData['value'], 16); // 1 Won = ? Coin
            $e_coin_rate = 1;
            // 1 Coin당 몇 E-Pay 인지
            if ( $coin_type2 == 'epay' ) {
                $db = getDbInstance();
                $db->where('module_name', $module_name2);
                $row2 = $db->getOne('settings');
                if (!empty($row2) && !empty($row2['value'])) {
                    $e_coin_rate = $row2['value'];
                }
                if ( $e_coin_rate != 1 ) { // 1:1 비율일 때는 할 필요 없으므로
                    $coin = bcmul($coin, $e_coin_rate, 16);
                }
            }

            if($coin_type == 'e-ctc'){
                $coin = new_floor($coin, 2);
            }
            else{
                $coin = new_floor($coin, 0);
            }

        }
    }
    return $coin;
}


function new_coin_settings_module_name ($coin) {
	$mod1 = '';
	$mod2 = '';
	
	$coin_s = strtolower($coin);
	if( stristr($coin_s, 'e-') == TRUE ) {
		$coin_s = str_replace('e-', '', $coin_s);
	}

    $mod1 = 'krw_per_'.$coin_s.'_kiosk';
	$mod2 = 'exchange_e'.$coin_s.'_per_'.$coin_s;

	return array($mod1, $mod2);

}


// DB에 저장된 값은 1 Coin이 ? 원인가가 저장됨
// 원하는값은 몇원이 ? Coin인가가 필요함
// settings1-multi.pro.php
// t_cron_coinprice
function new_coin_price_change_1won($coin_type, $won, $ctcprice) {
	if ( empty($coin_type) ) {
		$coin_type = 'ctc';
	}
	$coin_type = strtolower($coin_type);
	if ( empty($won) ) {
		$won = 1;
	}
	if ( empty($ctcprice) ) {
		$db = getDbInstance();
		$db->where('module_name', 'krw_per_coin');
		$db->where('coin_type', $coin_type);
		$ctcprice = $db->getValue('settings2', 'value');
	}

	$new_price = 0;
	$new_price = bcdiv($won, $ctcprice, 1); // bcdiv : 버림
	//$new_price = new_floor($new_price, 1);
	$new_price = new_number_format2($new_price);
	return $new_price;
}

// 소수점 이하 버림 함수
function new_floor($val, $len=1) {
	if ( stristr($val, '.') == true ) {
		$tmp = explode('.', $val);
		if ( strlen($tmp[1]) > $len ) {
			$tmp[1] = substr($tmp[1], 0, $len);
		}
		$val = $tmp[0].'.'.$tmp[1];
	}
	$val = new_number_format2($val);
	return $val;
}


// CoinIBT로부터 가져온 값 업데이트 : settings2 => settings
// t_cron_coinprice.php
function new_ex_set_coin_price() {
	$db = getDbInstance();
	$db->where('module_name', 'krw_per_coin');
	$setDatas2 = $db->get('settings2');
	if ( !empty($setDatas2) ) {
		foreach($setDatas2 as $row) {
			$db = getDbInstance();
			$db->where('module_name','krw_per_'.$row['coin_type'].'_kiosk');
			$r1 = $db->getOne('settings');
			if ( empty($r1) ) {
				$insertArr = [];
				$insertArr['module_name'] = 'krw_per_'.$row['coin_type'].'_kiosk';
				$insertArr['show_name'] = 'KRW per '.strtoupper($row['coin_type']);
				$insertArr['type'] = 'ex_rate';
				$insertArr['value'] = $row['value'];
				$insertArr['created'] = date("Y-m-d H:i:s");
				$insertArr['exp'] = '1 '.strtoupper($row['coin_type']).' = ? 원';
				$last_id = $db->insert('settings', $insertArr);
			} else {
				$db = getDbInstance();
				$db->where('id', $r1['id']);
				$updateArr = [];
				$updateArr['value'] = $row['value'];
				$updateArr['modified'] = date("Y-m-d H:i:s");
				$last_id = $db->update('settings', $updateArr);
			}

		}
	}
} //

// 21.03.05
function new_get_waddr_arrs($infos) {

	$new = '';
	$virtual = '';
	$old = '';
	if ( $infos['id'] >= 10900 ) {
		$new = $infos['wallet_address'];
	} else {
		if ( $infos['wallet_change_apply'] == 'Y' ) {
			$new = $infos['wallet_address'];
			if ( !empty($infos['wallet_address_change']) ) {
				$old = $infos['wallet_address_change'];
			}
		} else {
			$old = $infos['wallet_address'];
			if ( !empty($infos['wallet_address_change']) ) {
				$new = $infos['wallet_address_change'];
			}
		}
	}
	if ( !empty($infos['virtual_wallet_address']) ) {
		$virtual = $infos['virtual_wallet_address'];
	}
	$addr = array(
		'new' => $new,
		'old' => $old,
		'virtual' => $virtual
	);
	return $addr;
}

// 비포인트 잔액 얻기 : Get Bee point
function new_get_bee_point($id) {
	$pointSum = 0;
	$db = getDbInstance();
	$db->where("user_id", $id);
	$pointSum = $db->getValue("store_transactions", "sum(points)");
	$pointSum = new_number_format($pointSum, 2);
	return $pointSum;
}


// 특정 코인 전송 불가 사용자인지 체크
function new_get_untransmittable($user_id, $coin_type) {

	$db = getDbInstance();
	$db->where('user_id', $user_id);
	$db->where('coin_type', $coin_type);
	$count = $db->getValue('untransmittable', 'count(*)');
	
	return $count;
} //


// 받는사람이 CoinIBT일 경우 : 보낸이가본인인증되어있고, 본인걸로만 보낼 수 있음
function new_receive_getname_check ($from_id, $to_id) {
	$db = getDbInstance();

	$db->where('id', $from_id);
	$fromData = $db->getOne('admin_accounts');

	$db->where('id', $to_id);
	$toData = $db->getOne('admin_accounts');

	$to_phone = $toData['external_phone'];
	$from_phone = $fromData['auth_phone'];


	if ( $fromData['id_auth'] == 'Y' && !empty($to_phone) && !empty($from_phone) && $to_phone == $from_phone) {
		return true;
	} else {
		return false;
	}

} //


?>