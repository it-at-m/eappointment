<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\Application;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ApplicationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/zmscitizenapi_test_' . uniqid();
        mkdir($this->tempDir);
    }

    public function testInitializeMaintenanceMode(): void
    {
        // Test enabled
        putenv('MAINTENANCE_ENABLED=true');
        Application::initialize();
        $this->assertTrue(Application::$MAINTENANCE_MODE_ENABLED);

        // Test disabled
        putenv('MAINTENANCE_ENABLED=false');
        Application::initialize();
        $this->assertFalse(Application::$MAINTENANCE_MODE_ENABLED);

        // Test default value
        putenv('MAINTENANCE_ENABLED');
        Application::initialize();
        $this->assertFalse(Application::$MAINTENANCE_MODE_ENABLED);
    }

    public function testInitializeLogger(): void
    {
        // Test with custom values
        putenv('LOGGER_MAX_REQUESTS=500');
        putenv('LOGGER_RESPONSE_LENGTH=2097152');
        putenv('LOGGER_STACK_LINES=30');
        putenv('LOGGER_MESSAGE_SIZE=16384');
        putenv('LOGGER_CACHE_TTL=120');
        putenv('LOGGER_MAX_RETRIES=5');
        putenv('LOGGER_BACKOFF_MIN=200');
        putenv('LOGGER_BACKOFF_MAX=2000');
        putenv('LOGGER_LOCK_TIMEOUT=10');

        Application::initialize();
        $config = Application::getLoggerConfig();

        $this->assertEquals(500, $config['maxRequests']);
        $this->assertEquals(2097152, $config['responseLength']);
        $this->assertEquals(30, $config['stackLines']);
        $this->assertEquals(16384, $config['messageSize']);
        $this->assertEquals(120, $config['cacheTtl']);
        $this->assertEquals(5, $config['maxRetries']);
        $this->assertEquals(200, $config['backoffMin']);
        $this->assertEquals(2000, $config['backoffMax']);
        $this->assertEquals(10, $config['lockTimeout']);

        // Test default values
        putenv('LOGGER_MAX_REQUESTS');
        putenv('LOGGER_RESPONSE_LENGTH');
        putenv('LOGGER_STACK_LINES');
        putenv('LOGGER_MESSAGE_SIZE');
        putenv('LOGGER_CACHE_TTL');
        putenv('LOGGER_MAX_RETRIES');
        putenv('LOGGER_BACKOFF_MIN');
        putenv('LOGGER_BACKOFF_MAX');
        putenv('LOGGER_LOCK_TIMEOUT');

        Application::initialize();
        $config = Application::getLoggerConfig();

        $this->assertEquals(1000, $config['maxRequests']);
        $this->assertEquals(1048576, $config['responseLength']);
        $this->assertEquals(20, $config['stackLines']);
        $this->assertEquals(8192, $config['messageSize']);
        $this->assertEquals(60, $config['cacheTtl']);
        $this->assertEquals(3, $config['maxRetries']);
        $this->assertEquals(100, $config['backoffMin']);
        $this->assertEquals(1000, $config['backoffMax']);
        $this->assertEquals(5, $config['lockTimeout']);
    }

    public function testInitializeCaptcha(): void
    {
        // Test with custom values
        putenv('CAPTCHA_ENABLED=true');
        putenv('FRIENDLY_CAPTCHA_SECRET_KEY=test_secret');
        putenv('FRIENDLY_CAPTCHA_SITE_KEY=test_site');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT=https://test.example.com/verify');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE=https://test.example.com/puzzle');
        putenv('ALTCHA_CAPTCHA_SECRET_KEY=alt_secret');
        putenv('ALTCHA_CAPTCHA_SITE_KEY=alt_site');
        putenv('ALTCHA_CAPTCHA_ENDPOINT=https://test.altcha.org/form');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_PUZZLE=https://test.altcha.org');

        Application::initialize();

        $this->assertTrue(Application::$CAPTCHA_ENABLED);
        $this->assertEquals('test_secret', Application::$FRIENDLY_CAPTCHA_SECRET_KEY);
        $this->assertEquals('test_site', Application::$FRIENDLY_CAPTCHA_SITE_KEY);
        $this->assertEquals('https://test.example.com/verify', Application::$FRIENDLY_CAPTCHA_ENDPOINT);
        $this->assertEquals('https://test.example.com/puzzle', Application::$FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE);
        $this->assertEquals('alt_secret', Application::$ALTCHA_CAPTCHA_SECRET_KEY);
        $this->assertEquals('alt_site', Application::$ALTCHA_CAPTCHA_SITE_KEY);
        $this->assertEquals('https://test.altcha.org/form', Application::$ALTCHA_CAPTCHA_ENDPOINT);
        $this->assertEquals('https://test.altcha.org', Application::$ALTCHA_CAPTCHA_ENDPOINT_PUZZLE);
    }

    public function testInitializeCache(): void
    {
        $cacheDir = $this->tempDir . '/cache';
        putenv("CACHE_DIR=$cacheDir");
        putenv('SOURCE_CACHE_TTL=7200');

        Application::initialize();

        $this->assertEquals($cacheDir, Application::$CACHE_DIR);
        $this->assertEquals(7200, Application::$SOURCE_CACHE_TTL);
        $this->assertTrue(is_dir($cacheDir));
        $this->assertNotNull(Application::$cache);
    }

    public function testValidateCacheDirectoryThrowsExceptionWhenNotWritable(): void
    {
        $this->expectException(\RuntimeException::class);

        $readOnlyDir = $this->tempDir . '/readonly';
        mkdir($readOnlyDir, 0444);
        putenv("CACHE_DIR=$readOnlyDir");

        Application::initialize();
    }

    public function testInitializeMiddleware(): void
    {
        // Test rate limiting config
        putenv('RATE_LIMIT_MAX_REQUESTS=100');
        putenv('RATE_LIMIT_CACHE_TTL=300');
        putenv('RATE_LIMIT_MAX_RETRIES=4');
        putenv('RATE_LIMIT_BACKOFF_MIN=20');
        putenv('RATE_LIMIT_BACKOFF_MAX=100');
        putenv('RATE_LIMIT_LOCK_TIMEOUT=2');

        Application::initialize();
        $rateLimit = Application::getRateLimit();

        $this->assertEquals(100, $rateLimit['maxRequests']);
        $this->assertEquals(300, $rateLimit['cacheExpiry']);
        $this->assertEquals(4, $rateLimit['maxRetries']);
        $this->assertEquals(20, $rateLimit['backoffMin']);
        $this->assertEquals(100, $rateLimit['backoffMax']);
        $this->assertEquals(2, $rateLimit['lockTimeout']);

        // Test request limits
        putenv('MAX_REQUEST_SIZE=20971520');
        putenv('MAX_STRING_LENGTH=65536');
        putenv('MAX_RECURSION_DEPTH=20');

        Application::initialize();
        $requestLimits = Application::getRequestLimits();

        $this->assertEquals(20971520, $requestLimits['maxSize']);
        $this->assertEquals(65536, $requestLimits['maxStringLength']);
        $this->assertEquals(20, $requestLimits['maxRecursionDepth']);

        // Test CSRF config
        putenv('CSRF_TOKEN_LENGTH=64');
        putenv('CSRF_SESSION_KEY=custom_csrf_key');

        Application::initialize();
        $csrfConfig = Application::getCsrfConfig();

        $this->assertEquals(64, $csrfConfig['tokenLength']);
        $this->assertEquals('custom_csrf_key', $csrfConfig['sessionKey']);
    }

    public function testGetCorsAllowedOrigins(): void
    {
        putenv('CORS=https://example.com,https://test.com');
        Application::initialize();
        
        $origins = Application::getCorsAllowedOrigins();
        $this->assertCount(2, $origins);
        $this->assertEquals(['https://example.com', 'https://test.com'], $origins);

        // Test empty CORS
        putenv('CORS=');
        Application::initialize();
        $this->assertEmpty(Application::getCorsAllowedOrigins());
    }

    public function testGetIpBlacklist(): void
    {
        putenv('IP_BLACKLIST=192.168.1.1,10.0.0.1');
        Application::initialize();
        $this->assertEquals('192.168.1.1,10.0.0.1', Application::getIpBlacklist());

        putenv('IP_BLACKLIST=');
        Application::initialize();
        $this->assertEquals('', Application::getIpBlacklist());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up temporary directory
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }

        // Reset environment variables
        putenv('MAINTENANCE_ENABLED');
        putenv('LOGGER_MAX_REQUESTS');
        putenv('LOGGER_RESPONSE_LENGTH');
        putenv('LOGGER_STACK_LINES');
        putenv('LOGGER_MESSAGE_SIZE');
        putenv('LOGGER_CACHE_TTL');
        putenv('LOGGER_MAX_RETRIES');
        putenv('LOGGER_BACKOFF_MIN');
        putenv('LOGGER_BACKOFF_MAX');
        putenv('LOGGER_LOCK_TIMEOUT');
        putenv('CAPTCHA_ENABLED');
        putenv('FRIENDLY_CAPTCHA_SECRET_KEY');
        putenv('FRIENDLY_CAPTCHA_SITE_KEY');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT');
        putenv('FRIENDLY_CAPTCHA_ENDPOINT_PUZZLE');
        putenv('ALTCHA_CAPTCHA_SECRET_KEY');
        putenv('ALTCHA_CAPTCHA_SITE_KEY');
        putenv('ALTCHA_CAPTCHA_ENDPOINT');
        putenv('ALTCHA_CAPTCHA_ENDPOINT_PUZZLE');
        putenv('CACHE_DIR');
        putenv('SOURCE_CACHE_TTL');
        putenv('RATE_LIMIT_MAX_REQUESTS');
        putenv('RATE_LIMIT_CACHE_TTL');
        putenv('RATE_LIMIT_MAX_RETRIES');
        putenv('RATE_LIMIT_BACKOFF_MIN');
        putenv('RATE_LIMIT_BACKOFF_MAX');
        putenv('RATE_LIMIT_LOCK_TIMEOUT');
        putenv('MAX_REQUEST_SIZE');
        putenv('MAX_STRING_LENGTH');
        putenv('MAX_RECURSION_DEPTH');
        putenv('CSRF_TOKEN_LENGTH');
        putenv('CSRF_SESSION_KEY');
        putenv('CORS');
        putenv('IP_BLACKLIST');
    }

    private function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $path = $dir . '/' . $file;
                    if (is_dir($path)) {
                        $this->removeDirectory($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            rmdir($dir);
        }
    }
}