<?php
//Page in use
/*
 *
 *  신규 페이지 조작 control
 *
 */
if ($_SERVER["REMOTE_ADDR"] == '119.196.13.33') {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set( "display_errors", 1 );
}

session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once './config/new_config.php';
//권한 세션 체크...
//어드민이랑... 구분을 두고 써야 함..?
//class 자체를 rounte에 로딩 시켜도 되긴하는데...
//시간상 일단 route에.... 직접 작성....
//require_once './includes/auth_validate.php';
use DI\Container;
use Slim\Factory\AppFactory;
use control\handlers\WalletError;
use control\handlers\WalletErrorRender;
use control\handlers\WalletErrorRenderJson;

require_once(__DIR__.'/lib/control/Models/PointModel.php');
require_once(__DIR__.'/lib/control/Models/AttendanceModel.php');
require(BASE_PATH.'/vendor/autoload.php');

$bufferData = array();
ob_start();
require_once WALLET_PATH.'/includes/header.php';
$bufferData['header'] = ob_get_contents();
ob_clean();
require_once WALLET_PATH.'/includes/footer.php';
$bufferData['footer'] = ob_get_contents();
ob_end_clean();

$c = new Container();

$c->set('langArray',$langArr);
$c->set('bufferData',$bufferData);
$c->set('nDecimalPointArray',$n_decimal_point_array);
$c->set('newWalletappCoinList',$new_walletapp_coin_list);
$c->set('newWalletappEpayList',$new_walletapp_epay_list);
$c->set('contractAddressArray',$contractAddressArr);
$c->set('walletApiUserKey', 'BE14273125KL'); //old key
$c->set('walletApiAdminKey','ABS521!^6ec44(*');//old key
$c->set('remoteIp',['203.245.24.42','112.171.120.140']);//사이버트론 kr ip, new office ip

AppFactory::setContainer($c);

$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();


//추후 rewrite 적용 시 주의
$app->setBasePath('/wallet2/control.php');
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();

$errorHandler = new WalletError($callableResolver, $responseFactory);
$errorMiddleware = $app->addErrorMiddleware(false,false,false);
//기본 에러 핸들러를 지정해서 사용 하거나,... 랜더러를 이용해서 사용하거나 선택 해야함..
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$walletErrorHandler = $errorMiddleware->getDefaultErrorHandler();
$walletErrorHandler-> setDefaultErrorRenderer(false,WalletErrorRender::class);
foreach (['text/html','text/html;'] as $value){
    $walletErrorHandler->registerErrorRenderer($value, WalletErrorRender::class);
}
foreach (['application/json','application/json;'] as $value){
    $walletErrorHandler->registerErrorRenderer($value, WalletErrorRenderJson::class);
}

$routesListArray = array('auth','authAPI','kioskAPI','withdrawal','withdrawalAdmin','userLogAdmin','policy','usdtAPI','notice','barryAdminApi', 'admob');
foreach ($routesListArray as $routesListArrayKey => $routesListArrayValue){
    $routes = require_once(__DIR__.'/routes/'.$routesListArrayValue.'.php');
    $routes($app);
}
$app->run();



?>