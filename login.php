<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">

<?php

// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}

include_once 'includes/header.php';

?>
<link rel="stylesheet" type="text/css" href="css/login.css">
<style>
#wallet_change_agree .modal-content {
	min-height: 100vh;
}
#wallet_change_agree .agrees {
	max-height: 60vh;
	overflow-y : scroll;
	padding: 5px;
}
</style>
<div id="login" class="login_input">
	<?php
	if(isset($_SESSION['success'])){ ?>
		<div class="alert alert-success alert-dismissable fade in">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<?php echo $_SESSION['success']; unset($_SESSION['success']);?>
		</div>
	<?php } ?>


	<div class="login_logo"><!--<img src="images/eth_logo.png" alt="logo" />--><img src="images/logo3.png" alt="logo" /></div>
	<form class="form loginform" method="POST" action="authenticate.php" name="form1">
		<input type="hidden" name="dev_id" id="dev_id" value="" />
		<input type="hidden" name="dev_id2" id="dev_id2" value="" />
		<input type="hidden" name="dev_id3" id="dev_id3" value="" />
		<input type="hidden" name="dev_use" id="dev_use" value="" />
		<input type="hidden" name="onesignal_id" id="onesignal_id" value="" />
		<input type="hidden" name="onesignal_id2" id="onesignal_id2" value="" />
		<input type="hidden" name="app_version" id="app_version" value="" />

		<div class="">
            <!--div class="tab-content" style="border:2px dashed #aaa; border-radius:7px; padding:16px 25px; margin-bottom:50px; background:#ffeeee;">
                <span style="background:black;color:orange;padding:3px 6px 2px 6px;margin-right:10px">공지</span> 현재 시스템 점검중입니다. 4~5시간 정도 소요될 예정입니다. [2020-0616 20:00]
            </div-->

			<ul class="login_nav_tabs">
				<li role="presentation" class="active" onclick="callPhoneClick();"><a href="#phonebox" aria-controls="sign-up" role="tab" data-toggle="tab"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></a></li>
				<li class="bar">|</li>
				<li role="presentation" onclick="callEmailClick()"><a href="#emailbox" aria-controls="login" role="tab" data-toggle="tab"><?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?></a></li>
				<li class="lang">
					 <select name="getlang" onChange="changeLanguage(this);">
						<option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">KOR</option>
						<option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">ENG</option>
					</select>
				</li>
			</ul>

			<div class="tab-content">
				<div id="emailbox" class="form-group tab-pane fade">
					<input type="email"  name="email" id="emailfield" class="input login_input_back_none" placeholder="<?php echo !empty($langArr['login_input_email']) ? $langArr['login_input_email'] : 'Please enter your email address.'; ?>" >
				</div>
				<div id="phonebox" class="form-group tab-pane fade in active">
					<input type="text" id="phone"  class="input <?php echo isset($_COOKIE['phone']) ? 'login_input_back_color' : 'login_input_back_none'; ?>" value="<?php echo isset($_COOKIE['phone']) ? $_COOKIE['phone'] : ''; ?>" name="phone" ><!--  <?php echo isset($_COOKIE['phone']) ? ' input_back_yel' : ''; ?> -->
				</div>
			</div>

			<div class="form-group">
				<input type="password" id="psw"  name="passwd" class="input <?php echo isset($_COOKIE['password']) ? 'login_input_back_color' : 'login_input_back_none'; ?>" value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>" title="<?php echo $langArr['this_field_is_required']; ?>" placeholder="<?php echo !empty($langArr['login_input_password']) ? $langArr['login_input_password'] : 'Please enter your password.'; ?>" required="required"><!--  <?php echo isset($_COOKIE['password']) ? ' input_back_yel' : ''; ?> -->
			</div>
			<?php
			if(isset($_SESSION['login_failure'])){ ?>
			<div class="alert alert-danger alert-dismissable fade in">
				<div><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?></div>
			</div>
			<?php } ?>
			<button type="submit" class="btn" ><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></button>
			<!--<div style="margin: 10px;"><a  href="register.php" class="loginField" ><?php echo !empty($langArr['register']) ? $langArr['register'] : "Register"; ?></a></div>-->

			<div class="checkbox">
				<label>
					<input name="remember" type="checkbox" value="1"><?php echo !empty($langArr['remember_me']) ? $langArr['remember_me'] : "Auto-Login"; ?>
				</label>
			</div>

			<ul class="bottom">
				<li class="text1">
                    <a  href="register_agree.php" title="register" ><?php echo !empty($langArr['register']) ? $langArr['register'] : "Register"; ?></a>&nbsp; | &nbsp;<a href="#" onClick="chatChannel();" title="customer center" target="_blank"><?php echo !empty($langArr['customer_center']) ? $langArr['customer_center'] : "Customer Center"; ?></a>
                </li>
				<li class="text2">
					<a href=""  class="" data-toggle="modal" data-target="#wallet_change_agree"><?php echo !empty($langArr['login_text2']) ? $langArr['login_text2'] : "Reset Password"; ?></a>
				</li>
				<li class="text3" id="version_txt"><?php echo $n_version; ?></li>
			</ul>
		</div>

	</form>
</div>

<div id="tttt">

</div>


<!-- Update Agree Modal-->
<div class="modal fade" id="wallet_change_agree" role="dialog">
	<div class="modal-dialog">
		<form action="forgetpassword.php" method="POST">
			<input type="hidden" name="wallet_change_agree" value="Y" />
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><?php echo !empty($langArr['change_address_agree_msg']) ? $langArr['change_address_agree_msg'] : "Do you agree to move your virtual assets to a new wallet address?"; ?></h4>
				</div>
				<div class="modal-body">
					<div class="agrees">
						<p>ctc월렛 자산도난 방지 시스템 사용자 지갑주소 변경 후 코인이동 이용약관</p>
						제 1조 [이용계약의 성립]<br />
						① ctc월렛 자산도난 방지를 위한 지갑주소변경과 tp3, mc , ctc , krw ,usdt , 이더리움 을 새로운 지갑주소에 이동함에 있어 약관에 대해 "동의" 단추를 선택하면 본 약관에 동의한 것으로 간주합니다.<br />
						② 자산도난 방지 시스템은 본 약관을 필요 시 수시로 변경할 수 있고 회원은 언제든지 약관의 변경내용을 수시로 확인하여야 합니다. 회원이 변경된 약관에 동의하지 않을 경우, 서비스 이용을 중단하고 탈퇴할 수 있습니다. 약관이 변경된 이후에도 계속적으로 서비스를 이용하는 경우에는 회원이 약관의 변경 사항에 동의한 것으로 간주합니다.<br />
						③ 자산도난 방지 시스템 서비스를 이용하기 위해서는 자산도난 방지 시스템에서 정한 소정의 가입신청 양식에서 요구하는 모든 회원정보를 기록하여 신청해야 합니다.<br />
						④ 가입신청 양식에 쓰는 모든 회원 정보는 모두 실제 데이터인 것으로 간주됩니다. 실명이나 실제 정보를 입력하지 않은 사용자는 법적인 보호를 받을 수 없으며, 서비스의 제한, 해지 등의 불이익을 받을 수 있습니다.<br />
						⑤ 이용계약은 서비스 이용희망자의 이용약관 동의 후 이루어지는 이용신청에 대하여 자산도난 방지 시스템이 최종 승낙함으로써 성립합니다.<br />
						<br />
						제 2 조 [회원가입의 승낙유보 및 제한 사유]<br />
						① ctc월렛 자산도난 방지 시스템은 회원 가입을 위한 이용계약신청에 대하여 다음에 해당하는 경우에는 그 사유가 해소될 때까지 승낙을 유보할 수 있습니다.<br />
						가. 서비스 관련 설비에 여유가 없는 경우<br />
						나. 기술상 장애가 있는 경우<br />
						다. 기타 해킹방지 시스템서비스가 필요하다고 인정되는 경우<br />
						② 자산도난 방지 시스템은 다음에 해당하는 경우, 가입을 승낙하지 않을 수 있습니다.<br />
						가. 다른 사람의 명의 또는 가명을 사용하여 신청하였을 경우<br />
						나. 본인의 주민등록번호가 아닌 경우나 이용신청 시 필요내용을 허위로 기재하여 신청한 경우<br />
						다. 공공질서(사재기, 카드깡) 또는 자산의 가치상승을 저해할 목적으로 신청한 경우<br />
						라. 제3조 제2항에 의하여 이전에 회원자격을 상실한 적이 있는 경우<br />
						마. 기타 자산도난 방지 시스템이 정한 이용신청 요건에 맞지 않을 경우<br />
						<br />
						제 8 조 [회원의 탈퇴 및 자격 상실]<br />
						① 회원은 ctc월렛 자산도난 방지 시스템에 언제든지 자유로운 의사에 의하여 회원 본인이 회원 탈퇴를 요청할 수 있으며, 자산도난 방지 시스템은 위 요청을 받은 즉시 해당 회원의 회원 등록 말소를 위한 절차를 밟습니다.<br />
						② 회원이 다음 각 호의 사유에 해당하는 경우, 자산도난 방지 시스템은 회원의 회원자격을 적절한 방법으로 제한 및 정지, 상실시킬 수 있습니다.<br />
						1. 가입신청 시에 허위 내용을 등록한 경우<br />
						2. 다른 사람의 자산도난 방지 시스템 서비스 이용을 방해하거나 그 정보를 도용하는 등 전자거래질서를 위협하는 경우<br />
						3. 자산도난 방지 시스템 서비스를 이용하여 법령과 본 약관이 금지하거나 공공의 질서와 선량한 풍속에 반하는 행위를 하는 경우<br />
						4. 기타 회원의 귀책사유가 있는 경우에 자산도난 방지 시스템이 회원의 회원자격을 상실시키기로 결정한 경우
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-default pull-left"><?php echo !empty($langArr['confirm_btn_yes']) ? $langArr['confirm_btn_yes'] : "Yes"; ?></button>
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo !empty($langArr['confirm_btn_no']) ? $langArr['confirm_btn_no'] : "No"; ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
		
<div class="modal fade" id="login_modal_box" role="dialog">
	<div class="modal-dialog">
		<form action="" method="POST" name="modal_frm" id="modal_frm">
			<input type="hidden" name="modal_btn" id="modal_btn" value="" />

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-body">
					<p id="confirm_message"><?php echo !empty($langArr['login_device_id_message5']) ? $langArr['login_device_id_message5'] : "The system has been updated."; ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" id="submitModalbtn" class="btn_left"><?php echo !empty($langArr['confirm_btn_yes']) ? $langArr['confirm_btn_yes'] : "Yes"; ?> </button>
				</div>
			</div>
		</form>
	</div>
</div>

<style>
/* send : modal box (confirm) */
#login_modal_box .modal-dialog {
	top: 150px;
}
</style>
<script>

/* $(window).load(function() {
		
document.getElementById("refer_code").value = localStorage.getItem("ref_code");
	}); */
    $(function () {

        <?php if($_SERVER['REMOTE_ADDR'] == '112.171.120.140' || $_SERVER['REMOTE_ADDR'] == '112.171.120.162' || $_SERVER['REMOTE_ADDR'] == '3.37.251.249' || $_SERVER['REMOTE_ADDR'] == '2001:e60:316d:f9d7:a4e5:4426:19a9:f29') : ?>
        setTimeout(function() {
                $('#tttt').append('dev_id:'+$('#dev_id').val());
                $('#tttt').append('dev_id2:'+$('#dev_id2').val());
                $('#tttt').append('dev_id3:'+$('#dev_id3').val());
                $('#tttt').append('dev_use:'+$('#dev_use').val());
                $('#tttt').append('onesignal_id:'+$('#onesignal_id').val());
                $('#tttt').append('onesignal_id2:'+$('#onesignal_id2').val());
                $('#tttt').append('app_version:'+$('#app_version').val());
        }, 10000);
        <?php endif;  ?>

        console.log(navigator.userAgent.indexOf("ios-web-view"));
        if(navigator.userAgent.indexOf("ios-web-view") > - 1) {
            setTimeout(function () {
                $('#version_txt').html('ver ' + $('#app_version').val());
            }, 5000);
        }

		$(".input").on('propertychange change keyup paste input', function() {
			if ( $(this).val() == '') {
				$(this).removeClass('login_input_back_color').addClass('login_input_back_none');
			} else {
				$(this).removeClass('login_input_back_none').addClass('login_input_back_color');
			}
		});
      /*   $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        }); */
		$("#phone").intlTelInput({
		  initialCountry: "kr",
		  preferredCountries : ['cn','jp','us','kr'],
		  geoIpLookup: function(callback) {
			$.get('https://ipinfo.io/json?token=6ad007f53defcc', function() {}, "jsonp").always(function(resp) { // 6ad007f53defcc
			  var countryCode = (resp && resp.country) ? resp.country : "";
			  callback(countryCode);
			});
		  },
		  utilsScript: "flag/build/js/utils.js" // just for formatting/placeholders etc
		});
		$(".loginform").submit(function(){
			//$("#loading-o").removeClass('none');
			var countryData = $("#phone").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			var getFirstChar = getPhoneVal.charAt(0);
			if(getPhoneVal!='') {
				if(countryData.dialCode==82 && getFirstChar==0){
					getPhoneVal = getPhoneVal.substr(1);
				}
				$("#phone").val("+"+countryData.dialCode+getPhoneVal);
			}
			
			// add device id : 20.09.08
			if ( ( $("#dev_id").val() != '' || $("#dev_id2").val() != '' || $("#dev_id3").val() != '') && (navigator.userAgent.indexOf("android-web-view") > - 1 || navigator.userAgent.indexOf("ios-web-view") > - 1) ){
				get_device_id();
				if ( $("#login_modal_box #modal_frm #modal_btn").val() == 'S' ) {
					//if ( $("#dev_use").val() == 'N' ) {
					//	$("#login_modal_box #confirm_message").html("<?php echo !empty($langArr['login_device_id_message3']) ? $langArr['login_device_id_message3'] : 'You can log in after registering the device.'; ?>");
					//	$("#login_mldal_box").modal('show');
					//}
					return false;
				} else {
					$("#loading-o").removeClass('none');
				}
			}
			else {
				$("#loading-o").removeClass('none');
			}

		});

		$("#submitModalbtn").on('click', function() {
			if ( $("#modal_btn").val() != 'Y' ) {
				$("#dev_use").val('Y');
				$("#login_modal_box #modal_frm #modal_btn").val('Y');
				document.form1.submit();
			}
		});

		
    });
	
function callEmailClick(){
	$("#phone").val('');
}
function callPhoneClick(){
	$("#emailfield").val('');
}

function get_device_id() {
	var dev_id = $("#dev_id").val();
	var dev_id2 = $("#dev_id2").val();
	var dev_id3 = $("#dev_id3").val();
	if ( $(".login_nav_tabs li:nth-of-type(1)").attr('class') == 'active' ) {
		var email = $("#phone").val();
		email = email.replace(/-/g, '');
		email = email.replace(/ /g, '');
	} else {
		var email = $("#emailfield").val();
	}
	var pw = $("#psw").val();
	
	$.ajax({
		url : 'multi.pro.php',
		type : 'POST',
		data : {mode: 'login_set_dev_id2', email : email, dev_id : dev_id, pw: pw, dev_id2 : dev_id2, dev_id3 : dev_id3},
		dataType : 'json',
		async: false,
		success : function(resp){
			if ( resp.result == 'y' ) {
				$("#login_modal_box").modal('show');
				$("#login_modal_box #modal_frm #modal_btn").val('S');
				$("#login_modal_box #confirm_message").html(resp.subject);
			}
		},
		error : function(resp){
		}
	});
}
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>