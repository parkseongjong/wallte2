<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

require(BASE_PATH . '/vendor/autoload.php');



/*
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $util = walletUtil::singletonMethod();
    $walletDb = walletDb::singletonMethod();
    $walletDb = $walletDb->init();

	$data_to_store = filter_input_array(INPUT_POST);
	$getVode = $_POST['vcode'];
	if(empty($getVode)){
		$_SESSION['login_failure'] = $langArr['invalid_verification_code'];
		header('Location:resetpass.php');
		exit;
	}
	
	$db = getDbInstance();
	
    $db->where ("vcode", $getVode);
    $row = $db->get('admin_accounts');

	if($db->count == 0) {
		$_SESSION['login_failure'] = $langArr['invalid_verification_code'];
		header('Location:login.php');
		exit;		
	}
	
	$pass = $data_to_store['password'];
	$confirmPass = $data_to_store['confirm_password'];
	
	if($pass!=$confirmPass){
		$_SESSION['login_failure'] = $langArr['pass_conf_pass_match'];
		header('location: resetpassword.php?vcode='.$getVode);
		exit;
	}

    $regex = "/((?=.*[a-z])(?=.*[0-9])(?=.*[$@$!%*#?&])(?=.*[^a-zA-Z0-9]).{8,})/m";

    //신규 정책이 아닌 경우
    if(empty($row[0]['passwd_datetime']) || empty($row[0]['passwd_new'])){
        //기존 정책 비밀번호와 바꾸려는 비밀번호가 같으면, 에러
        if($row[0]['passwd'] == md5($pass)){
            $_SESSION['failure'] = !empty($langArr['passwordChangeStringDanger05']) ? $langArr['passwordChangeStringDanger05'] : "You cannot use the password you are using previously.";
            header('location: login.php');
            exit();
        }
        if(!preg_match($regex, $pass)){
            $_SESSION['failure'] = !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits)";
            header('location: login.php');
            exit();
        }
        //신규 비밀번호 해싱
        $salt = bin2hex(openssl_random_pseudo_bytes(8)).'.';
        $hash = hash('sha512',trim($salt.$pass));
        //$updateArr['passwd'] = 'none';
        $updateArr['passwd_new'] = $hash;
        $updateArr['passwd_salt'] = $salt;
        $updateArr['passwd_datetime'] = $util->getDateSql();
    }
    else{
        //신규 정책 기존 비밀번호 해싱

        //(신규)이전 비밀번호와 같을 때 fail
        if(!empty($row[0]['passwd_datetime']) && !empty($row[0]['passwd_new'])){
            $hash = hash('sha512',trim($row[0]['passwd_salt'].$pass));
            if(hash_equals($hash,$row[0]['passwd_new'])){
                $_SESSION['failure'] = !empty($langArr['passwordChangeStringDanger05']) ? $langArr['passwordChangeStringDanger05'] : "You cannot use the password you are using previously.";
                header('location: login.php');
                exit();
            }
        }
        else{
            //(이전)이전 비밀번호 와 같을 떄 fail
            if($row[0]['passwd'] == md5($pass)){
                $_SESSION['failure'] = !empty($langArr['passwordChangeStringDanger05']) ? $langArr['passwordChangeStringDanger05'] : "You cannot use the password you are using previously.";
                header('location: login.php');
                exit();
            }
        }

        if(!preg_match($regex, $pass)){
            $_SESSION['failure'] = !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits)";
            header('location: login.php');
            exit();
        }

        //신규 비밀번호 해싱
        $salt = bin2hex(openssl_random_pseudo_bytes(8)).'.';
        $hash = hash('sha512',trim($salt.$pass));
        $updateArr['passwd_new'] = $hash;
        $updateArr['passwd_salt'] = $salt;
        $updateArr['passwd_datetime'] = $util->getDateSql();
    }

    $db = getDbInstance();

    //조회한 회원이, 휴면 회원 인 경우?
    if(!empty($row[0]['email'])){
        //일반 회원 인 경우
        $db->where("id", $row[0]['id']);
        $updateArr['vcode'] = "";
        $last_id = $db->update('admin_accounts', $updateArr);
    }
    else{
        //휴면 회원 인 경우
        $updateProc = $db->where("id", $row[0]['id'])->update('admin_accounts_sleep', $updateArr);
        $updateProc = $db->where("id", $row[0]['id'])->update('admin_accounts', ['vcode'=>'']);
    }

	$_SESSION['success'] = $langArr['pass_update_success'];
    header('location: login.php');
	exit();
	
}

include_once 'includes/header.php';

?>

<link rel="stylesheet" type="text/css" href="css/login.css">

<div id="resetpass" class="login_input">
	<form class="form loginform" method="POST" >
		<input type="hidden" name="vcode" value="<?php echo !empty($vCode) ? $vCode : ''; ?>" >

		
		<div class="login_logo"><img src="images/logo3.png" alt="logo" /></div>


		<div class="form-group">
			<input type="text" id="sss"  name="vcode" title="<?php echo $langArr['this_field_is_required']; ?>" class="input" required="required" placeholder="<?php echo !empty($langArr['enter_verification_code']) ? $langArr['enter_verification_code'] : "Enter Verification Code"; ?>">
		</div>

		<div class="form-group">
			<input type="password" id="psw"  pattern=".{8,}" title="Must contain at least 8 or more characters" name="password" class="input" required="required" placeholder="<?php echo !empty($langArr['new_password']) ? $langArr['new_password'] : "New Password"; ?>">
		</div>
		<div id="message">
			<h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
			<!--<p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>-->
            <p id="length" class="invalid"><?php echo !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Minimum 8 characters"; ?></p>
        </div>
		<div class="form-group">
			<input type="password" id="confirm_psw" pattern=".{8,}" title="Must contain at least 8 or more characters" name="confirm_password" class="input" required="required" placeholder="<?php echo !empty($langArr['conf_password']) ? $langArr['conf_password'] : "Confirm Password"; ?>">
		</div>


		<?php
		if(isset($_SESSION['login_failure'])){ ?>
			<div class="alert alert-danger alert-dismissable fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
			</div>
		<?php }
		if(isset($_SESSION['success'])){ ?>
			<div class="alert alert-success alert-dismissable fade in">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				<?php echo $_SESSION['success']; unset($_SESSION['success']);?>
			</div>
		<?php } ?>
		<button type="submit" class="btn" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?></button>

		<ul class="bottom">
			<li class="text1"><a  href="login.php" title="login"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a></li>
		</ul>
		

	</form>
</div>
	
<?php include_once 'includes/footer.php'; ?>

			
<script>

$(function () {
	$(".loginform").submit(function(){

        var getPassword = $("#psw").val();
        var getConfirmPass = $("#confirm_psw").val();
        console.log(getPassword);
        console.log(getConfirmPass);
        if(!passwordValidCheck(getPassword)){
            pop_message("<?php echo !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : 'Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits).'; ?>");
            return false;
        }

        if(getConfirmPass != getPassword){
            pop_message("<?php echo !empty($langArr['pass_conf_pass_match']) ? $langArr['pass_conf_pass_match'] : 'Password and Confirm Password Should be Same'; ?>");
            return false;
        }
	})
});	
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
  /*var lowerCaseLetters = /[a-z]/g;
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
  }
  */
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
}</script>
<?php include_once 'includes/footer.php'; ?>
