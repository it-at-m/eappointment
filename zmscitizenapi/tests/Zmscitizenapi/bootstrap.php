<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Don't convert stream errors to exceptions in PHP 8.3
    if (strpos($errstr, 'rewind(): Stream does not support seeking') !== false ||
        strpos($errstr, 'stream_get_contents(): Read of') !== false) {
        return false; // Let PHP handle it normally
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require(dirname(dirname(__DIR__)) . '/bootstrap.php');

App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
App::$source_name = "unittest";
