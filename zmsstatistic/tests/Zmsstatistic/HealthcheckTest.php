<?php

namespace BO\Zmsstatistic\Tests;

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
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );

        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('DB connection without replication log detected', (string)$response->getBody());
    }
}
