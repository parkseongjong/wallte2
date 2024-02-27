<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\barry\Admin as walletBarryAdminController;

return function (App $app) {
    $app->group('/API/barry-admin', function (RouteCollectorProxy $group) {
        try {
            //barry admin 쪽은 RESTful을 따르지 않음.

            //member 조회
            $group->POST('/member',walletBarryAdminController::class.':member');

        }
        catch (Exception $e){
        }

    });
};
?>