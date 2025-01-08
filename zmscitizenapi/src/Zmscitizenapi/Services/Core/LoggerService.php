<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Helper\ClientIpHelper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoggerService
{
    private const LOG_FACILITY = LOG_LOCAL0;
    private const LOG_OPTIONS = LOG_PID | LOG_PERROR;

    private const MAX_RESPONSE_LENGTH = 1024 * 1024; // 1MB limit
    private const MAX_STACK_LINES = 20; // Limit stack trace length
    private const MAX_MESSAGE_SIZE = 8192; // 8KB limit for syslog messages
    private const MAX_LOGS_PER_MINUTE = 1000; // Rate limit

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
        //'host',
        //'x-forwarded-host',
        //'x-forwarded-proto',
        //'x-forwarded-for',
        //'content-type',
        //'content-length',
        'user-agent'
    ];

    private static bool $logOpened = false;
    private static string $cachedTimestamp = '';
    private static int $lastTimestampUpdate = 0;
    private static int $logCount = 0;
    private static int $lastCounterReset = 0;

    public static function init(): void
    {
        if (!self::$logOpened) {
            self::$logOpened = @openlog(
                \App::IDENTIFIER,
                self::LOG_OPTIONS,
                self::LOG_FACILITY
            ) !== false;

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

    public static function logError(
        \Throwable $exception,
        ?RequestInterface $request = null,
        ?ResponseInterface $response = null,
        array $context = []
    ): void {
        if (!self::checkRateLimit()) {
            return;
        }

        $message = self::formatErrorMessage($exception, $request, $response, $context);
        self::writeLog(LOG_ERR, $message);
    }

    public static function logInfo(string $message, array $context = []): void
    {
        if (!self::checkRateLimit()) {
            return;
        }

        $message = self::formatMessage($message, $context);
        self::writeLog(LOG_INFO, $message);
    }

    public static function logRequest(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): void {
        if (!self::checkRateLimit()) {
            return;
        }

        $uri = $request->getUri();
        $path = $uri->getPath();

        $queryParams = array_filter(
            $request->getQueryParams(),
            function ($key) {
                return !str_starts_with($key, '/');
            },
            ARRAY_FILTER_USE_KEY
        );

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
            'timestamp' => self::getTimestamp(),
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

                    $maxSafeSize = min(
                        self::MAX_RESPONSE_LENGTH,
                        (int) (self::MAX_MESSAGE_SIZE * 0.75)
                    );

                    if ($size > $maxSafeSize) {
                        $data['response'] = [
                            'error' => 'Response body too large to log',
                            'size' => $size
                        ];
                    } else {
                        $body = (string) $stream;
                        $stream->rewind();

                        try {
                            $decodedBody = json_decode($body, true);
                            if (
                                json_last_error() === JSON_ERROR_NONE &&
                                isset($decodedBody['errors']) &&
                                is_array($decodedBody['errors'])
                            ) {
                                // Only log if response contains an errors array
                                $data['response'] = $decodedBody;
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

        self::writeLog(
            $response->getStatusCode() >= 400 ? LOG_ERR : LOG_INFO,
            self::encodeJson($data)
        );
    }

    private static function formatErrorMessage(
        \Throwable $exception,
        ?RequestInterface $request,
        ?ResponseInterface $response,
        array $context
    ): string {
        $data = [
            'timestamp' => self::getTimestamp(),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => implode("\n", array_slice(
                explode("\n", $exception->getTraceAsString()),
                0,
                self::MAX_STACK_LINES
            ))
        ];

        if ($request) {
            $data['request'] = [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
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

                        $maxSafeSize = min(
                            self::MAX_RESPONSE_LENGTH,
                            (int) (self::MAX_MESSAGE_SIZE * 0.75)
                        );

                        if ($size > $maxSafeSize) {
                            $data['response']['body'] = [
                                'error' => 'Response body too large to log',
                                'size' => $size
                            ];
                        } else {
                            $body = (string) $stream;
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
            'timestamp' => self::getTimestamp(),
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
        if (strlen($message) > self::MAX_MESSAGE_SIZE) {
            $message = mb_substr($message, 0, self::MAX_MESSAGE_SIZE - 64)
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

    private static function checkRateLimit(): bool
    {
        // If cache is not available, allow logging but log a warning
        if (\App::$cache === null) {
            error_log('Cache not available for rate limiting');
            return true;
        }

        try {
            $key = self::CACHE_COUNTER_KEY;
            
            // Get current counter
            $count = \App::$cache->get($key, 0);
            
            // Increment counter
            $count++;
            
            // Store updated counter with 1-minute TTL
            \App::$cache->set($key, $count, 60);
            
            if ($count > self::MAX_LOGS_PER_MINUTE) {
                error_log('Log rate limit exceeded');
                return false;
            }
            
            return true;
        } catch (\InvalidArgumentException $e) {
            error_log('Cache rate limiting failed: ' . $e->getMessage());
            // Allow logging on cache failures
            return true;
        }
    }

    private static function getTimestamp(): string
    {
        $now = time();

        // Cache timestamp for 1 second
        if ($now - self::$lastTimestampUpdate >= 1) {
            self::$cachedTimestamp = date('Y-m-d H:i:s');
            self::$lastTimestampUpdate = $now;
        }

        return self::$cachedTimestamp;
    }

    private static function encodeJson(array $data): string
    {
        try {
            $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($json === false) {
                throw new \RuntimeException(json_last_error_msg(), json_last_error());
            }
            return $json;
        } catch (\Throwable $e) {
            error_log('JSON encoding failed: ' . $e->getMessage());
            // Return simplified JSON with error
            return json_encode([
                'error' => 'Failed to encode log data',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_SLASHES);
        }
    }
}