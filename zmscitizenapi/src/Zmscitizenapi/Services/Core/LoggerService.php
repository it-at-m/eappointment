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
    // Internal implementation constants
    private const LOG_FACILITY = LOG_LOCAL0;
    private const LOG_OPTIONS = LOG_PID | LOG_PERROR;
    private const CACHE_KEY_PREFIX = 'logger.';
    private const CACHE_COUNTER_KEY = self::CACHE_KEY_PREFIX . 'counter';
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
    protected static bool $logOpened = false;
    private static ?array $config = null;
    private static function ensureConfigLoaded(): void
    {
        if (self::$config === null) {
            self::$config = \App::getLoggerConfig();
        }
    }

    public static function init(): void
    {
        if (!self::$logOpened) {
            self::ensureConfigLoaded();
            self::$logOpened = @openlog(\App::IDENTIFIER, self::LOG_OPTIONS, self::LOG_FACILITY) !== false;
            if (!self::$logOpened) {
                error_log('Failed to open syslog');
            }
        }
    }

    public static function shutdown(): void
    {
        if (self::$logOpened) {
            @closelog();
            self::$logOpened = false;
        }
    }

    public static function logError(\Throwable $exception, ?RequestInterface $request = null, ?ResponseInterface $response = null, array $context = []): void
    {
        self::ensureConfigLoaded();
        if (!self::checkRateLimit()) {
            return;
        }

        $message = self::formatErrorMessage($exception, $request, $response, $context);
        self::writeLog(LOG_ERR, $message);
    }

    public static function logWarning(string $message, array $context = []): void
    {
        self::ensureConfigLoaded();
        if (!self::checkRateLimit()) {
            return;
        }

        $message = self::formatMessage($message, $context);
        self::writeLog(LOG_WARNING, $message);
    }

    public static function logInfo(string $message, array $context = []): void
    {
        self::ensureConfigLoaded();
        if (!self::checkRateLimit()) {
            return;
        }

        $message = self::formatMessage($message, $context);
        self::writeLog(LOG_INFO, $message);
    }

    public static function logRequest(ServerRequestInterface $request, ResponseInterface $response): void
    {
        self::ensureConfigLoaded();
        if (!self::checkRateLimit()) {
            return;
        }

        $uri = $request->getUri();
// Normalize path by removing double slashes
        $path = preg_replace('#/+#', '/', $uri->getPath());
// Filter out query params that look like paths
        $queryParams = array_filter($request->getQueryParams(), function ($key, $value) {

                // Remove if key or value starts with slash or contains multiple slashes
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
        $queryString = implode('&', $queryParts);
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->getMethod(),
            'path' => $path . ($queryString ? '?' . $queryString : ''),
            'status' => $response->getStatusCode(),
            'ip' => ClientIpHelper::getClientIp(),
            'headers' => self::filterSensitiveHeaders($request->getHeaders())
        ];
        if ($response->getStatusCode() >= 400) {
            $body = '';
            $stream = $response->getBody();
            if ($stream->isSeekable()) {
                try {
                    $stream->seek(0, SEEK_END);
                    $size = $stream->tell();
                    $stream->rewind();
                    $maxSafeSize = min(self::$config['responseLength'], (int)(self::$config['messageSize'] * 0.75));
                    if ($size > $maxSafeSize) {
                        $data['response'] = [
                            'error' => 'Response body too large to log',
                            'size' => $size
                        ];
                    } else {
                        $body = (string)$stream;
                        $stream->rewind();
                        try {
                            $decodedBody = json_decode($body, true);
                            if (
                                json_last_error() === JSON_ERROR_NONE &&
                                isset($decodedBody['errors']) &&
                                is_array($decodedBody['errors'])
                            ) {
                                $englishErrors = [];
                                foreach ($decodedBody['errors'] as $error) {
                                    if (isset($error['errorCode'])) {
                                            $englishErrors[] = ErrorMessages::get($error['errorCode'], 'en');
                                    } else {
                                        $englishErrors[] = $error;
                                    }
                                }
                                $data['response'] = ['errors' => $englishErrors];
                            }
                        } catch (\Throwable $e) {
                            $data['response'] = [
                            'error' => 'Failed to decode response body',
                            'message' => $e->getMessage()
                            ];
                        }
                    }
                } catch (\RuntimeException $e) {
                    $data['response'] = [
                    'error' => 'Failed to read response body',
                    'message' => $e->getMessage()
                    ];
                }
            } else {
                $data['response'] = [
                'error' => 'Response body not seekable'
                ];
            }
        }

        self::writeLog($response->getStatusCode() >= 400 ? LOG_ERR : LOG_INFO, self::encodeJson($data));
    }

    private static function checkRateLimit(): bool
    {
        if (Application::$cache === null) {
            error_log('Cache not available for rate limiting');
            return true;
        }

        $attempt = 0;
        $key = self::CACHE_COUNTER_KEY;
        $lockKey = $key . '_lock';
        while ($attempt < self::$config['maxRetries']) {
            try {
                if (self::acquireLock($lockKey)) {
                    try {
                        $data = Application::$cache->get($key);
                        if ($data === null) {
                                // First log in this window
                            Application::$cache->set($key, [
                                'count' => 1,
                                'timestamp' => time()
                            ], self::$config['cacheTtl']);
                                return true;
                        }

                        if (!is_array($data) || !isset($data['count'], $data['timestamp'])) {
        // Handle corrupted data
                            Application::$cache->delete($key);
                            return true;
                        }

                        $count = (int)$data['count'];
                        if ($count >= self::$config['maxRequests']) {
                            error_log('Log rate limit exceeded');
                            return false;
                        }

                        // Update the counter atomically
                        $data['count'] = $count + 1;
                        Application::$cache->set($key, $data, self::$config['cacheTtl']);
                        return true;
                    } finally {
                        self::releaseLock($lockKey);
                    }
                }
            } catch (\Throwable $e) {
                error_log('Rate limiting error: ' . $e->getMessage());
            }

            $attempt++;
            if ($attempt < self::$config['maxRetries']) {
                $backoffMs = min(self::$config['backoffMax'], (int)(self::$config['backoffMin'] * pow(2, $attempt)));
                $jitterMs = random_int(0, (int)($backoffMs * 0.1));
                usleep(($backoffMs + $jitterMs) * 1000);
            }
        }

        // If we can't acquire the lock after retries, allow logging
        error_log('Failed to acquire rate limit lock after retries');
        return true;
    }

    private static function acquireLock(string $lockKey): bool
    {
        if (!Application::$cache->has($lockKey)) {
            return Application::$cache->set($lockKey, true, self::$config['lockTimeout']);
        }
        return false;
    }

    private static function releaseLock(string $lockKey): void
    {
        Application::$cache->delete($lockKey);
    }

    private static function writeLog(int $priority, string $message): void
    {
        // Initialize syslog connection if needed
        self::init();
        if (!self::$logOpened) {
            error_log('Syslog not available, falling back to error_log');
            error_log($message);
            return;
        }

        // Truncate message if too large
        if (strlen($message) > self::$config['messageSize']) {
            $message = mb_substr($message, 0, self::$config['messageSize'] - 64)
                . ' ... [truncated, full length: ' . strlen($message) . ']';
        }

        try {
            if (@syslog($priority, $message) === false) {
                error_log('Failed to write to syslog');
                error_log($message);
            }
        } catch (\Throwable $e) {
            error_log('Logging failed: ' . $e->getMessage());
            error_log($message);
        }
    }

    private static function formatErrorMessage(\Throwable $exception, ?RequestInterface $request, ?ResponseInterface $response, array $context): string
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => implode("\n", array_slice(explode("\n", $exception->getTraceAsString()), 0, self::$config['stackLines']))
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
// Only include response body for errors
            if ($response->getStatusCode() >= 400) {
                $body = '';
                $stream = $response->getBody();
                if ($stream->isSeekable()) {
                    try {
                        $stream->seek(0, SEEK_END);
                        $size = $stream->tell();
                        $stream->rewind();
                        $maxSafeSize = min(self::$config['responseLength'], (int)(self::$config['messageSize'] * 0.75));
                        if ($size > $maxSafeSize) {
                                    $data['response']['body'] = [
                                'error' => 'Response body too large to log',
                                'size' => $size
                                            ];
                        } else {
                                $body = (string)$stream;
                                $stream->rewind();
                                $decodedBody = json_decode($body, true);
                            if (
                                json_last_error() === JSON_ERROR_NONE &&
                                    isset($decodedBody['errors']) &&
                                    is_array($decodedBody['errors'])
                            ) {
        // Only log if response contains an errors array
                                $data['response']['body'] = $decodedBody;
                            }
                        }
                    } catch (\Throwable $e) {
                        $data['response']['body'] = [
                        'error' => 'Failed to decode response body',
                        'message' => $e->getMessage()
                        ];
                    }
                } else {
                    $data['response']['body'] = [
                        'error' => 'Response body not seekable'
                    ];
                }
            }
        }

        if (!empty($context)) {
            $data['context'] = $context;
        }

        return self::encodeJson($data);
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

        return self::encodeJson($data);
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

    private static function encodeJson(array $data): string
    {
        try {
            $json = json_encode(
                $data,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE |
                JSON_INVALID_UTF8_SUBSTITUTE
            );
            if ($json === false) {
                throw new \RuntimeException(json_last_error_msg(), json_last_error());
            }
            return $json;
        } catch (\Throwable $e) {
            error_log('JSON encoding failed: ' . $e->getMessage());
            return json_encode([
                'error' => 'Failed to encode log data',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }
}
