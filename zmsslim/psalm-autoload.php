<?php

/**
 * Psalm-only autoloader for soft OAuth deps (zmsclient requires zmsslim, so
 * those packages are not Composer-required here). Maps sibling monorepo sources.
 */
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'BO\\Zmsclient\\' => __DIR__ . '/../zmsclient/src/Zmsclient/',
        'BO\\Zmsentities\\' => __DIR__ . '/../zmsentities/src/Zmsentities/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $baseDir . $relative;
        if (is_file($file)) {
            require_once $file;
        }
    }
});

require_once __DIR__ . '/vendor/autoload.php';
