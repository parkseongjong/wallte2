<?php
//die("Registration close for public user");
session_start();
require_once './config/config.php';
//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}
include_once 'includes/header.php';
?>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<style>
/* Style all input fields */
body {
	background-color: #ffcd07 !important;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  margin-top: 6px;
  margin-bottom: 16px;
}

/* Style the submit button */
input[type=submit] {
  background-color: #4CAF50;
  color: white;
}

.form-group .form-control {
	flex-basis: 90%;
	color: #000;
	padding: 12px 12px;
	border: none;
	box-sizing: border-box;
	outline: none;
	letter-spacing: 1px;
	font-size: 17px;
	font-weight: 700;
	border-left: 1px solid #fff;
	border-bottom: 1px solid #fff;
	background: rgba(255, 255, 255, 0.81);
	height:auto;
}

/* Style the container for inputs */
.container {
  background-color: #f1f1f1;
  padding: 20px;
}

/* The message box is shown when the user clicks on the password field */
#message {
  display:none;
  background: #f1f1f1;
  color: #000;
  position: relative;
  padding: 20px;
  margin-top: 10px;
}

#message p {
  padding: 10px 35px;
  font-size: 18px;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
}

.row.login-bg {
	margin:0;
	height:100%;
	background: #ffcd07;
}

.login-panel {
	background-color: #fff;
	background-position: center;
	background-size: cover;
	padding: 0.5em 2em;
	margin: 0em auto;
	border: rgba(23, 19, 19, 0.95) !important;
	box-shadow: 0px 0px 5px 4px rgba(121, 121, 121, 0.36) !important;
}

.form-group .form-control {
	flex-basis: 90%;
	color: #000;
	padding: 12px 12px;
	border: none;
	box-sizing: border-box;
	outline: none;
	letter-spacing: 1px;
	font-size: 17px;
	font-weight: 700;
	border: 1px solid #bbb;
	background: #e1e1e1;
	height:auto;
}

.panel-body {
	padding:0 !important;
	text-align:center;
	color:#000;	
}
#page- {
	padding:0;
}

.btn-success.loginField {
	width: 100%;
	background: #ffcd07;
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 18px;
	font-weight: 400;
	border: 1px solid #ffcd07;
	padding: 11px 11px;
	letter-spacing: 1px;
	text-transform: uppercase;
	border-radius: 20px;
	cursor: pointer;
	transition: 0.5s all;
	-webkit-transition: 0.5s all;
	-o-transition: 0.5s all;
	-moz-transition: 0.5s all;
	-ms-transition: 0.5s all;	
}

.login-panel {
    margin-top: 30px;
	margin-bottom: 30px;
}

.tab-content {
	padding-top: 12px;
}

.nav.nav-tabs select {
	float: right;
	background: #ffcd07;
	border: none;
	text-align: center;
	padding: 11px;
	border-radius: 3px;
}

</style>
<div class="row login-bg">
<div class="col-md-4"></div>
<div id="page-" class="col-md-4">

	<form class="form loginform" method="POST" action="save_register.php" >
		<div class="login-panel panel panel-default">
        <div style="text-align: center;" class="logo"><img src="images/eth_logo.png" width='35%'/></div>
		<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
			<div class="panel-body">
			
				<ul class="nav  nav-tabs" role="tablist">
					<li role="presentation"  class="active" onclick="callPhoneClick()"><a href="#phonebox" aria-controls="sign-up" role="tab" data-toggle="tab"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></a></li>
					<li role="presentation" onclick="callEmailClick()"><a href="#emailbox" aria-controls="login" role="tab" data-toggle="tab"><?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?></a></li>
                    
              <select name="getlang" onChange="changeLanguage(this);">
                <option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">Korea</option>
                <option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">English</option>
              </select>
					
				</ul>
				<div class="tab-content">
					<div id="emailbox" class="form-group tab-pane fade">
						<label class="control-label"><?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?></label>
						<input type="email" name="email" id="emailfield" class="form-control">
					</div>
					<div id="phonebox" class="form-group tab-pane fade in active">
						<label class="control-label"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></label>
						<input type="text" id="phone" name="phone" class="form-control" >
					</div>
				</div>
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?></label>
					<input type="text" name="name" class="form-control" title="<?php echo $langArr['this_field_is_required']; ?>" required="required">
				</div>
				<div class="form-group" id="lastname_field">
					<label class="control-label"><?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?></label>
					<input type="text" name="lname" class="form-control" title="<?php echo $langArr['this_field_is_required']; ?>" required="required">
				</div>
				
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['password']) ? $langArr['password'] : "Password"; ?></label>
					<input type="password" id="psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="passwd" class="form-control" required="required" >
				</div>
				
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?></label>
					<input type="password" id="confirm_psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="cofirm_passwd" class="form-control" required="required">
				</div>
				<div id="message">
				  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
				 
				  <p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>
				</div>
				
				<div class="form-group" >
					<div>
					<input  type="text" autocomplete="false" placeholder="<?php echo !empty($langArr['verification_code']) ? $langArr['verification_code'] : 'Verification Code'; ?>"  required="required" name="verify_code" title="<?php echo $langArr['this_field_is_required']; ?>" class="form-control input2 reg-input" >
					<span class="send-button btn btn-info" id="get_code" style="padding: 13px 17px 13px 17px;cursor:pointer;margin: 7px 0px 20px 0px;"><?php echo !empty($langArr['get_code']) ? $langArr['get_code'] : "Get Code"; ?></span>
					</div>
					<div id="show_msg"></div>
					
				</div>
				
				<button type="submit" class="btn btn-success loginField" ><?php echo !empty($langArr['sign_up']) ? $langArr['sign_up'] : "Sign Up"; ?></button>
				<a  href="login.php" class="loginField"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a>
			</div>
		</div>
	</form>
</div>
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
  if(myInput.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
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
		  initialCountry: "auto",
		  preferredCountries : ['cn','jp','us','kr'],
		  geoIpLookup: function(callback) {
			$.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
			  var countryCode = (resp && resp.country) ? resp.country : "";
			  callback(countryCode);
			});
		  },
		  utilsScript: "flag/build/js/utils.js" // just for formatting/placeholders etc
		});
		$(".loginform").submit(function(){
			$("#loading-o").removeClass('none');
			var getpasslength = $("#psw").val();
			if(getpasslength.length<8){
				 $("#loading-o").addClass('none');
				 document.getElementById("message").style.display = "block";
				 return false;
			}
			var countryData = $("#phone").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			var getFirstChar = getPhoneVal.charAt(0);
			if(getPhoneVal!='') {
				if(countryData.dialCode==82 && getFirstChar==0){
					getPhoneVal = getPhoneVal.substr(1);
				}
				$("#phone").val("+"+countryData.dialCode+getPhoneVal);
			}
			 var getPassword = $("#psw").val();
			 var getConfirmPass = $("#confirm_psw").val();
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
					url : 'sendverifycode_1.php',
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