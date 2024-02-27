<?php
// Page in use
error_reporting("E_ALL");
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Log as walletLog;

require(BASE_PATH . '/vendor/autoload.php');

$util = walletUtil::singletonMethod();
$walletDb = walletDb::singletonMethod();
$walletDb = $walletDb->init();

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('프로필 > 비밀번호 변경 조회',['target_id'=>$row[0]['id'],'action'=>'S']);

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
   // $data_to_store = filter_input_array(INPUT_POST);
    //Insert timestamp
    $log->info('프로필 > 비밀번호 변경',['target_id'=>$row[0]['id'],'action'=>'E']);

	$updateArr = [] ;
	if ($_POST['new_pass'] == $_POST['conf_pass']) {

        $regex = "/((?=.*[a-z])(?=.*[0-9])(?=.*[$@$!%*#?&])(?=.*[^a-zA-Z0-9])(?!.*(admin|root)).{8,})/m";

        //신규 정책이 아닌 경우
        if(empty($row[0]['passwd_datetime']) || empty($row[0]['passwd_new'])){
            if ( $row[0]['passwd'] == md5($_POST['old_pass']) ) {
                //기존 정책 비밀번호와 바꾸려는 비밀번호가 같으면, 에러
                if(md5($_POST['new_pass']) == md5($_POST['old_pass'])){
                    $_SESSION['failure'] = !empty($langArr['passwordChangeStringDanger05']) ? $langArr['passwordChangeStringDanger05'] : "You cannot use the password you are using previously.";
                    header('location: change_pass.php');
                    exit();
                }
                if(!preg_match($regex, $_POST['new_pass'])){
                    $_SESSION['failure'] = !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits)";
                    header('location: change_pass.php');
                    exit();
                }
                //신규 비밀번호 해싱
                $salt = bin2hex(openssl_random_pseudo_bytes(8)).'.';
                $hash = hash('sha512',trim($salt.$_POST['new_pass']));
                //$updateArr['passwd'] = 'none';
                $updateArr['passwd_new'] = $hash;
                $updateArr['passwd_salt'] = $salt;
                $updateArr['passwd_datetime'] = $util->getDateSql();
            }
            else { // 기존 비밀번호 불일치
                $_SESSION['failure'] = !empty($langArr['profile_old_pass_wrong']) ? $langArr['profile_old_pass_wrong'] : "Old password do not match";
                header('location: change_pass.php');
                exit();
            }
        }
        else{
            //신규 정책 기존 비밀번호 해싱

            //(신규)이전 비밀번호와 같을 때 fail
            if(!empty($row[0]['passwd_datetime']) && !empty($row[0]['passwd_new'])){
                $hash = hash('sha512',trim($row[0]['passwd_salt'].$_POST['new_pass']));
                if(hash_equals($hash,$row[0]['passwd_new'])){
                    $_SESSION['failure'] = !empty($langArr['passwordChangeStringDanger05']) ? $langArr['passwordChangeStringDanger05'] : "You cannot use the password you are using previously.";
                    header('location: change_pass.php');
                    exit();
                }
            }
            else{
                //(이전)이전 비밀번호 와 같을 떄 fail
                if(md5($_POST['new_pass']) == md5($_POST['old_pass'])){
                    $_SESSION['failure'] = !empty($langArr['passwordChangeStringDanger05']) ? $langArr['passwordChangeStringDanger05'] : "You cannot use the password you are using previously.";
                    header('location: change_pass.php');
                    exit();
                }
            }

            if(!preg_match($regex, $_POST['new_pass'])){
                $_SESSION['failure'] = !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits)";
                header('location: change_pass.php');
                exit();
            }

            $hash = hash('sha512',trim($row[0]['passwd_salt'].$_POST['old_pass']));
            if($row[0]['passwd_new'] == $hash){
                //신규 비밀번호 해싱
                $salt = bin2hex(openssl_random_pseudo_bytes(8)).'.';
                $hash = hash('sha512',trim($salt.$_POST['new_pass']));
                $updateArr['passwd_new'] = $hash;
                $updateArr['passwd_salt'] = $salt;
                $updateArr['passwd_datetime'] = $util->getDateSql();
            }
            else { // 기존 비밀번호 불일치
                $_SESSION['failure'] = !empty($langArr['profile_old_pass_wrong']) ? $langArr['profile_old_pass_wrong'] : "Old password do not match";
                header('location: change_pass.php');
                exit();
            }
        }

	}
	else { // 새 비밀번호랑 확인을 다르게 입력함
		$_SESSION['failure'] = !empty($langArr['pass_conf_pass_match']) ? $langArr['pass_conf_pass_match'] : "Password and Confirm Password Should be Same"; 
		header('location: change_pass.php');
		exit();
	}

	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
    $last_id = $db->update('admin_accounts', $updateArr);
    
    if( !empty($last_id) )  {
    	$_SESSION['success'] = $langArr['profile_updated_successfully'];
    	header('location: profile.php');
    	exit();
    }  else {
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
    	header('location: change_pass.php');
    	exit();
	}
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<style>
.tab-content {
	padding-top: 12px;
}
</style>
<link  rel="stylesheet" href="css/member.css"/>
<div id="page-wrapper">
	<div id="change_pass" class="member_common">
		<!--<div class="row">
			 <div class="col-lg-12">
					<h2 class="page-header"><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></h2>
				</div>
				
		</div>-->
		<?php include('./includes/flash_messages.php') ?>
		<!--<ul class="nav nav-tabs">
			<li><a  href="profile.php"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></a></li>
			<li  class="active"><a data-toggle="tab" ><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></a></li>
		</ul>-->
		<div class="tab-content" >
			<div class="col-md-3"></div>
				<div class="col-md-6 tab-pane fade in active" >
					<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
						<fieldset>
							<div class="form-group">
								<input type="password" name="old_pass" title="<?php echo $langArr['this_field_is_required']; ?>"  class="input" id = "old_pass" placeholder="<?php echo !empty($langArr['old_password']) ? $langArr['old_password'] : "Old Password"; ?>">
							</div> 
							
							<div class="form-group">
								  <input type="password" name="new_pass" value="" class="input" id = "new_pass" placeholder="<?php echo !empty($langArr['new_password']) ? $langArr['new_password'] : "New Password"; ?>">
							</div> 
							<div id="message">
							  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
							 
							  <!--<p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>-->
							  <p id="length" class="invalid"><?php echo !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : "Minimum 8 characters"; ?></p>
							</div>
							<div class="form-group">
								  <input type="password" name="conf_pass" value=""  class="input" id = "conf_pass" placeholder="<?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?>">
							</div> 

							<div class="form-group text-center">
								<label></label>
								<button type="submit" class="btn" ><?php echo !empty($langArr['transfer_pw_tip_s3']) ? $langArr['transfer_pw_tip_s3'] : "Modify"; ?> <span class="glyphicon glyphicon-send"></span></button>
							</div>            
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
$(document).ready(function(){
  /* $("#customer_form").validate({
       rules: {
            f_name: {
                required: true,
                minlength: 3
            },
            l_name: {
                required: true,
                minlength: 3
            },   
        }
    });
	
	
	var myInput = document.getElementById("new_pass");
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
	myInput.onkeyup = function() {*/
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
	/*  if(myInput.value.length >= 6) {
		length.classList.remove("invalid");
		length.classList.add("valid");
	  } else {
		length.classList.remove("valid");
		length.classList.add("invalid");
	  }
	}*/
	
	$("#customer_form").submit(function(){
		var getpasslength = $("#new_pass").val();
		var getoldpass = $("#old_pass").val();

		if (getoldpass == '') {
			pop_message("<?php echo !empty($langArr['plz_input_old_password']) ? $langArr['plz_input_old_password'] : 'Please enter a old password.'; ?>");
			 return false;
		}
        //else if ( getpasslength.length<6 ) {
        else if (!passwordValidCheck(getpasslength)) {
            console.log(passwordValidCheck(getpasslength));
            //document.getElementById("message").style.display = "block";
            pop_message("<?php echo !empty($langArr['passwordChangeString02']) ? $langArr['passwordChangeString02'] : 'Hangul, numbers, and special characters($@$!%*#?&) must be included. (More than 8 digits).'; ?>");
            return false;
		}
		else if ( getpasslength != $("#conf_pass").val() ) {
			 pop_message("<?php echo !empty($langArr['pass_conf_pass_match']) ? $langArr['pass_conf_pass_match'] : 'Password and Confirm Password Should be Same'; ?>");
			 return false;
		}
	});
});
</script>

<?php include_once 'includes/footer.php'; ?>
