<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

putenv('ZMS_CONFIG_SECURE_TOKEN=secure-token');
putenv('ZMS_BACKEND_TWIG_CACHE=false');

require_once(dirname(dirname(__DIR__)) . '/bin/script_bootstrap.php');

App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
