<?php
global $router;
$router->mount('/google', function() use ($router) {
    $router->post('/purchase', 'GoogleController@purchase');
    $router->get('/admob', 'GoogleController@admob');
    $router->get('/admob/display', 'GoogleController@admobDisplay');
});

