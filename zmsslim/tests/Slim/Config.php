<?php
// @codingStandardsIgnoreFile

class App extends \BO\Slim\Application
{
    const IDENTIFIER = 'Slim-ENV';
    const APP_PATH = APP_PATH;
    const DEBUG = true;
    const LOG_ERRORS = false;

    const TEMPLATE_PATH = '/Slim/templates/';

    const SESSION_NAME = "Unittest";
    const SESSION_ATTRIBUTE = 'session';
    const MULTILANGUAGE = true;
    const TWIG_CACHE = '/cache';
}

App::$now = new DateTimeImmutable('2016-04-01 08:00', new DateTimeZone('Europe/Berlin'));