<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\usdt\User as walletUsdtController;

return function (App $app) {
    $app->group('/API/usdt', function (RouteCollectorProxy $group) {
        try {
            $group->GET('/user/wallet',walletUsdtController::class.':getUserWalletInfo');
            $group->GET('/user/wallet/test/{address}',walletUsdtController::class.':getUserWalletInfoTest');
        }
        catch (Exception $e){
            //slim 에서 처리??
        }

    });
};
?>