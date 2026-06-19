<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

putenv('ZMS_CONFIG_SECURE_TOKEN=secure-token');
putenv('ZMS_BACKEND_TWIG_CACHE=false');

$moduleRoot = dirname(dirname(__DIR__));
if (!file_exists("$moduleRoot/config.php")) {
    copy("$moduleRoot/config.example.php", "$moduleRoot/config.php");
}

require_once("$moduleRoot/bin/script_bootstrap.php");

App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
