<?php

namespace BO\Zmsadmin\Tests;

class PickupQueueTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "PickupQueue";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/process/pickup/',
                    'parameters' => ['resolveReferences' => 1, 'selectedScope' => 141],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('pickup-table', (string)$response->getBody());
        $this->assertContains('change-scope', (string)$response->getBody());
        $this->assertContains('value="141"', (string)$response->getBody());
        $this->assertContains('value="140"', (string)$response->getBody());
        $this->assertContains('value="142"', (string)$response->getBody());
        $this->assertContains('value="380"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
