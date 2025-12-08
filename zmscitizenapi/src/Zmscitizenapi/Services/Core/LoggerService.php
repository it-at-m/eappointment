<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\Utils\ClientIpHelper;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\ProcessContextExtractor;
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
        'authkey',
        'captchaToken'
    ];

    private const SENSITIVE_PARAMS = [
        'authkey',
        'authKey',
        'auth_key',
        'auth-key',
        'key',
        'captchaToken',
        'captchatoken',
        'captcha-token'
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

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
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
            // Check if the key (case-insensitive) is in sensitive params
            if (in_array(strtolower($key), self::SENSITIVE_PARAMS, true)) {
                $queryParts[] = "$encodedKey=****";
            } else {
                $encodedValue = urlencode($value);
                $queryParts[] = "$encodedKey=$encodedValue";
            }
        }

        $data = [
            'method' => $request->getMethod(),
            'path' => $path . ($queryParts ? '?' . implode('&', $queryParts) : ''),
            'status' => $response->getStatusCode(),
            'ip' => ClientIpHelper::getClientIp(),
            'headers' => self::filterSensitiveHeaders($request->getHeaders())
        ];

        // Read response body once so it can be reused for both process extraction and error logging
        $bodyStream = $response->getBody();
        $rawBody = $bodyStream !== null ? (string) $bodyStream : null;

        $processContext = ProcessContextExtractor::extractProcessContext($request, $rawBody);
        if (!empty($processContext)) {
            $data = array_merge($data, $processContext);
        }

        if ($response->getStatusCode() >= 400) {
            if (!empty($rawBody)) {
                $decodedBody = json_decode($rawBody, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decodedBody['errors'])) {
                    $errorMessages = [];
                    foreach ($decodedBody['errors'] as $error) {
                        if (isset($error['errorCode'])) {
                            $errorMessages[] = ErrorMessages::get($error['errorCode']);
                        } else {
                            $errorMessages[] = $error;
                        }
                    }
                    $data['errors'] = $errorMessages;
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
