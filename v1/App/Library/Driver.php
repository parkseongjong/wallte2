<?php

namespace App\Library;

use Doctrine\DBAL\DriverManager;
use Exception;

class Driver
{
    protected function __construct(){}

    public static function init()
    {
        try {
            return DriverManager::getConnection([
                'dbname' => DB_NAME,
                'user' => DB_USER,
                'password' => DB_PASSWORD,
                'host' => DB_HOST,
                'driver' => 'pdo_mysql',
            ]);
        } catch (Exception $e) {
            return 'WALLET DB초기화 안내: ' . $e->getMessage();
        }
    }

    /**
     * @param $db
     * @return \Predis\Client
     */
    public static function redis($db = null)
    {
        $db = $db ? $db : REDIS['MASTER']['database'];
        if (is_null(self::$master) === true) {
            self::$master = new \Predis\Client([
                'scheme' => 'tcp',
                'host' => REDIS['MASTER']['host'],
                'port' => REDIS['MASTER']['port'],
                'database' => $db,
            ], [
                'prefix' => REDIS['MASTER']['prefix'],
            ]);
        }

        return self::$master;
    }
}

/* End of file Driver.php */
/* Location: /App/Library/Driver.php */