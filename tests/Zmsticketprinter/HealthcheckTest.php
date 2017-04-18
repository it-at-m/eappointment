<?php

namespace BO\Zmsticketprinter\Tests;

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
                'response' => $this->readFixture("GET_status.json"),
            ]
        ];
    }

    public function testRendering()
    {
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('WARN', (string)$response->getBody());
    }
}
