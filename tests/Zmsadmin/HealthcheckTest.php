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
                'response' => $this->readFixture("GET_status.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = parent::testRendering();
        $this->assertContains('DB connection without replication log detected', (string)$response->getBody());
    }
}
