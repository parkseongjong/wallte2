<?php declare(strict_types=1);

/*
 *
 *  PSR-3
 *  mono log 2.1.1 참조
 *  psr/log  loggerInterface 1.1.3 참조
 *  100,200,300,400,500,600 주로 사용.
 *
 */
namespace CtcLogger;

use \CtcLogger\Log\LoggerInterface;
use \InvalidArgumentException;
use \Exception;
include_once (dirname(__FILE__).'/LoggerInterface.php');

/**
 * Class Logger
 * @package CtcLogger
 */
class Logger implements LoggerInterface{

    /**
     * @var string
     */
    /**
     * @var string
     */
    protected $name, $handlers;

    /**
     * Detailed debug information
     */
    public const DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    public const INFO = 200;

    /**
     * Uncommon events
     */
    public const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    public const WARNING = 300;

    /**
     * Runtime errors
     */
    public const ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    public const CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    public const ALERT = 550;

    /**
     * Urgent alert.
     */
    public const EMERGENCY = 600;

    /**
     * This is a static variable and not a constant to serve as an extension point for custom levels
     *
     * @var string[] $levels Logging levels with the levels as key
     */
    protected static $levels = [
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    /**
     *
     */
    public const WALLET = 'wallet'; //월렛 내 관리자 페이지

    /**
     *
     */
    public const WALLETUSER = 'walletUser'; //월렛 내 유저
    /**
     *
     */
    public const BARRY = 'barry'; // 베리 관리자 페이지
    /**
     *
     */
    public const CTC_WALLET = 'ctc'; // CTC 관리자 페이지

    /**
     * @var string[]
     */
    protected static $defaultChannel = [
        self::WALLETUSER => 'W',
        self::WALLET => 'W',
        self::BARRY => 'B',
        self::CTC_WALLET => 'C',
    ];

    /**
     *
     */
    public const SEARCH = 'S';
    /**
     *
     */
    public const ADD = 'A';
    /**
     *
     */
    public const EDIT = 'E';
    /**
     *
     */
    public const DELETE = 'D';
    /**
     *
     */
    public const DOWNLOAD = 'DOWNLOAD';

    /**
     * @var int[]
     */
    protected static $defaultAction = [
        self::SEARCH => 1,
        self::ADD => 2,
        self::EDIT => 3,
        self::DELETE => 4,
        self::DOWNLOAD => 5,
    ];

    /**
     *
     */
    public const ADMIN_ID = 'admin_id';
    /**
     *
     */
    public const USER_ID = 'user_id';
    /**
     *
     */
    public const TARGET_ID = 'target_id';
    /**
     *
     */
    public const URL = 'url';
    /**
     *
     */
    public const ACTION = 'action';

    /**
     * @var string[]
     */
    protected static $defaultFields = [
        self::ADMIN_ID => self::ADMIN_ID,
        self::USER_ID => self::USER_ID,
        self::TARGET_ID => self::TARGET_ID,
        self::URL => self::URL,
        self::ACTION => self::ACTION,
    ];


    /**
     * Logger constructor.
     * @param string $name
     * @param $handlers
     */
    public function __construct(string $name, $handlers)
    {
        try{
            $this->name = static::getChannelName($name);
            $this->handlers = $handlers;
        }
        catch (InvalidArgumentException $e) {
            echo('construct invalid! msg : '.$e->getMessage());
            exit();
        }

    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = []): void
    {
        $this->addRecord(static::DEBUG, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = []): void
    {
        $this->addRecord(static::INFO, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = []): void
    {
        $this->addRecord(static::NOTICE, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = []): void
    {
        $this->addRecord(static::WARNING, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = []): void
    {
        $this->addRecord(static::ERROR, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = []): void
    {
        $this->addRecord(static::CRITICAL, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = []): void
    {
        $this->addRecord(static::ALERT, (string) $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->addRecord(static::EMERGENCY, (string) $message, $context);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = []): void
    {
        $this->addRecord($level, (string) $message, $context);
    }

    /**
     * @param int $level
     * @return string
     */
    public static function getLevelName(int $level): string
    {
        if (!isset(static::$levels[$level])) {
            throw new InvalidArgumentException('레벨: "'.$level.'" 정의 된 레벨이 아닙니다. 사용 가능 레벨: '.implode(', ', array_keys(static::$levels)));
        }

        return static::$levels[$level];
    }

    /**
     * @param string $channel
     * @return string
     */
    public static function getChannelName(string $channel): string
    {
        if (!isset(static::$defaultChannel[$channel])) {
            throw new InvalidArgumentException('필드: "'.$channel.'" 정의 된 필드가 아닙니다. 사용 가능 필드: '.implode(', ', array_keys(static::$defaultChannel)));
        }
        return static::$defaultChannel[$channel];
    }


    /**
     * @param string $action
     * @return int
     */
    public static function getActionName(string $action): int
    {
        if (!isset(static::$defaultAction[$action])) {
            throw new InvalidArgumentException('액션: "'.$action.'" 정의 된 액션이 아닙니다. 사용 가능 액션: '.implode(', ', array_keys(static::$defaultAction)));
        }
        return static::$defaultAction[$action];
    }

    /**
     * @param string $fields
     * @return string
     */
    public static function getFieldsName(string $fields): string
    {
        if (!isset(static::$defaultFields[$fields])) {
            throw new InvalidArgumentException('필드: "'.$fields.'" 정의 된 필드가 아닙니다. 사용 가능 필드: '.implode(', ', array_values(static::$defaultFields)));
        }
        return static::$defaultFields[$fields];
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addRecord(int $level, string $message, array $context = []): bool
    {
        try {
            $levelName = static::getLevelName($level);
            /*
			*
			*  id,channel,level,admin_id,user_id,url,action,description,agent,ip,created,
			*  array = admin_id, user_id, url, action
			*
			*/

            $record = [
                'channel' => $this->name,
                'log_level' => $level,
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
				$insertProc = $this->handlers->insert('system_log',$record);
				if(!$insertProc){
					throw new Exception('DB 반영에 실패 하였습니다.');
				}
			}
			else{
				$insertProc = $this->handlers->insert($record);
				if(!$insertProc){
					throw new Exception('DB 반영에 실패 하였습니다.');
				}
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

    /**
     * @param object $object
     * @return string
     */
    protected static function getClass(object $object): string
    {
        $class = \get_class($object);
        return 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).'@anonymous' : $class;
    }
}
