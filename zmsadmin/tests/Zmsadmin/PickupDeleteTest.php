<?php

namespace BO\Zmsadmin\Tests;

class PickupDeleteTest extends Base
{
    protected $arguments = [
        'id' => '82252'
    ];

    protected $parameters = [];

    protected $classname = "PickupDelete";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process_pickup.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2_pickup.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'parameters' => ['survey' => 0],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertStringContainsString(
            'Der Abholer mit der Wartenummer 82252 wurde erfolgreich aus der Liste entfernt.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteList()
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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'parameters' => ['survey' => 0],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['list' => 1], []);
        $this->assertStringContainsString(
            'Alle Termine wurden erfolgreich aus der Liste entfernt.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
