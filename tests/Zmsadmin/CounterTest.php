<?php

namespace BO\Zmsadmin\Tests;

class CounterTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Counter";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('counter-view', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithSelectedDate()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['date' => '2016-04-04'], []);
        $this->assertStringContainsString('2016-04-04', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
