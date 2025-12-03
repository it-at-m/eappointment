<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Application;
use BO\Zmscitizenapi\Utils\ClientIpHelper;
use BO\Zmscitizenapi\Utils\ErrorMessages;
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

            if ($request instanceof ServerRequestInterface) {
                $processContext = self::extractProcessContextFromRequest($request);
                if (!empty($processContext)) {
                    $data = array_merge($data, $processContext);
                }
            }
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

        $processContext = self::extractProcessContextFromRequest($request);
        if ($response->getStatusCode() < 400) {
            // Enrich with data from successful JSON responses (e.g. reserve-appointment result).
            $responseContext = self::extractProcessContextFromResponse($response);
            if (!empty($responseContext)) {
                $processContext = array_replace_recursive($processContext, $responseContext);
            }
        }

        if (!empty($processContext)) {
            $data = array_merge($data, $processContext);
        }

        if ($response->getStatusCode() >= 400) {
            $stream = $response->getBody();
            try {
                $body = (string)$stream;

                if (!empty($body)) {
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
                }
            } catch (\Exception $e) {
                $data['stream_error'] = 'Unable to read response body: ' . $e->getMessage();
            }
        }

        $level = $response->getStatusCode() >= 400 ? 'error' : 'info';
        \App::$log->$level('HTTP Request', $data);
    }

    private static function extractProcessContextFromRequest(ServerRequestInterface $request): array
    {
        $process = [];

        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody)) {
            if (isset($parsedBody['processId']) && is_numeric($parsedBody['processId'])) {
                $process['processId'] = (int)$parsedBody['processId'];
            }
            if (isset($parsedBody['officeId']) && is_numeric($parsedBody['officeId'])) {
                $process['officeId'] = (int)$parsedBody['officeId'];
            }
            if (isset($parsedBody['scopeId']) && is_numeric($parsedBody['scopeId'])) {
                $process['scopeId'] = (int)$parsedBody['scopeId'];
            }
            if (isset($parsedBody['serviceId']) && is_numeric($parsedBody['serviceId'])) {
                $process['serviceId'] = (int)$parsedBody['serviceId'];
            }
            if (isset($parsedBody['subRequestCounts']) && is_array($parsedBody['subRequestCounts'])) {
                $subRequestIds = [];
                foreach ($parsedBody['subRequestCounts'] as $sub) {
                    if (!is_array($sub) || !isset($sub['id']) || !is_numeric($sub['id'])) {
                        continue;
                    }
                    $subRequestIds[] = (int)$sub['id'];
                }
                if ($subRequestIds !== []) {
                    $process['subRequestCounts'] = $subRequestIds;
                }
            }
        }

        $queryParams = $request->getQueryParams();
        if (is_array($queryParams)) {
            if (!isset($process['processId']) && isset($queryParams['processId']) && is_numeric($queryParams['processId'])) {
                $process['processId'] = (int)$queryParams['processId'];
            }
            if (!isset($process['officeId']) && isset($queryParams['officeId']) && is_numeric($queryParams['officeId'])) {
                $process['officeId'] = (int)$queryParams['officeId'];
            }
            if (!isset($process['scopeId']) && isset($queryParams['scopeId']) && is_numeric($queryParams['scopeId'])) {
                $process['scopeId'] = (int)$queryParams['scopeId'];
            }
            if (!isset($process['serviceId']) && isset($queryParams['serviceId']) && is_numeric($queryParams['serviceId'])) {
                $process['serviceId'] = (int)$queryParams['serviceId'];
            }
        }

        if ($process === []) {
            return [];
        }

        return ['process' => $process];
    }

    private static function extractProcessContextFromResponse(ResponseInterface $response): array
    {
        $process = [];

        try {
            $body = (string)$response->getBody();
            if ($body === '') {
                return [];
            }

            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return [];
            }

            if (isset($decoded['processId']) && is_numeric($decoded['processId'])) {
                $process['processId'] = (int)$decoded['processId'];
            }
            if (isset($decoded['officeId']) && is_numeric($decoded['officeId'])) {
                $process['officeId'] = (int)$decoded['officeId'];
            }
            if (isset($decoded['scope']['id']) && is_numeric($decoded['scope']['id'])) {
                $process['scopeId'] = (int)$decoded['scope']['id'];
            }
            if (isset($decoded['serviceId']) && is_numeric($decoded['serviceId'])) {
                $process['serviceId'] = (int)$decoded['serviceId'];
            }
            if (isset($decoded['subRequestCounts']) && is_array($decoded['subRequestCounts'])) {
                $subRequestIds = [];
                foreach ($decoded['subRequestCounts'] as $sub) {
                    if (!is_array($sub) || !isset($sub['id']) || !is_numeric($sub['id'])) {
                        continue;
                    }
                    $subRequestIds[] = (int)$sub['id'];
                }
                if ($subRequestIds !== []) {
                    $process['subRequestCounts'] = $subRequestIds;
                }
            }
            if (isset($decoded['displayNumber']) && $decoded['displayNumber'] !== '') {
                $process['displayNumber'] = (string)$decoded['displayNumber'];
            }
        } catch (\Throwable $e) {
            // Ignore JSON / stream errors when extracting process context for logging
        }

        if ($process === []) {
            return [];
        }

        return ['process' => $process];
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
