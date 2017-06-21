<?php

namespace BO\Zmsadmin\Tests;

class AppointmentFormFreeProcessListTest extends Base
{
    protected $arguments = [];

    protected $parameters = ['selecteddate' => '2016-05-27'];

    protected $classname = "AppointmentFormFreeProcessList";

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
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertContains('Spontankunde', (string)$response->getBody());
    }

    public function testWithSelectedDate()
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
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], $this->parameters, []);
        $this->assertContains('11:20 (noch 1 frei)', (string)$response->getBody());
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Zmsclient\Exception');
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
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => '{}'
                ]
            ]
        );
        $this->render([], $this->parameters, []);
    }
}
