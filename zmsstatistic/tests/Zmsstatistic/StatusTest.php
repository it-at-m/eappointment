<?php

namespace BO\Zmsstatistic\Tests;

class StatusTest extends Base
{
    protected $arguments = [];
    protected $parameters = [];

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
        $this->assertStringContainsString('API Version', (string)$response->getBody());
        //check processes.confirmed:
        $this->assertStringContainsString('86861', (string)$response->getBody());
    }
}
