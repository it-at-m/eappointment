<?php

namespace BO\Zmscalldisplay\Tests;

class HealthcheckTest extends Base
{

    protected $classname = "Healthcheck";

    protected $arguments = [];

    protected $parameters = [];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/status/',
                'parameters' => ['includeProcessStats' => 0],
                'response' => $this->readFixture("GET_status.json"),
            ]
        ];
    }

    public function testRendering()
    {
        //$response = $this->render([ ], [ ], [ ]);
        //$this->assertStringContainsString('WARN', (string)$response->getBody());
    }
}
