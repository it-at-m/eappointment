<?php

namespace BO\Zmsadmin\Tests;

class AvailabilityDeleteTest extends Base
{
    protected $arguments = ['id' => 68985];

    protected $parameters = [];

    protected $classname = "AvailabilityDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/availability/68985/',
                    'response' => $this->readFixture("GET_availability_68985.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertStringContainsString('"id":"68985"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readDeleteResult',
                    'url' => '/availability/999999/',
                    'response' => $this->readFixture("GET_availability_notFound.json")
                ]
            ]
        );
        $response = $this->render(['id' => 999999], []);
        $this->assertStringContainsString('"message":"Not found"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
