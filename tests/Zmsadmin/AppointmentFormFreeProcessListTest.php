<?php

namespace BO\Zmsadmin\Tests;

class AppointmentFormFreeProcessListTest extends Base
{
    protected $arguments = [];

    protected $parameters = ['selecteddate' => '2016-05-27'];

    protected $classname = "AppointmentFormFreeProcessList";

    public function testRendering()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-04-01'], []);
        $this->assertContains('Spontankunde', (string)$response->getBody());
    }

    public function testWithoutExpired()
    {
        \App::$now = new \DateTimeImmutable('2016-05-27 11:30:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27'], []);
        $this->assertNotContains('11:20 (noch 1 frei)', (string)$response->getBody());
        $this->assertContains('11:40 (noch 1 frei)', (string)$response->getBody());
    }

    // time select field has to be disabled if no slot is existing
    public function testWithSelectedProcessChangedDate()
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
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 1],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
            ]
        );
        $response = $this->render([], [
            'selecteddate' => '2016-05-28',
            'selectedprocess' => 100044,
            'selectedscope' => 141
        ], []);
        $this->assertContains('disabled="disabled"', (string)$response->getBody());
    }

    public function testWithSelectedProcess()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
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
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 1],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
            ]
        );
        $response = $this->render([], [
            'selecteddate' => '2016-05-27',
            'selectedprocess' => 100044,
            'selectedscope' => 141
        ], []);
        $this->assertContains('11:40 (noch 1 frei)', (string)$response->getBody());
        $this->assertContains('17:00 (noch 0 frei)', (string)$response->getBody());
        $this->assertNotContains('Spontankunde', (string)$response->getBody());
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
