<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestLoggingMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request, 
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $response = $handler->handle($request);
        
        if ($response->getStatusCode() >= 400) {

            LoggerService::logRequest($request, $response);
        } else {
            LoggerService::logRequest($request, $response);
            /*LoggerService::logInfo('Processing request', [
                'path' => $request->getUri()->getPath(),
                'method' => $request->getMethod()
            ]);*/
        }
        
        return $response;
    }
}