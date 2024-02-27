<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\auth\Otp as walletOtpController;
use control\controller\auth\Android as walletAndroidController;
use control\controller\auth\Ios as walletIosController;


return function (App $app) {
    $app->group('/API/auth', function (RouteCollectorProxy $group) {
        try {

            //LOGIN unlook해제,

            //OTP 등록.
            $group->POST('/otp/process/{loginKey}',walletOtpController::class.':process');

            //APP 변조 인증(안드로이드)
            $group->POST('/android/authenticate',walletAndroidController::class.':authenticate');

            //APP 인증(안드로이드)
            $group->POST('/android/jailbreak',walletAndroidController::class.':jailbreak');

            //APP 변조 인증(ios)
            $group->POST('/ios/authenticate',walletIosController::class.':authenticate');

            //APP 인증(ios)
            $group->POST('/ios/jailbreak',walletIosController::class.':jailbreak');
        }
        catch (Exception $e){
            //slim 에서 처리??
//            var_dump($e->getMessage());
//            var_dump($e->getFile());
//            var_dump($e->getLine());
//            var_dump($e->getCode());
//            echo('에러에러에러...');
//            $response->withHeader('Location', '/....')->withStatus(302);
//            echo('aaaa');
//            exit();
        }

    });
};
?>