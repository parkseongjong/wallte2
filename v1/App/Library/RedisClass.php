<?php
namespace App\Library;

/**
 * Class RedisClass
 *
 * @author seungo.jo
 * @since 2019-11-14
 */
class RedisClass
{
    /**
     * @var
     */
    protected static $master;
    
    /**
     * @var
     */
    protected static $slave;

    /**
     * @var
     */
    protected static $data;
    
    /**
     * constructor.
     */
    public function __construct()
    {
    }
    
    /**
     * @param $db
     * @return \Predis\Client
     */
    public static function master($db = null)
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
    
    /**
     * @param null $db
     * @return \Predis\Client
     */
    public static function slave($db = null)
    {
        $db = $db ? $db : REDIS['SLAVE']['database'];
        if (is_null(self::$slave) === true) {
            self::$slave = new \Predis\Client([
                'scheme' => 'tcp',
                'host' => REDIS['SLAVE']['host'],
                'port' => REDIS['SLAVE']['port'],
                'database' => $db
            ], [
                'prefix' => REDIS['SLAVE']['prefix'],
            ]);
        }
        
        return self::$slave;
    }

    /**
     * @param null $db
     * @return \Predis\Client
     */
    public static function data($db = null)
    {
        if (is_null(self::$data) === true) {
            try {
                self::$data = new \Predis\Client([
                    'scheme' => 'tcp',
                    'host' => REDIS['DATA']['host'],
                    'port' => REDIS['DATA']['port'],
                    'database' => $db ?? REDIS['DATA']['database']
                ], [
                    'prefix' => REDIS['DATA']['prefix'],
                ]);
            } catch (\Exception $e) {

            }
        }

        return self::$data;
    }
}
