<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\Helper\ClientIpHelper;
use BO\Zmscitizenapi\Localization\ErrorMessages;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoggerService
{
    private const SENSITIVE_HEADERS = [
        'authorization',
        'cookie',
        'x-api-key',
        'auth-key',
        'authkey'
    ];
    private const IMPORTANT_HEADERS = [
        'user-agent'
    ];
    private const CACHE_KEY_PREFIX = 'logger.';
    private const CACHE_COUNTER_KEY = self::CACHE_KEY_PREFIX . 'counter';

    private static function checkRateLimit(): bool
    {
        if (Application::$cache === null) {
            error_log('Cache not available for rate limiting');
            return true;
        }

        $attempt = 0;
        $key = self::CACHE_COUNTER_KEY;
        $lockKey = $key . '_lock';
        
        while ($attempt < 3) { // Max retries
            try {
                if (!Application::$cache->has($lockKey)) {
                    if (Application::$cache->set($lockKey, true, 30)) { // 30 second lock timeout
                        try {
                            $data = Application::$cache->get($key);
                            if ($data === null) {
                                Application::$cache->set($key, [
                                    'count' => 1,
                                    'timestamp' => time()
                                ], 60);
                                return true;
                            }

                            if (!is_array($data) || !isset($data['count'])) {
                                Application::$cache->delete($key);
                                return true;
                            }

                            $count = (int)$data['count'];
                            if ($count >= 1000) {
                                return false;
                            }

                            $data['count'] = $count + 1;
                            Application::$cache->set($key, $data, 60);
                            return true;
                        } finally {
                            Application::$cache->delete($lockKey);
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('Rate limiting error: ' . $e->getMessage());
            }

            $attempt++;
            usleep(100000); // 100ms backoff
        }

        return true; // Allow logging if lock can't be acquired
    }

    public static function logError(\Throwable $exception, ?RequestInterface $request = null, ?ResponseInterface $response = null, array $context = []): void
    {
        if (!self::checkRateLimit()) {
            return;
        }

        $data = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => array_slice(explode("\n", $exception->getTraceAsString()), 0, 10)
        ];

        if ($request) {
            $data['request'] = [
                'method' => $request->getMethod(),
                'uri' => (string)$request->getUri(),
                'headers' => self::filterSensitiveHeaders($request->getHeaders())
            ];
        }

        if ($response) {
            $data['response'] = [
                'status' => $response->getStatusCode(),
                'headers' => self::filterSensitiveHeaders($response->getHeaders())
            ];
        }

        \App::$log->error($exception->getMessage(), array_merge($data, $context));
    }

    public static function logWarning(string $message, array $context = []): void
    {
        if (!self::checkRateLimit()) {
            return;
        }
        \App::$log->warning($message, $context);
    }

    public static function logInfo(string $message, array $context = []): void
    {
        if (!self::checkRateLimit()) {
            return;
        }
        \App::$log->info($message, $context);
    }

    public static function logRequest(ServerRequestInterface $request, ResponseInterface $response): void
    {
        if (!self::checkRateLimit()) {
            return;
        }

        $uri = $request->getUri();
        $path = preg_replace('#/+#', '/', $uri->getPath());
        
        // Filter out query params that look like paths
        $queryParams = array_filter($request->getQueryParams(), function ($key, $value) {
            return !preg_match('#^/|//#', $key) && !preg_match('#^/|//#', $value);
        }, ARRAY_FILTER_USE_BOTH);

        $queryParts = [];
        foreach ($queryParams as $key => $value) {
            $encodedKey = urlencode($key);
            $encodedValue = in_array(strtolower($key), ['authkey', 'auth_key', 'key'])
                ? '****'
                : urlencode($value);
            $queryParts[] = "$encodedKey=$encodedValue";
        }

        $data = [
            'method' => $request->getMethod(),
            'path' => $path . ($queryParts ? '?' . implode('&', $queryParts) : ''),
            'status' => $response->getStatusCode(),
            'ip' => ClientIpHelper::getClientIp(),
            'headers' => self::filterSensitiveHeaders($request->getHeaders())
        ];

        if ($response->getStatusCode() >= 400) {
            $stream = $response->getBody();
            if ($stream->isSeekable()) {
                try {
                    $stream->rewind();
                    $body = (string)$stream;
                    $decodedBody = json_decode($body, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decodedBody['errors'])) {
                        $englishErrors = [];
                        foreach ($decodedBody['errors'] as $error) {
                            if (isset($error['errorCode'])) {
                                $englishErrors[] = ErrorMessages::get($error['errorCode'], 'en');
                            } else {
                                $englishErrors[] = $error;
                            }
                        }
                        $data['errors'] = $englishErrors;
                    }
                    $stream->rewind();
                } catch (\Throwable $e) {
                    $data['error'] = 'Failed to read response body: ' . $e->getMessage();
                }
            }
        }

        $level = $response->getStatusCode() >= 400 ? 'error' : 'info';
        \App::$log->$level('HTTP Request', $data);
    }

    private static function filterSensitiveHeaders(array $headers): array
    {
        $filtered = [];
        foreach ($headers as $name => $values) {
            $lower = strtolower($name);
            if (in_array($lower, self::SENSITIVE_HEADERS, true)) {
                $filtered[$name] = ['[REDACTED]'];
            } elseif (in_array($lower, self::IMPORTANT_HEADERS, true)) {
                $filtered[$name] = $values;
            }
        }
        return $filtered;
    }
}