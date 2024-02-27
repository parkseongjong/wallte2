<?php 
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './config/config_exchange.php';
require_once './includes/auth_validate.php';

use wallet\common\Util as walletUtil;
use wallet\common\Log as walletLog;
use wallet\common\Filter as walletFilter;
use wallet\common\Push as walletPush;
use wallet\ctcDbDriver\Driver as walletDb;

require __DIR__ .'/vendor/autoload.php';

$filter = walletFilter::getInstance();

//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'p_token' => 'string',
    'p_kind' => 'string',
    'waddr2' => 'string',
    'token' => 'string',
    'amount' => 'string',
    'address' => 'string',
    'p_payment_no' => 'string',
);


$filterData = $filter->postDataFilter($_POST,$targetPostData);
$filterDataGet = $filter->postDataFilter($_GET,['token'=>'string']);

unset($targetPostData);

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('epay 목록 > epay 보내기 조회',['target_id'=>0,'action'=>'S']);


$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

$token = strtolower($filterDataGet['token']);

$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];
$user_name = get_user_real_name($row[0]['auth_name'], $row[0]['name'], $row[0]['lname']);
$user_etoken_ectc = !empty($row[0]['etoken_ectc']) ? $row[0]['etoken_ectc'] : 0;
$user_etoken_balance = !empty($row[0]['etoken_'.$token]) ? $row[0]['etoken_'.$token] : 0;

// 20.12.07
$user_id_auth = 'N';
if ( !empty($row[0]['id_auth']) && $row[0]['id_auth'] == 'Y' ) {
	$user_id_auth = 'Y';
}
$ip_kor = '';
$ip_kor = trim(new_ipinfo_ip_chk('2'));
if ($ip_kor == 'KR' && $user_id_auth != 'Y') {
	$_SESSION['failure'] = !empty($langArr['send_auth_need']) ? $langArr['send_auth_need'] : 'Can be used after authentication. Please use after verifying your identity in [My Info].';
	header('Location:profile.php');
	exit();
}



if ( empty($row[0]['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['transfer_pw_message4']) ? $langArr['transfer_pw_message4'] : 'Please set payment password.';
	header('Location:profile.php');
	exit();
}

$return_page = 'send_etoken.php?token='.$token;

$db = getDbInstance();

$module_name = 'send_etoken_fee';
if ( $row[0]['transfer_approved'] != 'C' ) {
	$module_name = 'send_etoken_fee_eth';
}
if ( $row[0]['transfer_fee_type'] == 'H' ) { // 201126
	$module_name = 'send_etoken_fee_h';
}

$getTokenFee = $db->where("module_name", $module_name)->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];

$getMinAmount = $db->where("module_name", 'min_send_amount_'.$token)->getOne('settings');
$getMinAmountVal = $getMinAmount['value'];

///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

	if ( isset($filterData['p_token']) && !empty($filterData['p_token']) && isset($filterData['p_kind']) && !empty($filterData['p_kind']) && $filterData['p_token'] != $filterData['p_kind'] ) {
		$_SESSION['failure'] = !empty($langArr['token_kind_error']) ? $langArr['token_kind_error'] : 'Tokens are different.';
		header('Location:'.$return_page);
		exit();
	}

	if ( $row[0]['etoken_use'] == 'N' ) {
		$_SESSION['failure'] = !empty($langArr['etoken_used']) ? $langArr['etoken_used'] : 'Can not use.';
		header('Location:'.$return_page);
		exit();
	}

	
	$totalAmt = trim($filterData['amount']);
	$address = $filterData['address'];

    //2021.06.21 키오스크 0 원 임시 처리
    $db->where('wallet_address', $address);
    $tempKioskInfo = $db->getOne('kiosk_config');
    if($tempKioskInfo){
        //set_tansferpw_frm_send에서 amount가 비어있는 상태로 날아옴.. 혹시 다른 값이 들어오고 숫자가 아니면 fail
        if(!empty($totalAmt) && !is_numeric($totalAmt)){
            $_SESSION['failure'] = !empty($langArr['input_invalid_value']) ? $langArr['input_invalid_value'] : 'Please enter a valid value.';
            header('Location:'.$return_page);
            exit();
        }

        if(empty($totalAmt)){
            $totalAmt = 0;
        }
    }
    else{
        if ( !is_numeric($totalAmt) ) { // 숫자가 아닐 경우
            $_SESSION['failure'] = !empty($langArr['input_invalid_value']) ? $langArr['input_invalid_value'] : 'Please enter a valid value.';
            header('Location:'.$return_page);
            exit();
        }

        if ( !empty($getMinAmountVal) &&$getMinAmountVal > $totalAmt ) {
            $ma_tmp = $getMinAmountVal.' '.$n_epay_name_array[$token];
            $_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
            header('location: '.$return_page);
            exit();
        }
    }
	
	$send_type = '';
	$send_user_id = '';

	// eToken 받는사람 설정
    //휴면 회원에게 전송 시 email, sms 발송으로 인해 추가.. 2021.06.16 By.OJT
    //휴면 계정 확인용 컬럼.
    $column = array(
        'A.account_type2','A.virtual_wallet_address','A.id_auth',
        'B.id','B.email','B.wallet_phone_email','B.register_with','B.passwd','B.passwd_new','B.passwd_salt','B.passwd_datetime',
        'B.name','B.lname','B.user_ip','B.phone','B.gender','B.dob','B.location','B.auth_phone','B.auth_name','B.auth_gender',
        'B.auth_dob','B.auth_local_code','B.n_country','B.n_phone','B.device','B.devId','B.devId2','B.devId3'
    );

    $db = getDbInstance();

    $db->where("A.wallet_address", $address);
    $db->orWhere("A.virtual_wallet_address", $address);
    $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
    $rowm = $db->getOne('admin_accounts A', $column);
    if (!$rowm) {
        $db->orWhere('wallet_address', $address);
        $db->orWhere('virtual_wallet_address', $address);
        $rowm = $db->getOne('admin_accounts');
    }

    //$db->orWhere('wallet_address', $address);
    // $db->orWhere('virtual_wallet_address', $address);
    //$rowm = $db->getOne('admin_accounts');




	if ( !empty($rowm) ) {
		$send_user_id = $rowm['id'];

		// 거래소 e-pay 거래 추가, 21.01.06
		if ( $rowm['account_type2'] == $con_exchange_type_value ) {
			$send_type = 'coinibt';
		}
		
		$db = getDbInstance();
		$db->where('wallet_address', $address);
		$row_kiosk = $db->getOne('kiosk_config');
		if ( !empty($row_kiosk) ) {
			$send_type = 'kiosk';
			$getTokenFeeVal = 0;
		} else {
			if ( $rowm['virtual_wallet_address'] == $address ) {
				$send_type = 'barry';
			}
		}
	}

	if ( $send_type != 'coinibt' ) {
		// 2020-12-22
        // 2021.11.03 제한 해제 etp3, emc, ectc
		/*
        if ( ($token == 'etp3' || $token == 'emc') && $send_type != 'kiosk' ) {
			$_SESSION['failure'] .= !empty($langArr['send_message8']) ? $langArr['send_message8'] : 'Personal transfer has been temporarily suspended.';
			header('location: '.$return_page);
			exit();	
		}
		*/

		// 2021-05-24
        // 2021.11.03 제한 해제 etp3, emc, ectc
		//if ( $token == 'ectc' || $token == 'eeth' || $token == 'eusdt') {
		if ( $token == 'eeth' || $token == 'eusdt') {
			$_SESSION['failure'] .= !empty($langArr['send_message8']) ? $langArr['send_message8'] : 'Personal transfer has been temporarily suspended.';
			header('location: '.$return_page);
			exit();	
		}
	}


	// eTP3가 아닌데 키오스크가 받을 경우, 201013
	//if ( $send_type == 'kiosk' && $token != 'etp3' ) {
	if ( $send_type == 'kiosk' && $token != 'etp3' && $token != 'emc' ) {
		//$_SESSION['failure'] .= !empty($langArr['send_text5']) ? $langArr['send_text5'] : 'Please use TP3 or eTP3 for store payment.';
		$_SESSION['failure'] .= !empty($langArr['send_text6']) ? $langArr['send_text6'] : 'Please use eTP3 or eMC for store payment.';
		header('location: '.$return_page);
		exit();	
	}
	
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row1 = $db->get('admin_accounts');
	$user_etoken_ectc = !empty($row1[0]['etoken_ectc']) ? $row1[0]['etoken_ectc'] : 0;
	$user_etoken_balance = !empty($row1[0]['etoken_'.$token]) ? $row1[0]['etoken_'.$token] : 0;

	// eToken 잔액 체크
	if ( $token == 'ectc' ) {
		if ( $totalAmt + $getTokenFeeVal > $user_etoken_balance ) {
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location:'.$return_page);
			exit();
		}
	} else {
		if ( $totalAmt > $user_etoken_balance ) {
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location:'.$return_page);
			exit();
		}
		
		// eCTC 잔액 체크
		if ( $getTokenFeeVal > 0 && $getTokenFeeVal > $user_etoken_ectc ) {
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location:'.$return_page);
			exit();
		}
	}


	// eCTC 수수료 받는사람 설정
	if ( $getTokenFeeVal > 0 ) {
		$receive_fee_id = $n_master_etoken_ctc_fee_id;
		$receiver_fee_address = $n_master_etoken_ctc_fee_wallet_address;
	}
	

	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$updateArr = [];
	if ( $token == 'ectc' ) {
		$tmp = $totalAmt + $getTokenFeeVal;
		$updateArr['etoken_'.$token] = $db->dec($tmp);
	} else {
		if ( $getTokenFeeVal > 0 ) {
			$updateArr['etoken_ectc'] = $db->dec($getTokenFeeVal);
		}
		$updateArr['etoken_'.$token] = $db->dec($totalAmt);
	}
	$last_id = $db->update('admin_accounts', $updateArr);

	if ( $last_id) {
		// eToken out
		$data_to_log = [];
		$data_to_log['user_id'] = $_SESSION['user_id'];
		$data_to_log['wallet_address'] = $walletAddress;
		$data_to_log['coin_type'] = $token;
		$data_to_log['points'] = '-'.$totalAmt;
		$data_to_log['in_out'] = 'out';
		if ( !empty($send_type) ) {
			$data_to_log['send_type'] = $send_type;
		}
		$data_to_log['send_user_id'] = $send_user_id;
		$data_to_log['send_wallet_address'] = $address;
		$data_to_log['send_fee'] = $getTokenFeeVal;
		if ( isset($filterData['p_payment_no']) && !empty($filterData['p_payment_no']) ) {
			$data_to_log['kiosk_payment_no'] = $filterData['p_payment_no'];
		}
		$data_to_log['created_at'] = date("Y-m-d H:i:s");
		$db = getDbInstance();
		$last_id_sl = $db->insert('etoken_logs', $data_to_log);

		if ( $getTokenFeeVal > 0 ) {
			// eCTC out
			$data_to_log2 = [];
			$data_to_log2['user_id'] = $_SESSION['user_id'];
			$data_to_log2['wallet_address'] = $walletAddress;
			$data_to_log2['coin_type'] = 'ectc';
			$data_to_log2['points'] = '-'.$getTokenFeeVal;
			$data_to_log2['in_out'] = 'out';
			if ( !empty($send_type) ) {
				$data_to_log2['send_type'] = $send_type;
			}
			$data_to_log2['send_user_id'] = $receive_fee_id;
			$data_to_log2['send_wallet_address'] = $receiver_fee_address;
			$data_to_log2['send_fee'] = '0';
			$data_to_log2['created_at'] = date("Y-m-d H:i:s");
			$db = getDbInstance();
			$last_id_sl2 = $db->insert('etoken_logs', $data_to_log2);
		}
	}


	// eToken in

	if ( $send_type != 'barry' ) { // 가상주소가 받을 때에는 합계를 내지 않음
		$db = getDbInstance();
		$db->where("id", $send_user_id);
		$updateArr = [];
		$updateArr['etoken_'.$token] = $db->inc($totalAmt);
		$last_id3 = $db->update('admin_accounts', $updateArr);
	}
	//if ( $last_id3 ) {
	$data_to_log = [];
	$data_to_log['user_id'] = $send_user_id;
	$data_to_log['wallet_address'] = $address;
	$data_to_log['coin_type'] = $token;
	$data_to_log['points'] = '+'.$totalAmt;
	$data_to_log['in_out'] = 'in';
	if ( !empty($send_type) ) {
		$data_to_log['send_type'] = $send_type;
	}
	$data_to_log['send_user_id'] = $_SESSION['user_id'];
	$data_to_log['send_wallet_address'] = $walletAddress;
	$data_to_log['send_fee'] = '0';
	$data_to_log['created_at'] = date("Y-m-d H:i:s");
	$db = getDbInstance();
	$last_id_sl3 = $db->insert('etoken_logs', $data_to_log);
	//}

	// 거래소 e-pay 거래 추가, 21.01.06
	// 받는사람이 coinibt 계정일 경우
	if ( $send_type == 'coinibt' ) {
			$tmp = e_pay_name_change2($token);
			ex_set_epay_logs($tmp, $totalAmt, $send_user_id, $_SESSION['user_id']);
	}

	//etoken general log 기록 by.ojt 2021.07.31 START

    $log->info('epay 목록 > epay 보내기 > epay 전송 ',['target_id'=>$send_user_id,'action'=>'A']);

    //etoken general log 기록 END

	// eCTC in

	if ( $getTokenFeeVal > 0 ) {
		$updateArr = [];
		$db = getDbInstance();
		$db->where("id", $receive_fee_id);
		$updateArr['etoken_ectc'] = $db->inc($getTokenFeeVal);
		$last_id4 = $db->update('admin_accounts', $updateArr);

		if ( $last_id4 ) {
			$data_to_log2 = [];
			$data_to_log2['user_id'] = $receive_fee_id;
			$data_to_log2['wallet_address'] = $receiver_fee_address;
			$data_to_log2['coin_type'] = 'ectc';
			$data_to_log2['points'] = '+'.$getTokenFeeVal;
			$data_to_log2['in_out'] = 'in';
			if ( !empty($send_type) ) {
				$data_to_log2['send_type'] = $send_type;
			}
			$data_to_log2['send_user_id'] = $_SESSION['user_id'];
			$data_to_log2['send_wallet_address'] = $walletAddress;
			$data_to_log2['send_fee'] = '0';
			$data_to_log2['created_at'] = date("Y-m-d H:i:s");
			$db = getDbInstance();
			$last_id_sl4 = $db->insert('etoken_logs', $data_to_log2);
		}

	}

	// SMS / Email 발송 추가
	if ( $last_id_sl && !empty($rowm) ) {
        //수신자가 KIOSK 주소면 push 분기
        if($tempKioskInfo){
            $util = walletUtil::getInstance();
            $walletDb = walletDb::singletonMethod();
            $walletDb = $walletDb->init();
            /*
            * 베리 키오스크 구매 완료 시 문자 메시지 발송.
            * [BARRY KIOSK] OOO 님이 OO etp3 입금하였습니다
            *  etoken log에 send wallet 데이터가 키오스크 주소임, 해당 주소로 kr 쪽 db에 조회
            */
            $pushInfo = $util->getCurl(
                'https://cybertronchain.kr/admin/index.php/api/getPushTargetInfo',
                ['authKey'=>'1b39309314f7b7e4e02','walletAddress'=>$address],
                    );
            /* 
              ["count"]=>
              int(1)
              ["list"]=>
              array(1) {
                [0]=>
                array(11) {
                  ["kp_id"]=>
                  string(1) "1"
                  ["kp_franchise_id"]=>
                  string(2) "17"
                  ["kp_admin_detail_id"]=>
                  string(1) "2"
                  ["kp_status"]=>
                  string(1) "Y"
                  ["kp_push_type"]=>
                  string(3) "SMS"
                  ["kp_push_type_corp"]=>
                  string(7) "COOLSMS"
                  ["kp_target"]=>
                  string(11) "01050958112"
                  ["kp_payment_type"]=>
                  string(4) "COIN"
                  ["kp_datetime"]=>
                  string(19) "2021-12-06 15:00:00"
                  ["kp_update_datetime"]=>
                  NULL
                  ["name"]=>
                  string(26) "아산 오프라인 베리"
                }
              }
              ["msg"]=>
              string(7) "success"
             */
            if(!empty($pushInfo)){
                $pushInfo = $util->jsonDecode($pushInfo);
                if($pushInfo['count'] >= 1){// json decode 후 , count가 1 이상 일 때 push를 수행 한다.
                    if($pushInfo['list'][0]['kp_status'] == 'Y'){
                        //본인인증 처리 함수 하나 만들어야 할 것 같음
                        $authNameInfo = $walletDb->createQueryBuilder()
                            ->select('name, lname, id_auth, auth_name')
                            ->from('admin_accounts')
                            ->where('id = ?')
                            ->setParameter(0,$userId)
                            ->execute()->fetch();
                        if($authNameInfo['auth_name'] == 'Y'){
                            $pushName = $authNameInfo['auth_name'];
                        }
                        else{
                            $pushName = $authNameInfo['lname'].$authNameInfo['name'];
                        }
                        $walletPush = new walletPush();
                        $msg = '[BARRY KIOSK]'.$pushName.' 님이 '.abs($totalAmt).' '.$token.' 입금 하였습니다.';
                        $walletPush->sendMessage($pushInfo['list'][0]['kp_target'],82,$msg, 'SMS');
                        $util->logFileWrite(['type'=>'SMS PUSH','selectWalletAddress'=>$address,'paymenetNo'=>$filterData['p_payment_no']],['status'=>true],'etoken-transaction-paymentNo','/var/www/ctc/wallet/logs/kioskAPI');
                    }
                }
            }
        }
        else{
            //수신자가 KIOSK가 아닌 경우
            $send_mail_result = '';
            require_once BASE_PATH.'/lib/SendMail.php';
            $wi_send_mail = new SendMail();

            $from_name = $user_name;
            $amount = $totalAmt;
            $coin_type = $token;
            $coin_type2 = lcfirst(strtoupper($coin_type));

            $subject = !empty($langArr['send_sms_message3']) ? $langArr['send_sms_message3'] : 'CyberTronChain : Coin has been sent.';
            $alert_msg = '';
            if ( $send_type != 'barry' ) {
                $send_sms_message1 = !empty($langArr['send_sms_message1']) ? $langArr['send_sms_message1'] : ' sent ';
                $alert_msg = $from_name.$send_sms_message1.new_number_format($amount, $n_decimal_point_array2[$coin_type]).$coin_type2;
                $alert_msg .= isset($langArr['send_sms_message2']) ? $langArr['send_sms_message2'] : '';
            } else {
                $send_sms_vertual_message1= !empty($langArr['send_sms_vertual_message1']) ? $langArr['send_sms_vertual_message1'] : " sent ";
                $send_sms_vertual_message2 = !empty($langArr['send_sms_vertual_message2']) ? $langArr['send_sms_vertual_message2'] : " for the purchase of goods.";
                $alert_msg = $from_name.$send_sms_vertual_message1.new_number_format($amount, $n_decimal_point_array2[$coin_type]).$coin_type2.$send_sms_vertual_message2;
            }

            if ( $rowm['register_with'] == 'phone' || ($rowm['id_auth'] == 'Y' && !empty($rowm['auth_phone']) ) ) {

                if ( $rowm['id_auth'] == 'Y' ) { // 본인인증한 경우
                    if ( !empty($rowm['n_country']) ) {
                        $country = $rowm['n_country'];
                    } else{
                        $country = '82';
                    }
                    $phone = $rowm['auth_phone'];
                } else {
                    $country = $rowm['n_country'];
                    $phone = $rowm['n_phone'];
                }
                $contents = $alert_msg;
                $send_mail_result = $wi_send_mail->send_sms ($country, $phone, $contents);

            } else {
                $contents[0] = $alert_msg;
                $send_mail_result = $wi_send_mail->send_email ($rowm['email'], $subject, $contents);
            }
        }
		
	}

	$_SESSION['success'] = !empty($langArr['send_success_message1']) ? $langArr['send_success_message1'] : "Transmission was successful.";
	header('Location:'.$return_page);
	exit();

}



//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>

<div id="page-wrapper">
	<div id="send_etoken" class="send_common">
		<?php include('./includes/flash_messages.php') ?>
		<div class="row">
			
			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->

				   <div id="main_content" class="panel-body">
					   <div class="card">  
							<ul class="index_token_block">
								<li class="token_block">
									<div class="a1">
										<div class="img2"><div><img src="images/logo2/<?php echo $token; ?>.png" alt="<?php echo $token; ?>" /></div></div>
										<span class="text"><?php echo $n_full_name_array2[$token]; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($user_etoken_balance,$n_decimal_point_array2[$token]); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array[$token]; ?></span></span>
									</div>
								</li>
								<?php
								if ( $token != 'ectc' ) { ?>
									<li class="token_block">
										<div class="a1">
											<div class="img2"><div><img src="images/logo2/ectc.png" alt="ectc" /></div></div>
											<span class="text"><?php echo $n_full_name_array2['ectc']; ?></span>
											<span class="amount"><span class="amount_t1"><?php echo new_number_format($user_etoken_ectc,$n_decimal_point_array2['ectc']); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array['ectc']; ?></span></span>
										</div>
									</li>
								<?php } ?>
							</ul>
				
							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">
								
								<form class="form" action="set_transferpw_frm_send.php" method="post"  id="customer_form" enctype="multipart/form-data">
									<input type="hidden" name="token" id="n_token" value="<?php echo $token; ?>" />
									<input type="hidden" name="lang" id="n_lang" value="<?php echo $_SESSION['lang']; ?>" />
									<input type="hidden" name="kind" id="kind" value="" />
									<input type="hidden" name="payment_no" id="payment_no" value="" />
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['send_text1']) ? $langArr['send_text1'] : "Address"; ?></span>
											<div id="to_name">
												<img src="images/icons/send_name_chk_t.png" alt="success" />
												<span id="receiver_addr_name"></span>
											</div>
											<div id="to_message">
												<img src="images/icons/send_name_chk_f.png" alt="fail" />
												<span id="receiver_message"></span>
											</div>
										</label>
										<!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class=""></textarea>-->
										<div class="barcode_img_area">
											<input type=text required title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="" placeholder="<?php echo !empty($langArr['send_explain1']) ? $langArr['send_explain1'] : 'Please paste your wallet address or take a barcode.'; ?>"><img src="images/icons/send_barcode.png" id="qrimg" alt="barcode" class="barcode_img" />
										</div>
									</div>
									<div class="clearfix"></div>
									<input type="hidden" name="get_name_result" id="get_name_result" value="0" />
									
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php echo $getTokenFeeVal; ?> <?php echo $n_epay_name_array['ectc']; ?></span>
										</label>
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="text">
									</div>
									<div class="clearfix"></div>

									<div id="show_msg" class="alert alert-info alert-dismissable"></div>
									<div class="clearfix"></div>

									<div class="col-md-12 btn_area">
										<!--<input name="submit" class="btn" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit">-->
                                        <input name="submit" class="btn" id="confirm_modal" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit" />
									</div>
								</form>
							</div>
						</div>
					</div>

					<div class="modal fade" id="confirm_modal_box" role="dialog">
						<div class="modal-dialog confirm_modal_box1">
							<form action="set_transferpw_frm_send.php" method="POST">
								<input type="hidden" name="token" value="<?php echo $token; ?>" />
								<input type="hidden" name="amount" id="m_amount" value="" />
								<input type="hidden" name="address" id="m_receiver_addr" value="" />
								<input type="hidden" name="kind" id="m_kind" value="" />
								<input type="hidden" name="payment_no" id="m_payment_no" value="" />

                                <!-- TEMP kiosk -->
                                <input type="hidden" name="kioskTempCheck" id="kioskTempCheck" value="" />
								
								<div class="modal-content">
									<div class="modal-body">
										<p id="confirm_message"></p>
									</div>
									<div class="modal-footer">
										<button type="submit" class="btn_left"><?php echo !empty($langArr['confirm_btn_yes']) ? $langArr['confirm_btn_yes'] : "Yes"; ?> </button>
										<button type="button" class="" id="closeModalBtn"><?php echo !empty($langArr['confirm_btn_no']) ? $langArr['confirm_btn_no'] : "No"; ?> </button>
									</div>
								</div>
							</form>
						</div>
					</div>

				</div>
			</div>

		</div>
	</div>
</div>

<style>
/* send : modal box (confirm) */
.confirm_modal_box1 {
	top: 150px;
}
.confirm_modal_box1 #confirm_message {
	font-size: 1.2rem;
}
.confirm_modal_box1 .modal-footer {
	background-color: #F2F2F2;
}
.confirm_modal_box1 .modal-footer button {
	font-size: 1.2rem;
}
.confirm_modal_box1 .modal-footer button:nth-child(1) {
	margin-right: 15px;
}
.send_common #show_msg {
	margin: 15px 15px 0 15px;
	display: none;
}
.confirm_modal_box1 .modal-content {
	-ms-overflow-style: none;
	scrollbar-width: none;
}
.confirm_modal_box1 .modal-content::-webkit-scrollbar {
	display: none;
}
</style>

<script type="text/javascript">

$(document).ready(function(){
	//pa_init();

	var target_id = "#qrimg"
	//if (navigator.userAgent == "android-web-view"){
	//if (navigator.userAgent.indexOf("android-web-view2") > - 1){
	if (navigator.userAgent.indexOf("android-web-view2") > - 1 || navigator.userAgent.indexOf("android-web-view3") > - 1 ){
		$(target_id).hide();
	} else if (navigator.userAgent.indexOf("android-web-view") > - 1){
		target_id = "#qrnull";
		var element = document.getElementById('qrimg');
		var href_el = document.createElement('a');
		href_el.href = 'activity://scanner_activity';
		element.parentNode.insertBefore(href_el, element);
		href_el.appendChild(element);
	} else if (navigator.userAgent.indexOf("ios-web-view") > - 1){
        $(target_id).hide();
	}

	$(target_id).click(function(){
		$(".loader").show();
		let scanner = null;
        Dynamsoft.BarcodeScanner.createInstance({
			UIElement: document.getElementById('div-video-container'),
            onFrameRead: function(results) { console.log(results);},
            onUnduplicatedRead: function(txt, result) {  $("#receiver_addr").val(txt);  $(".loader").hide(); scanner.hide(); addr_check();}
        }).then(function(s) {
            scanner = s;
			$("#div-video-container").click(function(){
				scanner.hide();
			});
			// Use back camera in mobile. Set width and height.
			// Refer [MediaStreamConstraints](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia#Syntax).
			//scanner.setVideoSettings({ video: { width: 200, height: 220, facingMode: "environment" } });

			let runtimeSettings = scanner.getRuntimeSettings();
			// Only decode OneD and QR
			runtimeSettings.BarcodeFormatIds = Dynamsoft.EnumBarcodeFormat.OneD | Dynamsoft.EnumBarcodeFormat.QR_CODE;
			// The default setting is for an environment with accurate focus and good lighting. The settings below are for more complex environments.
			runtimeSettings.localizationModes = [2,16,4,8,0,0,0,0];
			// Only accept results' confidence over 30
			runtimeSettings.minResultConfidence = 30;
			scanner.updateRuntimeSettings(runtimeSettings);

			let scanSettings = scanner.getScanSettings();
			// The same code awlways alert? Set duplicateForgetTime longer.
			scanSettings.duplicateForgetTime = 20000;
			// Give cpu more time to relax
			scanSettings.intervalTime = 300;
			scanner.setScanSettings(scanSettings);
            scanner.show().catch(function(ex){
                console.log(ex);
				 alert(ex.message || ex);
				scanner.hide();
            });
        });
		
		//$('#qrfield').trigger('click'); 
	})
	

	// Add (2020-05-18, YMJ)
	// It can only be sent to members.
	$("#receiver_addr").on('propertychange change keyup paste input', function(){
		addr_check();
	});

	//$("#confirm_modal").on('click', function(){
	$("#customer_form").on('submit', function(){

        //처리예정

        return;

		var get_name_result = $("#get_name_result").val();
		var amount = $("#amount").val();
		$("#show_msg").html('').hide();
		if (get_name_result == '0' || !amount) {
			return false;
		}
        else {
			var msg = send_before_msg_confirm();
			$("#m_amount").val($("#amount").val());
			$("#m_receiver_addr").val($("#receiver_addr").val());
			$("#m_kind").val($("#kind").val());
			$("#m_payment_no").val($("#payment_no").val());
			if ( $("#m_amount").val() == '' || $("#m_receiver_addr").val() == '' ) {
				$("#show_msg").html("<?php echo !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred.'; ?>").show();
                return false;
			}
            else {
				$("#confirm_message").html(msg);
				$("#confirm_modal_box").modal('show');
				return false;
			}
			
		}
	});
	$("#closeModalBtn").on('click', function(){
		$("#confirm_modal_box").modal('hide');
	});
	
});


/**
 * Checks if the given string is an address
 *
 * @method isAddress
 * @param {String} address the given HEX adress
 * @return {Boolean}
*/  
  
    var isAddress = function (address) {
		if (!/^(0x)?[0-9a-f]{40}$/i.test(address)) {
			// check if it has the basic requirements of an address
			return false;
		//} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address)) {
		} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address) || /^(0x)?[0-9a-fA-F]{40}$/.test(address)) {
			// If it's all small caps or all all caps, return true
			return true;
		} else {
			// Otherwise check each case
			return isChecksumAddress(address);
		}
};

/**
 * Checks if the given string is a checksummed address
 *
 * @method isChecksumAddress
 * @param {String} address the given HEX adress
 * @return {Boolean}
*/
var isChecksumAddress = function (address) {
	// Check each case
	address = address.replace('0x','');
	var addressHash = sha3(address.toLowerCase());
	for (var i = 0; i < 40; i++ ) {
		// the nth letter should be uppercase if the nth digit of casemap is 1
		if ((parseInt(addressHash[i], 16) > 7 && address[i].toUpperCase() !== address[i]) || (parseInt(addressHash[i], 16) <= 7 && address[i].toLowerCase() !== address[i])) {
			return false;
		}
	}
	return true;
};

function addr_check(){
	var addr = $("#receiver_addr").val();
	var addr_length = addr.length;
	$(".fee1").show();
	
	if( addr_length < 42){
		$("#to_name").removeClass('to_name');
		$("#receiver_addr_name").html('');
		$("#to_message").addClass('to_name');
		$("#receiver_message").html("<?php echo !empty($langArr['invalid_wallet_address']) ? $langArr['invalid_wallet_address'] : 'Invalid Wallet Address'; ?>");
		$("#get_name_result").val('0');
	} else {
		var get = isAddress(addr);
		if (get == false) {
			$("#to_name").removeClass('to_name');
			$("#receiver_addr_name").html('');
			$("#to_message").addClass('to_name');
			$("#receiver_message").html("<?php echo !empty($langArr['invalid_wallet_address']) ? $langArr['invalid_wallet_address'] : 'Invalid Wallet Address'; ?>");
			$("#get_name_result").val('0');
		} else {
			$("#to_message").removeClass('to_name');
			
			$.ajax({
				url : 'send.pro.php',
				type : 'POST',
				data : {mode: 'get_name2', waddr : addr},
				dataType : 'json',
				success : function(resp){
					var name = resp.result;
					var store_name = resp.store_name;
					if (name != '') {
						if ( name == 'coinibt_false' ) {
							$("#to_name").removeClass('to_name');
							$("#receiver_addr_name").html('');
							$("#to_message").addClass('to_name');
							$("#receiver_message").html("<?php echo !empty($langArr['send_member_msg2']) ? $langArr['send_member_msg2'] : 'You can only send to your own exchange address.'; ?>");
							$("#get_name_result").val('0');
						} else {
							$("#to_name").addClass('to_name');
							$("#receiver_addr_name").html(name);
							$("#to_message").removeClass('to_name');
							$("#receiver_message").html("");
							$("#get_name_result").val('1');
							if ( store_name == 'kiosk' ) {
								$(".fee1").hide();
                                $('#kioskTempCheck').val(resp.store_name);
							}
						}
						/*$("#to_name").addClass('to_name');
						$("#receiver_addr_name").html(name);
						$("#to_message").removeClass('to_name');
						$("#receiver_message").html("");
						$("#get_name_result").val('1');
						if ( store_name == 'kiosk' ) {
							$(".fee1").hide();
						}*/
					} else {
						$("#to_name").removeClass('to_name');
						$("#receiver_addr_name").html('');
						$("#to_message").addClass('to_name');
						$("#receiver_message").html("<?php echo !empty($langArr['send_member_msg1']) ? $langArr['send_member_msg1'] : 'It can only be sent to members.'; ?>");
						$("#get_name_result").val('0');
					}
				},
				error : function(name){
					$("#to_name").removeClass('to_name');
					$("#receiver_addr_name").html('');
					$("#to_message").addClass('to_name');
					$("#receiver_message").html("<?php echo !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred'; ?>");
					$("#get_name_result").val('0');
				}
			});
		}
	}
}

// Add : Check the recipient and amount before sending
function send_before_msg_confirm() {
	var to_name = $("#receiver_addr_name").html();
	var amount = $("#amount").val();
	var token = $("#n_token").val().toUpperCase();
	var lang = $("#n_lang").val();
	var msg_c1 = "<?php echo !empty($langArr['send_confirm_message1']) ? $langArr['send_confirm_message1'] : ' to '; ?>";
	var msg_c2 = "<?php echo !empty($langArr['send_confirm_message2']) ? $langArr['send_confirm_message2'] : 'Would you like to send '; ?>";
	if ( lang == 'en') {
		var msg = msg_c2 + amount + ' ' + token + msg_c1 + to_name + '?';
	} else {
		var msg = to_name + msg_c1 + amount + ' ' + token + msg_c2 + '?';
	}
	return msg;
}
</script>

<?php include_once 'includes/footer.php'; ?>
