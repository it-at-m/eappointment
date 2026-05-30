<?php

declare(strict_types=1);

namespace BO\Slim;

use BO\Slim\Helper\ClientIp;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

class LoggerService
{
    private const SENSITIVE_HEADERS = [
        'authorization',
        'cookie',
        'x-api-key',
        'auth-key',
        'authkey',
        'captchatoken',
    ];

    private const SENSITIVE_PARAMS = [
        'authkey',
        'auth_key',
        'auth-key',
        'key',
        'captchatoken',
        'captcha-token',
    ];

    private const IMPORTANT_HEADERS = [
        'user-agent',
    ];

    private const CACHE_KEY_PREFIX = 'logger.';
    private const CACHE_COUNTER_KEY = self::CACHE_KEY_PREFIX . 'counter';

    public static ?CacheInterface $cache = null;

    /** @var callable|null fn(ServerRequestInterface $request, ?string $rawBody): array */
    public static $requestContextEnricher = null;

    /** @var callable|null fn(string $errorCode): mixed */
    public static $errorCodeResolver = null;

    public static int $maxRequests = 1000;
    public static int $responseLength = 1048576;
    public static int $stackLines = 10;
    public static int $cacheTtl = 60;
    public static int $maxRetries = 3;
    public static int $backoffMin = 100;
    public static int $lockTimeout = 30;

    /**
     * @param array{
     *   maxRequests?: int,
     *   responseLength?: int,
     *   stackLines?: int,
     *   messageSize?: int,
     *   cacheTtl?: int,
     *   maxRetries?: int,
     *   backoffMin?: int,
     *   backoffMax?: int,
     *   lockTimeout?: int
     * } $config
     */
    public static function configure(array $config): void
    {
        if (isset($config['maxRequests'])) {
            self::$maxRequests = (int) $config['maxRequests'];
        }
        if (isset($config['responseLength'])) {
            self::$responseLength = (int) $config['responseLength'];
        }
        if (isset($config['stackLines'])) {
            self::$stackLines = (int) $config['stackLines'];
        }
        if (isset($config['cacheTtl'])) {
            self::$cacheTtl = (int) $config['cacheTtl'];
        }
        if (isset($config['maxRetries'])) {
            self::$maxRetries = (int) $config['maxRetries'];
        }
        if (isset($config['backoffMin'])) {
            self::$backoffMin = (int) $config['backoffMin'];
        }
        if (isset($config['lockTimeout'])) {
            self::$lockTimeout = (int) $config['lockTimeout'];
        }
    }

    private static function checkRateLimit(): bool
    {
        if (self::$cache === null) {
            \App::$log->notice('Cache not available for rate limiting');
            return true;
        }

        $attempt = 0;
        $key = self::CACHE_COUNTER_KEY;
        $lockKey = $key . '_lock';

        while ($attempt < self::$maxRetries) {
            try {
                if (!self::$cache->has($lockKey)) {
                    if (self::$cache->set($lockKey, true, self::$lockTimeout)) {
                        try {
                            $data = self::$cache->get($key);
                            if ($data === null) {
                                self::$cache->set($key, [
                                    'count' => 1,
                                    'timestamp' => time(),
                                ], self::$cacheTtl);
                                return true;
                            }

                            if (!is_array($data) || !isset($data['count'])) {
                                self::$cache->delete($key);
                                return true;
                            }

                            $count = (int) $data['count'];
                            if ($count >= self::$maxRequests) {
                                return false;
                            }

                            $data['count'] = $count + 1;
                            self::$cache->set($key, $data, self::$cacheTtl);
                            return true;
                        } finally {
                            self::$cache->delete($lockKey);
                        }
                    }
                }
            } catch (\Throwable $e) {
                \App::$log->warning('Rate limiting error', ['exception' => $e->getMessage()]);
            }

            $attempt++;
            usleep(self::$backoffMin * 1000);
        }

        return true;
    }

    public static function logError(
        \Throwable $exception,
        ?RequestInterface $request = null,
        ?ResponseInterface $response = null,
        array $context = []
    ): void {
        if (!self::checkRateLimit()) {
            return;
        }

        $data = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => array_slice(explode("\n", $exception->getTraceAsString()), 0, self::$stackLines),
        ];

        if ($request) {
            $data['request'] = [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'headers' => self::filterSensitiveHeaders($request->getHeaders()),
            ];
        }

        if ($response) {
            $data['response'] = [
                'status' => $response->getStatusCode(),
                'headers' => self::filterSensitiveHeaders($response->getHeaders()),
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
        $logPath = self::buildLogPath($path, $request->getQueryParams());

        $data = [
            'method' => $request->getMethod(),
            'path' => $logPath,
            'status' => $response->getStatusCode(),
            'ip' => ClientIp::getClientIp(),
            'headers' => self::filterSensitiveHeaders($request->getHeaders()),
        ];

        $bodyStream = $response->getBody();
        $rawBody = $bodyStream !== null ? (string) $bodyStream : null;
        if ($bodyStream !== null && $bodyStream->isSeekable()) {
            $bodyStream->rewind();
        }

        if (self::$requestContextEnricher !== null) {
            $processContext = (self::$requestContextEnricher)($request, $rawBody);
            if (!empty($processContext)) {
                $data = array_merge($data, $processContext);
            }
        }

        $data = self::appendResponseErrors($data, $response->getStatusCode(), $rawBody);

        $level = $response->getStatusCode() >= 400 ? 'error' : 'info';
        \App::$log->$level('HTTP Request', $data);
    }

    private static function buildLogPath(string $path, array $queryParams): string
    {
        $queryParts = [];
        foreach ($queryParams as $key => $value) {
            if (preg_match('#^/|//#', (string) $key)) {
                continue;
            }
            if (!is_array($value) && preg_match('#^/|//#', (string) $value)) {
                continue;
            }
            $queryParts[] = self::formatQueryParamForLog($key, $value);
        }

        return $path . ($queryParts ? '?' . implode('&', $queryParts) : '');
    }

    private static function formatQueryParamForLog(mixed $key, mixed $value): string
    {
        $encodedKey = urlencode((string) $key);
        if (in_array(strtolower((string) $key), self::SENSITIVE_PARAMS, true)) {
            return "$encodedKey=****";
        }
        if (is_array($value)) {
            return $encodedKey . '=' . urlencode(json_encode($value, JSON_UNESCAPED_UNICODE) ?: '[]');
        }

        return $encodedKey . '=' . urlencode((string) $value);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function appendResponseErrors(array $data, int $statusCode, ?string $rawBody): array
    {
        if ($statusCode < 400 || empty($rawBody)) {
            return $data;
        }

        $decodedBody = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($decodedBody['errors'])) {
            return $data;
        }

        $errorMessages = [];
        foreach ($decodedBody['errors'] as $error) {
            if (isset($error['errorCode']) && self::$errorCodeResolver !== null) {
                $errorMessages[] = (self::$errorCodeResolver)((string) $error['errorCode']);
            } else {
                $errorMessages[] = $error;
            }
        }

        $data['errors'] = $errorMessages;

        return $data;
    }

    private static function filterSensitiveHeaders(array $headers): array
    {
        $filtered = [];
        foreach ($headers as $name => $values) {
            $lower = strtolower((string) $name);
            if (in_array($lower, self::SENSITIVE_HEADERS, true)) {
                $filtered[$name] = ['[REDACTED]'];
            } elseif (in_array($lower, self::IMPORTANT_HEADERS, true)) {
                $filtered[$name] = $values;
            }
        }

        return $filtered;
    }
}
