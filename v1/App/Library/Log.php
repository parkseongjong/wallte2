<?php
namespace App\Library;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class Log
{
    /**
     * Constructor.
     *
     */
    public function __construct($channel = 'API')
    {
        $filename = $channel . '_'.date('Y_m_d').'.log';
        $url = __DIR__ . '/../../log/' . $filename;
        if (APP_ENV == 'production') {
            $url = '/var/www/html/wallet2/v1/log/' . $filename;
        }

        $this->instance = new Logger($channel);
        $this->instance->pushHandler(new StreamHandler($url, Logger::DEBUG));
    }
}