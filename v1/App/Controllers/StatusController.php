<?php
namespace App\Controllers;

use Exception;


/**
 * Class BetMatchController
 *
 * @package App\Controllers\BaseController
 * @author jso
 */
class StatusController extends BaseController
{

    /**
     * StatusController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     */
    public function index()
    {
	    global $db;
        $result = [];
        $result['APP_ENV'] = APP_ENV;
        $result['Core'] = self::getCpuCount();
        $result['LoadAvg'] = self::getLoadAvg();
        $result['IsOK'] = self::isOK();
        $result['SESSION'] = $_SESSION;
//        $result['SERVER'] = RedisClass::master()->ping('PING');
//        $MessagePush = MessagePush::getHealthStatus();
//        $userCount = MessagePush::getUserCount();
//        $result['MessagePush'] = json_decode($MessagePush);
//        $result['userCount'] = json_decode($userCount);

        static::responses($result);
    }

    /**
     * 유저 한명 토스트
     */
    public function pushUser()
    {
        try {
            MessagePush::send($_SESSION['id'], 'TOAST', '유저 타켓 벳블 토스트 테스트');
        } catch(Exception $e) {
            debug($e);
        }
    }

    /**
     * 전체유저 토스트 보내기
     */
    public function pushUserAll()
    {
        try {
            MessagePush::send('*', 'TOAST', '전체 유저 벳블 토스트 테스트');
        } catch(Exception $e) {
            debug($e);
        }
    }

    /**
     * @param int $threshold
     * @return bool
     */
    public function isOK($threshold = 100)
    {
        // threshold 초기화. 잘못 지정했다면 100% 로
        $threshold = (float)$threshold;
        if (!$threshold) {
            $threshold = 100;
        }

        // 최근 1분 load avg 가져오기
        $load_avg = self::getLoadAvg();

        // cpu 프로세서 수 가져오기
        $cpu_count = self::getCpuCount();
        if (!$cpu_count) {
            return false;
        }

        // 머신사용률 계산
        $load_avg_percentage = ($load_avg / $cpu_count) * 100;

        // 결과값 반환
        return $threshold > $load_avg_percentage;
    }

    /**
     * @return
     * @desc 최근 1분 load avg 반환
     */
    public function getLoadAvg()
    {
        // 함수가 있으면 함수 사용
        if (function_exists('sys_getloadavg')) {
            $load_avg = sys_getloadavg();
        } // 함수가 없으면 /proc/loadavg 파일을 읽어서 사용
        else {
            $load_avg_path = '/proc/loadavg';
            if (!file_exists($load_avg_path)) {
                return false;
            }

            $content = trim(file_get_contents($load_avg_path));
            if (!$content) {
                return false;
            }

            $load_avg = explode(' ', $content);
        }

        return $load_avg[0];
    }

    /**
     * @return
     * @desc cpu 프로세서 수 반환
     */
    public function getCpuCount($cpu_count = 0)
    {
        // 최초 1회만 카운트
        if ($cpu_count < 1) {

            $cpuinfo_path = '/proc/cpuinfo';
            if (!file_exists($cpuinfo_path)) {
                return false;
            }

            $fp = fopen($cpuinfo_path, 'r');

            if (!$fp) {
                return false;
            }

            while (($line = fgets($fp)) !== false) {

                if (strpos($line, 'processor') === false) {
                    continue;
                }

                ++$cpu_count;
            }

            fclose($fp);
        }

        return $cpu_count;
    }

    /**
     */
    public function phpInfo()
    {
        if (IS_OFFICE) {
            phpinfo();
        }
        die;
    }

    /**
     */
    public function serverInfo()
    {
        if (IS_OFFICE) {
            debug($_SERVER);
        }
        die;
    }

    public function isSecure()
    {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            $isSecure = true;
        }
        return $isSecure ? true : false;
    }

}
