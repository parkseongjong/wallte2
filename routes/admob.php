<?php
declare(strict_types=1);

use control\controller\maintenance\AdmobController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/admob', function (RouteCollectorProxy $group) {
        try {
            $group->GET('/callback', AdmobController::class . ':callback');
        } catch (Exception $e) {

        }
    });
};

