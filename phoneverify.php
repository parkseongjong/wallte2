<?php
//die("Registration close for public user");
session_start();
require_once './config/config.php';

include_once 'includes/header.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$getVode = $_POST['vcode'];
	if(empty($getVode)){
		$_SESSION['login_failure'] = $langArr['enter_verification_code'];
		header('Location:phoneverify.php');
		exit;
	}
	
	$db = getDbInstance();
	
    $db->where ("vcode", $getVode);
    $row = $db->get('admin_accounts');
	if($db->count == 0) {
		$_SESSION['login_failure'] = $langArr['invalid_verification_code'];
		header('Location:phoneverify.php');
		exit;		
	}
	
	$emailVerify = $row[0]['email_verify'];
	$email = $row[0]['email'];
	if($emailVerify=='Y') {
		$_SESSION['success'] = $langArr['phone_v_already'];
		header('location: login.php');
    	exit();
	}
	
	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$personal = $web3->personal;
	$newAccount="";
	$personal->newAccount($email.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM', function ($err, $account) use (&$newAccount) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			
		}
		else {
			$newAccount = $account;
		}
	});
	
	
	$db = getDbInstance();
	$db->where("vcode", $getVode);
	$last_id = $db->update('admin_accounts', ['email_verify'=>"Y",'wallet_address'=>$newAccount]);	
		
	$_SESSION['success'] = $langArr['phone_v_success'];
	header('location: login.php');
	exit();
	
}
?>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<style>
/* Style all input fields */
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

body {
    background-color: #ffcd07;
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
    margin-top: 30%;
}

</style>
<div class="row login-bg">
<div class="col-md-4"></div>
<div id="page-" class="col-md-4">

	<form class="form loginform" method="POST" >
		<div class="login-panel panel panel-default">
        <div style="text-align: center;" class="logo"><img src="images/logo3.png" width='35%'/></div>
			<div class="panel-body">
				
				
				<div class="form-group">
					<label class="control-label"><?php echo !empty($langArr['enter_verification_code']) ? $langArr['enter_verification_code'] : "Enter Verification Code"; ?></label>
					<input type="text" name="vcode" class="form-control" required="required">
				</div>
				
				
				<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
				<button type="submit" class="btn btn-success loginField" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?></button>
				<a  href="login.php" class="loginField"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?> </a>
			</div>
		</div>
	</form>
</div>
</div>
	
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>