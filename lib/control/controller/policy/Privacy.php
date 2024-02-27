<?php

namespace control\controller\Policy;

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

class Privacy
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function json(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $util = walletUtil::getInstance();
        $filter = walletFilter::getInstance();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        $targetPostData = array(
            'version' => 'string'
        );
        $filterData = $filter->postDataFilter($args,$targetPostData);
        unset($targetPostData);

        if(!$filterData['version']){
            throw new Exception($this->container->get('langArray')['commonErrorString01'].'(1)', 403);
        }

        $policyInfo = $walletDb->createQueryBuilder()
            ->select('*')
            ->from('policy')
            ->where('p_type = ?')
            ->andWhere('p_version = ?')
            ->setParameter(0, 'PRIVACY')
            ->setParameter(1,$filterData['version'])
            ->execute()->fetch();
        if(!$policyInfo){
            $policyInfo['p_content'] = false;
        }

        $returnArray['data'] = [
           'policyContents' => $policyInfo['p_content']
        ];

        $response->getBody()->write($util->success($returnArray));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }

}

?>