<?php
exit();
// Page in use
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once './config/new_config.php';
//kcp 본인 인증
include_once ("/var/www/ctc/wallet/kcp/kcp_config.php");

use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Util as walletUtil;
use wallet\common\Filter as walletFilter;
use \League\Plates\Engine as plateTemplate;
use \League\Plates\Extension\Asset as plateTemplateAsset;
require(BASE_PATH . '/vendor/autoload.php');

//echo "ajay";
//echo md5(1);
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
use Web3\Utils;

$userip = new_getUserIpAddr();

$db = getDbInstance();

//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
// config/new_config.php
$adminAccountWalletAddress = $n_master_wallet_address;
$adminAccountWalletPassword = $n_master_wallet_pass;

if(empty($_SESSION['lang'])) {
    $_SESSION['lang'] = "ko";
}
$langFolderPath = file_get_contents("lang/".$_SESSION['lang']."/index.json");
$langArr = json_decode($langFolderPath,true);

function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
        '8','9','A','B','C','D','E','F');
    $hexval = '';
    while($number != '0')
    {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}




// blocked IP Code, 20.10.20
if ( !empty($userip) ) {
    $blocked_ip_count = 0;
    $db = getDbInstance();
    $db->where("ip_name", $userip);
    $blocked_ip_count = $db->getValue('blocked_ips', 'count(*)');
    if ($blocked_ip_count > 0) {
        header('location: login.php.php');
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $email 	= filter_input(INPUT_POST, 'email');
    $passwd = filter_input(INPUT_POST, 'passwd');
    $phone = filter_input(INPUT_POST, 'phone');
    $remember = filter_input(INPUT_POST, 'remember');
    $passwd5	=  md5($passwd);
    $phone = str_replace("-","",$phone);
    $phone =  str_replace(" ","",$phone); //filter_input(INPUT_POST, 'passwd');
    //자동 로그인 기본 값 처리 START
    if (!isset($remember)) {
        $remember = false;
    }

    if ($remember >= 1) {
        $cookieAutoLoginKey = false;
    }
    else {
        //자동로그인 쿠키가 있는 상태에서..., 비밀번호를 그냥 입력 했을때는 자동로그인으로 간주 하지 않음.
        if(isset($_COOKIE['password'])){
            if($_COOKIE['password'] == $passwd){
                $cookieAutoLoginKey = $_COOKIE['password'];
            }
            else{
                $cookieAutoLoginKey = false;
            }
        }
        else{
            $cookieAutoLoginKey = false;
        }
    }



    //자동 로그인 기본 값 처리 END

    //Get DB instance. function is defined in config.php
    $db = getDbInstance();
    $email = !empty($email) ? $email : $phone;
    $email = trim($email);

    //레거시 db 불러오는게.. array 0을 불러와서 FetchAll으로..처리
    $row = false; // memeber 정보 담는 변수... 초기값 false
    $walletDb = walletDb::singletonMethod();
    $walletDb = $walletDb->init();
    $utill = walletUtil::singletonMethod();

    //자동로그인 인지 아닌지 판별. START
    try{
        if ($cookieAutoLoginKey) {

            $cookieAutoLoginKey = $passwd;

            //var_dump($cookieAutoLoginKey);

            $cookieAutoLoginKey = explode('|', $cookieAutoLoginKey);

            if (count($cookieAutoLoginKey) != 2) {
                throw new Exception('비정상적인 요청 입니다.', 9999);
            }
            if ($cookieAutoLoginKey[0] != 'autologin') {
                throw new Exception('비정상적인 요청 입니다.', 9999);
            }

            $memberInfo = $walletDb->createQueryBuilder()
                ->select('*')
                ->from('user_autologin')
                ->where('ua_key = ?')
                ->setParameter(0, $cookieAutoLoginKey[1])
                ->execute()->fetch();
            if (!$memberInfo) {
                throw new Exception('비정상적인 요청 입니다.(1)', 9999);
            }

            //autologin table
            //발급일 30일 이내 인지 $memberInfo['ua_datetime']
            //일치하지 않으면,, 딜리트문...

            //쿠키 passwd 삭제
            $targetUnixDatetime = strtotime($utill->getDateSql() . '+30 days');
            $dbUnixDatetime = strtotime($memberInfo['ua_datetime']);

            if ($dbUnixDatetime > $targetUnixDatetime) {
                $walletDb->createQueryBuilder()
                    ->delete('user_autologin')
                    ->where('ua_admin_accounts_id = ?')
                    ->setParameter(0, $memberInfo['ua_admin_accounts_id'])
                    ->execute();

                unset($_COOKIE['passwd']);
                setcookie('passwd', '', time() - 3600);
                throw new Exception('만료 된 KEY 값 입니다. 자동로그인을 다시 설정해 주세요.', 9999);
            }

            //key 값과 session id 값 ip값 세개다 일치
            $hash = hash('sha256', $memberInfo['ua_session_id'] . $memberInfo['ua_ip'], false);

            if ($hash != $cookieAutoLoginKey[1]) {
                throw new Exception('비정상적인 요청 입니다.(2)', 9999);
            }

            $row = $walletDb->createQueryBuilder()
                ->select('*')
                ->from('admin_accounts')
                ->where('id = ?')
                ->andWhere('email = ?')
                ->setParameter(0, $memberInfo['ua_admin_accounts_id'])
                ->setParameter(1, $email)
                ->execute()->fetchAll();
        }
    }
    catch (Exception $e){

        $data_to_login = [];
        $data_to_login['email'] = $email;
        $data_to_login['login_result'] = 'F';
        $data_to_login['msg'] = $e->getMessage();
        $data_to_login['ip'] = $userip;
        $login_logs_id = $db->insert('login_logs', $data_to_login);

        // 일치하는 아이디 없음
        $_SESSION['login_failure'] = $langArr['commonApiStringDanger01'];
        header('Location:login.php.php');
        exit();
    }
    //자동로그인 인지 아닌지 판별. END

/*
 *
 * 패스워드 정책 변경,
 * 1. 구체계 조회
 * 2. 신체계 조회
 */

    //비밀번호 구 체계 먼저 조회,
    if(!$row) {
        $row = $walletDb->createQueryBuilder()
            ->select('*')
            ->from('admin_accounts')
            ->where('email = ?')
            ->andWhere('passwd = ?')
            ->andWhere('passwd_new is NULL')
            ->andWhere('passwd_datetime is NULL')
            ->setParameter(0, $email)
            ->setParameter(1, $passwd5)
            ->execute()->fetchAll();
    }
    //구 체계로 정보 조회에 실패 하였다면, 신 체계로 조회
    if(!$row){
        //salt 값을 가져 와야해서 email로 조회
        $memberInfo = $walletDb->createQueryBuilder()
            ->select('passwd_new, passwd_salt, passwd_datetime')
            ->from('admin_accounts')
            ->where('email = ?')
            ->andWhere('passwd_new is not NULL')
            ->andWhere('passwd_datetime is not NULL')
            ->setParameter(0,$email)
            ->execute()->fetch();

        if($memberInfo){
            $hash = hash('sha512',trim($memberInfo['passwd_salt'].$passwd));
            if(hash_equals($hash,$memberInfo['passwd_new'])){
                $row = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('admin_accounts')
                    ->Where('email = ?')
                    ->setParameter(0,$email)
                    ->execute()->fetchAll();
            }
        }
    }

    //위 체계에서 조회가 안되었다면... 휴면 계정 table 다시 조회 START 2021.06.14 by.OJT
    //여러번 반복 되는데.. auth class로 나중에 따로 분리 할 것
    //비밀번호 구 체계 먼저 조회,
    if(!$row){
        //휴면 계정은 패스워드가 맞으면... 애내끼리.. 처리를...?
        $row = $walletDb->createQueryBuilder()
            ->select('id, passwd, passwd_new, passwd_salt, passwd_datetime')
            ->from('admin_accounts_sleep')
            ->where('email = ?')
            ->andWhere('passwd = ?')
            ->andWhere('passwd_new is NULL')
            ->andWhere('passwd_datetime is NULL')
            ->setParameter(0,$email)
            ->setParameter(1,$passwd5)
            ->execute()->fetchAll();

        //구 체계로 정보 조회에 실패 하였다면, 신 체계로 조회
        if(!$row){
            //salt 값을 가져 와야해서 email로 조회
            $memberInfo = $walletDb->createQueryBuilder()
                ->select('passwd, passwd_new, passwd_salt, passwd_datetime')
                ->from('admin_accounts_sleep')
                ->where('email = ?')
                ->andWhere('passwd_new is not NULL')
                ->andWhere('passwd_datetime is not NULL')
                ->setParameter(0,$email)
                ->execute()->fetch();

            if($memberInfo){
                $hash = hash('sha512',trim($memberInfo['passwd_salt'].$passwd));
                if(hash_equals($hash,$memberInfo['passwd_new'])){
                    $row = $walletDb->createQueryBuilder()
                        ->select('*')
                        ->from('admin_accounts_sleep')
                        ->Where('email = ?')
                        ->setParameter(0,$email)
                        ->execute()->fetchAll();
                }
            }
        }

        //휴면 계정이 있을 때... 휴면 계정 해제 페이지 노출
        if($row){
            ob_start();
            require_once WALLET_PATH.'/includes/header.php';
            $bufferData['header'] = ob_get_contents();
            ob_clean();
            require_once WALLET_PATH.'/includes/footer.php';
            $bufferData['footer'] = ob_get_contents();
            ob_end_clean();

            //본인인증 결과 페이지에서 사용 할 고유 id를 세션에 담아준다.
            //why...? 본인인증 절차가 히든 프레임으로.. form 으로 쏴주는 중....
            $_SESSION['tempUserId'] = $row[0]['id'];

            //내국인인 경우 외국인 경우 분기 START
            $authInfo = array();
            if($row[0]['auth_local_code'] == 'Kor'){
                $authInfo['type'] = 'realNameAuth';
                $authInfo['target'] = $row[0]['n_phone'];
            }
            else{
                if($row[0]['register_with'] == 'email'){
                    $authInfo['type'] = 'emailCode';
                    $authInfo['target'] = $row[0]['email'];
                }
                else{
                    //phone인 경우, 추가 이메일 컬럼이 비어 있으면, phone로 코드를 보낸다.
                    if(empty($row[0]['wallet_phone_email'])){
                        $authInfo['type'] = 'phoneCode';
                        $authInfo['target'] = $row[0]['n_phone'];
                    }
                    else{
                        $authInfo['type'] = 'emailCode';
                        $authInfo['target'] = $row[0]['wallet_phone_email'];
                    }
                }
            }
            //내국인인 경우 외국인 경우 분기 END

            $templates = new plateTemplate(WALLET_PATH.'/skin/sleepUser', 'html');
            $templates->loadExtension(new plateTemplateAsset(WALLET_PATH.'/skin/common/assets',false));
            $randerData = $templates->render('sleepRestoreForm', [
                'info' => [
                    'htmlHeader'=>$bufferData['header'],
                    'htmlFooter'=>$bufferData['footer'],
                    'lang'=>$langArr,
                    'authInfo' =>$authInfo,
                    'kcp' =>['siteid'=>$g_conf_web_siteid,'sitecd'=>$g_conf_site_cd,'returl'=>'https://cybertronchain.com/wallet2/auth.pro.res_sleepUser.php'],
                    'asstsUrl'=>WALLET_URL.'/skin/common/assets',
                ]
            ]);
            echo($randerData);
//            var_dump($row);
            exit();
        }
    }
    //휴면 계정 table 조회 END

    //탈퇴 신청을 한 회원 인지 조회 START
    if($row){
        //탈퇴 신청을 한 회원인 경우는 로그인을 막음.
        //탈퇴 신청 시 신청자는 세션을 파괴함.
        $withDrawalInfo = $walletDb->createQueryBuilder()
            ->select('wu_id')
            ->from('withdrawal_user')
            ->where('wu_accounts_id = ?')
            ->andWhere('wu_type = ?')
            ->andWhere('wu_status = ?')
            ->setParameter(0,$row[0]['id'])
            ->setParameter(1,'asset')
            ->setParameter(2,'PENDING')
            ->execute()->fetch();
        //var_dump($withDrawalInfo);
        if($withDrawalInfo){
            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'F';
            $data_to_login['msg'] = 'Id mismatch';
            $data_to_login['ip'] = $userip;
            $db = getDbInstance();
            $login_logs_id = $db->insert('login_logs', $data_to_login);

            // 일치하는 아이디 없음
            $_SESSION['login_failure'] = $langArr['withdrawalErrorString04'];
            header('Location:login.php.php');
            exit();
        }
    }
    //탈퇴 신청을 한 회원 인지 조회 END

    /*
 * 신체계 적용으로 구 체계 구문 주석 처리
    $db->where ("email", $email);
    $db->where ("passwd", $passwd5);
    $row = $db->get('admin_accounts');
    if ($db->count >= 1) {
*/
    if (count($row) >= 1) {
        //auth KEY 생성
        $sessionId = session_id();
        //$userip
        if (empty($userip)) {
            $nowIp = '999.999.999.999';
        } else {
            $nowIp = $userip;
        }

        //자동 로그인 생성 START
        try{
            if ($remember >= 1) {
                $cookieSessionId = session_id();
                //자동로그인 키 발급,
                //admin_id 와 session id를 조회해서 있으면, 기존 레코드 제거
                $keyTableInfo = $walletDb->createQueryBuilder()
                    ->select('ua_id')
                    ->from('user_autologin')
                    ->where('ua_admin_accounts_id =?')
                    ->andWhere('ua_session_id =?')
                    ->setParameter(0,$row[0]['id'])
                    ->setParameter(1,$cookieSessionId)
                    ->execute()->fetch();
                if($keyTableInfo){
                    //딜리트문....
                    $delProc = $walletDb->createQueryBuilder()
                        ->delete('user_autologin')
                        ->where('ua_id = ?')
                        ->setParameter(0,$keyTableInfo['ua_id'])
                        ->execute();

                    setcookie('passwd', '', time() - 3600);

                    if(!$delProc){
                        throw new Exception('삭제를 실패 하였습니다.',9999);
                    }
                }

                //키 값 생성
                $hash = hash('sha256', $cookieSessionId . $nowIp, false);
                //발급일 생성
                //$dateTime = (new DateTime('now'))->format('Y-m-d H:i:s');
                $dateTime = $utill->getDateSql();

                //session id 값 생성
                $insertProc = $walletDb->createQueryBuilder()
                    ->insert('user_autologin')
                    ->setValue('ua_admin_accounts_id', '?')
                    ->setValue('ua_session_id', '?')
                    ->setValue('ua_ip', '?')
                    ->setValue('ua_key', '?')
                    ->setValue('ua_datetime', '?')
                    ->setParameter(0, $row[0]['id'])
                    ->setParameter(1, $cookieSessionId)
                    ->setParameter(2, $nowIp)
                    ->setParameter(3, $hash)
                    ->setParameter(4, $dateTime)
                    ->execute();
                if (!$insertProc) {
                    //$this->logger->error('upload error');
                    throw new Exception('로그인에 실패하였습니다.', 406);
                }
                setcookie('email', $email, time() + 60 * 60 * 24 * 30); //레거시 소스
                setcookie('phone', $email, time() + 60 * 60 * 24 * 30); // 레거시 소스
                setcookie('password', 'autologin|' . $hash, time() + 60 * 60 * 24 * 30);
            }

        }
        catch (Exception $e){

            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'F';
            $data_to_login['msg'] = $e->getMessage();
            $data_to_login['ip'] = $userip;
            $login_logs_id = $db->insert('login_logs', $data_to_login);

            // 일치하는 아이디 없음
            $_SESSION['login_failure'] = $langArr['commonApiStringDanger01'];
            header('Location:login.php.php');
            exit();
        }
        //자동 로그인 생성 END

        //CANCEL
        $sleepMemberInfo = $walletDb->createQueryBuilder()
            ->select('sue_accounts_id')
            ->from('sleep_user_email')
            ->where('sue_accounts_id = ?')
            ->andWhere('sue_transfer = ?')
            ->setParameter(0,$row[0]['id'])
            ->setParameter(1,'WAIT')
            ->execute()->fetch();
        if($sleepMemberInfo){
            $walletDb->createQueryBuilder()
                ->update('sleep_user_email')
                ->set('sue_transfer','?')
                ->where('sue_accounts_id = ?')
                ->andWhere('sue_transfer = ?')
                ->setParameter(0,'CANCEL')
                ->setParameter(1,$sleepMemberInfo['sue_accounts_id'])
                ->setParameter(2,'WAIT')
                ->execute();
        }


        // PC���� ����� ���ٺҰ�, No user access from PC, 20.09.04
        //if ( $row[0]['id'] != '5885' ) {
        if ( $row[0]['id'] != '5885' && $row[0]['id'] != '10086' ) {
            // 10086 : �Ͻ�
            if ( stristr($_SERVER['HTTP_USER_AGENT'], 'android-web-view') == FALSE && stristr($_SERVER['HTTP_USER_AGENT'], 'ios-web-view') == FALSE && $row[0]['admin_type'] != 'admin' ) {
                $_SESSION['login_failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.';
                header('location: login.php.php');
                exit();
            }
        }


        if ( !empty($row[0]['login_or_not']) && $row[0]['login_or_not'] == 'N' ) {

            // 20.09.04
            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'F';
            $data_to_login['msg'] = 'Accounts unable to log in';
            $data_to_login['ip'] = $userip;
            $db = getDbInstance();
            $login_logs_id = $db->insert('login_logs', $data_to_login);


            $_SESSION['login_failure'] = !empty($langArr['login_failed_message1']) ? $langArr['login_failed_message1'] : "Login failed. Please contact customer service.";
            header('Location:login.php.php');
            exit;
        }

        $emailVerify = $row[0]['email_verify'];
        $registerWith = $row[0]['register_with'];
        $userSendApproved = $row[0]['sendapproved'];
        if($emailVerify=="N"){
            if($registerWith=="email") {
                $_SESSION['login_failure'] = $langArr['plz_v_em_lon'];
            }
            else {
                //$_SESSION['login_failure'] = $langArr['plz_v_ph_lon']."<a href='phoneverify.php'>".$langArr['cli_to_verify']."</a>";
                $_SESSION['login_failure'] = $langArr['plz_v_ph_lon'];
            }


            // 20.09.04
            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'F';
            $data_to_login['msg'] = 'Unauthorized Id account(EmailVerify)';
            $data_to_login['ip'] = $userip;
            $db = getDbInstance();
            $login_logs_id = $db->insert('login_logs', $data_to_login);

            header('Location:login.php.php');
            exit;
        }


        // 20.10.14
        $device = '';
        $post_dev_id = '';
        $post_dev_id1 = '';
        $post_dev_id2 = '';
        $post_dev_id3 = '';
        $app_name = 'wallet';
        $field_name = 'devId';
        if ( !empty($_POST['dev_id']) ) {
            $device = new_get_device($_POST['dev_id']);
            $post_dev_id1 = $_POST['dev_id'];
            $post_dev_id = $_POST['dev_id'];
            $app_name = 'wallet';
            $field_name = 'devId';
        }
        if ( !empty($_POST['dev_id2']) ) {
            $device = new_get_device($_POST['dev_id2']);
            $post_dev_id2 = $_POST['dev_id2'];
            $post_dev_id = $_POST['dev_id2'];
            $app_name = 'barrybarries';
            $field_name = 'devId2';
        }
        if ( !empty($_POST['dev_id3']) ) {
            $device = new_get_device($_POST['dev_id3']);
            $post_dev_id3 = $_POST['dev_id3'];
            $post_dev_id = $_POST['dev_id3'];
            $app_name = 'coinibt';
            $field_name = 'devId3';
        }

        if ( $row[0]['admin_type'] != 'admin' ) {

            // ���������� ��� => DeviceId ������ �ٸ� ��⿡ ��ϵǾ� �ִ��� �α��� �����ϰ� �� ��!
            if ( $row[0]['id_auth'] != 'Y' || $row[0]['auth_phone'] != $post_dev_id ) { // 21.04.07

                // devId �ߺ�üũ 20.10.12
                // app���� device������ �Ѿ�Դµ�, db�� ������ �Ǿ� ���� ���� ��� => �ߺ�üũ
                // wallet app���� ����� : admin_accounts.devId ���� ���� ���
                // barrybarries app���� ����� : admin_accounts.devId2 ���� ���� ���
                // coinibt app���� ����� : admin_accounts.devId3 ���� ���� ���
                if ( !empty($post_dev_id) && empty($row[0][$field_name]) && !empty($_POST['dev_use']) && $_POST['dev_use'] == 'Y' ) {

                    // �ߺ�üũ
                    $db = getDbInstance();

                    // ���� ������ ���������� post�� �Ѿ�� device ������ ��ϵǾ� �ִ� ���
                    $dev_count = $db->rawQueryValue("SELECT count(*) from admin_accounts where id != '".$row[0]['id']."' and device='".$device."' and ( devId='".$post_dev_id."' or devId2='".$post_dev_id."' or devId3='".$post_dev_id."')");
                    if ( $dev_count[0] > 0 ) {

                        // 20.10.14
                        $data_to_login = [];
                        $data_to_login['user_id'] = $row[0]['id'];
                        $data_to_login['email'] = $email;
                        $data_to_login['app_name'] = $app_name;
                        if ( !empty($device) ) {
                            $data_to_login['device'] = $device;
                        }
                        $data_to_login['devId'] = $post_dev_id;
                        $data_to_login['msg'] = 'Device already registered';
                        $data_to_login['ip'] = $userip;
                        $data_to_login['created_at'] = date("Y-m-d H:i:s");
                        $db = getDbInstance();
                        $logs_id = $db->insert('login_device_logs', $data_to_login);

                        $_SESSION['login_failure'] = !empty($langArr['login_device_id_message4']) ? $langArr['login_device_id_message4'] : 'Only one ID can be registered on one device.'; // �ϳ��� ��ġ�� 1���� ���̵� ����� �� �ֽ��ϴ�.
                        header('Location:login.php.php');
                        exit;
                    }

                    // 20.10.13
                    // device�� �ȵ���̵� && post�� �Ѿ�� device ������ 010���� �����ϴ� �ڵ��� ��ȣ�� ��� :
                    if (strlen($post_dev_id) == 11 && substr($post_dev_id, 0, 3) == '010' && $device == 'android' ) {
                        $device_id_phone = substr($post_dev_id, 1);

                        // �������� �� ��� : �ٸ��� ���� / ������ �Ѿ
                        //if ( $row[0]['id_auth'] == 'Y' && stristr($row[0]['auth_phone'], $device_id_phone) == FALSE ) {
                        if ( $row[0]['id_auth'] == 'Y' ) {
                            if ( $row[0]['auth_phone'] != $post_dev_id ) {
                                // 20.10.14
                                $data_to_login = [];
                                $data_to_login['user_id'] = $row[0]['id'];
                                $data_to_login['email'] = $email;
                                $data_to_login['app_name'] = $app_name;
                                if ( !empty($device) ) {
                                    $data_to_login['device'] = $device;
                                }
                                $data_to_login['devId'] = $post_dev_id;
                                $data_to_login['msg'] = 'Trying to register with another device1';
                                $data_to_login['ip'] = $userip;
                                $data_to_login['created_at'] = date("Y-m-d H:i:s");
                                $db = getDbInstance();
                                $logs_id = $db->insert('login_device_logs', $data_to_login);

                                $_SESSION['login_failure'] = !empty($langArr['login_device_id_message6']) ? $langArr['login_device_id_message6'] : 'You can only log in from your own phone.';
                                header('Location:login.php.php');
                                exit;
                            }

                            // �������� ���� ��� : �ڵ��������� �� ��ȣ email�� ������ ���� / ������ �Ѿ
                        } else {
                            if ( $row[0]['register_with'] == 'phone' && stristr($row[0]['email'], $device_id_phone) == FALSE ) {
                                // 20.10.14
                                $data_to_login = [];
                                $data_to_login['user_id'] = $row[0]['id'];
                                $data_to_login['email'] = $email;
                                $data_to_login['app_name'] = $app_name;
                                if ( !empty($device) ) {
                                    $data_to_login['device'] = $device;
                                }
                                $data_to_login['devId'] = $post_dev_id;
                                $data_to_login['msg'] = 'Trying to register with another device2';
                                $data_to_login['ip'] = $userip;
                                $data_to_login['created_at'] = date("Y-m-d H:i:s");
                                $db = getDbInstance();
                                $logs_id = $db->insert('login_device_logs', $data_to_login);

                                $_SESSION['login_failure'] = !empty($langArr['login_device_id_message6']) ? $langArr['login_device_id_message6'] : 'You can only log in from your own phone.';
                                header('Location:login.php.php');
                                exit;
                            }
                        }
                        //if ( $row[0]['id_auth'] == 'Y' || $row[0]['register_with'] == 'phone' ) {
                        //	if ( stristr($row[0]['auth_phone'], $device_id_phone) == FALSE && stristr($row[0]['email'], $device_id_phone) == FALSE ) {
                        //		$_SESSION['login_failure'] = !empty($langArr['login_device_id_message6']) ? $langArr['login_device_id_message6'] : 'You can only log in from your own phone.';
                        //		header('Location:login.php.php');
                        //		exit;
                        //	}
                        //}
                    }
                }


                // add device id : 20.09.08 ----- 20.10.12
                // -----------------------------------------------------��ϵ� ���θ� �α��� ����
                //if ( !empty($row[0]['devId']) && !empty($row[0]['device']) && $row[0]['devId'] != $_POST['dev_id'] ) {
                if ( ( ( $app_name == 'wallet' && !empty($row[0]['devId']) ) || ( $app_name == 'barrybarries' && !empty($row[0]['devId2']) ) || ( $app_name == 'coinibt' && !empty($row[0]['devId3']) ) ) && !empty($row[0]['device']) ) { // DB devId, devId2, devId3�� �ϳ��� ���� �ְ� && DB device ���� �ִ� ���
                    if ( $row[0]['devId'] != $post_dev_id && $row[0]['devId2'] != $post_dev_id && $row[0]['devId3'] != $post_dev_id ) { // post�� �Ѿ�� ���� DB���� ã���� ���� ���
                        // 20.10.14
                        $data_to_login = [];
                        $data_to_login['user_id'] = $row[0]['id'];
                        $data_to_login['email'] = $email;
                        $data_to_login['app_name'] = $app_name;
                        if ( !empty($device) ) {
                            $data_to_login['device'] = $device;
                        }
                        $data_to_login['devId'] = $post_dev_id;
                        $data_to_login['msg'] = 'Other Device Login';
                        $data_to_login['ip'] = $userip;
                        $data_to_login['created_at'] = date("Y-m-d H:i:s");
                        $db = getDbInstance();
                        $logs_id = $db->insert('login_device_logs', $data_to_login);

                        $_SESSION['login_failure'] = !empty($langArr['login_device_id_message2']) ? $langArr['login_device_id_message2'] : 'Please log in with the registered device.';
                        header('Location:login.php.php');
                        exit;
                    }
                }

            }

        }

        ///*
        // 20.10.20 : ������ ���� ��� ����
        // 21.06.04, YMJ, ���� ����
        $last_login_date_tmp = '';
        if ( !empty($row[0]['last_login_at']) ) {
            $last_login_date_tmp = explode(' ', $row[0]['last_login_at']);
            $last_login_date_tmp = $last_login_date_tmp[0];
        }
        if ( empty($row[0]['last_login_at']) || ( $last_login_date_tmp && $last_login_date_tmp != date("Y-m-d") ) ) { // �α����� �ѹ��� ���߰ų� ������ �α��� ��¥�� ������ �ƴϸ� ����, 20.10.16
            // 20.09.04
            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'S';
            $data_to_login['ip'] = $userip;
            //	$db = getDbInstance();
            $login_logs_id = $db->insert('login_logs', $data_to_login);
        }
        //*/

        $_SESSION['user_logged_in'] = TRUE;
        $_SESSION['admin_type'] = $row[0]['admin_type'];
        $_SESSION['user_id'] = $row[0]['id'];
        if( !empty($_POST['app_version']) ) {
            $_SESSION['app_version'] = $_POST['app_version'];
        }
        if ( !empty($app_name) ) { // 20.11.13
            $_SESSION['app_name'] = $app_name;
        }
        $userId = $row[0]['id'];
        $userRole = $row[0]['admin_type'];
        $userDbEmail =  $row[0]['email'];

        //비밀번호 정책을 변경하지 않은경우, 변경 유도
        if(empty($row[0]['passwd_datetime']) || empty($row[0]['passwd_new'])) {
            //레거시 skin에 합치기..
            ob_start();
            require_once WALLET_PATH.'/includes/header.php';
            $bufferData['header'] = ob_get_contents();
            ob_clean();
            require_once WALLET_PATH.'/includes/footer.php';
            $bufferData['footer'] = ob_get_contents();
            ob_end_clean();

            $templates = new plateTemplate(WALLET_PATH.'/skin/password', 'html');
            $templates->loadExtension(new plateTemplateAsset(WALLET_PATH.'/skin/common/assets',false));
            $randerData = $templates->render('changeForm', [
                'info' => [
                    'htmlHeader'=>$bufferData['header'],
                    'htmlFooter'=>$bufferData['footer'],
                    //'data'=>$returnData,
                    'lang'=>$langArr,
                    'asstsUrl'=>WALLET_URL.'/skin/common/assets',
                ]
            ]);
            echo($randerData);
            exit();
        }

        //레거시 자동로그인 코드.
        /*
        if ($remember) {
            setcookie('email', $email, time() + (86400 * 90), "/");
            setcookie('phone', $email, time() + (86400 * 90), "/");
            setcookie('password', $passwd, time() + (86400 * 90), "/");
        }
        */



        $updateArr = [] ;

        // ���� : 20.11.04
        if ( !empty($_POST['dev_use']) && $_POST['dev_use'] == 'Y' ) {
            if ( !empty($device) && empty($row[0]['device']) ) {
                $updateArr['device'] = $device;
            }
            if ( !empty($post_dev_id1) && empty($row[0]['devId']) ) {
                $updateArr['devId'] = $post_dev_id1;
            }
            if ( !empty($post_dev_id2) && empty($row[0]['devId2']) ) {
                $updateArr['devId2'] = $post_dev_id2;
            }
            //if ( !empty($post_dev_id3) && empty($row[0]['devId3']) ) {
            //	$updateArr['devId3'] = $post_dev_id3;
            //}


        }

        // 21.04.07
        // ���������� ������̰�, �������� ��ȣ�� �α��� �õ��� ��ȣ�� ������ ������ ������Ʈ
        if ( !empty($post_dev_id) && $row[0]['id_auth'] == 'Y' && $row[0]['auth_phone'] == $post_dev_id ) {
            if ( !empty($device) ) {
                $updateArr['device'] = $device;
            }
            if ( !empty($post_dev_id1) ) {
                $updateArr['devId'] = $post_dev_id1;
            }
            if ( !empty($post_dev_id2) ) {
                $updateArr['devId2'] = $post_dev_id2;
            }
            //if ( !empty($post_dev_id3) ) {
            //	$updateArr['devId3'] = $post_dev_id3;
            //}
        }

        // 20.10.15
        if ( !empty($_POST['onesignal_id'] )  && empty($row[0]['onesignal_id']) ) {
            $updateArr['onesignal_id'] = $_POST['onesignal_id'];
        }
        if ( !empty($_POST['onesignal_id2'] ) && empty($row[0]['onesignal_id2']) ) {
            $updateArr['onesignal_id2'] = $_POST['onesignal_id2'];
        }

        // 2020-07-07 15:04 (KST)
        $db = getDbInstance();
        $db->where("id", $userId);
        $updateArr['last_login_at'] =  date("Y-m-d H:i:s");
        $last_id = $db->update('admin_accounts', $updateArr);


        /* check for ctc token available */
        $walletAddress = $row[0]['wallet_address'];
        $sendApproved = $row[0]['sendapproved'];
        $asmSendApproved = $row[0]['asm_send_approved'];
        $getEthBalance = 0;
        $coinBalance = 0;
        //$web3 = new Web3('http://127.0.0.1:8545/');
        $web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // config/new_config.php
        $eth = $web3->eth;

        $gasPriceInWei = 40000000000;
        $eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
            if ( !empty($result) ) {
                $gasPriceInWei = $result->toString();
            }
        });

        $gas = 45000;
        $totalAmountInWei = $gasPriceInWei*3*$gas;
        $totalAmountInEth = $totalAmountInWei/1000000000000000000; // 1

        $gasPriceInWei = "0x".dechex($gasPriceInWei);
        // create walletAddress if not exists start
        $personal = $web3->personal;
        if(empty($walletAddress)){
            $walletAddress = '';
            try {
                $personal->newAccount($email.$n_wallet_pass_key, function ($err, $account) use (&$walletAddress) { // $n_wallet_pass_key : config/new_config.php
                    if ($err !== null) {
                        //echo 'Error: ' . $err->getMessage();
                        throw new Exception($err->getMessage(), 1);
                    }
                    else {
                        $walletAddress = $account;
                    }
                });
            } catch (Throwable $e) {
                new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            } catch (Exception $e) {
                new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            }

            $db = getDbInstance();
            $db->where("id", $userId);
            $updateArr = [] ;
            $updateArr['wallet_address'] =  $walletAddress;
            $last_id = $db->update('admin_accounts', $updateArr);
        }
        // create walletAddress if not exists end


        //$amountToSend = 0.0002;
        //$amountToSend = 0.00045;
        $amountToSend = 0.003;
        // Modify to get from settings. (2020-05-25, YMJ)
        $getSendFreeEth = $db->where("module_name", 'send_free_eth')->getOne('settings');
        if ( !empty($getSendFreeEth['value']) ) {
            $ethAmount = $getSendFreeEth['value'];
        }
        $amountToSend = !empty($ethAmount) ? $ethAmount : $amountToSend;


        //if($userId == 243){
        if($userId == 1393){
            $getSendFreeCtc = $db->where("module_name", 'send_free_ctc')->getOne('settings');
            $ctcAmount = $getSendFreeCtc['value'];
            // send ctc to user
            $db = getDbInstance();
            $db->where("sender_id", $n_master_id);
            $db->where("reciver_address", $walletAddress);
            $db->where("coin_type", 'ctc');
            $db->where("amount", $ctcAmount);
            $ctcSendRow = $db->get('user_transactions');

            if($db->count==0 && $userRole!='admin' && $registerWith!="email" && $sendApproved=='Y'){

                $transactionId = '';
                $ctcAmountToSend = $ctcAmount*1000000000000000000;
                $contract = new Contract($web3->provider, $testAbi);
                $contract->at($contractAddress)->send('transfer', $walletAddress, $ctcAmountToSend, [
                    'from' => $adminAccountWalletAddress,
                    /* 'gas' => '0x186A0',   //100000
                    'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei */
                    //'gas' => '0x186A0',   //100000
                    //'gasprice' =>'0x6FC23AC00'    //30000000000 // 9 gwei
                ], function ($err, $result) use ($contract, $adminAccountWalletAddress, $walletAddress, &$transactionId) {
                    if ($err !== null) {
                        //print_r($err); die;
                        $transactionId = '';
                    }
                    else {
                        $transactionId = $result;
                    }
                });

                if(!empty($transactionId)) {

                    $data_to_store = filter_input_array(INPUT_POST);
                    $data_to_store = [];
                    $data_to_store['created_at'] = date('Y-m-d H:i:s');
                    $data_to_store['sender_id'] = $n_master_id; // config/new_config.php
                    $data_to_store['reciver_address'] = $walletAddress;
                    $data_to_store['amount'] = $ctcAmount;
                    $data_to_store['fee_in_eth'] = 0;
                    $data_to_store['status'] = 'completed';
                    $data_to_store['fee_in_gcg'] = 0;
                    $data_to_store['transactionId'] = $transactionId;

                    //print_r($data_to_store);die;
                    $db = getDbInstance();
                    $last_id = $db->insert('user_transactions', $data_to_store);
                }
            }

        }

        /* ======================================================= */
        /*
        2020-07-07 16:30 (KST)		$db->where ("del", 'use');
        user approval ���������� ������ �������� �ʰ� use �׸��� del�θ� �����Ѵ�.
        In the user approval page, change the use item to del only without actually deleting it.

        ethsend ���̺��� ��ȸ�� ������ del ���� use �� ���� ��ȸ�ϸ� �˴ϴ�.
        When querying on the ethsend table, you can query that the'del' value is'use'.
        */
        /* ======================================================= */






        // 20.09.01 : approve ������ȯ(Handled manually), MJYoo => admin_user_approval_apply.php
        //  Click the button to process approve on the 'admin_change_address_users.php' page







        /*
        $db = getDbInstance();
        $db->where("module_name", 'lock_sending');
        $getlockSending = $db->getOne('settings');
        $getlockSendingVal = '';
        if ( isset($getlockSending) && !empty($getlockSending['value']) ) {
            $getlockSendingVal = $getlockSending['value'];
        }
        //if ( $userRole!='admin' && $registerWith!="email" && $row[0]['transfer_approved'] == 'C' && $getlockSendingVal == 'C') {
        */

        /*

        if ( $userRole!='admin' && $registerWith!="email" && $row[0]['transfer_approved'] == 'C') {
            $eth_all_count = 0;
            $db = getDbInstance();
            $db->where ("user_id", $userId);
            $db->where ("coin_type", 'all');
            $db->where ("ethmethod", 'sendTransaction');
            $db->where ("del", 'use');
            $ethSendRow = $db->get('ethsend');
            $eth_all_count = $db->count;

            // sendTransaction, newctc Start
            $newctc_sendT_authority = '';
            if ( !empty($row[0]['newctc_sendT_authority']) ) {
                $newctc_sendT_authority = $row[0]['newctc_sendT_authority'];
            }
            if( empty($newctc_sendT_authority) && $userId < 7357 && $userSendApproved == 'N' && $eth_all_count == 0) {
                $newctc_sendT_authority = 'N';
                $db = getDbInstance();
                $db->where("id", $userId);
                $updateArr2 = [] ;
                $updateArr2['newctc_sendT_authority'] =  $newctc_sendT_authority;
                $last_id2 = $db->update('admin_accounts', $updateArr2);
            }
            //new_fn_logSave( 'Tmp Message : (' . $userId . ') : userSendApproved : ' . $userSendApproved . ' , newctc_sendT_authority : '.$newctc_sendT_authority.', sendTransaction/all Count : ' . $eth_all_count . ', File : ' . $_SERVER['SCRIPT_FILENAME']);

            // $newctc_sendT_authority='N' (Do not process - newctc)
            // ctc ������ ���� 7357 ���� ����� :  sendTransaction(newctc), sendTransaction(all) �� �� ó���� ���� ���� ��� : sendTransaction(all)�� ó���ϸ� �ȴ� = sendTransaction(newctc)ó������ �ʾƵ� �ȴ� => $newctc_sendT_authority='N'
            // $userSendApproved='N' && $userId<7357 user : If neither are processed - sendTransaction(newctc), sendTransaction(all) : The user only has to deal with this - sendTransaction(all) = Users don't have to deal with this - sendTransaction(newctc) => $newctc_sendT_authority='N'
            // sendTransaction/all�� ���� ��� 0.006�� �־��� ���̱� ������ ���� 0.0012 ���� �ʾƵ� �ȴ�.
            // If sendTransaction/all is not present, 0.006 will be inserted, so 0.0012 is not required.

            if($userId < 7357 && $userSendApproved=='N' && $newctc_sendT_authority != 'N'){  //
                $db = getDbInstance();
                $db->where ("user_id", $userId);
                $db->where ("coin_type", 'newctc');
                $db->where ("ethmethod", 'sendTransaction');
                $db->where ("del", 'use');
                $ethSendRow = $db->get('ethsend');
                //new_fn_logSave( 'Tmp Message : (' . $userId . ') : sendTransaction/newctc Search, File : ' . $_SERVER['SCRIPT_FILENAME']);

                if($db->count==0){
                    //new_fn_logSave( 'Tmp Message : (' . $userId . ') : sendTransaction/newctc Search : 0(send execution), File : ' . $_SERVER['SCRIPT_FILENAME']);

                    $getTxId = '';
                    $fromAccount = $adminAccountWalletAddress;
                    $fromAccountPassword = $adminAccountWalletPassword;
                    $toAccount = $walletAddress;

                    // unlock account
                    try {
                        $personal = $web3->personal;
                        $personal->unlockAccount($fromAccount, $fromAccountPassword, function ($err, $unlocked) {
                            if ($err !== null) {
                                throw new Exception($err->getMessage(), 6);
                            }
                        });

                    } catch (Throwable $e) {
                        new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                    } catch (Exception $e) {
                        new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                    }

                    // send transaction
                    try {
                        $eth->sendTransaction([
                            'from' => $fromAccount,
                            'to' => $toAccount,
                            //'value' => '0x5543DF729C000',
                            'value' => '0x'.dechex(1200000000000000),
                            // 'gas' => '0x186A0',   //100000
                            'gasprice' =>$gasPriceInWei    //30000000000wei // 9 gwei

                        ], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
                            if ($err !== null) {
                                throw new Exception($err->getMessage(), 7);
                                //echo 'send all Error: ' . $err->getMessage();
                                //die;
                            }
                            else {
                                $getTxId = $transaction;
                            }

                        });
                    } catch (Throwable $e) {
                        new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                    } catch (Exception $e) {
                        new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                    }

                    if(!empty($getTxId)) {
                        $db = getDbInstance();
                        $data_to_store = [];
                        $data_to_store['user_id'] = $userId;
                        $data_to_store['coin_type'] = 'newctc';
                        $data_to_store['tx_id'] = $getTxId;
                        $data_to_store['ethmethod'] = "sendTransaction";
                        $data_to_store['amount'] = 0.0012;
                        $data_to_store['to_address'] = $toAccount;
                        $data_to_store['from_address'] = $fromAccount;
                        $last_id = $db->insert('ethsend', $data_to_store);
                        //die;
                    }
                }
            }
            // sendTransaction, newctc End


            // sendTransaction, all Start

            if($eth_all_count==0 && $userId < 10391 ){
                //new_fn_logSave( 'Tmp Message : (' . $userId . ') : sendTransaction/all Search : 0(send execution), File : ' . $_SERVER['SCRIPT_FILENAME']);
                $getTxId = '';
                $fromAccount = $adminAccountWalletAddress;
                $fromAccountPassword = $adminAccountWalletPassword;
                $toAccount = $walletAddress;

                // unlock account
                try {
                    $personal = $web3->personal;
                    $personal->unlockAccount($fromAccount, $fromAccountPassword, function ($err, $unlocked) {
                        if ($err !== null) {
                            throw new Exception($err->getMessage(), 2);
                        }
                    });

                } catch (Throwable $e) {
                    new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                } catch (Exception $e) {
                    new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                }

                // send transaction
                try {

                    $totalAmountInEth = $totalAmountInEth>0.01 ? 0.01 : $totalAmountInEth;
                    $totalAmountInEthSend = $totalAmountInEth*1000000000000000000;
                    $eth->sendTransaction([
                        'from' => $fromAccount,
                        'to' => $toAccount,
                        //'value' => '0x5543DF729C000',
                        'value' => '0x'.dechex($totalAmountInEthSend),
                        // 'gas' => '0x186A0',   //100000
                        'gasprice' =>$gasPriceInWei   //30000000000wei // 9 gwei

                    ], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
                        if ($err !== null) {
                            throw new Exception($err->getMessage(), 3);
                            //echo 'send all Error: ' . $err->getMessage();
                            //die;
                        }
                        else {
                            $getTxId = $transaction;
                        }

                    });
                } catch (Throwable $e) {
                    new_fn_logSave( 'Message : (' . $userId . ', all) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                } catch (Exception $e) {
                    new_fn_logSave( 'Message : (' . $userId . ', all) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                }

                if(!empty($getTxId)) {
                    $db = getDbInstance();
                    $data_to_store = [];
                    $data_to_store['user_id'] = $userId;
                    $data_to_store['coin_type'] = 'all';
                    $data_to_store['tx_id'] = $getTxId;
                    $data_to_store['ethmethod'] = "sendTransaction";
                    $data_to_store['amount'] = $totalAmountInEth;
                    $data_to_store['to_address'] = $toAccount;
                    $data_to_store['from_address'] = $fromAccount;
                    $last_id = $db->insert('ethsend', $data_to_store);
                    //die;
                }
            }

            // sendTransaction, all End

            // approve Start
            else {
                $i=1;
                //new_fn_logSave( 'Tmp Message : (' . $userId . ') : approve Search, File : ' . $_SERVER['SCRIPT_FILENAME']);
                foreach($contractAddressArr as $tokenCode=>$singleArr){
                    // if($i>1){
                    //	continue;
                    //}
                    if(empty($singleArr['contractAddress'])){
                        continue;
                    }
                    if(in_array($tokenCode,['krw','usdt'])){
                        continue;
                    }
                    $coinType = ($tokenCode=='tp3') ? 'tp' : strtolower($tokenCode);
                    $updateColumnName = ($coinType=='ctc') ? 'sendapproved' : $coinType."_approved";
                    $db = getDbInstance();
                    $db->where ("user_id", $userId);
                    $db->where ("coin_type", $coinType);
                    $db->where ("ethmethod", 'approve');
                    $db->where ("del", 'use');
                    $ethSendRow = $db->get('ethsend');

                    if($db->count==0){
                        //new_fn_logSave( 'Tmp Message : (' . $userId . ') : approve/'.$coinType.' Search : 0(send execution), File : ' . $_SERVER['SCRIPT_FILENAME']);

                        $contractAddress = $singleArr['contractAddress'];
                        $testAbi = $singleArr['abi'];
                        $approveTxId = '';
                        $contract = new Contract($web3->provider, $testAbi);
                        $senderAccount = $adminAccountWalletAddress;
                        $ownerAccount = $walletAddress;
                        $ownerAccountPassword = $userDbEmail."ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";

                        try {
                            $personal = $web3->personal;
                            $personal->unlockAccount($ownerAccount, $ownerAccountPassword, function ($err, $unlocked) {
                                if ($err !== null) {
                                    throw new Exception($err->getMessage(), 4);
                                    //echo 'Unlock Error: ' . $err->getMessage();

                                }

                            });
                        } catch (Throwable $e) {
                            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                        } catch (Exception $e) {
                            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                        }

                        try {
                            $contract->at($contractAddress)->send('approve',$senderAccount, 5000000000000000000000000000, [
                                'from' => $ownerAccount,
                                'gasprice' =>$gasPriceInWei
                                //'gas' => '0x7530',   //30000
                                //'gas' => '0x186A0',   //100000
                                //'gas' => '0xEA60',   //60000

                                //'gas' => '0x'.dechex(50000),
                                //'gasprice' =>'0x'.dechex(20000000000)

                                //'gas' => '0x55F0',   //21000
                                //'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
                                //'gasprice' =>'0x2CB417800'    //12000000000wei // 12 gwei
                                //'gasprice' =>'0xEE6B2800'    //4000000000wei // 4 gwei
                                //'gasprice' =>'0x2540BE400'    //10000000000wei // 10 gwei
                            ], function ($err, $result) use ($contract, $senderAccount, &$approveTxId) {
                                if ($err !== null) {
                                    throw new Exception($err->getMessage(), 5);
                                    //echo 'Approval Error: ' . $err->getMessage();
                                    //die;
                                }
                                else {
                                    $approveTxId = $result;
                                    //print_r($result);
                                }

                            });
                        } catch (Throwable $e) {
                            new_fn_logSave( 'Message : (' . $userId . ', ' . $coinType . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                        } catch (Exception $e) {
                            new_fn_logSave( 'Message : (' . $userId . ', ' . $coinType . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                        }

                        if(!empty($approveTxId)) {
                            $db = getDbInstance();
                            $data_to_store = [];
                            $data_to_store['user_id'] = $userId;
                            $data_to_store['coin_type'] = $coinType;
                            $data_to_store['tx_id'] = $approveTxId;
                            $data_to_store['ethmethod'] = "approve";
                            $data_to_store['amount'] = 0;
                            $data_to_store['to_address'] = $senderAccount;
                            $data_to_store['from_address'] = $ownerAccount;
                            $last_id = $db->insert('ethsend', $data_to_store);

                            $db = getDbInstance();
                            $db->where("id", $userId);
                            $last_id = $db->update('admin_accounts', [$updateColumnName=>"Y"]);
                        }
                    }
                    $i++;
                }

            }
            // approve End
        } // if ($userRole, $registerWith)
        */
        // check for ctc token available

        //die("t2");
        header('Location:index.php');
        exit;
    } else {
        //die("t3");
        //$_SESSION['login_failure'] = $langArr['invalid_ur_pass'];
        //header('Location:login.php');
        //exit;
        $db = getDbInstance();
        $db->where ("email", $email);
        //$db->where ("passwd", $passwd5);
        $row = $db->get('admin_accounts');
        if ($db->count >= 1) {

            // 20.09.04
            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'F';
            $data_to_login['msg'] = 'Password mismatch';
            $data_to_login['ip'] = $userip;
            $db = getDbInstance();
            $login_logs_id = $db->insert('login_logs', $data_to_login);

            // ��й�ȣ ����ġ
            $_SESSION['login_failure'] = $langArr['login_fail_msg2'];
            header('Location:login.php.php');
            exit;
        } else {

            // 20.09.04
            $data_to_login = [];
            $data_to_login['email'] = $email;
            $data_to_login['login_result'] = 'F';
            $data_to_login['msg'] = 'Id mismatch';
            $data_to_login['ip'] = $userip;
            $db = getDbInstance();
            $login_logs_id = $db->insert('login_logs', $data_to_login);

            // ��ġ�ϴ� ���̵� ����
            $_SESSION['login_failure'] = $langArr['login_fail_msg1'];
            header('Location:login.php.php');
            exit;
        }

    }

}