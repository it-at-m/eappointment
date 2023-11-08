<?php

namespace BO\Zmsadmin\Tests;

class HealthcheckTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Healthcheck";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'parameters' => ['includeProcessStats' => 0],
                    'response' => $this->readFixture("GET_status_200.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test500()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'parameters' => ['includeProcessStats' => 0],
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertEquals(500, $response->getStatusCode());
    }
}
