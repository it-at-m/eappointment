<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

use BO\Slim\LoggerService;
use BO\Slim\Middleware\RequestLoggingMiddleware;
use BO\Slim\Middleware\RequestSanitizerMiddleware;
use BO\Slim\Middleware\SecurityHeadersMiddleware;
use Psr\SimpleCache\CacheInterface;

final class ModuleLoggerInitializer
{
    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function configure(string $envPrefix): void
    {
        LoggerService::configure([
            'maxRequests' => (int) (getenv("{$envPrefix}_LOGGER_MAX_REQUESTS") ?: 1000),
            'maxErrorRequests' => (int) (getenv("{$envPrefix}_LOGGER_MAX_ERROR_REQUESTS") ?: 0),
            'responseLength' => (int) (getenv("{$envPrefix}_LOGGER_RESPONSE_LENGTH") ?: 1048576),
            'stackLines' => (int) (getenv("{$envPrefix}_LOGGER_STACK_LINES") ?: 20),
            'messageSize' => (int) (getenv("{$envPrefix}_LOGGER_MESSAGE_SIZE") ?: 8192),
            'cacheTtl' => (int) (getenv("{$envPrefix}_LOGGER_CACHE_TTL") ?: 60),
            'maxRetries' => (int) (getenv("{$envPrefix}_LOGGER_MAX_RETRIES") ?: 3),
            'backoffMin' => (int) (getenv("{$envPrefix}_LOGGER_BACKOFF_MIN") ?: 100),
            'backoffMax' => (int) (getenv("{$envPrefix}_LOGGER_BACKOFF_MAX") ?: 1000),
            'lockTimeout' => (int) (getenv("{$envPrefix}_LOGGER_LOCK_TIMEOUT") ?: 5),
        ]);
    }

    public static function initializeCache(?string $cacheDir = null): CacheInterface
    {
        if ($cacheDir !== null) {
            $ttl = (int) (getenv('SOURCE_CACHE_TTL') ?: 3600);

            return CacheBootstrap::create($cacheDir, $ttl);
        }

        return CacheBootstrap::createFromEnv(sys_get_temp_dir());
    }

    public static function tryInitializeCache(?string $cacheDir = null): ?CacheInterface
    {
        try {
            return self::initializeCache($cacheDir);
        } catch (\RuntimeException) {
            LoggerService::$cache = null;

            return null;
        }
    }

    /**
     * @return array{maxStringLength: int, maxRecursionDepth: int}
     */
    public static function getRequestLimits(): array
    {
        return [
            'maxStringLength' => (int) (getenv('MAX_STRING_LENGTH') ?: 32768),
            'maxRecursionDepth' => (int) (getenv('MAX_RECURSION_DEPTH') ?: 10),
        ];
    }

    public static function registerHttpMiddleware(bool $includeSecurityHeaders = true): void
    {
        $logger = new LoggerService();
        $requestLimits = self::getRequestLimits();

        \App::$slim->add(new RequestLoggingMiddleware($logger));
        if ($includeSecurityHeaders) {
            \App::$slim->add(new SecurityHeadersMiddleware($logger));
        }
        \App::$slim->add(new RequestSanitizerMiddleware(
            $logger,
            $requestLimits['maxRecursionDepth'],
            $requestLimits['maxStringLength']
        ));
    }
}
