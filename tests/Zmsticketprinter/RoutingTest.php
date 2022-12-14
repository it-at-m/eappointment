<?php

namespace BO\Zmsticketprinter\Tests;

use \BO\Slim\Render;

class RoutingTest extends Base
{
    protected $classname = "Helper\TwigExceptionHandler";

    public function testRendering()
    {
        $this->assertEmpty(\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php'));
    }

    public function testErrorHandlerWithCustomTemplate()
    {
        $request = static::createBasicRequest('GET', '/');
        \App::$language = new \BO\Slim\Language($request, \App::$supportedLanguages);
        $exception = new \BO\Zmsticketprinter\Exception\ScopeNotFound();
        $errorHandler = \App::$slim->getContainer()->get('errorHandler');
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString(
            'Es konnte zu den angegeben Daten kein Standort gefunden werden.',
            (string)$response->getBody()
        );
    }

    public function testErrorHandlerWithExceptionTemplateData()
    {
        $request = static::createBasicRequest('GET', '/');
        $exception = new \Exception('System Failure', 404);
        $errorHandler = \App::$slim->getContainer()->get('errorHandler');
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString('Es ist ein Fehler aufgetreten', (string)$response->getBody());
        $this->assertStringContainsString('System Failure', (string)$response->getBody());
    }
}
