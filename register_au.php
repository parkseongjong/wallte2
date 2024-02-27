<?php
// Page in use
//die("Registration close for public user");
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}
?>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<link  rel="stylesheet" href="css/login.css?v=1.0"/>

<?php
$t_id = '';
$auth_phone_no = '';
$auth_name = '';
$auth_dob = '';
$auth_gender = '';
$auth_local_code = '';
$id_auth = 'N';
$id_auth_at = '';
$auth_gender_local = '';


// Check country code by IP
function kisa_ip_chk(){

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


function ipinfo_ip_chk($key) { // 수량 체크 테스트용. whois 대신 사용 가능한지 check (2020.05.14, YMJ)
	// https://ipinfo.io/
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

$ip_kor = '';
$ip_kor = trim(ipinfo_ip_chk('2'));
if ($ip_kor == '') {
	$ip_kor = kisa_ip_chk();
}

if ( empty($_GET['tid']) ) {
	header('Location:login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
	if ( !empty($_GET['tid']) ) {
		$t_id = $_GET['tid'];
		
		$db = getDbInstance();
		$db->where("id", $t_id);
		$row_t = $db->get('temp_accounts');
		
		$user_ip = new_getUserIpAddr();

		if ( empty($row_t[0]['id']) ) {
			fn_logSave("Personal Identification Error1 : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
			header('Location:login.php');
			exit();
		}

		if ( $ip_kor != 'KR') {
			fn_logSave("Personal Identification Error2 : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
			header('Location:login.php');
			exit();
		} else if ($row_t[0]['user_ip'] != $user_ip ) { // 인증한 아이피와 현재 아이피 다르면 -> 다시 인증해라
			$db->where('id', $t_id);
			$stat = $db->delete('temp_accounts');
			fn_logSave("Personal Identification Error3 : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
			header('Location:login.php');
			exit();
		} else {
			$id_auth = 'Y';
			$auth_phone_no = $row_t[0]['phone'];
			$auth_name = $row_t[0]['name'];
			$auth_dob = $row_t[0]['dob'];
			$auth_gender = $row_t[0]['gender'];
			$auth_local_code = $row_t[0]['local_code'];
			$id_auth_at = $row_t[0]['id_auth_at'];
			$tmp1 = $auth_local_code == 'Kor' ? $langArr['korean'] : $langArr['foreigner'];
			$tmp2 = $auth_gender == 'male' ? $langArr['male'] : $langArr['female'];
			$auth_gender_local = $tmp1.' / '.$tmp2;
			
		}
	}
}

// IP 자동판별 값이 KR(국내)인 사용자 중 본인인증 미진행한 경우(강제로 이 페이지로 접근한 경우) 로그인 페이지로이동
if ($ip_kor == 'KR' && $id_auth != 'Y') {
	fn_logSave( "Personal Identification Error : It's a wrong approach , File : " . $_SERVER['SCRIPT_FILENAME'] );
	header('Location:login.php');
	exit();
}

include_once 'includes/header.php';
?>


<div id="register_au" class="login_input">
	<form class="form loginform" method="POST" action="save_register_au.php" >
		<input type="hidden" name="id_auth" id="id_auth" value="<?php echo $id_auth; ?>" />
		<input type="hidden" name="tid" value="<?php echo $t_id; ?>" />
		<input type="hidden" name="phone" id="phone" value="<?php echo $auth_phone_no; ?>" />				
		<input type="hidden" name="phone_code" id="phone_code" value="" />
        <!-- 이메일 인증 잠시 주석 -->
		<!--<input type="hidden" name="email_code" id="email_code" value="" />-->
        <!-- 21.06.04, YMJ,  이메일인증추가 -->
		
		<div class="login_logo"><img src="images/eth_logo.png" alt="logo" /></div>
		<div><!-- class="login-panel" -->
		<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
			<div id="register_au_1"><!-- class="panel-body" -->
										
				<p class="text1"><?php echo $langArr['register_text1']; ?></p>
				<p class="text2">
					<?php echo $langArr['register_text2']; ?><br /><?php echo $langArr['register_text3']; ?>
				</p>
				<p class="text3"><?php echo $langArr['register_text4']; ?></p>

				<div id="phone_t" class="input_p"><div class="auth_input"><?php echo $auth_phone_no; ?></div></div>
				<div class="input_n"><div class="auth_input"><?php echo $auth_name; ?></div></div>
				<div class="input_d"><div class="auth_input"><?php echo $auth_dob; ?></div></div>
				<div class="input_g"><div class="auth_input"><?php echo $auth_gender_local; ?></div></div>
				
				<!-- 21.06.04, YMJ,  이메일인증추가 -->
				<div class="form-group">
					<div class="vcode_area">
						<input type="text" id="email"  name="new_email" class="input_vcode" required="required" placeholder="<?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?>">
						<!--<span class="button_vcode" id="get_code"><?php /*echo !empty($langArr['get_code']) ? $langArr['get_code'] : "Get Code"; */?></span>-->
					</div>
				</div>
                <!--
				<div class="form-group" >
					<div class="vcode_area">
						<input  type="text" autocomplete="false" placeholder="<?php /*echo !empty($langArr['verification_code']) ? $langArr['verification_code'] : 'Verification Code'; */?>"  required="required" name="verify_code" id="verify_code" title="<?php /*echo $langArr['this_field_is_required']; */?>" class="input_vcode" >
						<span class="button_vcode" id="confirm_code"><?php /*echo !empty($langArr['emailCollectionString10']) ? $langArr['emailCollectionString10'] : "Publishing"; */?></span>
					</div>
					<div id="show_msg"></div>
				</div>-->

				<div class="form-group">
					<input type="password" id="psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="passwd" class="input" required="required" placeholder="<?php echo !empty($langArr['password']) ? $langArr['password'] : "Password"; ?>" >
				</div>
				
				<div class="form-group">
					<input type="password" id="confirm_psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="cofirm_passwd" class="input" required="required" placeholder="<?php echo !empty($langArr['conf_password']) ? $langArr['conf_password'] : "Confirm Password"; ?>">
				</div>
				<div id="message">
				  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
				 
				  <!--<p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>-->
				  <p id="length" class="invalid"><?php echo !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits)"; ?></p>
				</div>
				<div id="show_msg"></div>
								
				<button type="submit" class="btn" ><?php echo !empty($langArr['sign_up']) ? $langArr['sign_up'] : "Sign Up"; ?></button>

				
				<ul class="bottom">
					<li class="text1"><a href="login.php" title="login"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a></li>
					<li class="text3"><?php echo $n_version; ?></li>
				</ul>

			</div>
		</div>
	</form>
</div>



<?php
function fn_logSave($log){ //로그내용 인자
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
?>
			
<script>
var myInput = document.getElementById("psw");
var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");

// When the user clicks on the password field, show the message box
myInput.onfocus = function() {
  document.getElementById("message").style.display = "block";
}

// When the user clicks outside of the password field, hide the message box
myInput.onblur = function() {
  document.getElementById("message").style.display = "none";
}

// When the user starts to type something inside the password field
myInput.onkeyup = function() {
  // Validate lowercase letters
 /*  var lowerCaseLetters = /[a-z]/g;
  if(myInput.value.match(lowerCaseLetters)) {  
    letter.classList.remove("invalid");
    letter.classList.add("valid");
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }
  
  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {  
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {  
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  } */
  
  // Validate length
  //if(myInput.value.length >= 6) {
  if(passwordValidCheck(myInput.value)){
      length.classList.remove("invalid");
      length.classList.add("valid");
  }
  else {
      length.classList.remove("valid");
      length.classList.add("invalid");
  }
}
</script>
<script>
/* $(window).load(function() {
		
document.getElementById("refer_code").value = localStorage.getItem("ref_code");
	}); */
    $(function () {
      /*   $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        }); */
		// initialCountry: "auto", -> kr
		$("#phone_t").intlTelInput({
		  initialCountry: "kr",
		  preferredCountries : ['cn','jp','us','kr'],
		  geoIpLookup: function(callback) {
			$.get('https://ipinfo.io/json?token=6ad007f53defcc', function() {}, "jsonp").always(function(resp) {
			  var countryCode = (resp && resp.country) ? resp.country : "";
			  callback(countryCode);
			});
		  },
		  utilsScript: "flag/build/js/utils.js" // just for formatting/placeholders etc
		});
		$(".loginform").submit(function(){
			$("#loading-o").removeClass('none');
			
			// 21.06.04, YMJ,  이메일인증추가
/*			if($("#email_code").val() != '1' ) { // 인증미진행
				 $("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['emailAuthCode_no']) ? $langArr['emailAuthCode_no'] : "Please verify your email.";  ?></div>').show();
				 $("#loading-o").addClass('none');
				 return false;
			}*/

			var getpasslength = $("#psw").val();
			if(getpasslength.length<6){
				 $("#loading-o").addClass('none');
				 document.getElementById("message").style.display = "block";
				 return false;
			}
			var countryData = $("#phone_t").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			if(getPhoneVal!='') {
				$("#phone_code").val(countryData.dialCode);
			}
			 var getPassword = $("#psw").val();
			 var getConfirmPass = $("#confirm_psw").val();

            if(!passwordValidCheck(getPassword)){
                $("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['password_and_confirm_password_should_be_match']) ? $langArr['password_and_confirm_password_should_be_match'] : "Password and Confirm Password should be match";  ?></div>').show();
                setTimeout(function(){ $("#show_msg").hide(); }, 10000);
                $("#loading-o").addClass('none');
                return false;
            }

			 if(getConfirmPass != getPassword){
				 $("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['password_and_confirm_password_should_be_match']) ? $langArr['password_and_confirm_password_should_be_match'] : "Password and Confirm Password should be match";  ?></div>').show();
				 setTimeout(function(){ $("#show_msg").hide(); }, 10000);
				 $("#loading-o").addClass('none');
				 return false;
			 }
			
		});

		
		// 21.06.04, YMJ,  이메일인증추가
		var clicked = 0;
		/*$("#get_code").click(function(){
			if(clicked==1){
				return false;
			}
			
			var sourceValue = '';
			//var sourceValue2 = '';
			var sourceType = '';

			sourceValue = $("#email").val();
			sourceType = "email";
			$("#email_code").val('0');

			if(sourceValue!=='' && sourceType!=='' && sourceValue!==undefined && sourceType!==undefined) {
				//clicked  = 1;
				$("#get_code").css('cursor','unset').css('background-color','#b2b4b5').css('border-color','#b2b4b5');
				
				$.ajax({
					beforeSend:function(){
						//$("#show_msg").html('<img src="images/ajax-loader.gif" />');
						$("#loading-o").removeClass('none');
					},
					url : 'multiemail.php',
					type : 'POST',
					data:{mode:'send_email_code',source_value:sourceValue},
					dataType : 'json',
					success : function(resp){
						var tmp = resp.result;
						if (tmp == 'success' ) {
							//$("#show_msg").html('<div class="alert alert-success">Verification code send to your '+sourceType+'.</div>').show();
							$("#show_msg").html('<div class="alert alert-success"><?php echo !empty($langArr['emailCollectionJsString02']) ? $langArr['emailCollectionJsString02'] : "Sent you the authentication number by e-mail";  ?></div>').show();
							$("#loading-o").addClass('none');
							setTimeout(function(){ $("#show_msg").hide(); }, 10000);
							$("#email").attr('readonly', 'true');
							clicked  = 1;
						}else if(tmp == 'duple'){
							$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['emailCollectionApiStringDanger06']) ? $langArr['emailCollectionApiStringDanger06'] : "This account has already been registered for email.";  ?></div>').show();
							$("#loading-o").addClass('none');
							setTimeout(function(){ $("#show_msg").hide(); }, 10000);
						} else {
							$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['emailAuthCode_send_failed']) ? $langArr['emailAuthCode_send_failed'] : "The mail could not be sent. please try again..";  ?></div>').show();
							$("#loading-o").addClass('none');
							setTimeout(function(){ $("#show_msg").hide(); }, 10000);
						}
					},
					error : function(resp){
						$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['emailAuthCode_send_failed']) ? $langArr['emailAuthCode_send_failed'] : "The mail could not be sent. please try again..";  ?></div>').show();
						$("#loading-o").addClass('none');
						setTimeout(function(){ $("#show_msg").hide(); }, 10000);
					}
				}) 
			}
			else {
				$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['login_input_email']) ? $langArr['login_input_email'] : "Please enter your email address.";  ?></div>').show();
				
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			}
	 });*/
	
	// 21.06.04, YMJ,  이메일인증추가
	/*$("#confirm_code").click(function(){
		var inputcode = $("#verify_code").val();
		
		if ( !inputcode ) {
			$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['emailCollectionJsString03']) ? $langArr['emailCollectionJsString03'] : "Please, Auth number enter";  ?></div>').show();
			return false;
		}
		$("#email_code").val('0');

		$.ajax({
			url: 'multiemail.php',
			type: 'POST',
			data: {mode:'check_email_code', inputcode:inputcode},
			dataType: 'json',
			success: function(resp){
				var tmp = resp.result;
				if (tmp == 'success' ) {
					$("#email_code").val('1');
					$("#show_msg").html('<div class="alert alert-success"><?php echo !empty($langArr['emailAuthCode_success']) ? $langArr['emailAuthCode_success'] : "Authentication is complete.";  ?></div>').show();
					return false;
				} else{
					$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['emailCollectionApiStringDanger09']) ? $langArr['emailCollectionApiStringDanger09'] : "The authentication number does not match.";  ?></div>').show();
					return false;
				}
			},
			error: function(resp){
			}
		});
		

	 });*/

    });
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>