<?php

declare(strict_types=1);

namespace BO\Slim\Middleware;

use BO\Slim\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware for logging HTTP requests and responses
 */
class RequestLoggingMiddleware implements MiddlewareInterface
{
    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            $this->logger->logRequest($request, $response);
            return $response;
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }
}
