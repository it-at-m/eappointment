<?php

namespace BO\Zmsadmin\Tests;

use \BO\Slim\Render;

class RoutingTest extends Base
{
    protected $classname = "Counter";

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

        $container = \App::$slim->getContainer();
        $response = $container['errorHandler']($request, $this->getResponse(), $exception);
        $this->assertContains('board exception', (string)$response->getBody());
        $this->assertContains(
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
        $container = \App::$slim->getContainer();
        $response = $container['errorHandler']($request, $this->getResponse(), $exception);
        $this->assertContains('Es ist ein Fehler aufgetreten', (string)$response->getBody());
        $this->assertContains('System Failure', (string)$response->getBody());
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
        $container = \App::$slim->getContainer();
        $response = $container['errorHandler']($request, $this->getResponse(), $exception);
        $this->assertContains('board exception', (string)$response->getBody());
        $this->assertContains(
            'Um diese Seite aufzurufen fehlen Ihnen die notwendigen Rechte',
            (string)$response->getBody()
        );
    }
}
