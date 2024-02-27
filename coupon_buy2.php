<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';


//require_once BASE_PATH.'/lib/WalletInfos.php';
//$wi_wallet_infos = new WalletInfos();

//include_once 'includes/header.php';

 header('Content-Type: charset=utf-8'); 
    /* ============================================================================== */
    /* =   PAGE : 결제 요청 PAGE                                             = */
    /* = -------------------------------------------------------------------------- = */
    /* =   아래의 ※ 필수, ※ 옵션 부분과 매뉴얼을 참조하셔서 연동을   = */
    /* =   진행하여 주시기 바랍니다.                                          = */
    /* = -------------------------------------------------------------------------- = */
    /* =   연동시 오류가 발생하는 경우 아래의 주소로 접속하셔서 확인하시기 바랍니다.= */
    /* =   접속 주소 : http://kcp.co.kr/technique.requestcode.do			        = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2016  NHN KCP Inc.   All Rights Reserverd.                = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   환경 설정 파일 Include                                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* =   테스트 및 실결제 연동시 site_conf_inc.jsp 파일을 수정하시기 바랍니다.    = */
    /* = -------------------------------------------------------------------------- = */

     include "kcp7/cfg/site_conf_inc.php";       // 환경설정 파일 include
	

	// 20.11.17 추가, YMJ
	$req_tx          = '';
	$res_cd          = '';
	$tran_cd         = '';
	$ordr_idxx       = '';
	$good_name       = '';
	$good_mny        = '';
	$buyr_name       = '';
	$buyr_tel1       = '';
	$buyr_tel2       = '';
	$buyr_mail       = '';
	$use_pay_method  = '';
	$enc_info        = '';
	$enc_data        = '';
	$cash_yn         = '';
	$cash_tr_code    = '';
	/* 기타 파라메터 추가 부분 - Start - */
	$param_opt_1    = '';
	$param_opt_2    = '';
	$param_opt_3    = '';

    /* = -------------------------------------------------------------------------- = */
    /* =   환경 설정 파일 Include END                                               = */
    /* ============================================================================== */
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST')  { // 20.11.17 추가, YMJ
		/* kcp와 통신후 kcp 서버에서 전송되는 결제 요청 정보 */
		/*$req_tx          = $_POST[ "req_tx"         ]; // 요청 종류         
		$res_cd          = $_POST[ "res_cd"         ]; // 응답 코드         
		$tran_cd         = $_POST[ "tran_cd"        ]; // 트랜잭션 코드     
		$ordr_idxx       = $_POST[ "ordr_idxx"      ]; // 쇼핑몰 주문번호   
		$good_name       = $_POST[ "good_name"      ]; // 상품명            
		$good_mny        = $_POST[ "good_mny"       ]; // 결제 총금액       
		$buyr_name       = $_POST[ "buyr_name"      ]; // 주문자명          
		$buyr_tel1       = $_POST[ "buyr_tel1"      ]; // 주문자 전화번호   
		$buyr_tel2       = $_POST[ "buyr_tel2"      ]; // 주문자 핸드폰 번호
		$buyr_mail       = $_POST[ "buyr_mail"      ]; // 주문자 E-mail 주소
		$use_pay_method  = $_POST[ "use_pay_method" ]; // 결제 방법         
		$enc_info        = $_POST[ "enc_info"       ]; // 암호화 정보       
		$enc_data        = $_POST[ "enc_data"       ]; // 암호화 데이터     
		$cash_yn         = $_POST[ "cash_yn"        ];
		$cash_tr_code    = $_POST[ "cash_tr_code"   ];*/


		$req_tx          = isset($_POST["req_tx"]) ? $_POST["req_tx"] : ''; // 요청 종류         
		$res_cd          = isset($_POST[ "res_cd"] ) ? $_POST[ "res_cd"] : ''; // 응답 코드         
		$tran_cd         = isset($_POST[ "tran_cd"]) ? $_POST[ "tran_cd"] : ''; // 트랜잭션 코드     
		$ordr_idxx       = isset($_POST[ "ordr_idxx"]) ? $_POST[ "ordr_idxx"] : ''; // 쇼핑몰 주문번호   
		$good_name       = isset($_POST[ "good_name"]) ? $_POST[ "good_name"] : ''; // 상품명            
		$good_mny        = isset($_POST[ "good_mny"]) ? $_POST[ "good_mny"] : ''; // 결제 총금액       
		$buyr_name       = isset($_POST[ "buyr_name"]) ? $_POST[ "buyr_name"] : ''; // 주문자명          
		$buyr_tel1       = isset($_POST[ "buyr_tel1"]) ? $_POST[ "buyr_tel1"] : ''; // 주문자 전화번호   
		$buyr_tel2       = isset($_POST[ "buyr_tel2"]) ? $_POST[ "buyr_tel2"] : ''; // 주문자 핸드폰 번호
		$buyr_mail       = isset($_POST[ "buyr_mail"]) ? $_POST[ "buyr_mail"] : ''; // 주문자 E-mail 주소
		$use_pay_method  = isset($_POST[ "use_pay_method"]) ? $_POST[ "use_pay_method"] : ''; // 결제 방법         
		$enc_info        = isset($_POST[ "enc_info"]) ? $_POST[ "enc_info"] : ''; // 암호화 정보       
		$enc_data        = isset($_POST[ "enc_data"]) ? $_POST[ "enc_data"] : ''; // 암호화 데이터     
		$cash_yn         = isset($_POST[ "cash_yn"]) ? $_POST[ "cash_yn"] : '';
		$cash_tr_code    = isset($_POST[ "cash_tr_code"]) ? $_POST[ "cash_tr_code"] : '';


		/* 기타 파라메터 추가 부분 - Start - */
		//$param_opt_1    = $_POST[ "param_opt_1"     ]; // 기타 파라메터 추가 부분
		//$param_opt_2    = $_POST[ "param_opt_2"     ]; // 기타 파라메터 추가 부분
		//$param_opt_3    = $_POST[ "param_opt_3"     ]; // 기타 파라메터 추가 부분
		$param_opt_1    = isset($_POST[ "param_opt_1"]) ? $_POST[ "param_opt_1"] : ''; // 기타 파라메터 추가 부분
		$param_opt_2    = isset($_POST[ "param_opt_2"]) ? $_POST[ "param_opt_2"] : ''; // 기타 파라메터 추가 부분
		$param_opt_3    = isset($_POST[ "param_opt_3"]) ? $_POST[ "param_opt_3"] : ''; // 기타 파라메터 추가 부분
		/* 기타 파라메터 추가 부분 - End -   */
	}
	$tablet_size     = "1.0"; // 화면 사이즈 고정
	$url = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

$coupon_type = 'fee_change';
$coupon_coin_type = 'none';

$db = getDbInstance();
$db->where('used',  'Y');
$db->where('kind',  $coupon_type);
$db->where('coin_type',  $coupon_coin_type);
$coupon_row = $db->getOne('coupon_list');

//$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row77 = $db->get('admin_accounts');
//$order_name=$row77[0]['lname'].$row77[0]['name'];	
$order_name=$row77[0]['auth_name'];
$order_phone=$row77[0]['auth_phone'];
$order_id_auth=$row77[0]['id_auth'];
//echo $order_id_auth;
if($order_id_auth=="N"){ // 본인인증받은 회원만 이용가능
	$_SESSION['failure'] = !empty($langArr['coupon_buy_use_auth_y']) ? $langArr['coupon_buy_use_auth_y'] : 'Only members who have authenticated themselves can use it.';
	header('Location:coupon_list.php');
	exit();
}
if ( $row77[0]['transfer_approved'] == 'C' ) {
	//$_SESSION['failure'] = !empty($langArr['change_fee_message4']) ? $langArr['change_fee_message4'] : "You are already using CTC as a commission."; 
	header('Location:change_fee.php');
	exit();
}

$coupon_result_count = 0;

// 이 페이지에 접속하면 무조건 kcp_order 필드 추가
if($enc_data ==""){

	// 2021-01-29
	$db = getDbInstance();
	$getPayService = $db->where("module_name", 'payment_service_available')->getOne('settings');
	if ( !empty($getPayService) && $getPayService['value'] == 'N' ) {
		$_SESSION['failure'] = !empty($langArr['coupon_buy_message9']) ? $langArr['coupon_buy_message9'] : 'Payment service has been temporarily suspended. We apologize for the inconvenience.';
		header('Location:index.php');
		exit();
	}
		
	$db = getDbInstance();
	$db->where('user_id', $_SESSION['user_id']);
	$db->where('coupon_id', $coupon_row['id']);
	$db->where('status', 'canceled', '!=');
	$db->where('gstatus', '0'); // 가상계좌 입금기한 만료일 경우가 있기 때문에 결제완료 건만 카운트함
	$row = $db->getOne('coupon_result', 'count(*) as counts');
	$coupon_result_count = $row['counts'];

	if ( $coupon_result_count > 0 ) { // -------------------------------------------------------------------------------
		$_SESSION['failure'] = !empty($langArr['coupon_buy_message8']) ? $langArr['coupon_buy_message8'] : "You have already paid."; 
		header('Location:coupon_list.php');
		exit();
	}


	$order1['order_num'] = date("Ymd") . time() . substr(md5(microtime()), 0, 6);   ; // 주문번호  생성
	$order1['order_uid'] =  $_SESSION['user_id'];
	$order1['order_name']= $order_name;
	$order1['wdate'] = date('Y-m-d H:i:s');      
	$order_last_id = $db->insert ('kcp_order', $order1);
}

include_once 'includes/header.php';

?>

<link  rel="stylesheet" href="css/coupon.css?ver=2.1.7"/>

<!-- 거래등록 하는 kcp 서버와 통신을 위한 스크립트-->
<script type="text/javascript" src="kcp7/mobile_sample/js/approval_key.js"></script>

<script type="text/javascript">
  var controlCss = "kcp7/css/style_mobile.css";
  var isMobile = {
    Android: function() {
      return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
      return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
      return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
      return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
      return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
      return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
  };

  if( isMobile.any() )
    document.getElementById("cssLink").setAttribute("href", controlCss);
</script>
<script type="text/javascript">
   /* 주문번호 생성 예제 */
  function init_orderid()
  {
    var today = new Date();
    var year  = today.getFullYear();
    var month = today.getMonth() + 1;
    var date  = today.getDate();
    var time  = today.getTime();

    if (parseInt(month) < 10)
      month = "0" + month;

    if (parseInt(date) < 10)
      date  = "0" + date;

    var order_idxx = "TEST" + year + "" + month + "" + date + "" + time;
    var ipgm_date  = year + "" + month + "" + date;

  //  document.order_info.ordr_idxx.value = order_idxx;
    document.order_info.ipgm_date.value = ipgm_date;
  }

   /* kcp web 결제창 호츨 (변경불가) */
  function call_pay_form()
  {
    var v_frm = document.order_info; 
    
    v_frm.action = PayUrl;

    if (v_frm.Ret_URL.value == "")
    {
	  /* Ret_URL값은 현 페이지의 URL 입니다. */
	  pop_message("<?php echo !empty($langArr['coupon_buy_message4']) ? $langArr['coupon_buy_message4'] : 'When linking, you must set Ret_URL.'; ?>");
	  //alert("연동시 Ret_URL을 반드시 설정하셔야 됩니다.");

      return false;
    }
    else
    {
      v_frm.submit();
    }
  }

   /* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청 (변경불가) */
  function chk_pay()
  {
    self.name = "tar_opener";
    var pay_form = document.pay_form;

    if (pay_form.res_cd.value == "3001" )
    {
      //alert("사용자가 취소하였습니다.");
	  location.href="coupon_buy2.php";
	  //pop_message("<?php echo !empty($langArr['coupon_buy_message5']) ? $langArr['coupon_buy_message5'] : 'User canceled.'; ?>");
      pay_form.res_cd.value = "";
    }
    
    if (pay_form.enc_info.value)
      pay_form.submit();
  }

  function jsf__chk_type()
  {
    if ( document.order_info.ActionResult.value == "card" )
    {
      document.order_info.pay_method.value = "CARD";
    }
    else if ( document.order_info.ActionResult.value == "acnt" )
    {
      document.order_info.pay_method.value = "BANK";
    }
    else if ( document.order_info.ActionResult.value == "vcnt" )
    {
      document.order_info.pay_method.value = "VCNT";
    }
    else if ( document.order_info.ActionResult.value == "mobx" )
    {
      document.order_info.pay_method.value = "MOBX";
    }
    else if ( document.order_info.ActionResult.value == "ocb" )
    {
      document.order_info.pay_method.value = "TPNT";
      document.order_info.van_code.value = "SCSK";
    }
    else if ( document.order_info.ActionResult.value == "tpnt" )
    {
      document.order_info.pay_method.value = "TPNT";
      document.order_info.van_code.value = "SCWB";
    }
    else if ( document.order_info.ActionResult.value == "scbl" )
    {
      document.order_info.pay_method.value = "GIFT";
      document.order_info.van_code.value = "SCBL";
    }
    else if ( document.order_info.ActionResult.value == "sccl" )
    {
      document.order_info.pay_method.value = "GIFT";
      document.order_info.van_code.value = "SCCL";
    }
    else if ( document.order_info.ActionResult.value == "schm" )
    {
      document.order_info.pay_method.value = "GIFT";
      document.order_info.van_code.value = "SCHM";
    }
  }

</script>


<body onload="jsf__chk_type();init_orderid();chk_pay();">

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	?>
	<div class="coupon_payment_processing">
		<img src="images/icons/loading.gif" alt="loading" />
		<p><?php echo !empty($langArr['coupon_payment_processing']) ? $langArr['coupon_payment_processing'] : "Processing"; ?></p>
	</div>
	<?php
}
?>

<div id="page-wrapper" style="border:0px solid red; ">
	<div class="row" style="border:0px solid red; ">
		 <div class="col-lg-6">
			<h1 class="page-header"><?php echo !empty($langArr['coupon_buy_subject1']) ? $langArr['coupon_buy_subject1'] : "Fee conversion"; ?></h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php') ?>
	
    <div class="tab-content">
		<div id="coupon_buy">
			
			<form name="order_info" method="post" accept-charset="euc-kr">
													
				<div class="sample">
					<fieldset>
						<div id="coupon_buy2" class="coupon_fee_change">
							<ul class="list_notice">
								<li class="bold"><?php echo !empty($langArr['coupon_payment_text5']) ? $langArr['coupon_payment_text5'] : "Amount of payment"; ?> : <span id="coin_price"><?php echo $coupon_row['amount']; ?></span> <?php echo !empty($langArr['krw_unit']) ? $langArr['krw_unit'] : "WON"; ?></li>
								<li><?php echo !empty($langArr['coupon_payment_text7']) ? $langArr['coupon_payment_text7'] : "Payment Method"; ?>
									<select name="ActionResult" onchange="jsf__chk_type();">
										<option value="" ><?php echo !empty($langArr['coupon_buy_message3']) ? $langArr['coupon_buy_message3'] : "Please select a payment method."; ?></option>
										<option value="card"><?php echo !empty($langArr['coupon_payment_text8']) ? $langArr['coupon_payment_text8'] : "Credit card"; ?></option>
										<option value="acnt"><?php echo !empty($langArr['coupon_payment_text9']) ? $langArr['coupon_payment_text9'] : "Bank Transfer"; ?></option>
										<option value="vcnt"><?php echo !empty($langArr['coupon_payment_text10']) ? $langArr['coupon_payment_text10'] : "Virtual Account"; ?></option>
									</select>
								</li>
							</ul>

							<div class="right">※ <?php echo !empty($langArr['coupon_buy_message6']) ? $langArr['coupon_buy_message6'] : "This coupon is applied only once per account and is not renewed."; ?></div>							

							<ul class="list_agree">
								<li class="subject"><?php echo !empty($langArr['coupon_buy_agree9']) ? $langArr['coupon_buy_agree9'] : "Cautions when applying for fee conversion"; ?></li>
								<li><?php echo !empty($langArr['coupon_buy_agree10']) ? $langArr['coupon_buy_agree10'] : "After applying, the fee conversion application is completed only after payment is completed."; ?></li>
								<li><?php echo !empty($langArr['coupon_buy_agree11']) ? $langArr['coupon_buy_agree11'] : "After payment, it may take some time to apply depending on the circumstances of the system operation."; ?></li>
								<li><?php echo !empty($langArr['coupon_buy_agree14']) ? $langArr['coupon_buy_agree14'] : "Refunds and cancellations after purchase are not possible in principle. Please check again before purchasing."; ?></li>
								<li>
								<input type="checkbox" name="c_buy_agree_chk" value="1" id="c_buy_agree_chk" /><label for="c_buy_agree_chk"><?php echo !empty($langArr['coupon_buy_agree8']) ? $langArr['coupon_buy_agree8'] : "If you agree to the precautions, please check Agree."; ?></label></li>
							</ul>

							

						</div>			

					</fieldset>
						<!-- 주문 정보 -->

					<?php
						$message1 =  !empty($langArr['coupon_buy_message1']) ? $langArr['coupon_buy_message1'] : 'Please check the precautions.';
						$message2 =  !empty($langArr['coupon_buy_message2']) ? $langArr['coupon_buy_message2'] : 'Please select an amount.';
						$message3 =  !empty($langArr['coupon_buy_message3']) ? $langArr['coupon_buy_message3'] : 'Please select a payment method.';
					?>
					<div class="btnset" id="display_pay_button" style="display:block">
						<input type="button"  value="<?php echo !empty($langArr['coupon_list_btn2']) ? $langArr['coupon_list_btn2'] : "Payment"; ?>" onclick="kcp_AJAX('<?php echo $message1; ?>', '<?php echo $message2; ?>', '<?php echo $message3; ?>');"  class="btn_c1">
					</div>
				 </div>
				

				  <!--//footer-->
				  <!--<input type="hidden" name="ordr_idxx" class="w200" value="<?=$order1['order_num']?>">-->
				  <input type="hidden" name="ordr_idxx" class="w200" value="<?php echo isset($order1['order_num']) ? $order1['order_num'] : ''; ?>">
				  <input type="hidden" name="good_name" class="w100" value="<?php echo isset($coupon_row['name']) ? $coupon_row['name'] : ''; ?>">
				  <input type="hidden" name="good_mny" class="w100" value="<?php echo isset($coupon_row['amount']) ? $coupon_row['amount'] : ''; ?>">
				  <input type="hidden" name="buyr_name" class="w100" value="<?=$order_name?>">
				  <input type="hidden" name="buyr_mail" class="w200" value="">
				  <input type="hidden" name="buyr_tel1" class="w100" value="02-2108-1000">
				  <input type="hidden" name="buyr_tel2" class="w100" value="010-0000-0000">
				  <!-- 공통정보 -->
				  <input type="hidden" name="req_tx"          value="pay">                           <!-- 요청 구분 -->
				  <input type="hidden" name="shop_name"       value="<?= $g_conf_site_name ?>">      <!-- 사이트 이름 --> 
				  <input type="hidden" name="site_cd"         value="<?= $g_conf_site_cd   ?>">      <!-- 사이트 코드 -->
				  <input type="hidden" name="currency"        value="410"/>                          <!-- 통화 코드 -->
				  <input type="hidden" name="eng_flag"        value="N"/>                            <!-- 한 / 영 -->
				  <!-- 결제등록 키 -->
				  <input type="hidden" name="approval_key"    id="approval">
				  <!-- 인증시 필요한 파라미터(변경불가)-->
				  <input type="hidden" name="escw_used"       value="N">
				  <input type="hidden" name="pay_method"      value="">
				  <input type="hidden" name="van_code"        value="">
				  <!-- 신용카드 설정 -->
				  <input type="hidden" name="quotaopt"        value="12"/>                           <!-- 최대 할부개월수 -->
				  <!-- 가상계좌 설정 -->
				  <input type="hidden" name="ipgm_date"       value=""/>
				  <!-- 가맹점에서 관리하는 고객 아이디 설정을 해야 합니다.(필수 설정) -->
				  <input type="hidden" name="shop_user_id"    value=""/>
				  <!-- 복지포인트 결제시 가맹점에 할당되어진 코드 값을 입력해야합니다.(필수 설정) -->
				  <input type="hidden" name="pt_memcorp_cd"   value=""/>
				  <!-- 현금영수증 설정 -->
				  <input type="hidden" name="disp_tax_yn"     value="Y"/>
				  <!-- 리턴 URL (kcp와 통신후 결제를 요청할 수 있는 암호화 데이터를 전송 받을 가맹점의 주문페이지 URL) -->
				  <input type="hidden" name="Ret_URL"         value="<?=$url?>">
				  <!-- 화면 크기조정 -->
				  <input type="hidden" name="tablet_size"     value="<?=$tablet_size?>">

				  <!-- 추가 파라미터 ( 가맹점에서 별도의 값전달시 param_opt 를 사용하여 값 전달 ) -->
				  <input type="hidden" name="param_opt_1"     value="<?=$_SESSION['user_id']?>">
				  <input type="hidden" name="param_opt_2"     value="<?php echo isset($coupon_row['id']) ? $coupon_row['id'] : ''; ?>">
				  <input type="hidden" name="param_opt_3"     value="<?php echo isset($coupon_row['coin_amount']) ? $coupon_row['coin_amount'] : ''; ?>">

			<?
				/* ============================================================================== */
				/* =   옵션 정보                                                                = */
				/* = -------------------------------------------------------------------------- = */
				/* =   ※ 옵션 - 결제에 필요한 추가 옵션 정보를 입력 및 설정합니다.             = */
				/* = -------------------------------------------------------------------------- = */
				/* 카드사 리스트 설정
				예) 비씨카드와 신한카드 사용 설정시
				<input type="hidden" name='used_card'    value="CCBC:CCLG">

				/*  무이자 옵션
						※ 설정할부    (가맹점 관리자 페이지에 설정 된 무이자 설정을 따른다)                             - "" 로 설정
						※ 일반할부    (KCP 이벤트 이외에 설정 된 모든 무이자 설정을 무시한다)                           - "N" 로 설정
						※ 무이자 할부 (가맹점 관리자 페이지에 설정 된 무이자 이벤트 중 원하는 무이자 설정을 세팅한다)   - "Y" 로 설정
				<input type="hidden" name="kcp_noint"       value=""/> */

				/*  무이자 설정
						※ 주의 1 : 할부는 결제금액이 50,000 원 이상일 경우에만 가능
						※ 주의 2 : 무이자 설정값은 무이자 옵션이 Y일 경우에만 결제 창에 적용
						예) BC 2,3,6개월, 국민 3,6개월, 삼성 6,9개월 무이자 : CCBC-02:03:06,CCKM-03:06,CCSS-03:06:04
				<input type="hidden" name="kcp_noint_quota" value="CCBC-02:03:06,CCKM-03:06,CCSS-03:06:09"/> */

				/* KCP는 과세상품과 비과세상품을 동시에 판매하는 업체들의 결제관리에 대한 편의성을 제공해드리고자, 
				   복합과세 전용 사이트코드를 지원해 드리며 총 금액에 대해 복합과세 처리가 가능하도록 제공하고 있습니다
				   복합과세 전용 사이트 코드로 계약하신 가맹점에만 해당이 됩니다
				   상품별이 아니라 금액으로 구분하여 요청하셔야 합니다
				   총결제 금액은 과세금액 + 부과세 + 비과세금액의 합과 같아야 합니다. 
				   (good_mny = comm_tax_mny + comm_vat_mny + comm_free_mny)
				
					<input type="hidden" name="tax_flag"       value="TG03">  <!-- 변경불가	   -->
					<input type="hidden" name="comm_tax_mny"   value=""    >  <!-- 과세금액	   --> 
					<input type="hidden" name="comm_vat_mny"   value=""    >  <!-- 부가세	   -->
					<input type="hidden" name="comm_free_mny"  value=""    >  <!-- 비과세 금액 --> */
				/* = -------------------------------------------------------------------------- = */
				/* =   옵션 정보 END                                                            = */
				/* ============================================================================== */
			?>

			</form>
		</div>

		<form name="pay_form" method="post" action="kcp7/mobile_sample/pp_cli_hub.php">
			<input type="hidden" name="req_tx"         value="<?=$req_tx?>">               <!-- 요청 구분          -->
			<input type="hidden" name="res_cd"         value="<?=$res_cd?>">               <!-- 결과 코드          -->
			<input type="hidden" name="tran_cd"        value="<?=$tran_cd?>">              <!-- 트랜잭션 코드      -->
			<input type="hidden" name="ordr_idxx"      value="<?=$ordr_idxx?>">            <!-- 주문번호           -->
			<input type="hidden" name="good_mny"       value="<?=$good_mny?>">             <!-- 휴대폰 결제금액    -->
			<input type="hidden" name="good_name"      value="<?=$good_name?>">            <!-- 상품명             -->
			<input type="hidden" name="buyr_name"      value="<?=$buyr_name?>">            <!-- 주문자명           -->
			<input type="hidden" name="buyr_tel1"      value="<?=$buyr_tel1?>">            <!-- 주문자 전화번호    -->
			<input type="hidden" name="buyr_tel2"      value="<?=$buyr_tel2?>">            <!-- 주문자 휴대폰번호  -->
			<input type="hidden" name="buyr_mail"      value="<?=$buyr_mail?>">            <!-- 주문자 E-mail      -->
			<input type="hidden" name="cash_yn"		   value="<?=$cash_yn?>">              <!-- 현금영수증 등록여부-->
			<input type="hidden" name="enc_info"       value="<?=$enc_info?>">
			<input type="hidden" name="enc_data"       value="<?=$enc_data?>">
			<input type="hidden" name="use_pay_method" value="<?=$use_pay_method?>">
			<input type="hidden" name="cash_tr_code"   value="<?=$cash_tr_code?>">

			<!-- 추가 파라미터 -->
			<input type="hidden" name="param_opt_1"	   value="<?=$param_opt_1?>">
			<input type="hidden" name="param_opt_2"	   value="<?=$param_opt_2?>">
			<input type="hidden" name="param_opt_3"	   value="<?=$param_opt_3?>">
		</form>    
    
	    <!--</div>-->
	</div>
</div>

<?php
include_once 'includes/footer.php'; ?>

