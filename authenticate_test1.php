<?php
// Page in use
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once './config/new_config.php';
//kcp 본인 인증
include_once ("/var/www/ctc/wallet/kcp/kcp_config.php");

use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Auth as wallatAuth;
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

//WEB APP 변조 및 탈옥 여부 확인 START

//androidAuthenticate
//androidJailbreak
try{
    if(!isset($_SESSION['androidAuthenticateCheck']) || !isset($_SESSION['androidJailbreakCheck'])){
        throw new Exception('androidSession not found',5000);
    }

    if($_SESSION['androidAuthenticateCheck'] !== true){
        throw new Exception('androidAuthenticate fail!');
    }
    if($_SESSION['androidJailbreakCheck'] !== true){
        throw new Exception('androidJailbreak fail!');
    }

}
catch (Exception $e){
    if($e->getCode() == 5000){
        $_SESSION['login_failure'] = '앱 초기화 상태에서 API 통신을 하지 않은 것 같습니다.(It seems that API communication did not occur in the initial state of the app.)';
    }
    else{
        $_SESSION['login_failure'] = '앱 또는 장치가 순정 상태가 아닙니다.(The app or device is not in pristine condition.)';
    }

    $_SESSION['androidAuthenticate'] = $_SESSION['androidJailbreak'] = false;
    header('Location:login_test1.php');
    exit;
}

//WEB APP 변조 및 탈옥 여부 확인 END

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
        header('location: login_test1.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    var_dump($_POST);
    echo('<hr>');
    var_dump($_SESSION);
    echo('<hr>');
    $email = filter_input(INPUT_POST, 'email');
    $passwd = filter_input(INPUT_POST, 'passwd');
    $phone = filter_input(INPUT_POST, 'phone');
    $remember = filter_input(INPUT_POST, 'remember');
    $passwd5 = md5($passwd);
    $phone = str_replace("-", "", $phone);
    $phone = str_replace(" ", "", $phone); //filter_input(INPUT_POST, 'passwd');

    echo('login ok!!');
}

echo('<br>TEST PAGE');
echo('<br><a href="https://cybertronchain.com/wallet2/login_test1.php">test login page url go</a>');