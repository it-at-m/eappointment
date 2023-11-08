<?php

namespace BO\Zmsstatistic\Tests;

use \BO\Slim\Render;

class RoutingTest extends Base
{
    protected $classname = "Index";

    public function testRendering()
    {
        $this->assertEmpty(\BO\Slim\Bootstrap::loadRouting(\App::APP_PATH . '/routing.php'));
    }

    public function testErrorHandlerWithCustomTemplate()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );

        $request = static::createBasicRequest('GET', '/workstation/');
        $exception = new \BO\Zmsentities\Exception\UserAccountMissingRights();

        $errorHandler = \App::$slim->getContainer()->get('errorMiddleware')->getDefaultErrorHandler();
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString('board exception', (string)$response->getBody());
        $this->assertStringContainsString(
            'Um diese Seite aufzurufen fehlen Ihnen die notwendigen Rechte',
            (string)$response->getBody()
        );
    }

    public function testErrorHandlerWithExceptionTemplateData()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );

        $request = static::createBasicRequest('GET', '/workstation/');
        $exception = new \Exception('System Failure', 404);
        $errorHandler = \App::$slim->getContainer()->get('errorMiddleware')->getDefaultErrorHandler();
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString('Es ist ein Fehler aufgetreten', (string)$response->getBody());
        $this->assertStringContainsString('System Failure', (string)$response->getBody());
    }

    public function testErrorHandlerIgnoreException()
    {
        $exception = new \BO\Zmsentities\Exception\UserAccountMissingRights();
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'exception' => $exception
                ]
            ]
        );

        $request = static::createBasicRequest('GET', '/workstation/');
        $errorHandler = \App::$slim->getContainer()->get('errorMiddleware')->getDefaultErrorHandler();
        $response = $errorHandler($request, $exception, true, false, false);
        $this->assertStringContainsString('board exception', (string)$response->getBody());
        $this->assertStringContainsString(
            'Um diese Seite aufzurufen fehlen Ihnen die notwendigen Rechte',
            (string)$response->getBody()
        );
    }
}
