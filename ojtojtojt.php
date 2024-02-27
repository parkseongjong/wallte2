<?php
/*
 *
 *
 *  OJT TEST PAGE 입니다.
 *
 *
 */

session_start();
require_once './config/config.php';
require_once './config/new_config.php';
//require_once 'includes/auth_validate.php';

use wallet\common\Push as push;
//use wallet\common\Log as walletLog;
//use wallet\sleep\Restore as walletSleepRestore;

require __DIR__ .'/vendor/autoload.php';
//
//if($_SERVER['REMOTE_ADDR'] != '112.171.120.140' && $_SERVER['REMOTE_ADDR'] != '112.171.120.162'){
//    var_dump($_SERVER['REMOTE_ADDR']);
//    exit();
//}

require_once 'includes/header.php';

//$test = new push();
//$test->sendMail('test','kick8888@naver.com');
//phpinfo();
?>

...

<?php
include_once('includes/footer.php');
?>
