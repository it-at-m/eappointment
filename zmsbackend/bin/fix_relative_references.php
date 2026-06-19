<?php
declare(strict_types=1);

require __DIR__ . '/migrate_all.php';

$roots = [
    $GLOBALS['src'],
    $GLOBALS['zb'] . '/tests/Zmsbackend',
];

foreach ($roots as $root) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
            continue;
        }
        $path = $fileInfo->getPathname();
        $content = file_get_contents($path);
        $fixed = fixRelativeReferences($content);
        if ($fixed !== $content) {
            file_put_contents($path, $fixed);
            echo "fixed: $path\n";
        }
    }
}

$fixturesFrom = $GLOBALS['zapi'] . '/tests/Zmsapi/fixtures';
$fixturesTo = $GLOBALS['zb'] . '/tests/Zmsbackend/fixtures';
if (is_dir($fixturesFrom)) {
    ensureDir($fixturesTo);
    foreach (glob($fixturesFrom . '/*') as $fixture) {
        copy($fixture, $fixturesTo . '/' . basename($fixture));
    }
    echo "copied api fixtures to $fixturesTo\n";
}
$serviceFixturesFrom = $GLOBALS['zdb'] . '/tests/Zmsdb/fixtures';
$serviceFixturesTo = $GLOBALS['zb'] . '/tests/Zmsbackend/Service/fixtures';
if (is_dir($serviceFixturesFrom)) {
    ensureDir($serviceFixturesTo);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($serviceFixturesFrom, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $target = $serviceFixturesTo . '/' . $iterator->getSubPathName();
        if ($item->isDir()) {
            ensureDir($target);
        } else {
            ensureDir(dirname($target));
            copy($item->getPathname(), $target);
        }
    }
    echo "copied service fixtures to $serviceFixturesTo\n";
}
$requestVariantFixture = $fixturesTo . '/requestvariant_list.json';
$requestVariantDestDir = $GLOBALS['zb'] . '/tests/Zmsbackend/Request/Api/fixtures';
if (is_file($requestVariantFixture)) {
    ensureDir($requestVariantDestDir);
    copy($requestVariantFixture, $requestVariantDestDir . '/requestvariant_list.json');
}
$calendarFixture = $fixturesTo . '/calendar.json';
$calendarDestDir = $GLOBALS['zb'] . '/tests/Zmsbackend/Calendar/Api/fixtures';
if (is_file($calendarFixture)) {
    ensureDir($calendarDestDir);
    copy($calendarFixture, $calendarDestDir . '/calendar.json');
}
