<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Don't convert stream errors to exceptions for LoggerService in PHP 8.3
    if (str_contains($errstr, 'rewind(): Stream does not support seeking') ||
        str_contains($errstr, 'stream_get_contents(): Read of')) {
        return false; // Let PHP handle it normally
    }
    // For all other errors, throw an exception as before
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Set environment variable for SECURE_TOKEN before loading config
putenv('ZMS_CONFIG_SECURE_TOKEN=hash');

require(dirname(dirname(__DIR__)) . '/bootstrap.php');

App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
App::$source_name = "unittest";
