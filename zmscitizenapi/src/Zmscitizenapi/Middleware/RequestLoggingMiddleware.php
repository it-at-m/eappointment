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

        LoggerService::logRequest($request, $response);

        return $response;
    }
}