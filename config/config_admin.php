<?php
    //db 인스턴스는 config 에 존재 함.
    define('WALLET_ADMIN', true);
    if ($_SESSION['admin_type'] !== 'admin') {
        // show permission denied message
        /*   header('HTTP/1.1 401 Unauthorized', true, 401);
          exit("401 Unauthorized"); */
        header('Location:index.php');
        exit();
    }

    //허용 된 IP만 관리자 페이지 접근
    $walletConfigDb = getDbInstance();
    //temp 임시로 관리자 페이지...접근제어 사용 여부..
    $TEMP = false;
    if($TEMP){
        $walletConfigDb->where('ip',$_SERVER['REMOTE_ADDR'])
            ->where('status',1);
        $adminBlockIpInfo = $walletConfigDb->get('blocked_admin_ips', null, '*');
        if(!$adminBlockIpInfo){
            echo('접근이 허용 되지 않았습니다.');
            exit();
        }
    }

    //var_dump(CURRENT_PAGE); //wallet2/config/config.php 상수
    require_once WALLET_PATH.'/includes/auth_validate.php';
    include_once (WALLET_PATH.'/lib/WalletLogger.php');
    use WalletLogger\Logger as walletLogger;
    use Pachico\Magoo\Magoo as walletMasking;

    require WALLET_PATH.'/vendor/autoload.php';

    $walletMasking = new walletMasking();

    $walletLoggerLoader = new walletLogger();
    $walletLogger = $walletLoggerLoader->init();
    $walletLoggerUtil = $walletLoggerLoader->initUtil();
    unset($walletLoggerLoader);

    unset($walletConfigDb);
?>