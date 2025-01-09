<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Services\Core\LoggerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestSanitizerMiddleware implements MiddlewareInterface
{
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
            $request = $this->sanitizeRequest($request);
            
            $this->logger->logInfo('Request sanitized', [
                'uri' => (string)$request->getUri()
            ]);
            
            return $handler->handle($request);
        } catch (\Throwable $e) {
            $this->logger->logError($e, $request);
            throw $e;
        }
    }

    private function sanitizeRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        // Sanitize query parameters
        $queryParams = $request->getQueryParams();
        $sanitizedQueryParams = $this->sanitizeData($queryParams);
        $request = $request->withQueryParams($sanitizedQueryParams);
        
        // Sanitize parsed body
        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody)) {
            $sanitizedParsedBody = $this->sanitizeData($parsedBody);
            $request = $request->withParsedBody($sanitizedParsedBody);
        }
        
        return $request;
    }

    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    private function sanitizeString(string $value): string
    {
        // Remove invisible characters
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        
        // Convert special characters to HTML entities
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}