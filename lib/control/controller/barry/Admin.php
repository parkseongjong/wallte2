<?php

namespace control\controller\barry;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpSpecializedException;
use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class Admin
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    public function member(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        var_dump($request->getParsedBody());

        $response->getBody()->write('hi');
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

}

?>