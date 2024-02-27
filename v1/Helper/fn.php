<?php

/**
 * Get instance of DB object
 */

if (!function_exists('getDateTime')) :
    function getDateTime($times)
    {
        return date('Y-m-d H:i:s', $times);
    }
endif;

/**
 * Get instance of DB object
 */
function getLangException($code)
{
    $message = LANG['EXCEPTION'][$code];
    if (empty($message) === true) {
        foreach (explode("_", $code) as $item) {
            $arr[] = ucwords($item);
        }
        $message = implode(" ", $arr);
    }

    return $message;
}

/**
 * @param $userIdx
 * @return mixed
 */
function fnGetUserLevel($userIdx)
{
    global $u;

    $userInfo = $u->userInfo($userIdx);

    $userInfo['level'] = 1;
    if ($userInfo['payAmount'] > 0 && $userInfo['expAmount'] >= 50000) {
        $userInfo['level'] = 2;
    } else if ($userInfo['payAmount'] > 0 && $userInfo['expAmount'] >= 50000) {
        $userInfo['level'] = 3;
    } else if ($userInfo['payAmount'] > 50000 && $userInfo['expAmount'] >= 30000000) {
        $userInfo['level'] = 4;
    } else if ($userInfo['payAmount'] >= 50000 && $userInfo['expAmount'] >= 100000000) {
        $userInfo['level'] = 5;
    } else if ($userInfo['payAmount'] >= 50000 && $userInfo['expAmount'] >= 500000000) {
        $userInfo['level'] = 6;
    } else if ($userInfo['payAmount'] >= 50000 && $userInfo['expAmount'] >= 1000000000) {
        $userInfo['level'] = 7;
    }

    return $userInfo['level'];
}

function checkArrayValue($arr, $key, $val = null)
{
    if (!is_array($arr)) return $val;
    return (array_key_exists($key, $arr) && ($arr[$key] !== null && $arr[$key] !== "")) ? $arr[$key] : $val;
}

function alertAndMove($m, $mode = "")
{
    $returnUrl = urlencode($_SERVER['REQUEST_URI']);
    switch ($mode) {
        case "login" :
            echo "<script type=\"text/javascript\">alert('" . $m . "');document.location.href='/members/login.php?returnUrl={$returnUrl}';</script>";
            break;
        case "back" :
            echo "<script type=\"text/javascript\">alert('" . $m . "');history.back(-1);</script>";
            break;
        case "close" :
            echo "<script type=\"text/javascript\">alert('" . $m . "');window.close();</script>";
            break;
        case "reload" :
            echo "<script type=\"text/javascript\">alert('" . $m . "');opener.location.reload();window.close();</script>";
            break;
        default:
            echo "<script type=\"text/javascript\">alert('" . $m . "');history.back(-1);</script>";
    }
    exit;
}

function alertAndMovePage($url, $m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "');document.location.href=\"" . $url . "\";</script>";
    die();
}

function faqMovePage($url)
{
    echo "<script type=\"text/javascript\">document.location.href=\"" . $url . "\";</script>";
    die();
}

function gotoUrl($url)
{
    echo "<script type='text/javascript'> location.replace('$url'); </script>";
    exit;
}

function openerGotoUrl($url)
{
    echo "<html><body><script type='text/javascript'> opener.location.href = '$url';self.close() </script></body></html>";
    exit;
}

function isMobile()
{
    if ($_COOKIE['enablePcVersion'] === 'Y') {
        return false;
    }

    $arrMobie = ["iPhone", "iPod", "IPad", "Android", "Blackberry", "SymbianOS|SCH-M\d+", "Opera Mini", "Windows CE", "Nokia", "Sony", "Samsung", "LGTelecom", "SKT", "Mobile", "Phone"];
    foreach ($arrMobie as $m) {
        if (preg_match("/$m/i", strtolower(checkArrayValue($_SERVER, 'HTTP_USER_AGENT')))) {
            return 'm';
            break;
        }
    }
    return false;
}

function alertAndReload($url, $m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "' , function(){ document.location.href=\"" . $url . "\" });</script>";
    die();
}

function alertAndPopClose($m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "');window.close();</script>";
    die();
}

function alertAndPopLoginClose($m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "' );window.opener.location.href=\"#/members/login.php\";window.close(); </script>";
    die();
}

function alertAndMoveBack($m, $mode = "")
{
    if ($mode == "back") {
        echo "<script type=\"text/javascript\">alert('" . $m . "', function(){ history.back(-1); });</script>";
        die();
    }
    if (checkArrayValue($_SERVER, "HTTP_REFERER")) {
        echo "<script type=\"text/javascript\">alert('" . $m . "');document.location.href=\"" . $_SERVER['HTTP_REFERER'] . "\";</script>";
    } else {
        echo "<script type=\"text/javascript\">alert('" . $m . "');history.back(-1);</script>";
    }
    die();
}

function alert($m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "')</script>";
}

function alertAndClose($m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "' , function(){ self.close(); });</script>";
}

function alertAndClose2($m)
{
    echo "<script type=\"text/javascript\">alert('" . $m . "'); self.close(); </script>";
}

function setJsVariants($arrData, $name, $depth = 0)
{
    if ($depth == 0) {
        echo "var " . $name . " = new Array;\n";
    } else {
        echo $name . " = new Array;\n";
    }

    foreach ($arrData as $key => $value) {
        if (@sizeof($value) > 1) {
            if (is_int($key)) setJsVariants($value, $name . "[" . $key . "]", 1);
            else setJsVariants($value, $name . "['" . $key . "']", 1);
        } else {
            if (is_int($key)) echo $name . "[" . $key . "] = '" . addslashes($value) . "';\n";
            else echo $name . "['" . $key . "'] = '" . addslashes($value) . "';\n";
        }
    }

}

function getWaitTime($timediffer)
{
    $day = floor(($timediffer)/(60*60*24));
    $hour = floor(($timediffer-($day*60*60*24))/(60*60));
    $minute = floor(($timediffer-($day*60*60*24)-($hour*60*60))/(60));
    $second = $timediffer-($day*60*60*24)-($hour*60*60)-($minute*60);

    $res = $day ? $day."일 " : '';
    $res .= $hour ? $hour."시간 " : '';
    $res .= $minute ? $minute."분 " : '';
    $res .= $second ? $second."초 " : '';
    return $res;
}

function getCreatetime($datetime)
{
    $time = strtotime($datetime);
    $current = strtotime(date("Y-m-d H:i:s"));

    if ($time <= $current - 86400 * 365) {
        $str = (int)(($current - $time) / (86400 * 365)) . "년전";
    } else if ($time <= $current - 86400 * 31) {
        $str = (int)(($current - $time) / (86400 * 31)) . "개월전";
    } else if ($time <= $current - 86400 * 1) {
        $str = (int)(($current - $time) / 86400) . "일전";
    } else if ($time <= $current - 3600 * 1) {
        $str = (int)(($current - $time) / 3600) . "시간전";
    } else if ($time <= $current - 60 * 1) {
        $str = (int)(($current - $time) / 60) . "분전";
    } else {
        $str = (int)($current - $time) . "초전";
    }

    return $str;
}

function checkRequest($txt, $mode)
{
    switch ($mode) {
        case "num" :
            $txt = (int)preg_replace('/[^0-9]/', '', $txt);
            return $txt;
            break;
        case "char" :
            $txt = preg_replace('/[^a-z0-9]/i', '', $txt);
            return $txt;
            break;
        case "type" :
            $txt = preg_replace('/[^a-z0-9\-\_]/i', '', $txt);
            return $txt;
            break;
        case "id" :
            $txt = preg_replace('/[^a-z0-9@\_\*]/i', '', $txt);
            return $txt;
            break;
        case "pw" :
            $txt = preg_replace('/[^a-zA-Z0-9가-힣~!@#$%<>^&]/', '', $txt);
            return $txt;
        case "date" :
            $txt = preg_replace('/[^0-9\-\:\s]/i', '', $txt);
            return $txt;
            break;
        case "json" :
            $txt = preg_replace('/[ #\&\+\-%@=\/\\;,\'\^~\_|\!\?\*$#<>()]/i', '', $txt);
            return $txt;
            break;
        case "nick" :
            $txt = preg_replace('/[^a-z0-9가-힣_]/i', '', $txt);
            return $txt;
            break;
        case "search" :
            $txt = preg_replace('/[^a-z0-9가-힣\-\[\]\(\)]/i', '', $txt);
            return $txt;
            break;
        case "betidx" :
            $txt = preg_replace('/[^0-9\;]/i', '', $txt);
            return $txt;
            break;
        case "msg" :
        case "all" :
            $txt = preg_replace('/[^_a-z0-9가-힣~\!@\#$%<>^&\?\:\/\-\"\.\n\r\s()\=\;\,\+]/i', '', $txt);
            return $txt;
            break;
        default :
            return '';
    }

}


if (!function_exists('debug')) :
    function debug()
    {
        $args = func_get_args();
        $themes = [
            'default' => 'color:#000080;background-color:#eeeeea;',
            'warring' => 'color:#ff0033;background-color:#FFC7CE',
            'error' => 'color:red;background-color:#333;',
            'info' => 'color:#000080;background-color:#F7A694;',
            'stick' => 'color:#FF2200;background-color:#F8D79B;',
            'memo' => 'color:black;background-color:#FFFFCC',
            'blue' => 'color:black;background-color:#CBE9FF',
            'green' => 'color:#FFFFF1;background-color:#2E762D',
            'lame' => 'color:#cc0066;background-color:#99FF99',
            'black' => 'color:#FFFFFF;background-color:black;',
        ];
        $theme = $themes['black'];
        $inner_style = '';
        $data = null;
        $is_skip = false;
        $content_type = null;

        foreach ($args as $index => $arg) {
            if (is_string($arg)) {
                $lower = strtolower(trim($arg));
                if (preg_match('/^(theme:)([\w\d\-\_]+)$/', $lower, $m)) {
                    if ($themes[$m[2]]) {
                        $theme = $themes[$m[2]];
                        continue;
                    }
                } else if (preg_match('/^(style[\t\s]*["\']?=)([^\t\s]+)/i', $lower, $m)) {
                    $inner_style = $m[1];
                    continue;
                } else if ($lower == 'force') {
                    $is_skip = false;
                    continue;
                }
            }
            $data[] = $arg;
        }
        if ($is_skip) {
            return true;
        } else if (count($data) <= 1) {
            $data = array_shift($data);
        }

        $content = is_scalar($data) ? $data : print_r($data, true);

        if (php_sapi_name() == 'cli') {
            print "\n{$content}\n";
        } else {
            $accept = $_SERVER['HTTP_ACCEPT'];
            if (!empty($accept)) {
                if (strpos($accept, 'application/json') !== false) {
                    $content_type = 'json';
                } else if (strpos($accept, 'text/javascript') !== false) {
                    $content_type = 'js';
                } else if (strpos($accept, 'text/xml') !== false) {
                    $content_type = 'xml';
                } else if (strpos($accept, 'text/plan') !== false) {
                    $content_type = 'text';
                }
            }

            switch ($content_type) {
                case 'json' :
                {
                    print "\n{$content}\n";
                    break;
                }
                case 'js' :
                {
                    print 'var message=' . json_encode(!is_array($content) ? [$content] : $content) . ';';
                    print 'alert(message.join(\'\n\'))';
                    break;
                }
                default:
                {
                    print '<div style="clear:both;margin:5px auto;padding:5px;zoom:1;display:block;text-align:left;' . $theme . '">';
                    print '<xmp style="margin:5px 0px;padding:5px;text-align:left;word-break:break-all;word-wrap:break-word;white-space:pre-wrap;line-height:1.5em;font-size:11px;font-family:Verdana;' . $theme . ';' . $inner_style . '">';
                    print $content;
                    print '</xmp></div>';
                    break;
                }
            }
        }
    }
endif;

function output($vars = [], $options = 'NUMERIC')
{
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    $responses = [];
    if (is_array($vars) === false) {
        $responses = $vars;
    } else {
        $responses = $vars;
    }

    switch ($options) {
        case 'NUMERIC':
            echo json_encode($responses, JSON_NUMERIC_CHECK);
            break;
        case "TAG":
            echo json_encode($responses, JSON_HEX_TAG);
            break;
        case "APOS":
            echo json_encode($responses, JSON_HEX_APOS);
            break;
        case "QUOT":
            echo json_encode($responses, JSON_HEX_QUOT);
            break;
        case "AMP":
            echo json_encode($responses, JSON_HEX_AMP);
            break;
        case "UNICODE":
            echo json_encode($responses, JSON_UNESCAPED_UNICODE);
            break;
        case "OBJECT":
            echo json_encode($responses, JSON_FORCE_OBJECT);
            break;
        case "ALL":
            echo json_encode($responses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
            break;
        default:
            echo json_encode($responses);
            break;
    }
    die;
}

function telegramPush($message)
{
    $key = TELEGRAM['QNA']['KEY'];
    $chatId = TELEGRAM['QNA']['ID'];
    $url = "https://api.telegram.org/bot{$key}/sendmessage?chat_id={$chatId}&text={$message}";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {

    }
}

function telegramPushError($message)
{
    $key = TELEGRAM['ERROR']['KEY'];
    $chatId = TELEGRAM['ERROR']['ID'];
    $url = "https://api.telegram.org/bot{$key}/sendmessage?chat_id={$chatId}&text={$message}";
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        curl_close($ch);
    } catch (Exception $e) {

    }
}

function getVideoImgCount($content)
{
    $match_size = preg_match_all("/<img.*?src=['\"]?([^>\"'\s?]+)['\"]?.*?>/i", stripcslashes($content), $out);

    $photo_count = 0;
    for ($i = 0; $i < $match_size; $i++) {
        $photo_count++;
    }

    $video_count = preg_match_all("/<figure class=\"media\">([^`]*?)<\/figure>/", stripcslashes($content), $out);

    return [
        'photo' => $photo_count,
        'video' => $video_count,
    ];
}

/**
 * @param int $length
 * @return string
 */
function generateString($length = 20)
{
    $characters = "0123456789";
    $characters .= "abcdefghijklmnopqrstuvwxyz";
    $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    $stringGenerated = "";

    $nmrLoops = $length;
    while ($nmrLoops--) {
        $stringGenerated .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    return $stringGenerated;
}

/**
 * Sort array by Bubble sort
 */
function sortByCondition($arr, $f)
{
    $sortIndexs = array();
    $arraySize = count($arr);
    $temp = '';

    foreach ($arr as $obj) {
        array_push($sortIndexs, $f($obj));
    }

    for ($i = 0; $i < $arraySize; $i++) {
        for ($j = 0; $j < $arraySize - 1; $j++) {
            if ($sortIndexs[$j] > $sortIndexs[$j + 1]) {
                $temp = $arr[$j];

                $arr[$j] = $arr[$j + 1];
                $arr[$j + 1] = $temp;
            }
        }
    }

    return $arr;
}


function isOfficeIp($addr)
{
    switch (true) {
        case ($addr === "119.196.13.33") :
            return true;
        default :
            return false;
    }
}

function isSecure()
{
    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $isSecure = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
        $isSecure = true;
    }
    return $isSecure ? true : false;
}

function convertContents($contents)
{
    $contents = substr(trim($contents), 0, 65536);
    $contents = preg_replace("#[\\\]+$#", "", $contents);
    if (substr_count($contents, '&#') > 50) {
        die(json_encode(setResultArray(0, 1, "내용에 올바르지 않은 코드가 다수 포함되어 있습니다.")));
    }
    return $contents;
}

function getBrowser()
{
    $broswerList = array('MSIE', 'Chrome', 'Firefox', 'iPhone', 'iPad', 'Android', 'PPC', 'Safari', 'Trident', 'none');
    $browserName = 'none';

    foreach ($broswerList as $userBrowser) {
        if ($userBrowser === 'none') break;
        if (strpos($_SERVER['HTTP_USER_AGENT'], $userBrowser)) {
            $browserName = $userBrowser;
            break;
        }
    }
    return $browserName;
}

if (!function_exists('custom_encrypt')): {
    function custom_encrypt($string)
    {
        $key = hash(CUSTOM_ENCRYPTION_INFO['HASH'], CUSTOM_ENCRYPTION_INFO['KEY']);
        $iv = substr(hash(CUSTOM_ENCRYPTION_INFO['HASH'], CUSTOM_ENCRYPTION_INFO['IV']), 0, 16);
        $output = openssl_encrypt($string, CUSTOM_ENCRYPTION_INFO['METHOD'], $key, 0, $iv);
        return base64_encode($output);
    }
}
endif;

if (!function_exists('custom_decrypt')): {
    function custom_decrypt($string)
    {
        $key = hash(CUSTOM_ENCRYPTION_INFO['HASH'], CUSTOM_ENCRYPTION_INFO['KEY']);
        $iv = substr(hash(CUSTOM_ENCRYPTION_INFO['HASH'], CUSTOM_ENCRYPTION_INFO['IV']), 0, 16);
        $value = base64_decode($string);
        return openssl_decrypt($value, CUSTOM_ENCRYPTION_INFO['METHOD'], $key, 0, $iv);
    }
}
endif;

if (!function_exists('randomId')): {
    function randomId()
    {
        $string = implode('', array_merge(range('a', 'z'), range('0', '9')));
        $randChar = substr(str_shuffle($string), 0, 8);

        list($usec, $sec) = explode(' ', microtime());
        $key = (string)$sec . str_replace('0.', '', (string)$usec);

        return $randChar . md5($key);
    }
}
endif;

