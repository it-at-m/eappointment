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
    private int $maxSize;
    private LoggerService $logger;
    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
        $config = \App::getRequestLimits();
        $this->maxSize = $config['maxSize'];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $contentLength = $request->getHeaderLine('Content-Length');
            if ($contentLength === '') {
                return $handler->handle($request);
            }
            $contentLength = (int)$contentLength;
            if ($contentLength > $this->maxSize) {
                $this->logger->logInfo(sprintf('Request too large: %d bytes. URI: %s', $contentLength, $request->getUri()));
                $response = \App::$slim->getResponseFactory()->createResponse();
                $language = $request->getAttribute('language');
                $response = $response->withStatus(ErrorMessages::get(self::ERROR_TOO_LARGE, $language)['statusCode'])
                    ->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode([
                    'errors' => [ErrorMessages::get(self::ERROR_TOO_LARGE, $language)]
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
