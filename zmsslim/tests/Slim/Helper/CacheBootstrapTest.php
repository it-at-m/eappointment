<?php

declare(strict_types=1);

namespace BO\Slim\Tests\Helper;

use BO\Slim\Helper\CacheBootstrap;
use BO\Slim\LoggerService;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class CacheBootstrapTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/zms-cache-bootstrap-' . uniqid('', true);
        putenv('CACHE_DIR');
        putenv('SOURCE_CACHE_TTL');
    }

    protected function tearDown(): void
    {
        putenv('CACHE_DIR');
        putenv('SOURCE_CACHE_TTL');
        LoggerService::$cache = null;
        if (is_dir($this->tempDir)) {
            @rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    public function testCreateFromEnvUsesFallbackDirectoryAndTtl(): void
    {
        putenv('SOURCE_CACHE_TTL=1800');

        $cache = CacheBootstrap::createFromEnv($this->tempDir);

        $this->assertInstanceOf(CacheInterface::class, $cache);
        $this->assertTrue(is_dir($this->tempDir));
        $this->assertSame($cache, LoggerService::$cache);
        [$dir, $ttl] = CacheBootstrap::resolveConfig($this->tempDir);
        $this->assertSame($this->tempDir, $dir);
        $this->assertSame(1800, $ttl);
    }

    public function testValidateDirectoryThrowsForUnwritablePath(): void
    {
        $this->expectException(\RuntimeException::class);
        CacheBootstrap::validateDirectory('/dev/null/not-a-writable-cache-dir');
    }
}
