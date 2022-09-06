<?php

namespace BO\Zmsadmin\Tests;

class HealthcheckTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "Healthcheck";

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/status/',
                'parameters' => ['includeProcessStats' => 0],
                'response' => $this->readFixture("GET_status_200.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
