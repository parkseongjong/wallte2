<?php
// Page in use
//die("Registration close for public user");
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

//$_SESSION['login_failure'] = !empty($langArr['notice_construction']) ? $langArr['notice_construction'] : 'Checking server. Access is not possible from 2020-07-20 11:30 to 2020-07-23 00:00. Please understand that this is to provide better service. Thank you.';
//header('Location:login.php'); // -------------------
//exit(); // -------------------


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


//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}
include_once 'includes/header.php';
?>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<link  rel="stylesheet" href="css/login.css"/>

<div id="register" class="login_input">

	<div class="login_logo"><img src="images/eth_logo.png" alt="logo" /></div>

	<form class="form loginform" method="POST" action="save_register.php" >
		<input type="hidden" name="n_country" id="n_country" />
		<input type="hidden" name="n_phone" id="n_phone" />
		<div class="login-panel">
		<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
			<div><!-- class="panel-body"-->

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
						<input type="email" name="email" id="emailfield" class="input" placeholder="<?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?>">
					</div>
					<div id="phonebox" class="form-group tab-pane fade in active">
						<input type="text" id="phone" name="phone" class="input" >
					</div>
				</div>
				<div class="form-group">
					<input type="text" name="name" class="input" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" placeholder="<?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?>">
				</div>
				<div class="form-group" id="lastname_field">
					<input type="text" name="lname" class="input" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" placeholder="<?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?>">
				</div>
				
				<div class="form-group">
					<input type="password" id="psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="passwd" class="input" required="required" placeholder="<?php echo !empty($langArr['password']) ? $langArr['password'] : "Password"; ?>">
				</div>
				
				<div class="form-group">
					<input type="password" id="confirm_psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="cofirm_passwd" class="input" required="required" placeholder="<?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?>">
				</div>
				<div id="message">
				  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
				 
				  <!--<p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>-->
				  <p id="length" class="invalid"><?php echo !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Minimum 8 characters"; ?></p>
				</div>
				
				<div class="form-group" >
					<div class="vcode_area">
						<input  type="text" autocomplete="false" placeholder="<?php echo !empty($langArr['verification_code']) ? $langArr['verification_code'] : 'Verification Code'; ?>"  required="required" name="verify_code" title="<?php echo $langArr['this_field_is_required']; ?>" class="input_vcode" >
						<span class="button_vcode" id="get_code"><?php echo !empty($langArr['get_code']) ? $langArr['get_code'] : "Get Code"; ?></span>
					</div>
					<div id="show_msg"></div>
					
				</div>
				
				<button type="submit" class="btn" ><?php echo !empty($langArr['sign_up']) ? $langArr['sign_up'] : "Sign Up"; ?></button>

				<ul class="bottom">
					<li class="text1"><a href="login.php" title="login"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a></li>
					<!--<li class="text3"><?php echo $n_version; ?></li>-->
				</ul>

			</div>
		</div>
	</form>

</div>

				
<script>

function callEmailClick(){
	$("#phone").val('');
}
function callPhoneClick(){
	$("#emailfield").val('');
	
}
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
			$("#loading-o").removeClass('none');
			var getpasslength = $("#psw").val();
			if(getpasslength.length<6){
				 $("#loading-o").addClass('none');
				 document.getElementById("message").style.display = "block";
				 return false;
			}
			var countryData = $("#phone").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			var getFirstChar = getPhoneVal.charAt(0);
			$("#n_phone").val($("#phone").val());
			if(getPhoneVal!='') {
				if(countryData.dialCode==82 && getFirstChar==0){
					getPhoneVal = getPhoneVal.substr(1);
				}
				$("#phone").val("+"+countryData.dialCode+getPhoneVal);
				$("#n_country").val(countryData.dialCode);
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
		
		var clicked = 0;
		$("#get_code").click(function(){
			if(clicked==1){
				return false;
			}
			
			var sourceValue = '';
			var sourceValue2 = '';
			var sourceCountry = '';
			var sourceType = '';
			var getPhoneVal = $("#phone").val();
			var getPhoneVal2 = $("#phone").val();
			
			if(getPhoneVal!==''){
				
			var countryData = $("#phone").intlTelInput("getSelectedCountryData");
			var getFirstChar = getPhoneVal.charAt(0);
				if(countryData.dialCode==82 && getFirstChar==0){
					getPhoneVal = getPhoneVal.substr(1);
				}
				
				sourceCountry = countryData.dialCode;
				sourceValue2 = getPhoneVal2;

				sourceValue = countryData.dialCode+getPhoneVal;
				sourceType = "phone";
			}
			else {
				sourceValue = $("#emailfield").val();
				sourceType = "email";
			}
			
			//alert(sourceValue);
			//return false;
			if(sourceValue!=='' && sourceType!=='' && sourceValue!==undefined && sourceType!==undefined) {
				clicked  = 1;
				$("#get_code").css('cursor','unset').css('background-color','#b2b4b5').css('border-color','#b2b4b5');
				
				$.ajax({
					beforeSend:function(){
						//$("#show_msg").html('<img src="images/ajax-loader.gif" />');
						$("#loading-o").removeClass('none');
					},
					url : 'sendverifycode.php',
					type : 'POST',
					data:{source_country:sourceCountry,source_value2:sourceValue2,source_value:sourceValue,source_type:sourceType},
					dataType : 'json',
					success : function(resp){
						$("#show_msg").html('<div class="alert alert-success">Verification code send to your '+sourceType+'.</div>').show();
						$("#loading-o").addClass('none');
						setTimeout(function(){ $("#show_msg").hide(); }, 10000);
						
					},
					error : function(resp){
						$("#show_msg").html('<div class="alert alert-success">Verification code send to your '+sourceType+'.</div>').show();
						$("#loading-o").addClass('none');
						setTimeout(function(){ $("#show_msg").hide(); }, 10000);
					}
				}) 
			}
			else {
				$("#show_msg").html('<div class="alert alert-danger"><?php echo !empty($langArr['plz_fill_eth_em_ph']) ? $langArr['plz_fill_eth_em_ph'] : "Please Fill Either Email Or Phone";  ?></div>').show();
				
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			}
	 });
		
    });
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>