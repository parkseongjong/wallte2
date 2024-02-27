<?php
$router = new \Bramus\Router\Router();
$router->setBasePath(APP_PATH);
$router->setNamespace('\App\Controllers');
foreach (glob(__DIR__ .'/Routes/*.php') as $routesFile) {
    require $routesFile;
}

$router->set404(function () {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
});

$router->run();
