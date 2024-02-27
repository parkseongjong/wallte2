<?php

namespace control\controller\auth;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use wallet\exception\WalletHttpForbiddenException;
use wallet\exception\WalletHttpNotacceptableException;

use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class Kcp
{
    private $container;
    private $templatesOption = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->templatesOption = array(
            'info' => [
                'lang' => $this->container->get('langArray'),
                'asstsUrl' => WALLET_URL . '/skin/common/assets',
            ]
        ) ;
    }

    //KCP KOR 핸드폰 본인인증 REQUEST(요청).
    public function phoneAuthRequest(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        /* ============================================================================== */
        /* =   인증창 호출 및 수신 페이지 :  공 통 본 인 인 증                          = */
        /* = -------------------------------------------------------------------------- = */
        /* =   해당 페이지는 반드시 가맹점 서버에 업로드 되어야 하며                    = */
        /* =   가급적 수정없이 사용하시기 바랍니다.                                     = */
        /* ============================================================================== */

        /* ============================================================================== */
        /* =   라이브러리 파일 Include                                                  = */
        /* = -------------------------------------------------------------------------- = */
        $sbParam = ""; // 추가, YMJ
        //include "/var/www/ctc/wallet/kcp/kcp_config.php";
        //require "/var/www/ctc/wallet/kcp/lib/ct_cli_lib.php";

        include "/var/www/ctc/wallet/kcp/kcp_config.php";
        require "/var/www/ctc/wallet/kcp/lib/ct_cli_lib.php";

        /* = -------------------------------------------------------------------------- = */
        /* =   라이브러리 파일 Include END                                               = */
        /* ============================================================================== */
        /* ============================================================================== */
        /* =   null 값을 처리하는 메소드                                                = */
        /* = -------------------------------------------------------------------------- = */
        function f_get_parm_str( $val )
        {
            if ( $val == null ) $val = "";
            if ( $val == ""   ) $val = "";
            return  $val;
        }

        //!!중요 해당 함수는 year, month, day 변수가 null 일 경우 00 으로 치환합니다
        function f_get_parm_int( $val )
        {
            $ret_val = "";

            if ( $val == null ) $val = "00";
            if ( $val == ""   ) $val = "00";

            $ret_val = strlen($val) == 1? ("0" . $val) : $val;

            return  $ret_val;
        }
        /* ============================================================================== */
        ?>
        <?
        $req_tx        = "";

        $site_cd       = "";
        $ordr_idxx     = "";

        $year          = "";
        $month         = "";
        $day           = "";
        $user_name     = "";
        $sex_code      = "";
        $local_code    = "";

        $cert_able_yn  = "";
        $web_siteid    = "";
        $web_siteid_hashYN    = "";

        $up_hash       = "";

        /*------------------------------------------------------------------------*/
        /*  :: 전체 파라미터 남기기                                               */
        /*------------------------------------------------------------------------*/

        $ct_cert = new \C_CT_CLI;
        $ct_cert->mf_clear();

        // request 로 넘어온 데이터 처리
        foreach($_POST as $nmParam => $valParam)
        {
            if ( $nmParam == "site_cd" )
            {
                $site_cd = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "req_tx" )
            {
                $req_tx = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "ordr_idxx" )
            {
                $ordr_idxx = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "user_name" )
            {
                $user_name = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "year" )
            {
                $year = f_get_parm_int ( $valParam );
            }

            if ( $nmParam == "month" )
            {
                $month = f_get_parm_int ( $valParam );
            }

            if ( $nmParam == "day" )
            {
                $day = f_get_parm_int ( $valParam );
            }

            if ( $nmParam == "sex_code" )
            {
                $sex_code = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "local_code" )
            {
                $local_code = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "cert_able_yn" )
            {
                $cert_able_yn = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "web_siteid_hashYN" )
            {
                $web_siteid_hashYN = f_get_parm_str ( $valParam );
            }

            if ( $nmParam == "web_siteid" )
            {
                $web_siteid = f_get_parm_str ( $valParam );
            }

            // 인증창으로 넘기는 form 데이터 생성 필드
            $sbParam .= "<input type='hidden' name='" . $nmParam . "' value='" . f_get_parm_str( $valParam ) . "'/>";
        }

        if ( $req_tx == "cert" )
        {

            if ( $web_siteid_hashYN !="Y")
            {
                // web_siteid 검증을 안할시 해당 값을 ""(null) 로 설정
                $web_siteid = "";
            }

            if ( $cert_able_yn == "Y" )
            {
                // input 박스 활성화시 up_hash 생성 데이터
                $hash_data = $site_cd                  .
                    $ordr_idxx                .
                    $web_siteid               .
                    ""                        .
                    "00"                      .
                    "00"                      .
                    "00"                      .
                    ""                        .
                    "";
            }
            else
            {
                // !!up_hash 데이터 생성시 주의 사항
                // year , month , day 가 비어 있는 경우 "00" , "00" , "00" 으로 설정이 됩니다
                // 그외의 값은 없을 경우 ""(null) 로 세팅하시면 됩니다.
                // up_hash 데이터 생성시 site_cd 와 ordr_idxx 는 필수 값입니다.
                $hash_data = $site_cd                  .
                    $ordr_idxx                .
                    $web_siteid               .
                    $user_name                .
                    f_get_parm_int ( $year  ) .
                    f_get_parm_int ( $month ) .
                    f_get_parm_int ( $day   ) .
                    $sex_code                 .
                    $local_code;
            }
            $up_hash = $ct_cert->make_hash_data( $g_conf_home_dir, $g_conf_ENC_KEY ,$hash_data );

            // 인증창으로 넘기는 form 데이터 생성 필드 ( up_hash )
            $sbParam .= "<input type='hidden' name='up_hash' value='" . $up_hash . "'/>";
            // KCP 본인확인 라이브러리 버전 정보
            $sbParam .= "<input type='hidden' name='kcp_cert_lib_ver' value='" . $ct_cert->get_kcp_lib_ver( $g_conf_home_dir ) . "'/>";// ENC1(TEST용)
        }

        $ct_cert->mf_clear();
        $templates = new plateTemplate(WALLET_PATH . '/skin/auth', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

        $this->templatesOption['info']['param'] = $sbParam;
        $this->templatesOption['info']['gateWayUrl'] = $g_conf_gw_url;

        $randerData = $templates->render('kcpPhoneAuthRequest', $this->templatesOption);

        $response->getBody()->write($randerData);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    //KCP KOR 핸드폰 본인인증 RESPONSE(응답).
    public function phoneAuthResponse(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        /* ============================================================================== */
        /* =   인증데이터 수신 및 복호화 페이지 : 공 통 본 인 인 증                       */
        /* = -------------------------------------------------------------------------- = */
        /* =   해당 페이지는 반드시 가맹점 서버에 업로드 되어야 하며                    = */
        /* =   가급적 수정없이 사용하시기 바랍니다.                                     = */
        /* ============================================================================== */

        /* ============================================================================== */
        /* =   라이브러리 파일 Include                                                  = */
        /* = -------------------------------------------------------------------------- = */

        include "/var/www/ctc/wallet/kcp/kcp_config.php";
        require "/var/www/ctc/wallet/kcp/lib/ct_cli_lib.php";

        /* = -------------------------------------------------------------------------- = */
        /* =   라이브러리 파일 Include END                                               = */
        /* ============================================================================== */

        /* ============================================================================== */
        /* =   null 값을 처리하는 메소드                                                = */
        /* = -------------------------------------------------------------------------- = */
        function f_get_parm_str( $val )
        {
            if ( $val == null ) $val = "";
            if ( $val == ""   ) $val = "";
            return  $val;
        }
        /* ============================================================================== */

        $site_cd       = "";
        $ordr_idxx     = "";

        $cert_no       = "";
        $cert_enc_use  = "";
        $enc_info      = "";
        $enc_data      = "";
        $req_tx        = "";

        $enc_cert_data2 = "";
        $cert_info     = "";

        $tran_cd       = "";
        $res_cd        = "";
        $res_msg       = "";

        $dn_hash       = "";

// 추가(YMJ)
        $param_opt_1 = "";
        $param_opt_2 = "";
        $param_opt_3 = "";

        $auth_phone_no = '';
        $auth_name = '';
        $auth_dob_y = '';
        $auth_dob_m = '';
        $auth_dob_d = '';
        $auth_gender = '';
        $auth_local_code = '';
//$t_id = ''; // 회원가입시 넘겨줄 temp_accounts.id
        $auth_ci = '';
        $auth_di = '';
        $last_id = '';

        /*------------------------------------------------------------------------*/
        /*  :: 전체 파라미터 남기기                                               */
        /*------------------------------------------------------------------------*/

    // request 로 넘어온 값 처리
        foreach ($_POST as $nmParam => $valParam)   {

            if ( $nmParam == "site_cd" ) {
                $site_cd = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "ordr_idxx" ) {
                $ordr_idxx = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "res_cd" ) {
                $res_cd = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "cert_enc_use" ) {
                $cert_enc_use = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "req_tx" ) {
                $req_tx = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "cert_no" ) {
                $cert_no = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "enc_cert_data2" ) {
                $enc_cert_data2 = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "dn_hash" ) {
                $dn_hash = f_get_parm_str ( $valParam );
            }

            // 추가(YMJ)
            if ( $nmParam == "param_opt_1" ) {
                $param_opt_1 = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "param_opt_2" ) {
                $param_opt_2 = f_get_parm_str ( $valParam );
            }
            if ( $nmParam == "param_opt_3" ) {
                $param_opt_3 = f_get_parm_str ( $valParam );
            }
        }

        if ( empty($_SESSION['user_id']) && !empty($param_opt_3) ) { // 세션이 없고 값이 있으면 다시 넣어줄것
            $_SESSION['user_id'] = $param_opt_3;
        }
        if ( empty($_SESSION['admin_type']) && !empty($param_opt_2) ) { // 세션이 없고 값이 있으면 다시 넣어줄것
            $_SESSION['admin_type'] = $param_opt_2;
        }
        if ( empty($_SESSION['user_logged_in']) && !empty($param_opt_2) && !empty($param_opt_3)) {
            $_SESSION['user_logged_in'] = TRUE;
        }

        $ct_cert = new \C_CT_CLI;
        $ct_cert->mf_clear();

        // 결과 처리


        $browser_infos = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $browser_infos = $_SERVER['HTTP_USER_AGENT'];
        }
        $page_url = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $page_url = $_SERVER['REQUEST_URI'];
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $page_url = $_SERVER['SCRIPT_NAME'];
        } else if (isset($_SERVER['PHP_SELF'])) {
            $page_url = $_SERVER['PHP_SELF'];
        }
        $user_ip = '';
        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $user_ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $user_ip = $_SERVER['REMOTE_ADDR'];
        }

        $next_url = 'login.php';

        if ( !isset($_SESSION['auth_re']) || $_SESSION['auth_re'] != 'fin') {
            if( $cert_enc_use == "Y" ) {
                if( $res_cd == "0000" ) {
                    // dn_hash 검증
                    // KCP 가 리턴해 드리는 dn_hash 와 사이트 코드, 요청번호 , 인증번호를 검증하여
                    // 해당 데이터의 위변조를 방지합니다
                    $veri_str = $site_cd.$ordr_idxx.$cert_no; // 사이트 코드 + 요청번호 + 인증거래번호

                    if ( $ct_cert->check_valid_hash ( $g_conf_home_dir , $g_conf_ENC_KEY , $dn_hash , $veri_str ) != "1" ) {
                        // 검증 실패시 처리 영역

                        $_SESSION['failure'] = $langArr['auth_failed'];
                        $_SESSION['auth_re'] = 'fin';
                        //fn_logSave('Personal Identification Error : '.$langArr['auth_failed']);

                        $data_auth_err = [];
                        $data_auth_err['type'] = 'auth';
                        $data_auth_err['cause'] = 'decrypt';
                        $data_auth_err['message'] = 'dn_hash 변조 위험있음';
                        $data_auth_err['browser_infos'] = $browser_infos;
                        $data_auth_err['page_url'] = $page_url;
                        $data_auth_err['user_ip'] = $user_ip;
                        $data_auth_err['created_at'] = date("Y-m-d H:i:s");
                        $db_err_insert = getDbInstance();
                        $db_err_insert->insert('auth_error', $data_auth_err);

                        echo "<script>if( ( navigator.userAgent.indexOf('Android') > - 1 || navigator.userAgent.indexOf('iPhone') > - 1 || navigator.userAgent.indexOf('android-web-view') > - 1 || navigator.userAgent.indexOf('ios-web-view') > - 1 ) ){ parent.location.replace('".$next_url."'); } else { opener.location.replace('".$next_url."');window.close(); }</script>";
                        exit();
                    }

                    // 가맹점 DB 처리 페이지 영역

                    //echo "========================= 리턴 데이터 ======================="       ."<br>";
                    //echo "사이트 코드           :" . $site_cd                                 ."<br>";
                    //echo "인증 번호              :" . $cert_no                                 ."<br>";
                    //echo "암호된 인증정보     :" . $enc_cert_data2                   ."<br>";

                    try {
                        // 인증데이터 복호화 함수
                        // 해당 함수는 암호화된 enc_cert_data2 를
                        // site_cd 와 cert_no 를 가지고 복화화 하는 함수 입니다.
                        // 정상적으로 복호화 된경우에만 인증데이터를 가져올수 있습니다.
                        $opt = "1" ; // 복호화 인코딩 옵션 ( UTF - 8 사용시 "1" )
                        $ct_cert->decrypt_enc_cert( $g_conf_home_dir , $g_conf_ENC_KEY , $site_cd , $cert_no , $enc_cert_data2 , $opt );

                        //echo "========================= 복호화 데이터 ====================="       ."<br>";
                        //echo "복호화 이동통신사 코드 :" . $ct_cert->mf_get_key_value("comm_id")."<br>"; // 이동통신사 코드
                        //echo "복호화 전화번호           :" . $ct_cert->mf_get_key_value("phone_no")."<br>"; // 전화번호
                        //echo "복호화 이름                 :" . $ct_cert->mf_get_key_value("user_name")."<br>"; // 이름
                        //echo "복호화 생년월일           :" . $ct_cert->mf_get_key_value("birth_day")."<br>"; // 생년월일
                        //echo "복호화 성별코드           :" . $ct_cert->mf_get_key_value("sex_code")."<br>"; // 성별코드
                        //echo "복호화 내/외국인 정보   :" . $ct_cert->mf_get_key_value("local_code")."<br>"; // 내/외국인 정보
                        //echo "복호화 CI                   :" . $ct_cert->mf_get_key_value("ci_url")."<br>"; // CI
                        //echo "복호화 DI                   :" . $ct_cert->mf_get_key_value("di_url")."<br>"; // DI 중복가입 확인값
                        //echo "복호화 WEB_SITEID      :" . $ct_cert->mf_get_key_value("web_siteid")."<br>"; // WEB_SITEID
                        //echo "복호화 결과코드           :" . $ct_cert->mf_get_key_value("res_cd")."<br>"; // 암호화된 결과코드
                        //echo "복호화 결과메시지        :" . $ct_cert->mf_get_key_value("res_msg")."<br>"; // 암호화된 결과메시지

                        if ( empty($ct_cert->mf_get_key_value("phone_no")) ) {
                            $ct_cert->decrypt_enc_cert( $g_conf_home_dir , $g_conf_ENC_KEY , $site_cd , $cert_no , $enc_cert_data2 , $opt );
                        }

                        //추가 파라메터가 일치 하지 않는 경우... 오류
                        if($param_opt_1 != 'sleepUser'){
                            throw new Exception('잘못된 접근 입니다.',9999);
                        }
//                var_dump($param_opt_1);
                        $auth_phone_no = $ct_cert->mf_get_key_value("phone_no");
//                var_dump($auth_phone_no);
//                var_dump($_SESSION);

                        $auth = walletAuth::singletonMethod();
                        $util = walletUtil::singletonMethod();
                        $db = walletDb::singletonMethod();
                        $db = $db->init();
                        //$tempId = filter_var($_SESSION['tempUserId'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                        //var_dump($tempId);
                        if(!$auth->sessionAuthTemp()) {
                            throw new Exception('잘못 된 호출 입니다.',9999);
                        }
                        $tempId = $auth->getSessionIdTemp();

                        //휴면 계정 복원 완료 페이지 노출+
                        $walletSleepRestore = new walletSleepRestore($tempId);
                        if ($walletSleepRestore->userRetore()) {

                            ob_start();
                            require_once WALLET_PATH . '/includes/header.php';
                            $bufferData['header'] = ob_get_contents();
                            ob_clean();
                            require_once WALLET_PATH . '/includes/footer.php';
                            $bufferData['footer'] = ob_get_contents();
                            ob_end_clean();

                            $templates = new plateTemplate(WALLET_PATH . '/skin/sleepUser', 'html');
                            $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));
                            $randerData = $templates->render('sleepRestoreFormComplete', [
                                'info' => [
                                    'htmlHeader' => $bufferData['header'],
                                    'htmlFooter' => $bufferData['footer'],
                                    'lang' => $langArr,
                                    'asstsUrl' => WALLET_URL . '/skin/common/assets',
                                    'walletUrl' => WALLET_URL,

                                ]
                            ]);

                            echo($randerData);
                            exit();

                        }

                    }
                    catch (Exception $e){
                        $data_auth_err = [];
                        // 인증실패
                        if($e->getCode() == 9999){
                            $data_auth_err['message'] = $e->getMessage();
                        }
                        else{
                            $data_auth_err['message'] = $langArr['auth_failed'];
                        }
                        $_SESSION['failure'] = $langArr['auth_failed'];
                        $_SESSION['auth_re'] = 'fin';
                        //fn_logSave('Personal Identification Error : '.$langArr['auth_failed']);


                        $data_auth_err['type'] = 'auth';
                        $data_auth_err['cause'] = 'failed';

                        $data_auth_err['browser_infos'] = $browser_infos;
                        $data_auth_err['page_url'] = $page_url;
                        $data_auth_err['user_ip'] = $user_ip;
                        $data_auth_err['created_at'] = date("Y-m-d H:i:s");
                        $db_err_insert = getDbInstance();
                        $db_err_insert->insert('auth_error', $data_auth_err);
                        echo "<script>if( ( navigator.userAgent.indexOf('Android') > - 1 || navigator.userAgent.indexOf('iPhone') > - 1 || navigator.userAgent.indexOf('android-web-view') > - 1 || navigator.userAgent.indexOf('ios-web-view') > - 1 ) ){ parent.location.replace('".$next_url."'); } else { opener.location.replace('".$next_url."');window.close(); }</script>";
                    }

                }
                else { //if( res_cd.equals( "0000" ) != true )
                    // 인증실패
                    $_SESSION['failure'] = $langArr['auth_failed'];
                    $_SESSION['auth_re'] = 'fin';
                    //fn_logSave('Personal Identification Error : '.$langArr['auth_failed']);

                    $data_auth_err = [];
                    $data_auth_err['type'] = 'auth';
                    $data_auth_err['cause'] = 'failed';
                    $data_auth_err['message'] = $langArr['auth_failed'];
                    $data_auth_err['browser_infos'] = $browser_infos;
                    $data_auth_err['page_url'] = $page_url;
                    $data_auth_err['user_ip'] = $user_ip;
                    $data_auth_err['created_at'] = date("Y-m-d H:i:s");
                    $db_err_insert = getDbInstance();
                    $db_err_insert->insert('auth_error', $data_auth_err);

                    echo "<script>if( ( navigator.userAgent.indexOf('Android') > - 1 || navigator.userAgent.indexOf('iPhone') > - 1 || navigator.userAgent.indexOf('android-web-view') > - 1 || navigator.userAgent.indexOf('ios-web-view') > - 1 ) ){ parent.location.replace('".$next_url."'); } else { opener.location.replace('".$next_url."');window.close(); }</script>";
                }
            }
            else { //if( cert_enc_use.equals( "Y" ) != true )
                // 암호화 인증 안함
                $_SESSION['failure'] = $langArr['auth_not_encryption'];
                $_SESSION['auth_re'] = 'fin';
                //fn_logSave('Personal Identification Error : '.$langArr['auth_not_encryption']);

                $data_auth_err = [];
                $data_auth_err['type'] = 'auth';
                $data_auth_err['cause'] = 'none';
                $data_auth_err['message'] = $langArr['auth_not_encryption'];
                $data_auth_err['browser_infos'] = $browser_infos;
                $data_auth_err['page_url'] = $page_url;
                $data_auth_err['user_ip'] = $user_ip;
                $data_auth_err['created_at'] = date("Y-m-d H:i:s");
                $db_err_insert = getDbInstance();
                $db_err_insert->insert('auth_error', $data_auth_err);

                echo "<script>if( ( navigator.userAgent.indexOf('Android') > - 1 || navigator.userAgent.indexOf('iPhone') > - 1 || navigator.userAgent.indexOf('android-web-view') > - 1 || navigator.userAgent.indexOf('ios-web-view') > - 1 ) ){ parent.location.replace('".$next_url."'); } else { opener.location.replace('".$next_url."');window.close(); }</script>";
            }
        }
        else {
            unset($_SESSION['success']);
            unset($_SESSION['failure']);
            echo "<script>if( ( navigator.userAgent.indexOf('Android') > - 1 || navigator.userAgent.indexOf('iPhone') > - 1 || navigator.userAgent.indexOf('android-web-view') > - 1 || navigator.userAgent.indexOf('ios-web-view') > - 1 ) ){ parent.location.replace('".$next_url."'); } else { opener.location.replace('".$next_url."');window.close(); }</script>";
        }
        $ct_cert->mf_clear();
        /*

        $templates = new plateTemplate(WALLET_PATH . '/skin/auth', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

        $this->templatesOption['info']['param'] = $sbParam;
        $this->templatesOption['info']['gateWayUrl'] = $g_conf_gw_url;

        $randerData = $templates->render('kcpPhoneAuthRequest', $this->templatesOption);

        $response->getBody()->write($randerData);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
        */

        $response->getBody()->write('test');
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}

?>