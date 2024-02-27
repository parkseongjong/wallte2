<?php
global $router;
$router->mount('/status', function() use ($router) {
    $router->get('/info', 'StatusController@index');
    $router->get('/phpinfo', 'StatusController@phpInfo');
});