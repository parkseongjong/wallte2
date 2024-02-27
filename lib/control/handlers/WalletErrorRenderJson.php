<?php
declare(strict_types=1);

namespace control\handlers;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

use wallet\common\Util as walletUtill;

class WalletErrorRenderJson implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if($displayErrorDetails === true){
            var_dump($exception->getCode());
            var_dump($exception->getMessage());
            var_dump($exception->getLine());
            var_dump($exception->getFile());
        }

        $utill = walletUtill::singletonMethod();
        $returnArray = array();

        //error 핸들링에서.. 다국어를 가져와서 쓸 방법이 없나..?
        //익셉션 발생 시키는 곳에서 다국어로..?
        $returnArray['code'] = $exception->getCode();
        $returnArray['msg'] = $exception->getMessage();
        return $utill->jsonEncode($returnArray);
    }
}

?>