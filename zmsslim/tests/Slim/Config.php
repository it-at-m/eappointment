<?php
// @codingStandardsIgnoreFile

class App extends \BO\Slim\Application
{
    public const string IDENTIFIER = 'Slim-ENV';
    public const string APP_PATH = APP_PATH;
    public const bool DEBUG = true;
    const bool LOG_ERRORS = false;

    const string TEMPLATE_PATH = '/Slim/templates/';

    const string SESSION_NAME = "Unittest";
    const string SESSION_ATTRIBUTE = 'session';
    const bool MULTILANGUAGE = true;
    const TWIG_CACHE = '/cache';
}

App::$now = new DateTimeImmutable('2016-04-01 08:00', new DateTimeZone('Europe/Berlin'));
