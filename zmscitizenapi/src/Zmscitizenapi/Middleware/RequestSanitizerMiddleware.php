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
    private const MAX_RECURSION_DEPTH = 10;
    private const MAX_STRING_LENGTH = 32768; // 32KB

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
                'uri' => (string) $request->getUri()
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
        } elseif (is_object($parsedBody)) {
            $sanitizedParsedBody = $this->sanitizeObject($parsedBody);
            $request = $request->withParsedBody($sanitizedParsedBody);
        }

        return $request;
    }

    private function sanitizeData(array $data): array
    {
        return $this->sanitizeDataWithDepth($data, 0);
    }

    private function sanitizeDataWithDepth(array $data, int $depth): array
    {
        if ($depth >= self::MAX_RECURSION_DEPTH) {
            throw new \RuntimeException('Maximum recursion depth exceeded');
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeDataWithDepth($value, $depth + 1);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    private function sanitizeObject(object $data): object
    {
        return $this->sanitizeObjectWithDepth($data, 0);
    }

    private function sanitizeObjectWithDepth(object $data, int $depth): object
    {
        if ($depth >= self::MAX_RECURSION_DEPTH) {
            throw new \RuntimeException('Maximum recursion depth exceeded');
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data->$key = $this->sanitizeDataWithDepth($value, $depth + 1);
            } elseif (is_object($value)) {
                $data->$key = $this->sanitizeObjectWithDepth($value, $depth + 1);
            } elseif (is_string($value)) {
                $data->$key = $this->sanitizeString($value);
            }
        }
        return $data;
    }

    private function sanitizeString(string $value): string
    {
        if (strlen($value) > self::MAX_STRING_LENGTH) {
            throw new \RuntimeException('String exceeds maximum length');
        }

        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        $value = trim($value);
        if (!mb_check_encoding($value, 'UTF-8')) {
            $this->logger->logWarning('Invalid string encoding detected.', ['value' => $value]);
            $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        }
        return $value;
    }
}