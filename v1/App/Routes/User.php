<?php
global $router;
$router->mount('/user', function() use ($router) {
    $router->get('/', 'UserController@info');
    $router->get('/token', 'UserController@token');
    $router->get('/free', 'UserController@freeList');
    $router->get('/paid', 'UserController@paidList');
    $router->mount('/purchase', function() use ($router) {
        $router->get('/', 'UserController@purchaseList');
    });
});