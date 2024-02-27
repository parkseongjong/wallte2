<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<style>
body {
	background-color: #ffcd07 !important;
}

.row.login-bg {
	margin:0;
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
<?php

session_start();
require_once './config/config.php';
//If User has already logged in, redirect to dashboard page.
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
    header('Location:index.php');
}

include_once 'includes/header.php'; 
?>
<div class="row login-bg">
<div class="col-md-4"></div>

<div id="page-" class="col-md-4">
	<form class="form loginform" method="POST" action="test_authenticate.php">
    <div class="login-panel panel panel-default">
        <div style="text-align: center;" class="logo"><img src="images/logo3.png" width='35%'/></div>
        
			<?php
				if(isset($_SESSION['success'])){ ?>
				<div class="alert alert-success alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['success']; unset($_SESSION['success']);?>
				</div>
				<?php } ?>
				
			<div class="panel-body">
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation"  class="active"  onclick="callPhoneClick()"><a href="#phonebox" aria-controls="sign-up" role="tab" data-toggle="tab"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></a></li>
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
					<label class="control-label"><?php echo !empty($langArr['password']) ? $langArr['password'] : "Password"; ?></label>
					<input type="password" id="psw"  name="passwd" class="form-control" required="required">
				</div>
				
				<div class="checkbox">
					<label>
						<input name="remember" type="checkbox" value="1"><?php echo !empty($langArr['remember_me']) ? $langArr['remember_me'] : "Remember Me"; ?>
					</label>
				</div>
				<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<div><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?></div>
				</div>
				<?php } ?>
				<button type="submit" class="btn btn-success loginField" ><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></button>
				<div style="margin: 10px;"><a  href="register.php" class="loginField" ><?php echo !empty($langArr['register']) ? $langArr['register'] : "Register"; ?></a></div>
			    <div><a href="forgetpassword.php"><?php echo !empty($langArr['forgot_password']) ? $langArr['forgot_password'] : "Forgot Password ?"; ?></a></div>
			</div>
		</div>
	</form>
</div></div>
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
			var countryData = $("#phone").intlTelInput("getSelectedCountryData");
			var getPhoneVal = $("#phone").val();
			var getFirstChar = getPhoneVal.charAt(0);
			if(getPhoneVal!='') {
				if(countryData.dialCode==82 && getFirstChar==0){
					getPhoneVal = getPhoneVal.substr(1);
				}
				$("#phone").val("+"+countryData.dialCode+getPhoneVal);
			}
		});
		
    });
	
	function callEmailClick(){
	$("#phone").val('');
}
function callPhoneClick(){
	$("#emailfield").val('');
}
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>