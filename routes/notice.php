<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\maintenance\Notice as walletNotice;

return function (App $app) {
    $app->group('/notice', function (RouteCollectorProxy $group) {
        try {
            //서버점검
            $group->GET('/v1',walletNotice::class.':v1');

        }
        catch (Exception $e){
        }
    });
};
?>