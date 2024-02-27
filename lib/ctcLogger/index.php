<?php
exit();
include_once ('./Logger.php');

use CtcLogger\Logger as log;


$test = new log('barry','hendle');
//정의 메소드 테스트
$test->error('햐햐햐',['admin_id'=>111,'user_id'=>2222,'url'=>'hghh','action'=>'S']);
$test->log(100,'햐햐햐',['admin_id'=>111,'user_id'=>2222,'url'=>'hghh','action'=>'A']);
?>