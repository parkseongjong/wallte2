<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\admin\UserLog as walletAdminUserLogController;

return function (App $app) {
    $app->group('/admin/user/log', function (RouteCollectorProxy $group) {
        try {
            $group->GET('[/{page:[0-9]+}]',walletAdminUserLogController::class.':list');
        }
        catch (Exception $e){
            //slim 에서 처리??
        }

    });
};
?>