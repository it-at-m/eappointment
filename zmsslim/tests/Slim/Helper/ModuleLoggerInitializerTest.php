<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\ModuleLoggerInitializer;
use BO\Slim\LoggerService;
use PHPUnit\Framework\TestCase;

class ModuleLoggerInitializerTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('ZMS_ADMIN_LOGGER_MAX_REQUESTS');
        putenv('ZMS_ADMIN_LOGGER_MAX_ERROR_REQUESTS');
        LoggerService::$cache = null;
        LoggerService::$requestContextEnricher = null;
        LoggerService::$errorCodeResolver = null;
        LoggerService::configure([
            'maxRequests' => 1000,
            'maxErrorRequests' => 0,
            'responseLength' => 1048576,
            'stackLines' => 10,
            'cacheTtl' => 60,
            'maxRetries' => 3,
            'backoffMin' => 100,
            'lockTimeout' => 30,
        ]);
        parent::tearDown();
    }

    public function testConfigureUsesModuleEnvPrefix(): void
    {
        putenv('ZMS_ADMIN_LOGGER_MAX_REQUESTS=42');
        putenv('ZMS_ADMIN_LOGGER_MAX_ERROR_REQUESTS=7');

        ModuleLoggerInitializer::configure('ZMS_ADMIN');

        $this->assertSame(42, LoggerService::$maxRequests);
        $this->assertSame(7, LoggerService::$maxErrorRequests);
    }

    public function testGetRequestLimitsUsesDefaults(): void
    {
        putenv('MAX_STRING_LENGTH');
        putenv('MAX_RECURSION_DEPTH');

        $limits = ModuleLoggerInitializer::getRequestLimits();

        $this->assertSame(32768, $limits['maxStringLength']);
        $this->assertSame(10, $limits['maxRecursionDepth']);
    }

    public function testTryInitializeCacheReturnsNullWhenDirectoryIsInvalid(): void
    {
        $cache = ModuleLoggerInitializer::tryInitializeCache('/dev/null/not-a-writable-cache-dir');

        $this->assertNull($cache);
        $this->assertNull(LoggerService::$cache);
    }
}
