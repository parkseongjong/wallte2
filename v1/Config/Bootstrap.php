<?php
date_default_timezone_set('Asia/Seoul');

$APP_ENV = gethostname() == 'ip-172-31-80-200' ? 'production' : 'development';
defined('APP_ENV') || define('APP_ENV', (getenv('APP_ENV') ? getenv('APP_ENV') : $APP_ENV));

// 보안설정이나 프레임이 달라도 쿠키가 통하도록 설정
header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');

//==============================================================================
// SESSION 설정
//------------------------------------------------------------------------------
@ini_set("session.use_trans_sid", 0);    // PHPSESSID를 자동으로 넘기지 않음
@ini_set("url_rewriter.tags", ""); // 링크에 PHPSESSID가 따라다니는것을 무력화함

include_once __DIR__ . '/Constants.php';

@session_cache_limiter("no-cache, must-revalidate");

ini_set("session.cache_expire", SESSION['TIME']); // 세션 캐시 보관시간 (분)
ini_set("session.gc_maxlifetime", SESSION['TIME']); // session data의 garbage collection 존재 기간을 지정 (초)
ini_set("session.gc_probability", 1); // session.gc_probability는 session.gc_divisor와 연계하여 gc(쓰레기 수거) 루틴의 시작 확률을 관리합니다. 기본값은 1입니다. 자세한 내용은 session.gc_divisor를 참고하십시오.
ini_set("session.gc_divisor", 100); // session.gc_divisor는 session.gc_probability와 결합하여 각 세션 초기화 시에 gc(쓰레기 수거) 프로세스를 시작할 확률을 정의합니다. 확률은 gc_probability/gc_divisor를 사용하여 계산합니다. 즉, 1/100은 각 요청시에 GC 프로세스를 시작할 확률이 1%입니다. session.gc_divisor의 기본값은 100입니다.

//**********************************************************************************
//* 도메인 관련 설정
//**********************************************************************************
preg_match("/^(image|score|external|api|www)?\.?(.+?)$/", $_SERVER['HTTP_HOST'], $out);
$cookie_domain = "." . $out[2];

session_set_cookie_params(0, '/', $cookie_domain);

ini_set("session.cookie_domain", $cookie_domain);

switch (APP_ENV) {
    case 'development':
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        ini_set('display_errors', 1);
        break;
    case 'production':
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        ini_set('display_errors', 1);
        break;
    default:
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}

//==========================================================================================================================
// extract($_GET); 명령으로 인해 page.php?_POST[var1]=data1&_POST[var2]=data2 와 같은 코드가 _POST 변수로 사용되는 것을 막음
// 081029 : letsgolee 님께서 도움 주셨습니다.
//--------------------------------------------------------------------------------------------------------------------------
$ext_arr = ['PHP_SELF', '_ENV', '_GET', '_POST', '_FILES', '_SERVER', '_COOKIE', '_SESSION', '_REQUEST',
    'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_SERVER_VARS',
    'HTTP_COOKIE_VARS', 'HTTP_SESSION_VARS', 'GLOBALS'];
$ext_cnt = count($ext_arr);
for ($i = 0; $i < $ext_cnt; $i++) {
    // POST, GET 으로 선언된 전역변수가 있다면 unset() 시킴
    if (isset($_GET[$ext_arr[$i]])) unset($_GET[$ext_arr[$i]]);
    if (isset($_POST[$ext_arr[$i]])) unset($_POST[$ext_arr[$i]]);
}

// multi-dimensional array에 사용자지정 함수적용
function array_map_deep($fn, $array)
{
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = array_map_deep($fn, $value);
            } else {
                $array[$key] = call_user_func($fn, $value);
            }
        }
    } else {
        $array = call_user_func($fn, $array);
    }

    return $array;
}

// SQL Injection 대응 문자열 필터링
function sql_escape_string($str)
{
    if (defined('G5_ESCAPE_PATTERN') && defined('G5_ESCAPE_REPLACE')) {
        $pattern = G5_ESCAPE_PATTERN;
        $replace = G5_ESCAPE_REPLACE;

        if ($pattern)
            $str = preg_replace($pattern, $replace, $str);
    }

    $str = call_user_func('addslashes', $str);

    return $str;
}

//==============================================================================
// SQL Injection 등으로 부터 보호를 위해 sql_escape_string() 적용
//------------------------------------------------------------------------------
// magic_quotes_gpc 에 의한 backslashes 제거
if (get_magic_quotes_gpc()) {
    $_POST = array_map_deep('stripslashes', $_POST);
    $_GET = array_map_deep('stripslashes', $_GET);
    $_COOKIE = array_map_deep('stripslashes', $_COOKIE);
    $_REQUEST = array_map_deep('stripslashes', $_REQUEST);
}

define('ESCAPE_FUNCTION', 'sql_escape_string');

// sql_escape_string 적용
$_POST = array_map_deep(ESCAPE_FUNCTION, $_POST);
$_GET = array_map_deep(ESCAPE_FUNCTION, $_GET);
$_COOKIE = array_map_deep(ESCAPE_FUNCTION, $_COOKIE);
$_REQUEST = array_map_deep(ESCAPE_FUNCTION, $_REQUEST);
$_POST = array_map_deep("trim",$_POST);
$_GET = array_map_deep("trim",$_GET);

// php.ini 의 register_globals=off 일 경우
@extract($_GET);
@extract($_POST);
@extract($_SERVER);

header("Cache-Control: no-cache,must-revalidate");
header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

require_once __DIR__ . "/../Helper/fn.php";

$_SERVER['REMOTE_ADDR'] && define("USER_IP", $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '');

if (!empty($_SERVER['HTTP_HOST'])) {
    $url = "http://" . $_SERVER['HTTP_HOST'];
    if (isSecure() === true) {
        $url = "https://" . $_SERVER['HTTP_HOST'];
    }

    define("URL_WEB", $url);
}

define("IS_MOBILE", isMobile());
const DEVICE = (IS_MOBILE) ? "mobile" : "PC";

if ((empty($_SERVER['HTTPS']) === false && $_SERVER['HTTPS'] == "on")) {
    define("HTTPSCHEKCK", "Y");
} else {
    define("HTTPSCHEKCK", "N");
}

require_once __DIR__ . '/../vendor/autoload.php';

@session_start();

include_once __DIR__ . '/Language.php';
if (isOfficeIp($_SERVER['REMOTE_ADDR']) ) {
    define("IS_OFFICE", true);
} else {
    define("IS_OFFICE", false);
}
