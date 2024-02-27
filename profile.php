<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

use wallet\common\Log as walletLog;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}
//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();
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
/* = -------------------------------------------------------------------------- = */

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

$log->info('프로필 조회',['target_id'=>$row[0]['id'],'action'=>'S']);

// 본인인증
$id_auth = '';
if ( !empty($row[0]['id_auth']) ) {
	$id_auth = $row[0]['id_auth'];
} else {
	$id_auth = 'N';
}

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    //$data_to_store = filter_input_array(INPUT_POST);
    //Insert timestamp
    //$data_to_store['created_at'] = date('Y-m-d H:i:s');
    //$db = getDbInstance();
	//$db->where("id", $_SESSION['user_id']);
	//$row1 = $db->get('admin_accounts');

    $filter = walletFilter::getInstance();

    //2021-11-09 XSS Filter by.ojt
    $targetPostData = array(
        'id_auth_t' => 'string',
        'fname' => 'string',
        'lname' => 'string',
        'gender' => 'string',
        'dob' => 'string',
        'location' => 'string',
    );

    $filterData = $filter->postDataFilter($_POST,$targetPostData);
    unset($targetPostData);

    $log->info('프로필 수정',['target_id'=>$row[0]['id'],'action'=>'E']);

	$updateArr = [] ;
	if ($filterData['id_auth_t'] !='Y') {
		$updateArr['name'] =  addslashes(htmlspecialchars($filterData['fname']));
		$updateArr['lname'] =  addslashes(htmlspecialchars($filterData['lname']));
		$updateArr['gender'] =  $filterData['gender'];
		$updateArr['dob'] =  addslashes(htmlspecialchars($filterData['dob']));
	}
	$updateArr['location'] =  addslashes(htmlspecialchars($filterData['location']));

	// password
	/*if(trim($_POST['new_pass'])!=""){
		if ($_POST['new_pass'] == $_POST['conf_pass']) {
			if ( $row[0]['passwd'] == md5($_POST['old_pass']) ) {
				$updateArr['passwd'] =  md5($_POST['new_pass']);
			} else { // 기존 비밀번호 불일치
				$_SESSION['failure'] = !empty($langArr['profile_old_pass_wrong']) ? $langArr['profile_old_pass_wrong'] : "Old password do not match"; 
				header('location: profile.php');
			 	exit();
			}
		} else { // 새 비밀번호랑 확인을 다르게 입력함
			$_SESSION['failure'] = !empty($langArr['pass_conf_pass_match']) ? $langArr['pass_conf_pass_match'] : "Password and Confirm Password Should be Same"; 
    		header('location: profile.php');
    		exit();
		}
	}
	*/
	
	// Profile Image (20.06.29)
	if($_FILES['userfile']['name']) {

		// File size Check
		//print_r($_FILES['userfile']);
		if ( $_FILES['userfile']['error'] > 0) {
			// 1 : upload_max_filesize 초과
			// 2 : max_file_size 초과
			// 3 : 파일이 부분만 업로드
			// 4 : 파일을 선택해라
			// 6 : 임시 폴더가 존재하지 않음
			// 7 : 임시 폴더에 파일을 넣을 수 없다. 퍼미션 확인
			// 8 : 확장에 의해 파일 업로드 중지
			switch ($_FILES['userfile']['error']) {
				case '1':
				case '2':
					$_SESSION['failure'] = !empty($langArr['profile_img_message4']) ? $langArr['profile_img_message4'] : "File size is too large.";
					header('location: profile.php');
					exit();
					break;
				default:
					$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred";
					header('location: profile.php');
					exit();
					break;
			}
		}
		if($_FILES['userfile']['size'] <= 0) { // fail
			$_SESSION['failure'] = !empty($langArr['profile_img_message1']) ? $langArr['profile_img_message1'] : "Please check the file size.";
			header('location: profile.php');
			exit();
		}
		// 파일 이름의 특수문자가 있을 경우 업로드를 금지
	//	if (eregi("[^a-zA-Z0-9\._\-]",$_FILES['userfile']['name'])) { // fail
	//		echo 'File name check';
	//		exit;
	//	}
		// 파일 확장자 체크
		$full_filename = explode(".", $_FILES['userfile']['name']);
		$extension = $full_filename[sizeof($full_filename)-1];
		$extension= strtolower($extension);
		if ( $extension != 'jpg' && $extension != 'jpeg' && $extension != 'gif' && $extension != 'png') {
			$_SESSION['failure'] = !empty($langArr['profile_img_message2']) ? $langArr['profile_img_message2'] : "Only image files (jpg, gif, png) can be uploaded.";
			header('location: profile.php');
			exit();
		}
		
		// Profile Image : Upload
		$filename = new_profile_set_filename($n_profile_uploaddir) . '.' . $extension;
		$uploadfile = $n_profile_uploaddir . $filename;
		$uploadfile2 = new_profile_compress_image($_FILES['userfile']['tmp_name'], $uploadfile, 60);
		$buffer = file_get_contents($uploadfile);

		if ( is_file($uploadfile) ) {
		//if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) { // success
			$updateArr['profile_img'] =  $filename;
			
			// 이전 파일 삭제. Delete old files
			if ( !empty($row[0]['profile_img']) && is_file($n_profile_uploaddir . $row[0]['profile_img']) ) {
				unlink($n_profile_uploaddir . $row[0]['profile_img']);
			}

		} else { // fail
			$_SESSION['failure'] = !empty($langArr['profile_img_message3']) ? $langArr['profile_img_message3'] : "It can't be uploaded. Please try again."; 
			header('location: profile.php');
			exit();
		}
	}
	
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);

    $last_id = $db->update('admin_accounts', $updateArr);
    
    if( !empty($last_id) )
    {
    	$_SESSION['success'] = $langArr['profile_updated_successfully'];
    	header('location: profile.php');
    	exit();
    }  else {
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
    	header('location: profile.php');
    	exit();
	}
	
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
   <!-- MetisMenu CSS -->
<link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 

<link  rel="stylesheet" href="css/member.css"/>
<div id="page-wrapper">
	<div id="profile" class="member_common">
		<!--<div class="row">
				 <div class="col-lg-12">
					<h2 class="page-header"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></h2>
				</div>
				
		</div>-->
		
		<?php include('./includes/flash_messages.php') ?>
		<!--<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#home"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></a></li>
			<li><a  href="change_pass.php"><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></a></li>
			<li><a href="change_transfer_pass.php"><?php echo !empty($langArr['change_transfer_pass']) ? $langArr['change_transfer_pass'] : "Set transmission password"; ?></a></li>
			
		</ul>-->
		<div class="tab-content" >
			<div class="col-md-3"></div>
			<div class="col-md-6 tab-pane fade in active" >

				<p class="profile_subject"><?php echo !empty($langArr['profile_subject1']) ? $langArr['profile_subject1'] : 'Profile'; ?></p>
				
				<!-- 본인인증 -->
				<form method="post" name="form_auth">
					<input type="hidden" name="ordr_idxx" id="auth_ordr_idxx" class="frminput" value="" readonly maxlength="40"/>
					<?php
					if ($id_auth != 'Y' ) {
					?>
						<div id="show_pay_btn">
							<input type="submit" id="id_auth_btn" class="btn_auth" onclick="return auth_type_check();" value="<?php echo $langArr['personal_identification']; ?>" />
						</div>
					<?php } ?>

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
					<input type="hidden" name="param_opt_1"  value="member" /> <!-- 가맹점 사용 필드 (인증완료시 리턴), member.pro.res-->
					<input type="hidden" name="param_opt_2"  value="<?php if ( !empty($_SESSION['admin_type']) ) { echo $_SESSION['admin_type']; } ?>" /> 
					<input type="hidden" name="param_opt_3"  value="<?php if ( !empty($_SESSION['user_id']) ) { echo $_SESSION['user_id']; } ?>" /> 
				</form>

				<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
					<input type="hidden" name="id_auth_t" value="<?php echo $id_auth; ?>" />
					
					<div class="profile_image_area">
						<?php
						if ( !empty($row[0]['profile_img']) && is_file($n_profile_uploaddir . $row[0]['profile_img']) ) {
							$profile_img_path = $n_profile_uploaddir . $row[0]['profile_img'];
							$profile_circle2_class = 'profile_circle2_back';
							$img_size = GetImageSize($profile_img_path); // 0: 가로, 1 : 세로
							if ( $img_size[0] >= $img_size[1]) {
								$profile_img_class = 'profile_img_w';
							} else {
								$profile_img_class = 'profile_img';
							}
						} else {
							$profile_img_path = 'images/icons/person.png';
							$profile_img_class = 'profile_img_none';
							$profile_circle2_class = 'profile_circle2_back_none';
						} ?>
						<div id="preview" class="circle1"><div id="circle2" class="<?php echo $profile_circle2_class; ?>"><img src="<?php echo $profile_img_path; ?>" class="<?php echo $profile_img_class; ?>" alt="profile image" /></div></div>
						<div class="profile_input_area">
							<div class="profile_img_btn"><img src="images/icons/profile_img.png" alt="profile image" /></div>
							<?php
							if ( stristr($_SERVER['HTTP_USER_AGENT'], 'ios-web-view') == TRUE ) { ?>
								<input type="file" name="userfile" id="userfile" accept="image/*" capture="camera" />
								<img id="frame11">
							<?php } else { ?>
								<input type="file" name="userfile" id="userfile" />
							<?php } ?>
						</div>
					</div>
				<script>
					if (navigator.userAgent.indexOf("ios-web-view") > - 1){
						var camera = document.getElementById('userfile');
						var frame = document.getElementById('frame11');
						camera.addEventListener('change', function(e) {
							var file = e.target.files[0];
							frame.src = URL.createObjectURL(file);
						});
					}
					</script>
					<?php
					// 본인인증 완료시 인증정보 보여줌
					if ($id_auth == 'Y') {
						$auth_gender_local = '';
						if ( $row[0]['auth_local_code'] == 'Kor' ) {
							$tmp1 = !empty($langArr['korean']) ? $langArr['korean'] : 'Local';
						} else {
							$tmp1 = !empty($langArr['foreigner']) ? $langArr['foreigner'] : 'Foreigner';
						}
						if ( $row[0]['auth_gender'] == 'male' ) {
							$tmp2 = !empty($langArr['male']) ? $langArr['male'] : 'Male';
						} else {
							$tmp2 = !empty($langArr['female']) ? $langArr['female'] : 'Female';
						}
						//$tmp1 = $row[0]['auth_local_code'] == 'Kor' ? $langArr['korean'] : $langArr['foreigner'];
						//$tmp2 = $row[0]['auth_gender'] == 'male' ? $langArr['male'] : $langArr['female'];
						$auth_gender_local = $tmp1.' / '.$tmp2;


						?>
						<div class="profile_infos_a">
							<dl class="profile_infos">
								<dt><?php echo !empty($langArr['profile_text1']) ? $langArr['profile_text1'] : 'Name'; ?></dt>
								<dd><?php echo $row[0]['auth_name']; ?></dd>
								<dt><?php echo !empty($langArr['profile_text2']) ? $langArr['profile_text2'] : 'Contact'; ?></dt>
								<dd><?php echo $row[0]['auth_phone']; ?></dd>
								<dt><?php echo !empty($langArr['dob']) ? $langArr['dob'] : 'Date of Birth'; ?></dt>
								<dd><?php echo $row[0]['auth_dob']; ?></dd>
								<dt><?php echo !empty($langArr['gender']) ? $langArr['gender'] : 'Gender'; ?></dt>
								<dd><?php echo $auth_gender_local; ?></dd>
							</dl>
						</div>
					<?php } ?>

				
					<?php
                    /*
					//if ( !empty($_SESSION['user_id']) && $_SESSION['user_id'] == '6135') {
					if ( !empty($_SESSION['user_id']) && $_SESSION['user_id'] == '5137') { ?>
					<div style="margin-top:10px;">
						<div id="pvt_btn" class="btn btn-success"><?php echo $langArr['show_private_key'] ?></div>
						<div id="pvt_resp" style="word-break:break-all;"></div>
					</div>
					<?php }*/
                    ?>
					<fieldset>
						<?php if ($id_auth != 'Y') { ?>
							<div class="form-group">
								<label for="fname"><?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?></label>
								  <input type="text" name="fname" value="<?php echo $row[0]['name']; ?>" placeholder="<?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?>" class="input" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "fname">
							</div> 
							<div class="form-group">
								<label for="lname"><?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?></label>
								  <input type="text" name="lname" value="<?php echo $row[0]['lname']; ?>" placeholder="<?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?>" class="input" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "lname">
							</div> 
							<div class="form-group">
								<label for="gender"><?php echo !empty($langArr['gender']) ? $langArr['gender'] : "Gender"; ?></label>
								  <select class="input" id="gender" name="gender">
								  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
								  <option <?php echo ($row[0]['gender']=="male") ? "Selected" : ""; ?> value="male"><?php echo !empty($langArr['male']) ? $langArr['male'] : "Male"; ?></option>
								  <option <?php echo ($row[0]['gender']=="female") ? "Selected" : ""; ?> value="female"><?php echo !empty($langArr['female']) ? $langArr['female'] : "Female"; ?></option>
								  <option <?php echo ($row[0]['gender']=="other") ? "Selected" : ""; ?> value="other"><?php echo !empty($langArr['other']) ? $langArr['other'] : "Other"; ?></option>
								 </select>
							</div> 
							<div class="form-group">
								<label for="dob"><?php echo !empty($langArr['dob']) ? $langArr['dob'] : "Date of Birth"; ?></label>
								  <input type="text" name="dob" readonly value="<?php echo $row[0]['dob']; ?>" placeholder="<?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?>" class="input" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" id = "dob">
							</div> 
						<?php } ?>
						<div class="form-group">
							<label for="location"><?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?></label>
							  <input type="text" name="location" value="<?php echo $row[0]['location']; ?>" placeholder="<?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?>" title="<?php echo $langArr['this_field_is_required']; ?>" class="input" required="required" id = "location">
						</div> 
						 <!-- <select class="form-control">
						  <option>KYC</option>
						  <option>PAN NO</option>
						  <option>BANK A/C NO</option>
						  <option>IFSC CODE</option>
						  <option>BANK NAME</option>
						 </select> -->
						 <br/>

						<!--
						<hr />
						<p class="profile_subject"><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : 'Change Password'; ?></p>
						<div class="form-group">
							  <input type="password" name="old_pass" title="<?php echo $langArr['this_field_is_required']; ?>"  class="input" id = "old_pass" placeholder="<?php echo !empty($langArr['old_password']) ? $langArr['old_password'] : "Old Password"; ?>">
						</div> 
						
						<div class="form-group">
							  <input type="password" name="new_pass" value="" class="input" id = "new_pass" placeholder="<?php echo !empty($langArr['new_password']) ? $langArr['new_password'] : "New Password"; ?>">
						</div> 
						<div id="message">
						  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
						 
						  <p id="length" class="invalid"><?php echo !empty($langArr['passwd_minimum_char']) ? $langArr['passwd_minimum_char'] : "Minimum 6 characters"; ?></p>
						</div>
						<div class="form-group">
							  <input type="password" name="conf_pass" value=""  class="input" id = "conf_pass" placeholder="<?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?>">
						</div> 
						-->


				
						<?php
						if ( !empty($row[0]['transfer_passwd']) ) {
							$stf_btn = !empty($langArr['transfer_pw_btn_in_profile']) ? $langArr['transfer_pw_btn_in_profile'] : 'Edit payment password';
						} else {
							$stf_btn = !empty($langArr['transfer_pw_subject']) ? $langArr['transfer_pw_subject'] : 'Set payment password';
						}

						?><ul class="profile_link_list">
							<li><a href="change_pass.php" title="password"><span>
								<?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : 'Change Password'; ?></span><img src="images/icons/arrow_gray.png" alt="set password" />
							</a></li>
							<li><a href="set_transferpw_frm.php" title="password"><span>
								<?php echo $stf_btn; ?></span><img src="images/icons/arrow_gray.png" alt="set password" />
							</a></li>
							
							<?php if ( !empty($row[0]['transfer_passwd']) ) { ?>
								<li><a href="change_transfer_forgetpass.php" title="forget transfer password"><span>
									<?php echo !empty($langArr['transfer_pw_btn_in_profile2']) ? $langArr['transfer_pw_btn_in_profile2'] : 'Find payment password (Send Verification Code)'; ?></span><img src="images/icons/arrow_gray.png" alt="set password" />
								</a></li>
							<?php } ?>

						</ul>

						<div class="form-group text-center">
							<label></label>
							<button type="submit" class="btn" ><?php echo !empty($langArr['profile_btn1']) ? $langArr['profile_btn1'] : "Update"; ?></button>
						</div>            
					</fieldset>
				</form>


                <section>
                    <a href="<?php echo WALLET_URL; ?>/control.php/withdrawal">
                        <p>회원 탈퇴</p>
                    </a>

                    <a href="<?php echo WALLET_URL; ?>/control.php/withdrawal/assetinfo/upload">
                        <p>자산포기각서 제출</p>
                    </a>
                </section>


				<p class="nw_tip_text"><a href="javascript:;" onclick="tip_open();"><?php echo !empty($langArr['transfer_pw_tip_s1']) ? $langArr['transfer_pw_tip_s1'] : "What is a payment password?"; ?></a></p>
				<div id="nw_tip" class="none">
	
					<h4><?php echo !empty($langArr['transfer_pw_tip_s1']) ? $langArr['transfer_pw_tip_s1'] : "What is a payment password?"; ?></h4>
					<ul>
						<li><?php echo !empty($langArr['transfer_pw_tip_s1c1']) ? $langArr['transfer_pw_tip_s1c1'] : "When you 'send' you must enter your password."; ?></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s1c2']) ? $langArr['transfer_pw_tip_s1c2'] : "This password must be set separately from the login password."; ?></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s1c3']) ? $langArr['transfer_pw_tip_s1c3'] : "This password can be set on the 'My Info' page."; ?></li>
					</ul>

					<h4><?php echo !empty($langArr['transfer_pw_tip_s2']) ? $langArr['transfer_pw_tip_s2'] : "Setting for the first time"; ?></h4>
					<h5><?php echo !empty($langArr['transfer_pw_tip_c1']) ? $langArr['transfer_pw_tip_c1'] : "Enter carefully as the arrangement of the numeric keys will change."; ?></h5>
					<ol>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c1']) ? $langArr['transfer_pw_tip_s2c1'] : "Please press this button"; ?> : <span class="bold">[<?php echo !empty($langArr['transfer_pw_subject']) ? $langArr['transfer_pw_subject'] : 'Set payment password'; ?>]</span></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c2']) ? $langArr['transfer_pw_tip_s2c2'] : "Please enter the password you want to set."; ?></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c3']) ? $langArr['transfer_pw_tip_s2c3'] : "Please enter the password again to confirm."; ?></li>
					</ol>

					<h4><?php echo !empty($langArr['transfer_pw_tip_s3']) ? $langArr['transfer_pw_tip_s3'] : "Modify"; ?></h4>
					<h5><?php echo !empty($langArr['transfer_pw_tip_c1']) ? $langArr['transfer_pw_tip_c1'] : "Enter carefully as the arrangement of the numeric keys will change."; ?></h5>
					<ol>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c1']) ? $langArr['transfer_pw_tip_s2c1'] : "Please press this button"; ?> : <span class="bold">[<?php echo !empty($langArr['transfer_pw_btn_in_profile']) ? $langArr['transfer_pw_btn_in_profile'] : 'Edit payment password'; ?>]</span></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s3c1']) ? $langArr['transfer_pw_tip_s3c1'] : "Please enter your old password."; ?></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c2']) ? $langArr['transfer_pw_tip_s2c2'] : "Please enter the password you want to set."; ?></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c3']) ? $langArr['transfer_pw_tip_s2c3'] : "Please enter the password again to confirm."; ?></li>
					</ol>

					<h4><?php echo !empty($langArr['transfer_pw_tip_s4']) ? $langArr['transfer_pw_tip_s4'] : "Find your password"; ?></h4>
					<ol>
						<li><?php echo !empty($langArr['transfer_pw_tip_s2c1']) ? $langArr['transfer_pw_tip_s2c1'] : "Please press this button"; ?> : <span class="bold">[<?php echo !empty($langArr['transfer_pw_btn_in_profile2']) ? $langArr['transfer_pw_btn_in_profile2'] : 'Find payment password (Send Verification Code)'; ?>]</span></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s4c1']) ? $langArr['transfer_pw_tip_s4c1'] : "Please check the phone number/email address and enter the verification code."; ?></li>
						<li><?php echo !empty($langArr['transfer_pw_tip_s4c2']) ? $langArr['transfer_pw_tip_s4c2'] : "Please reset the password from the beginning."; ?></li>
					</ol>
				</div>

			</div>
		</div>
	</div>
</div>
<iframe id="kcp_cert" name="kcp_cert" width="100%" height="700" frameborder="0" scrolling="yes" style="display: none;"></iframe>

<script type="text/javascript">


function get_ios_id(){
	if (navigator.userAgent.indexOf("ios-web-view") > - 1){
	}
}


// Profile Image (20.06.29)
function readInputFile(input) {
    if(input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#profile #preview #circle2').html("<img class=\"profile_img\" src="+ e.target.result +">");
			if ($('#profile #preview #circle2').attr('class') == 'profile_circle2_back_none') {
				$('#profile #preview #circle2').removeClass('profile_circle2_back_none').addClass('profile_circle2_back');
			}
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function tip_open() {
	if ( $("#nw_tip").attr('class') == 'none') {
		$("#nw_tip").removeClass('none');
	} else {
		$("#nw_tip").addClass('none');
	}
}
$(document).ready(function(){
	
	// 20.08.27
	//if (navigator.userAgent.indexOf("android-web-view2") > - 1){
	//	$(".profile_input_area").addClass('none');
	//}

	// Profile Image (20.06.29)
	$("#profile #userfile").on('change', function(){
		readInputFile(this);
	});

   /*$("#customer_form").validate({
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
	myInput.onkeyup = function() {		  
	  // Validate length
	  if(myInput.value.length >= 6) {
		length.classList.remove("invalid");
		length.classList.add("valid");
	  } else {
		length.classList.remove("valid");
		length.classList.add("invalid");
	  }
	}*/
	
	$(".form").submit(function(){
		$("#loading-o").removeClass('none');
		/*var getpasslength = $("#new_pass").val();
		if(getpasslength != ''){
			if ($("#old_pass").val() == '') {
				pop_message("<?php echo !empty($langArr['plz_input_old_password']) ? $langArr['plz_input_old_password'] : 'Please enter a old password.'; ?>");
				 return false;
			} else if ( getpasslength.length<6 ) {
				 //document.getElementById("message").style.display = "block";
				 pop_message("<?php echo !empty($langArr['profile_password_msg1']) ? $langArr['profile_password_msg1'] : 'Please set it to 6 digits or more.'; ?>");
				 return false;
			} else if ( getpasslength != $("#conf_pass").val() ) {
				 pop_message("<?php echo !empty($langArr['pass_conf_pass_match']) ? $langArr['pass_conf_pass_match'] : 'Password and Confirm Password Should be Same'; ?>");
				 return false;
			}
		}*/
	});
	

	$('#dob').datepicker({format: "yyyy/mm/dd"});
	
	
	$("#pvt_btn").click(function(){
		$.ajax({
			beforeSend:function(){
				$("#pvt_resp").html('<img src="images/ajax-loader.gif" />');
			},
			url : 'showpvt.php',
			type : 'POST',
			//dataType : 'json',
			success : function(resp){
				$("#pvt_resp").html(resp);
			},
			error : function(resp){
				$("#pvt_resp").html(resp);
			}
		}) 
	 }); 


	// 본인인증 추가
	init_orderid();
});



// 본인인증 추가
// MOBILE(SMART)
function auth_type_check() {
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
			
			document.getElementById( "page-wrapper" ).style.display = "none";
			document.getElementById( "kcp_cert"  ).style.display = "";
			$("#wrapper nav").css('display', 'none');
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

		auth_form.action = "./auth.pro.req.php"; // 인증창 호출 및 결과값 리턴 페이지 주소
		
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

</script>


<?php include_once 'includes/footer.php'; ?>