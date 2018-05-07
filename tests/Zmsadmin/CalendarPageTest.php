<?php

namespace BO\Zmsadmin\Tests;

class CalendarPageTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'slottype' => 'intern',
        'slotsrequired' => 0
    ];

    protected $classname = "CalendarPage";

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
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_calendar.json")
                ]
            ]
        );
        $response = $this->render([], $this->parameters, []);
        $this->assertContains(
            'data-date="2016-05-27" title="Noch bis zu 2 Termine verfügbar"',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithSelectedScope()
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
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_calendar.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
            'slottype' => 'intern',
            'slotsrequired' => 0,
            'selectedscope' => 141
            ],
            []
        );
        $this->assertContains(
            'data-date="2016-05-27" title="Noch bis zu 2 Termine verfügbar"',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
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
                    'url' => '/calendar/',
                    'parameters' => ['fillWithEmptyDays' => 1, 'slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => '{}'
                ]
            ]
        );
        $this->render([], $this->parameters, []);
    }
}
