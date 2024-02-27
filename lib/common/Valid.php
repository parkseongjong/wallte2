<?php

namespace wallet\common;

class Valid{

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

    public function emailRegex($plainData){
        $regex = "/(?:[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*"
            . "|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")"
            . "@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]"
            . "*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}"
            . "(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:"
            . "(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/";

        if(!preg_match($regex, $plainData)){
            return false;
        }
        else{
            return true;
        }
    }

    public function phoneRegex($plainData,$type = null){
        if($type == 'normal'){
            $regex = '/(\d{2,4})[ ,-](\d{2,4})[ ,-](\d{2,4})/m';
        }
        else{
            $regex = '/(\d{3})(\d{2,4})(\d{3,4})/m';
        }

        if(!preg_match($regex, $plainData)){
            return false;
        }
        else{
            return true;
        }
    }

    public function intRegex($plainData){
        $regex = '/^[0-9]/';

        if(!preg_match($regex, $plainData)){
            return false;
        }
        else{
            return true;
        }
    }
}

?>
