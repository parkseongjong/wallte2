<?php
declare(strict_types=1);

namespace control\handlers;

use Slim\Interfaces\ErrorRendererInterface;
use Throwable;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

class WalletErrorRender implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if($displayErrorDetails === true){
            var_dump($exception->getCode());
            var_dump($exception->getMessage());
            var_dump($exception->getLine());
            var_dump($exception->getFile());
        }
        
        $templates = new plateTemplate(WALLET_PATH . '/skin/error', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));
        $returnArray = array(
            'info' => [
                'asstsUrl' => WALLET_URL . '/skin/common/assets',
            ]
        );
        if($exception->getCode() == 406 || $exception->getCode() == 403){
            $returnArray['info']['code'] = $exception->getCode();
            $returnArray['info']['msg'] = $exception->getMessage();
        }
        else if($exception->getCode() == 404){
            //404 코드 별도로 ... 빼서 쓰기.. (다국어 문제)
            $returnArray['info']['code'] = $exception->getCode();
            $returnArray['info']['msgArray'] = $exception->getMessage();
        }
        else if($exception->getCode() == 200){
            $returnArray['info']['code'] = $exception->getCode();
            $returnArray['info']['msg'] = $exception->getMessage();
        }
        else{
            //error 핸들링에서.. 다국어를 가져와서 쓸 방법이 없나..?
            $returnArray['info']['code'] = $returnArray['info']['msg'] = false;
        }
        $randerData = $templates->render('p404',$returnArray);
        return $randerData;
    }
}

?>