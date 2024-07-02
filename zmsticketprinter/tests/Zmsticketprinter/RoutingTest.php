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

    public function testErrorHandlerWithExceptionTemplateData()
    {
        $request = static::createBasicRequest('GET', '/');
        \App::$language = new \BO\Slim\Language($request, \App::$supportedLanguages);
        $exception = new \BO\Zmsticketprinter\Exception\ScopeNotFound();
        $container = \App::$slim->getContainer()->get('errorHandler');
        
        // Properly invoking the error handler with the correct arguments
        $errorHandler = new \BO\Slim\TwigExceptionHandler();
        $response = $errorHandler($request, $exception, true, true, true);
        
        $this->assertStringContainsString(
            'Es konnte zu den angegeben Daten kein Standort gefunden werden.',
            (string)$response->getBody()
        );
    }

    public function testErrorHandlerWithCustomTemplate()
    {
        $request = static::createBasicRequest('GET', '/');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsapi\Exception\Organisation\OrganisationNotFound';
        $exception->data = ['scope' => new \BO\Zmsentities\Scope(['id' => 141])];
        $container = \App::$slim->getContainer()->get('errorHandler');
        
        // Properly invoking the error handler with the correct arguments
        $errorHandler = new \BO\Slim\TwigExceptionHandler();
        $response = $errorHandler($request, $exception, true, true, true);
        
        $this->assertStringContainsString('Ein Fehler ist aufgetreten', (string)$response->getBody());
        $this->assertStringContainsString(
            'Zu dieser Auswahl konnte keine Organisation gefunden werden. Bitte prüfen Sie Ihre Angaben.',
            (string)$response->getBody()
        );
    }
}
