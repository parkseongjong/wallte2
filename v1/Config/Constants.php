<?php

if (APP_ENV == 'development') {
    define('APP_PATH', '/v1');
    define('DB_HOST', "arch.cybertronchain.com");
    define('DB_USER', "web3_cybertron");
    define('DB_PASSWORD', "db1234");
    define('DB_NAME', "wallet");
} elseif (APP_ENV == 'production')  {
    define('APP_PATH', '/wallet2/v1');
    define('DB_HOST', "arch.cybertronchain.com");
    define('DB_USER', "web3_cybertron");
    define('DB_PASSWORD', "db1234");
    define('DB_NAME', "wallet");
}

define('SERVER_TIME',    time());
define('TIME_YMDHIS',    date('Y-m-d H:i:s', SERVER_TIME));
define('TIME_YMD',       substr(TIME_YMDHIS, 0, 10));
define('TIME_HIS',       substr(TIME_YMDHIS, 11, 8));

const SESSION = [
    'KEY' => 'betblesess',
    'COOKIE_TIME' => 60 * 60 * 24 * 15,
    'TIME' => 60 * 60 * 24 * 15,
    'PHPSESSID' => 'PHPREDIS_SESSION:'
];

const security = [
    'iss' => 'cybertronchain.com',
    'alg' => 'HS256',
    'exp' => 60 * 60 * 24 * 15,
    'refresh_exp' => 60 * 60 * 24 * 15,
    'secret_token' => '^MKdsc6JshmQ9XBSnOHk9FRy4gt-QZqhfb1xiWDTuG7u$',
    'secret_password' => '^poxxevm7ujsa2q$',
];
const API_TYPE = 'wallet';
const GOOGLE = [
    'credentials' => __DIR__ . '/client_secret_896244388754-it6j45n0e68bgjpc2usifjog52icnfnf.apps.googleusercontent.com.json'
];
const PURCHASE = [
    'PACKAGE_NAME' => 'com.cybertronchain.wallet2',
    'WALLET' => 'ca-app-pub-4386406738424643~4004351005',
    'ADMOB' => 'ca-app-pub-4386406738424643/3373795614'
];

