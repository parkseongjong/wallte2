<?php

namespace wallet\ctcDbDriver;

use \Doctrine\DBAL\DriverManager as DbalDb;
use \PDO;
use \Exception;

//include_once (__DIR__.'/../../config/config.php'); 에 db 정보가 담겨 있음.

class Driver{

    private $connectionParams = array(
        'dbname' => DB_NAME,
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'host' => DB_HOST,
        'driver' => 'pdo_mysql',
    );

    private $connectionTestStorageParams = array(
        'dbname' => 'wallet_test',
        'user' => DB_USER,
        'password' => DB_PASSWORD,
        'host' => DB_HOST,
        'driver' => 'pdo_mysql',
    );

    public static function getInstance(){
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }
    public static function singletonMethod(){
        return self::getInstance();// static 멤버 함수 호출
    }
    protected function __construct() {

    }
    private function __clone(){

    }
    private function __wakeup(){

    }

    //old driver
    public function ctcWallet(){
        try{
            return getDbInstance();
        }
        catch(Exception $e){
            return 'CTC WALLET DB초기화 안내: ' .$e->getMessage();
        }
    }

    //new driver
    public function init(){
        try{
            $conn = DbalDb::getConnection($this->connectionParams);
            //$queryBuilder = $conn->createQueryBuilder();
            //쿼리빌더는 쿼리 짤 때 마다 불러오기...
            return($conn);
        }
        catch(Exception $e){
            return 'WALLET DB초기화 안내: ' .$e->getMessage();
        }
    }

    //new driver
    public function initTestStorage(){
        try{
            $conn = DbalDb::getConnection($this->connectionTestStorageParams);
            //$queryBuilder = $conn->createQueryBuilder();
            //쿼리빌더는 쿼리 짤 때 마다 불러오기...
            return($conn);
        }
        catch(Exception $e){
            return 'WALLET DB초기화 안내: ' .$e->getMessage();
        }
    }
}