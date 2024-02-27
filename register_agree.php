<?php
// Page in use
//die("Registration close for public user");
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

//$_SESSION['login_failure'] = !empty($langArr['notice_construction']) ? $langArr['notice_construction'] : 'Checking server. Access is not possible from 2020-07-20 11:30 to 2020-07-22 00:00. Please understand that this is to provide better service. Thank you.';
//header('Location:login.php'); // -------------------
//exit(); // -------------------

//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}


/* ============================================================================== */
/* =   PAGE : 인증 요청 PAGE                                                    = */
/* = -------------------------------------------------------------------------- = */
/* =   Copyright (c)  2012.02   KCP Inc.   All Rights Reserved.                 = */
/* ============================================================================== */

/* ============================================================================== */
/* =   환경 설정 파일 Include                                                   = */
/* = -------------------------------------------------------------------------- = */
//include "./config/kcp_config.php";      // 환경설정 파일 include
include "/var/www/ctc/wallet/kcp/kcp_config.php";
//include "../wallet/kcp/kcp_config.php";
$g_conf_Ret_URL      = "https://cybertronchain.com/wallet2/auth.pro.res_r.php"; // 수정금지

// blocked IP Code, 20.10.20
$userip = new_getUserIpAddr();
if ( !empty($userip) ) {
	$blocked_ip_count = 0;
	$db = getDbInstance();
	$db->where("ip_name", $userip);
	$blocked_ip_count = $db->getValue('blocked_ips', 'count(*)');
	if ($blocked_ip_count > 0) { 
		header('location: login.php');
		exit();
	}
}

$ip_kor = '';
$ip_kor = trim(new_ipinfo_ip_chk('2'));
if ($ip_kor == '') {
	$ip_kor = new_kisa_ip_chk();
}

// IP, Country Check (20.06.25)
//$uip = new_getUserIpAddr();
//new_fn_logSave( 'IP : ' . $uip . ', CountryCode : ' . $ip_kor . ' , File : ' . $_SERVER['SCRIPT_FILENAME']);


if ($ip_kor == 'KR') {
	header('Location:register.php');
	exit();
}

$serviceInfo = $db->where('p_type','SERVICE')->orderBy('p_datetime','DESC')
    ->getOne('policy','*');
$privacyInfo = $db->where('p_type','PRIVACY')->orderBy('p_datetime','DESC')
    ->getOne('policy','*');

include_once 'includes/header.php';
?>

<link  rel="stylesheet" href="css/login.css"/>

<div class="login-bg" id="cert_info">

	<div id="register_agree" class="login_input">

		<div id="contents">
		
			<?php
			if(isset($_SESSION['failure']))
			{
                /*echo "본인인증 작업중입니다.";
                exit;*/
			echo '<div class="alert alert-danger alert-dismissable">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
						<strong>Oops! </strong>'. $_SESSION['failure'].'
				  </div>';
			  unset($_SESSION['failure']);
			}

			?>

			<p class="loginc_text1"><?php echo !empty($langArr['login_agree_text1']) ? $langArr['login_agree_text1'] : 'Membership can be registered after authentication'; ?></p>
			<p class="loginc_text2"><?php echo !empty($langArr['login_agree_text2']) ? $langArr['login_agree_text2'] : 'Only your mobile phone'; ?><br /><?php echo !empty($langArr['login_agree_text3']) ? $langArr['login_agree_text3'] : 'can be authenticated'; ?></p>
			
			<div class="agree_box">
				<div class="check_box"><input type="checkbox" name="agree_chk1" class="checkbox1" id="agree_chk1"  /></div>
				<label for="agree_chk1">
					<span class="check"><?php echo !empty($langArr['login_agree_check']) ? $langArr['login_agree_check'] : '(Essential)'; ?></span>
					<span class="text1"><?php echo !empty($langArr['login_agree_agree1']) ? $langArr['login_agree_agree1'] : 'Terms and Conditions of Use of Members'; ?></span>
				</label>
				<div class="more_box">
					<span class="more more1" onclick="login_agree_check('1');"  id="agree_more1_more"><?php echo !empty($langArr['login_agree_more']) ? $langArr['login_agree_more'] : 'More'; ?></span>
					<span class="more more1 none" onclick="login_agree_check('1');" id="agree_more1_close"><?php echo !empty($langArr['login_agree_close']) ? $langArr['login_agree_close'] : 'close'; ?></span>
				</div>
			</div>

			<div id="agree_more1_contents" class="none">
                <?php echo $serviceInfo['p_content']?>
			</div>
			
			<div class="agree_box">
				<div class="check_box"><input type="checkbox" name="agree_chk2" class="checkbox1" id="agree_chk2"  /></div>
				<label for="agree_chk2">
					<span class="check"><?php echo !empty($langArr['login_agree_check']) ? $langArr['login_agree_check'] : '(Essential)'; ?></span>
					<span class="text1"><?php echo !empty($langArr['login_agree_agree2']) ? $langArr['login_agree_agree2'] : "Member's personal information collection and use consent"; ?></span>
				</label>
				<div class="more_box">
					<span class="more more2" onclick="login_agree_check('2');" id="agree_more2_more"><?php echo !empty($langArr['login_agree_more']) ? $langArr['login_agree_more'] : 'More'; ?></span>
					<span class="more more2 none" onclick="login_agree_check('2');" id="agree_more2_close"><?php echo !empty($langArr['login_agree_close']) ? $langArr['login_agree_close'] : 'close'; ?></span>
				</div>
			</div>
			<div id="agree_more2_contents" class="none">
                <?php echo $privacyInfo['p_content']?>
			</div>

            <div class="privacyVersionList">
                <ul>
                    <li onclick="privacy('v1.0')">회원 개인정보 수집 및 이용동의 1.0 보기</li>
                    <li onclick="privacy('v1.1')">회원 개인정보 수집 및 이용동의 1.1 보기</li>
                </ul>
            </div>

			<div id="show_msg" style="display: none;"><?php echo !empty($langArr['login_agree_check_msg']) ? $langArr['login_agree_check_msg'] : 'Please agree to the terms and conditions.'; ?></div>
		
			<form method="post" name="form_auth">
				<input type="hidden" name="ordr_idxx"  class="frminput" value="" readonly="readonly" maxlength="40"/>

				<div id="show_pay_btn">
					<input type="submit" id="id_auth_btn" class="btn" onclick="return auth_type_check();" value="<?php echo !empty($langArr['login_agree_btn1']) ? $langArr['login_agree_btn1'] : 'Authentication'; ?>" /><!-- personal_identification / Cell Phone authentication -->
				</div>

				<input type="hidden" name="req_tx" value="cert" /><!-- 요청종류 -->
				<input type="hidden" name="cert_method" value="01" /><!-- 요청구분 -->
				<input type="hidden" name="web_siteid"   value="<?= $g_conf_web_siteid ?>" /><!-- 웹사이트아이디 : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
				<!-- <input type="hidden" name="fix_commid" value="KTF"/>--><!-- 노출 통신사 default 처리시 아래의 주석을 해제하고 사용하십시요 - SKT : SKT , KT : KTF , LGU+ : LGT-->
				<input type="hidden" name="site_cd" value="<?= $g_conf_site_cd ?>" /><!-- 사이트코드 : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
				<input type="hidden" name="Ret_URL" value="<?= $g_conf_Ret_URL ?>" /><!-- Ret_URL : ../cfg/cert_conf.php 파일에서 설정해주세요 -->
				<input type="hidden" name="cert_otp_use" value="Y" /><!-- cert_otp_use 필수 ( 메뉴얼 참고) - Y : 실명 확인 + OTP 점유 확인 , N : 실명 확인 only -->
				<input type="hidden" name="cert_enc_use" value="Y" /><!-- cert_enc_use 필수 (고정값 : 메뉴얼 참고) -->
				<input type="hidden" name="cert_enc_use_ext" value="Y" />      <!-- 리턴 암호화 고도화 -->
				<input type="hidden" name="res_cd" value="" />
				<input type="hidden" name="res_msg" value="" />
				<input type="hidden" name="veri_up_hash" value="" /><!-- up_hash 검증 을 위한 필드 -->
				<input type="hidden" name="cert_able_yn" value="Y" /><!-- 본인확인 input 비활성화 -->
				<input type="hidden" name="web_siteid_hashYN" value="Y" /><!-- web_siteid 을 위한 필드 -->
				<input type="hidden" name="param_opt_1"  value="register" /> <!-- 가맹점 사용 필드 (인증완료시 리턴)-->
				<input type="hidden" name="param_opt_2"  value="" />
				<input type="hidden" name="param_opt_3"  value="" />

			</form>

			<ul class="bottom">
				<li class="text1"><a  href="login.php" title="login" ><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a></li>
				<!--<li class="text2"><a href="forgetpassword.php" title="forgot password"><?php echo !empty($langArr['login_text1']) ? $langArr['login_text1'] : "Forgot Password ?"; ?></a></li>
				<li class="text3" id="version_txt"><?php echo $n_version; ?></li>-->
			</ul>


		</div>
	</div>
</div>

<iframe id="kcp_cert" name="kcp_cert" width="100%" height="700" frameborder="0" scrolling="yes" style="display: none;"></iframe>


<script type="text/javascript">
$(function() {
	init_orderid();
});

function privacy(version){
    $.ajax({
        cache: false,
        url: WALLET_URL + "/control.php/policy/privacy/view/" + version,
        type: 'GET',
        processData: true,
        contentType: 'application/json; charset=UTF-8',
        dataType: 'json',
        data: false,
        success: function (data, textStatus) {
            $("#agree_more2_contents").html(data.data.policyContents);
        },
        error: function (xhr, status) {
            console.log('error');
        }
    });
}

// MOBILE(SMART)
function auth_type_check() {

	if ( document.getElementById('agree_chk1').checked == false || document.getElementById('agree_chk2').checked == false)
	{
		$("#show_msg").css('display', 'block');
		return false;
	}
	$("#show_msg").css('display', 'none');


	var auth_form = document.form_auth;
	
	if (auth_form.ordr_idxx.value == '')
	{
		//alert( "요청번호는 필수 입니다." );
		return false;
	}
	
	else
	{
		
		if( navigator.userAgent.indexOf("Android") > - 1 || navigator.userAgent.indexOf("iPhone") > - 1 || navigator.userAgent.indexOf("android-web-view") > - 1 || navigator.userAgent.indexOf("ios-web-view") > - 1 )
		{
			auth_form.target = "kcp_cert";
			
			document.getElementById( "cert_info" ).style.display = "none";
			document.getElementById( "kcp_cert"  ).style.display = "";
		}
		else
		{
			var return_gubun;
			var width  = 410;
			var height = 500;

			var leftpos = screen.width  / 2 - ( width  / 2 );
			var toppos  = screen.height / 2 - ( height / 2 );

			var winopts  = "width=" + width   + ", height=" + height + ", toolbar=no,status=no,statusbar=no,menubar=no,scrollbars=no,resizable=no";
			var position = ",left=" + leftpos + ", top="    + toppos;
			var AUTH_POP = window.open('','auth_popup', winopts + position);
			
			auth_form.target = "auth_popup";
		}

		auth_form.action = "./auth.pro.req_r.php"; // 인증창 호출 및 결과값 리턴 페이지 주소
		
		return true;
	}
}

// 본인인증 : 요청번호 생성 예제 ( up_hash 생성시 필요 ) 
function init_orderid()
{
	var today = new Date();
	var year  = today.getFullYear();
	var month = today.getMonth()+ 1;
	var date  = today.getDate();
	var time  = today.getTime();

	if (parseInt(month) < 10)
	{
		month = "0" + month;
	}

	var vOrderID = year + "" + month + "" + date + "" + time;
	document.form_auth.ordr_idxx.value = vOrderID;
}


function login_agree_check(num){
    if ($("#agree_more"+num+"_contents").attr('class') == 'none'){
        $("#agree_more"+num+"_contents").removeClass('none');
        $("#agree_more"+num+"_more").addClass('none');
        $("#agree_more"+num+"_close").removeClass('none');
        $(".privacyVersionList").show(500);
    }
    else {
        $("#agree_more"+num+"_contents").addClass('none');
        $("#agree_more"+num+"_more").removeClass('none');
        $("#agree_more"+num+"_close").addClass('none');
        $(".privacyVersionList").hide();
    }
}

</script>

<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>