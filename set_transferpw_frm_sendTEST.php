<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

use wallet\common\Log as walletLog;

require __DIR__ .'/vendor/autoload.php';

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('e-pay/token 전송 비밀번호 입력 폼 조회',['target_id'=>0,'action'=>'S']);
/* ============================================================================== */
/*
// 전송할 때 비밀번호 입력해야 전송가능. When sending, you must enter a password to send.
// new_config.php : $n_transfer_pw_count 참고

*/
/* ============================================================================== */

$pas1 = !empty($_POST['pas1']) ? $_POST['pas1'] : '';
$token = !empty($_POST['token']) ? $_POST['token'] : '';
$kind = !empty($_POST['kind']) ? $_POST['kind'] : '';
$payment_no = !empty($_POST['payment_no']) ? $_POST['payment_no'] : '';

//2021.06.21 kiosk 0원 처리 임시
$kioskTempCheck = !empty($_POST['kioskTempCheck']) ? $_POST['kioskTempCheck'] : false;


if ( !empty($token) ) {
    switch($token) {
        case 'ctc':
            $return_url = 'send_token_test1.php';
            break;
        case 'eth':
            $return_url = 'send_eth.php';
            break;
        case 'etp3':
        case 'ectc':
        case 'ekrw':
        case 'emc':
        case 'eeth':
        case 'eusdt':
            $return_url = 'send_etokenTest.php?token='.$token;
            break;
        default:
            $return_url = 'send_other.php?token='.$token;
            break;
    }
} else {
    $_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.';
    header("Location: index.php");
    exit();
}
//2021.06.21 키오스크 일때는 최소 전송 해제...
if($kioskTempCheck == 'kiosk'){
    //비어있을때 0원처리 아래 hidden에서 '' 비어있는 값 보냄.
    if(empty($_POST['amount'])){
        $_POST['amount'] = 0;
    }
}
else{
    if ( empty($_POST['address']) || empty($_POST['amount']) ) {
        $_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.';
        header("Location: ".$return_url);
        exit();
    }
}


require_once 'includes/header.php';

?>
    <script src="js/other.js" type="text/javascript"></script>
    <link  rel="stylesheet" href="css/other.css"/>

    <div id="set_transferpw_frm_send"  class="set_transferpw_frm">
        <div class="top">
            <p class="subject"><?php echo !empty($langArr['transfer_pw_send_subject']) ? $langArr['transfer_pw_send_subject'] : 'payment password'; ?></p>

            <p id="stf_message" class="explain2 none">
                <span id="stf_message_s1" class="none"><?php echo !empty($langArr['transfer_pw_message1']) ? $langArr['transfer_pw_message1'] : 'Passwords do not match.'; ?></span>
                <span id="stf_message_s2" class="none"><?php echo !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.'; ?></span>
                <span id="stf_message_s3" class="none"><?php echo !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred'; ?></span>
                <span id="stf_message_s4" class="none"><?php echo !empty($langArr['send_message5']) ? $langArr['send_message5'] : 'A transfer password is required to transfer. Please use it after setting the transfer password.'; ?></span>
                <span id="stf_message_s6" class="none"><?php echo !empty($langArr['transfer_pw_message3']) ? $langArr['transfer_pw_message3'] : 'The number of payment password inputs has been exceeded. Please try again the next day.'; ?></span>
            </p>

            <p id="explain1" class="explain"><?php echo !empty($langArr['transfer_pw_send_text1']) ? $langArr['transfer_pw_send_text1'] : 'Please enter your payment password.'; ?></p>

            <div class="password_area">
                <?php
                for($i = 0; $i < $n_transfer_pw_length; $i++) {
                    ?><span id="pass_area_<?php echo $i; ?>"><img src="images/icons/pass_input_n.png" alt="password" /></span><?php
                } // foreach
                ?>
            </div>
            <div id="stf_message_btn" class="none">
                <a href="javascript:;" title="move" data-num="">
                    <img src="images/icons/top_subject_back_btn.png" alt="move" />
                    <span></span>
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


        function stf_message_box_setting(data_num, btn_text, msg_index) {
            $("#stf_message_btn a").attr({'data-num':data_num});
            $("#stf_message_btn a span").html(btn_text);
            $("#set_transferpw_frm_send #stf_message_s"+msg_index).removeClass('none');
            $("#set_transferpw_frm_send #explain1").addClass('none');
            $("#set_transferpw_frm_send #stf_message").removeClass('none');
            $("#set_transferpw_frm_send #stf_message_btn").removeClass('none');
        }

        function stf_num_result(page, status) {
            if (status == 'reload') {

                pass_length = 0;
                pass = '';
                document.pass_frm.pas1.value = pass;
                $("#stf_message").addClass('none');
                $("#stf_message_btn").addClass('none');
                $(".password_area img").attr('src','images/icons/pass_input_n.png');
                $("#explain1").removeClass('none');
                stf_num_re();

            } else if ( status == 'move') {
                location.href = page + '.php';
            }
        } //

        $(function(){
            var password_length = $("#transfer_pw_length").val();


            $("#stf_message_btn").on('click tap', function(){
                var num = $("#stf_message_btn a").attr('data-num');
                if (num == 'fail') {
                    stf_num_result('set_transferpw_frm_send', 'reload');
                } else if (num == 'set') {
                    stf_num_result('profile', 'move');
                } else if (num == 'main') {
                    stf_num_result('index', 'move');
                }
            });

            $("#set_transferpw_frm_send .number p").on('click tap', function(){
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

                        var mid = $("#mid").val();
                        $.ajax({
                            url : 'send.pro.php',
                            type : 'POST',
                            dataType : 'json',
                            data : {mode: 'get_transfer_pw2', mid : mid, pas1 : pass},
                            success : function(resp){
                                if (resp.result == 'success') {
                                    document.pass_frm.submit();
                                } else if (resp.result == 'over') {
                                    stf_message_box_setting('main', "<?php echo !empty($langArr['send_sms_message6']) ? $langArr['send_sms_message6'] : 'HOME'; ?>", '6');

                                } else if (resp.result == 'fail' ) {
                                    stf_message_box_setting(resp.result, "<?php echo !empty($langArr['transfer_pw_btn2']) ? $langArr['transfer_pw_btn2'] : 'Back to previous level'; ?>", '1');

                                } else if ( resp.result == 'set') {
                                    stf_message_box_setting(resp.result, "<?php echo !empty($langArr['change_transfer_pass_set']) ? $langArr['change_transfer_pass_set'] : 'Set transmission password'; ?>", '4');
                                } else {
                                    stf_message_box_setting(resp.result, "<?php echo !empty($langArr['transfer_pw_btn2']) ? $langArr['transfer_pw_btn2'] : 'Back to previous level'; ?>", '2');
                                }
                                // none : 잘못된접근, set:셋팅이필요함, fail:비밀번호다름, success:성공
                            },
                            error : function(resp){
                                stf_message_box_setting('none', "<?php echo !empty($langArr['transfer_pw_btn2']) ? $langArr['transfer_pw_btn2'] : 'Back to previous level'; ?>", '3');
                            }
                        });
                    }
                }
                return false; // 브라우저에 따라서 중복실행하는 경우 방지
            });
        });
    </script>
    <form method="post" name="pass_frm" action="<?php echo $return_url; ?>">
        <input type="hidden" name="pas1" id="pas1" value="" />
        <input type="hidden" name="address" value="<?php echo !empty($_POST['address']) ? $_POST['address'] : ''; ?>" />
        <input type="hidden" name="amount" value="<?php echo !empty($_POST['amount']) ? $_POST['amount'] : ''; ?>" />

        <input type="hidden" name="mid" id="mid" value="<?php echo !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>" />
        <input type="hidden" name="transfer_pw_length" id="transfer_pw_length" value="<?php echo $n_transfer_pw_length; ?>" />

        <input type="hidden" name="p_token" value="<?php echo $token; ?>" />
        <input type="hidden" name="p_kind" value="<?php echo $kind; ?>" />
        <input type="hidden" name="p_payment_no" value="<?php echo $payment_no; ?>" />

    </form>
<?php include_once 'includes/footer.php'; ?>