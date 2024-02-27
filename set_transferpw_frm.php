<?php 
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

use wallet\common\Log as walletLog;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$filter = walletFilter::getInstance();
//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'pas1' => 'string',
    'pas_count' => 'string',
    'pas2' => 'string',
);

$filterData = $filter->postDataFilter($_POST,$targetPostData);
unset($targetPostData);
/* ============================================================================== */
/*
// 전송비밀번호 설정 페이지. transfer password setting page

비밀번호 6자리는 : new_config에서

Step 1
	pas_count = 1
	이미 비밀번호를 설정한 사람들은 기존 비밀번호를 확인해야 새 비밀번호를 설정할 수 있다.
	Confirm old password : Only users who have already set their payment password

Step 2
	pas_count = 2
	처음 비밀번호를 설정한 사람들은 Step 2부터 시작함
	새롭게 사용할 비밀번호 설정
 
Step 3
	pas_count = 3
	비밀번호 확인 단계. Step2에서 입력한 비밀번호와 일치하는지 확인 후 Database에 반영

*/
/* ============================================================================== */

$log->info('프로필 > 결제 비밀번호 수정 조회',['target_id'=>0,'action'=>'S']);

$pas1 = !empty($filterData['pas1']) ? $filterData['pas1'] : '';
$pas_count = !empty($filterData['pas_count']) ? $filterData['pas_count'] : '1';

// 처음 셋팅시 2단계부터 시작 -> 3단계에서 다른 비밀번호 입력 -> 이전단계로 버튼 누르면 1단계 보여지는 문제 해결 위해 $fix_pas_count 추가
if ( $pas_count == '1') { // 1단계. 비밀번호 없으면 1단계(기존비번 확인하는 단계) 건너뛰고 2단계(새비밀번호 입력하는 단계)로 넘어갈 것
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$userData = $db->getOne('admin_accounts');
	if ( empty($userData['transfer_passwd']) ) {
		$pas_count = '2';
	}
	$fix_pas_count =  $pas_count;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ( !empty($filterData['pas1']) && !empty($filterData['pas2']) && $filterData['pas1'] == $filterData['pas2'] ) { // pas1과 pas2값이 동시에 넘어오는 경우는 오직 3단계뿐임.
		// 비밀번호 변경
		if ( !empty($_SESSION['user_id']) ) {
			$db = getDbInstance();
			$db->where("id", $_SESSION['user_id']);
            $log->info('프로필 > 결제 비밀번호 수정',['target_id'=>0,'action'=>'E']);
			
			$updateArr = [] ;
			$updateArr['transfer_passwd'] =  password_hash($filterData['pas1'], PASSWORD_DEFAULT);
			$last_id = $db->update('admin_accounts', $updateArr);
			if($last_id) {
				$_SESSION['success'] = !empty($langArr['password_changed_successfully']) ? $langArr['password_changed_successfully'] : "Password Changed successfully"; 
				header('location: index.php');
				exit();
			} else {
				$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
				header('location: profile.php');
				exit();
			}
		} else {
			$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
			header('location: profile.php');
			exit();
		}
	} else {
		$_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : "The wrong approach."; 
		header('location: profile.php');
		exit();
	}
}
require_once 'includes/header.php'; 

?>
<script src="js/other.js" type="text/javascript"></script> 
<link  rel="stylesheet" href="css/other.css"/>

<div id="set_transferpw_frm" class="set_transferpw_frm">
	<div class="top">
		<p class="subject"><?php echo !empty($langArr['transfer_pw_subject']) ? $langArr['transfer_pw_subject'] : 'Set payment password'; ?></p>
		
		<p id="stf_message" class="explain2 none">
			<span id="stf_message_s1" class="none"><?php echo !empty($langArr['transfer_pw_message1']) ? $langArr['transfer_pw_message1'] : 'Passwords do not match.'; ?></span>
			<span id="stf_message_s2" class="none"><?php echo !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.'; ?></span>
			<span id="stf_message_s3" class="none"><?php echo !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred'; ?></span>
			<span id="stf_message_s5" class="none"><?php echo !empty($langArr['transfer_pw_message2']) ? $langArr['transfer_pw_message2'] : 'It is different from the payment password you set.'; ?></span>
		</p>
		<p id="explain1" class="explain <?php echo $pas_count == '1' ? ' ' : ' none'; ?>"><?php echo !empty($langArr['transfer_pw_text5']) ? $langArr['transfer_pw_text5'] : 'Please enter a old payment password.'; ?></p>
		<p id="explain2" class="explain <?php echo $pas_count == '2' ? ' ' : ' none'; ?>"><?php echo isset($langArr['transfer_pw_text2']) ? $langArr['transfer_pw_text2'] : 'Please set your '; ?><?php echo $n_transfer_pw_length; ?><?php echo !empty($langArr['transfer_pw_text3']) ? $langArr['transfer_pw_text3'] : ' digit payment password.'; ?></p>
		<p id="explain3" class="explain <?php echo $pas_count == '3' ? ' ' : ' none'; ?>"><?php echo !empty($langArr['transfer_pw_text4']) ? $langArr['transfer_pw_text4'] : 'Please enter again to confirm.'; ?></p>
		
		<div class="password_area">
			<?php
			for($i = 0; $i < $n_transfer_pw_length; $i++) {
				?><span id="pass_area_<?php echo $i; ?>"><img src="images/icons/pass_input_n.png" alt="password" /></span><?php
			} // foreach
			?>
		</div>

		<div id="stf_message_btn" class="none">
			<a href="javascript:;" title="move" data-num="reload">
				<img src="images/icons/top_subject_back_btn.png" alt="move" />
				<span><?php echo !empty($langArr['transfer_pw_btn2']) ? $langArr['transfer_pw_btn2'] : 'Back to previous level'; ?></span>
			</a>
		</div>

	</div>
	<div class="number">
		<?php
		$num_arr = array('1', '2', '3', '4', '5', '6', '7', '8', '9');
		shuffle($num_arr);
		foreach ($num_arr as $k1=>$v1) {
			?><p id="pass_number_<?php echo $v1;?>" data-num="<?php echo $v1;?>"><span><img src="images/icons/<?php echo $v1; ?>.png" alt="<?php echo $v1; ?>" /></span></p><?php
		} // foreach
		?><p data-num="re" class="re"><span><img src="images/icons/rearrangement_<?php echo !empty($_SESSION['lang']) ? $_SESSION['lang'] : 'en'; ?>.png" alt="rearrangement" /></span></p><p id="pass_number_0" data-num="0"><span><img src="images/icons/0.png" alt="0" /></span></p><p id="pass_number_del" data-num="del" class="del"><span><img src="images/icons/pass_input_del.png" alt="delete" /></span></p>
	</div>
</div>

<script>
var pass = '';
var pass_length = 0;

// 실패시 메세지 박스 뿌려주는 부분. 
function stf_message_box_setting(msg_index, explain_id) {
	$("#set_transferpw_frm #stf_message_s"+msg_index).removeClass('none');
	$("#set_transferpw_frm #explain"+explain_id).addClass('none');
	$("#set_transferpw_frm #stf_message").removeClass('none');
	$("#set_transferpw_frm #stf_message_btn").removeClass('none');
}
// 단계별로 form submit하면 뒤로가기 했을 때 문제 발생 / submit 대체하기 위해 값 초기화 함수사용
function stf_form_reset(pass_count) {
	pass_length = 0;
	pass = '';
	document.pass_frm.pas1.value = pass;
	document.pass_frm.pas_count.value = pass_count;
	$("#stf_message").addClass('none');
	$("#stf_message_btn").addClass('none');
	$(".password_area img").attr('src','images/icons/pass_input_n.png');
	stf_num_re();
}


$(function(){
	var password_length = $("#transfer_pw_length").val();

	$("#stf_message_btn").on('click tap', function(){
		document.pass_frm.pas2.value = '';
		
		$(".explain").addClass('none');
		if ( $("#fix_pas_count").val() == '2') {
			stf_form_reset('2');
			$("#explain2").removeClass('none');
		} else {
			$("#explain1").removeClass('none');
			stf_form_reset('1');
		}

	});

	$("#set_transferpw_frm .number p").on('click tap', function(){
		var num = $(this).attr('data-num');
		if (num == 'del') { // 삭제
			stf_num_del();
		} else if (num == 're') { // 재배열
			stf_num_re();
		} else if (num != '' && pass_length < password_length) { // 0~9
			pass = pass + num;
			$("#pas1").val(pass);
			$("#pass_area_"+pass_length+" img").attr('src','images/icons/pass_input_y.png');
			pass_length = pass_length + 1;
			if (pass_length == password_length) {
				var pas_count = $("#pas_count").val();
				if (pas_count == '1') { // Confirm old password
					
					var mid = $("#mid").val();
					$.ajax({
						url : 'send.pro.php',
						type : 'POST',
						dataType : 'json',
						data : {mode: 'get_transfer_pw', mid : mid, pas1 : pass},
						success : function(resp){
							if (resp.result == 'success') {
								document.pass_frm.pas2.value = '';
								$("#explain1").addClass('none');
								$("#explain2").removeClass('none');
								stf_form_reset('2');

							} else if (resp.result == 'fail' ) {
								stf_message_box_setting('1', '1');
							} else {
								stf_message_box_setting('2', '1');
							}
							// none : 잘못된접근, set:셋팅이필요함, fail:비밀번호다름, success:성공
						},
						error : function(resp){
							stf_message_box_setting('3', '1');
						}
					});


				} else if (pas_count == '2') { // new password

					document.pass_frm.pas2.value = pass;
					$("#explain2").addClass('none');
					$("#explain3").removeClass('none');
					stf_form_reset('3');

				} else { // confirm password
					if ( pass == $("#pas2").val() ) { // success
						document.pass_frm.action='set_transferpw_frm.php';
						document.pass_frm.submit();
					} else { // Inconsistency
						stf_message_box_setting('5', '3');
					}
				}
			}
		}
		return false; // 브라우저에 따라서 중복실행하는 경우 방지
	});
});
</script>
<form method="post" name="pass_frm">
	<input type="hidden" name="pas1" id="pas1" value="" />
	<input type="hidden" name="pas2" id="pas2" value="<?php echo $pas1; ?>" /><!-- 넘어온 값 -->
	<input type="hidden" name="pas_count" id="pas_count" value="<?php echo $pas_count; ?>" />

	<input type="hidden" name="mid" id="mid" value="<?php echo !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>" />
	<input type="hidden" name="transfer_pw_length" id="transfer_pw_length" value="<?php echo $n_transfer_pw_length; ?>" />
	<input type="hidden" name="fix_pas_count" id="fix_pas_count" value="<?php echo $fix_pas_count; ?>" />
</form>
<?php include_once 'includes/footer.php'; ?>