<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestSizeLimitMiddleware implements MiddlewareInterface
{
    private const ERROR_TOO_LARGE = 'requestEntityTooLarge';
    private const MAX_SIZE = 10485760; // 10MB

    private LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $contentLength = $request->getHeaderLine('Content-Length');
            if ($contentLength === '') {
                return $handler->handle($request);
            }
            $contentLength = (int)$contentLength;
            
            if ($contentLength > self::MAX_SIZE) {
                $this->logger->logInfo(sprintf(
                    'Request too large: %d bytes. URI: %s',
                    $contentLength,
                    $request->getUri()
                ));
                
                $response = \App::$slim->getResponseFactory()->createResponse();
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_TOO_LARGE)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_TOO_LARGE)]
                ]));
                
                return $response;
            }

            return $handler->handle($request);
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }
}