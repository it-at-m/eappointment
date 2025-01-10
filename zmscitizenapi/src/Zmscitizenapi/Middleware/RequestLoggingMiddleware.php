<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Services\Core\LoggerService;
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

    /**
     * @param LoggerService $logger Service for logging requests and responses
     */
    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Process an incoming server request and log its details
     *
     * @param ServerRequestInterface $request The request to process
     * @param RequestHandlerInterface $handler The handler to process the request
     * @return ResponseInterface The resulting response
     * @throws \Throwable If an error occurs during request handling
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $response = $handler->handle($request);

            $responseToLog = clone $response;
            $responseToLog->getBody()->rewind();

            $this->logger->logRequest($request, $responseToLog);

            return $response;
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }
}