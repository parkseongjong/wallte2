<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
echo 'MySQLi 사용 '.(class_exists('mysqli') ? '가능' : '불가').'<br>';
$hostname = 'arch.cybertronchain.com';
$username = 'psj';
$password = '6634tjdwhd@';
$dbname = 'wallet';

$mysqli = new mysqli($hostname,$username,$password,$dbname);
if ( $mysqli->connect_error ) exit('접속 실패 : '.$mysqli->connect_error);
echo '접속 성공';
