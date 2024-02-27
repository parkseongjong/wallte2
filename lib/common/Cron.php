<?php

namespace wallet\common;

use wallet\common\Filter as walletFilter;
use wallet\ctcDbDriver\Driver as walletDb;

class Cron{

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

    public function mainCron($source, $sourceType, $ip, $date, $description){
        $walletDb = walletDb::getInstance();
        $walletDb = $walletDb->init();

        $insertProc = $walletDb->createQueryBuilder()
            ->insert('cron')
            ->setValue('c_source','?')
            ->setValue('c_source_type','?')
            ->setValue('c_ip','?')
            ->setValue('c_description', '?')
            ->setValue('c_datetime','?')
            ->setParameter(0,$source)
            ->setParameter(1,$sourceType)
            ->setParameter(2,$ip)
            ->setParameter(3,$description)
            ->setParameter(4,$date)
            ->execute();
        if(!$insertProc){
            return false;
        }

        return $walletDb->lastInsertId();
    }

    public function mainCronLog($cronId, $targetId, $targetTable, $responseType, $responseMsg, $date){
        $walletDb = walletDb::getInstance();
        $walletDb = $walletDb->init();
        $insertProc = $walletDb->createQueryBuilder()
            ->insert('cron_log')
            ->setValue('c_id','?')
            ->setValue('cl_target_id','?')
            ->setValue('cl_target_table','?')
            ->setValue('cl_response_type','?')
            ->setValue('cl_response_message','?')
            ->setValue('cl_datetime','?')
            ->setParameter(0,$cronId)
            ->setParameter(1,$targetId)
            ->setParameter(2,$targetTable)
            ->setParameter(3,$responseType)
            ->setParameter(4,$responseMsg)
            ->setParameter(5,$date)
            ->execute();
        if(!$insertProc){
            return false;
        }

        return true;
    }

}

?>
