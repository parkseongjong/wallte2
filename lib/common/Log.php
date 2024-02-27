<?php declare(strict_types=1);

namespace wallet\common;

use wallet\ctcDbDriver\Driver as walletDb;
use \Exception;

//기존에 사용하던 어드민 system_log 상속
use CtcLogger\Logger as ctcAdminLog;
use CtcLogger\common\Util as ctcAdminUtil;

include_once (dirname(__FILE__).'/../ctcLogger/Logger.php');
include_once (dirname(__FILE__).'/../ctcLogger/Util.php');

class Log extends ctcAdminLog{

    public function __construct()
    {
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();
        parent::__construct('walletUser', $walletDb);
    }

    public function addRecord(int $level, string $message, array $context = []): bool
    {
        try {
            $util = ctcAdminUtil::singletonMethod();
            $levelName = static::getLevelName($level);

            //ctcLogger 유틸로 불러오는 데이터들은 정의가 안되어 있다면 넣어주기...

            $record = [
                'channel' => $this->name,
                'log_level' => $level,
                'user_id' => $util->getUserSession() !== null ? $util->getUserSession() : 0,
                'url' => $util->getUrl(),
                'description' => '[' . $levelName . ']' . $message,
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : 'NOT FOUND',
                'user_ip' => isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : 'NOT FOUND',
                //'created' => (string) date('Y-m-d H:i:s', time()) SQL timestamp 사용
            ];

            foreach ($context as $key => $value){
                static::getFieldsName($key);
                if($key == self::ACTION){
                    $record[self::ACTION] = static::getActionName($value);
                }
                else{
                    $record[$key] = $value;
                }
            }
            unset($context);

            //var_dump($record);
            //핸들러는 CTC Model 뿐, 따로 분리안함..
            if(!self::getClass($this->handlers)){
                throw new Exception('잘못된 핸들러 입니다.');
            }


            //wallet 일때 DB 드라이버를 다르게..
            if($this->name == 'W'){
                $insertProc = $this->handlers->insert('system_user_log',$record);
                if(!$insertProc){
                    throw new Exception('DB 반영에 실패 하였습니다.');
                }
            }
            else{
                throw new Exception('채널에 맞는 DB 드라이버 테이블이 존재하지 않습니다.');
            }
            return true;
        }
        catch (InvalidArgumentException $e) {
            echo('msg : '.$e->getMessage());
            exit();
        }
        catch (Exception $e) {
            echo('msg : '.$e->getMessage());
            exit();
        }
    }
}

?>
