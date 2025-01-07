<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoggerService
{
    private const LOG_FACILITY = LOG_LOCAL0;
    private const LOG_OPTIONS = LOG_PID | LOG_PERROR;
    
    public static function logError(
        \Throwable $exception,
        ?RequestInterface $request = null,
        ?ResponseInterface $response = null,
        array $context = []
    ): void {
        $message = self::formatErrorMessage($exception, $request, $response, $context);
        self::log(LOG_ERR, $message);
    }

    public static function logInfo(string $message, array $context = []): void
    {
        $message = self::formatMessage($message, $context);
        self::log(LOG_INFO, $message);
    }

    public static function logRequest(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): void {
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        // Get query params and filter out path-like parameters
        $queryParams = array_filter(
            $request->getQueryParams(),
            function($key) {
                return !str_starts_with($key, '/');
            },
            ARRAY_FILTER_USE_KEY
        );
        
        // Build query string manually to avoid encoding asterisks
        $queryParts = [];
        foreach ($queryParams as $key => $value) {
            $encodedKey = urlencode($key);
            $encodedValue = in_array(strtolower($key), ['authkey', 'auth_key', 'key']) 
                ? '****' 
                : urlencode($value);
            $queryParts[] = "$encodedKey=$encodedValue";
        }
        $queryString = implode('&', $queryParts);
        
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'path' => $path . ($queryString ? '?' . $queryString : ''),
            'status' => $response->getStatusCode()
        ];
    
        // Only include response body for errors
        if ($response->getStatusCode() >= 400) {
            $body = (string)$response->getBody();
            // Rewind body stream after reading
            if ($response->getBody()->isSeekable()) {
                $response->getBody()->rewind();
            }
            $data['response'] = json_decode($body, true);
        }
    
        self::log(LOG_ERR, json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    private static function formatErrorMessage(
        \Throwable $exception,
        ?RequestInterface $request,
        ?ResponseInterface $response,
        array $context
    ): string {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];

        if ($request) {
            $data['request'] = [
                'method' => $request->getMethod(),
                'uri' => (string)$request->getUri(),
                'headers' => $request->getHeaders(),
                'body' => (string)$request->getBody()
            ];
        }

        if ($response) {
            $data['response'] = [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => (string)$response->getBody()
            ];
        }

        if ($context) {
            $data['context'] = $context;
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    private static function formatMessage(string $message, array $context): string
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $message
        ];

        if ($context) {
            $data['context'] = $context;
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    private static function log(int $priority, string $message): void
    {
        openlog(\App::IDENTIFIER, self::LOG_OPTIONS, self::LOG_FACILITY);
        syslog($priority, $message);
        closelog();
    }
}