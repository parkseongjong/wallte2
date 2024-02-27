<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\auth\Otp as walletOtpController;
use control\controller\auth\Kcp as walletKcpController;
use control\controller\auth\Android as walletAndroidController;


return function (App $app) {
    $app->group('/auth', function (RouteCollectorProxy $group) {
        try {
            //$group->GET('/otp',[walletOtpController::class,'view']); 컨테이너에 di 등록을 해야 배열 형식을 사용 가능.. 한듯?
			
			//KCP 핸드폰 본인 인증(요청)
            $group->POST('/kcp/phone/request',walletKcpController::class.':phoneAuthRequest');

            //KCP 핸드폰 본인 인증(응답)
            $group->POST('/kcp/phone/response',walletKcpController::class.':phoneAuthResponse');


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