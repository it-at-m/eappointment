<?php
// @codingStandardsIgnoreFile

class App extends \BO\Zmsapi\Application
{
    const APP_PATH = APP_PATH;
    const IDENTIFIER = 'Zmsapi-unconfigured';
    const DEBUG = true;
}

// Uncomment the following line for production data, this is testing only
App::$now = new DateTimeImmutable('2016-04-01 11:55:00', new DateTimeZone('Europe/Berlin'));
