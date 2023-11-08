<?php

namespace BO\Zmsadmin\Tests;

class CalendarPageTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'slotType' => 'intern',
        'slotsRequired' => 0
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
        $this->assertStringContainsString(
            'data-date="2016-05-27" title="Fr. 27. Mai 2016 - noch bis zu 2 Termine frei"',
            (string)$response->getBody()
        );
        $this->assertStringContainsString(
            'data-date="2016-04-10" title="So. 10. April 2016 - noch bis zu 3 Termine frei"',
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
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
            'slotType' => 'intern',
            'slotsRequired' => 0,
            'selectedscope' => 141
            ],
            []
        );
        $this->assertStringContainsString(
            'data-date="2016-05-27" title="Fr. 27. Mai 2016 - noch bis zu 2 Termine frei"',
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
