<?php

namespace BO\Zmsadmin\Tests;

class CounterQueueInfoTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'ghostworkstationcount' => 2
    ];

    protected $classname = "CounterQueueInfo";

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
                    'function' => 'readPostResult',
                    'url' => '/scope/141/ghostworkstation/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/queue/',
                    'response' => $this->readFixture("GET_scope_141_queuelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/availability/',
                    'response' => $this->readFixture("GET_scope_141_availability.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141_workstationlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('2 (1)</strong> Arbeitsplatz besetzt', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
