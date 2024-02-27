<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use control\controller\policy\Privacy as walletPolicyController;
use control\controller\policy\Service as walletServiceController;


return function (App $app) {
    $app->group('/policy', function (RouteCollectorProxy $group) {
        try {
            $group->GET('/privacy/view/{version}',walletPolicyController::class.':json');
        }
        catch (Exception $e){
            //slim 에서 처리??
        }

    });
};
?>