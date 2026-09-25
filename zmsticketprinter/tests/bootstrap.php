<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // catch errors on bootstrapping
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

putenv('ZMS_CONFIG_SECURE_TOKEN=secure-token');

$testToken = getenv('TEST_TOKEN');
if (is_string($testToken) && $testToken !== '') {
    $cacheDir = dirname(__DIR__) . '/cache/paratest-' . $testToken;
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    putenv('ZMS_TICKETPRINTER_TWIG_CACHE=/cache/paratest-' . $testToken . '/');
}

require(dirname(__DIR__) . '/bootstrap.php');

App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
