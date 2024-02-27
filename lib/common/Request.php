<?php

namespace wallet\common;

use Slim\Factory\ServerRequestCreatorFactory;

class Request{

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

    public function getRequest(){
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $contentType = $request->getHeaderLine('Content-Type');
        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            $request = $request->withParsedBody($contents);
        }
        return $request;
    }
}

?>
