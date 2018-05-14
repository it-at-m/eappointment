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
        $container = \App::$slim->getContainer();
        $response = $container['errorHandler']($request, $this->getResponse(), $exception);
        $this->assertContains(
            'Es konnte zu den angegeben Daten kein Standort gefunden werden.',
            (string)$response->getBody()
        );
    }

    public function testErrorHandlerWithExceptionTemplateData()
    {
        $request = static::createBasicRequest('GET', '/');
        $exception = new \Exception('System Failure', 404);
        $container = \App::$slim->getContainer();
        $response = $container['errorHandler']($request, $this->getResponse(), $exception);
        $this->assertContains('Es ist ein Fehler aufgetreten', (string)$response->getBody());
        $this->assertContains('System Failure', (string)$response->getBody());
    }
}
