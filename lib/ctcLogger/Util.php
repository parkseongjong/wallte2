<?php
namespace CtcLogger\common;

use \Exception;

class Util{
	
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
	
	public function getAdminSession(){
		return $_SESSION['user_id'];
	}

	public function getUserSession(){
		return $_SESSION['user_id'];
	}

	public function getUrl(){
		return $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
}

?>