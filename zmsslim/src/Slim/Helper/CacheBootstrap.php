<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

use BO\Slim\LoggerService;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Shared filesystem cache bootstrap for module Application classes.
 */
final class CacheBootstrap
{
    private const string ACTIVE_FILE = '.active-namespace';

    private const string LOCK_FILE = '.staging.lock';

    /** @var resource|null */
    private static $stagingLock = null;

    private static ?string $stagingBaseNamespace = null;

    /**
     * @return array{0: string, 1: int}
     */
    public static function resolveConfig(?string $fallbackCacheDir = null): array
    {
        $cacheDir = getenv('CACHE_DIR');
        if ($cacheDir === false || $cacheDir === '' || $cacheDir === '0') {
            $cacheDir = $fallbackCacheDir ?? sys_get_temp_dir();
        }
        $ttl = self::getenvInt('SOURCE_CACHE_TTL', 3600);

        return [$cacheDir, $ttl];
    }

    public static function create(string $cacheDir, int $ttl): CacheInterface
    {
        self::validateDirectory($cacheDir);

        $cache = self::openNamespace($cacheDir, $ttl, self::activeNamespace($cacheDir));
        LoggerService::$cache = $cache;

        return $cache;
    }

    /**
     * Copy the cache that is serving into the standby namespace.
     * Callers refresh entries there, then publishNamespace() switches readers over.
     *
     * @return array{0: CacheInterface, 1: string}
     */
    public static function openStaging(string $cacheDir, int $ttl): array
    {
        self::validateDirectory($cacheDir);
        self::acquireStagingLock($cacheDir);
        try {
            $active = self::activeNamespace($cacheDir);
            $next = $active === 'g1' ? 'g2' : 'g1';
            self::$stagingBaseNamespace = $active;
            $target = self::namespaceDirectory($cacheDir, $next);
            self::removeTree($target);
            $source = self::namespaceDirectory($cacheDir, $active);
            if (is_dir($source)) {
                self::copyTree($source, $target);
            }

            return [self::openNamespace($cacheDir, $ttl, $next), $next];
        } catch (\Throwable $exception) {
            self::$stagingBaseNamespace = null;
            self::releaseStagingLock();
            throw $exception;
        }
    }

    public static function openNamespace(string $cacheDir, int $ttl, string $namespace): CacheInterface
    {
        if ($namespace !== '' && $namespace !== 'g1' && $namespace !== 'g2') {
            throw new \InvalidArgumentException('Unknown cache namespace');
        }

        return new Psr16Cache(new FilesystemAdapter(namespace: $namespace, defaultLifetime: $ttl, directory: $cacheDir));
    }

    public static function publishNamespace(string $cacheDir, string $namespace): void
    {
        try {
            if ($namespace !== 'g1' && $namespace !== 'g2') {
                throw new \InvalidArgumentException('Unknown cache namespace');
            }

            self::validateDirectory($cacheDir);
            if (
                self::$stagingBaseNamespace !== null
                && self::activeNamespace($cacheDir) !== self::$stagingBaseNamespace
            ) {
                throw new \RuntimeException('The live cache changed while the standby copy was built');
            }

            $path = $cacheDir . '/' . self::ACTIVE_FILE;
            $temporary = $path . '.tmp';
            if (file_put_contents($temporary, $namespace) === false || !rename($temporary, $path)) {
                throw new \RuntimeException('Could not publish the standby cache');
            }
            self::$stagingBaseNamespace = null;
        } finally {
            self::releaseStagingLock();
        }
    }

    public static function abortStaging(): void
    {
        self::$stagingBaseNamespace = null;
        self::releaseStagingLock();
    }

    public static function createFromEnv(?string $fallbackCacheDir = null): CacheInterface
    {
        [$cacheDir, $ttl] = self::resolveConfig($fallbackCacheDir);

        return self::create($cacheDir, $ttl);
    }

    public static function validateDirectory(string $cacheDir): void
    {
        if (!is_dir($cacheDir)) {
            if (!@mkdir($cacheDir, 0750, true) && !is_dir($cacheDir)) {
                throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', $cacheDir));
            }
        }

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" is not writable', $cacheDir));
        }
    }

    private static function activeNamespace(string $cacheDir): string
    {
        $path = $cacheDir . '/' . self::ACTIVE_FILE;
        if (!is_file($path)) {
            return '';
        }

        $value = trim((string) file_get_contents($path));
        if ($value === 'g1' || $value === 'g2') {
            return $value;
        }

        return '';
    }

    private static function namespaceDirectory(string $cacheDir, string $namespace): string
    {
        $folder = $namespace === '' ? '@' : $namespace;

        return rtrim($cacheDir, '/') . '/' . $folder;
    }

    private static function copyTree(string $source, string $target): void
    {
        if (!is_dir($target) && !mkdir($target, 0750, true) && !is_dir($target)) {
            throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', $target));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $destination = $target . substr($item->getPathname(), strlen($source));
            if ($item->isDir()) {
                if (!is_dir($destination) && !mkdir($destination, 0750, true) && !is_dir($destination)) {
                    throw new \RuntimeException(sprintf('Cache directory "%s" could not be created', $destination));
                }
                continue;
            }
            if (!copy($item->getPathname(), $destination)) {
                throw new \RuntimeException(sprintf('Cache file "%s" could not be copied', $item->getPathname()));
            }
        }
    }

    private static function removeTree(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }
            unlink($item->getPathname());
        }
        rmdir($directory);
    }

    private static function acquireStagingLock(string $cacheDir): void
    {
        $handle = fopen($cacheDir . '/' . self::LOCK_FILE, 'c');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new \RuntimeException('Could not lock the cache warmup');
        }

        self::$stagingLock = $handle;
    }

    private static function releaseStagingLock(): void
    {
        if (!is_resource(self::$stagingLock)) {
            self::$stagingLock = null;
            return;
        }

        flock(self::$stagingLock, LOCK_UN);
        fclose(self::$stagingLock);
        self::$stagingLock = null;
    }

    private static function getenvInt(string $name, int $default): int
    {
        $value = getenv($name);
        if ($value === false || $value === '' || $value === '0') {
            return $default;
        }

        return (int) $value;
    }
}
