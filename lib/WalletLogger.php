<?php
namespace WalletLogger;
use CtcLogger\Logger as log;
use CtcLogger\common\Util as util;
use wallet\ctcDbDriver\Driver as walletDb;

include_once (dirname(__FILE__).'/ctcLogger/Logger.php');
include_once (dirname(__FILE__).'/ctcLogger/Util.php');

//autoload를 전역으로 사용해도 문제 없다고 판단되어, autoloa로 변경.
//2021.11.08 By.OJT
//include_once (dirname(__DIR__).'/config/config.php');

class Logger{

	//admin init
	public function init(){
		$walletDb = walletDb::singletonMethod();
		$walletDb = $walletDb->ctcWallet();
		return new log('wallet',$walletDb);
	}

	public function initUtil(){
		return util::singletonMethod();
	}
}
?>
