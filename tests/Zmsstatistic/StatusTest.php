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
        $this->assertContains('API Version', (string)$response->getBody());
        //check processes.confirmed:
        $this->assertContains('86861', (string)$response->getBody());
    }
}
