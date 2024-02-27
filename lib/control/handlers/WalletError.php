<?php
declare(strict_types=1);

namespace control\handlers;

use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;
use Exception;
use Throwable;

class WalletError extends ErrorHandler
{
    public const BAD_REQUEST = 'BAD_REQUEST';
    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
    public const NOT_ALLOWED = 'NOT_ALLOWED';
    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';

    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $statusCode = 500;
        $type = self::SERVER_ERROR;
        $description = 'An internal error has occurred while processing your request.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $description = $exception->getMessage();

            if ($exception instanceof HttpNotFoundException) {
                $type = self::RESOURCE_NOT_FOUND;
            }
            elseif ($exception instanceof HttpMethodNotAllowedException) {
                $type = self::NOT_ALLOWED;
            }
            elseif ($exception instanceof HttpUnauthorizedException) {
                $type = self::UNAUTHENTICATED;
            }
            elseif ($exception instanceof HttpForbiddenException) {
                $type = self::UNAUTHENTICATED;
            }
            elseif ($exception instanceof HttpBadRequestException) {
                $type = self::BAD_REQUEST;
            }
            elseif ($exception instanceof HttpNotImplementedException) {
                $type = self::NOT_IMPLEMENTED;
            }
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
            && $this->displayErrorDetails
        ) {
            $description = $exception->getMessage();
        }

        /*
        $error = [
            'statusCode' => $statusCode,
            'error' => [
                'type' => $type,
                'description' => $description,
            ],
        ];
        */

//        $payload = json_encode($error, JSON_PRETTY_PRINT);
//        $response = $this->responseFactory->createResponse($statusCode);
//        $response->getBody()->write($payload);
//        $response = $this->responseFactory->createResponse($this->statusCode);

        $response = $this->responseFactory->createResponse($statusCode);
        if ($this->contentType !== null && array_key_exists($this->contentType, $this->errorRenderers)) {
            $response = $response->withHeader('Content-type', $this->contentType);
        } else {
            $response = $response->withHeader('Content-type', $this->defaultErrorRendererContentType);
        }

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $allowedMethods = implode(', ', $this->exception->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowedMethods);
        }

        $renderer = $this->determineRenderer();
        $body = call_user_func($renderer, $this->exception, $this->displayErrorDetails);
        $response->getBody()->write($body);


        return $response;
    }
}

?>