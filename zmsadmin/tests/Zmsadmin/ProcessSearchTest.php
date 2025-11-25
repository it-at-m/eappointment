<?php

namespace BO\Zmsadmin\Tests;

class ProcessSearchTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'query' => 'Test%20BO'
    ];

    protected $classname = "ProcessSearch";

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
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'query' => 'Test%20BO'
                    ],
                    'response' => $this->readFixture("GET_searchresult.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => 'Test%2520BO',
                        'page' => 1,
                        'perPage' => 100,
                        'service' => null,
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null
                    ],
                    'response' => $this->readFixture("GET_loglist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('<input type="text" name="query" value="Test%20BO"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithProcessId()
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
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'query' => '100005'
                    ],
                    'response' => $this->readFixture("GET_searchresult_processid.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => '100005',
                        'page' => 2,
                        'perPage' => 20,
                        'service' => null,
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null
                    ],
                    'response' => $this->readFixture("GET_loglist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'query' => '100005',
            'page' => 2,
            'perPage' => 20
        ], []);
        $this->assertStringContainsString('Log-Ergebnisse', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNoSuperuser()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/search/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'query' => 'Test%20BO'
                    ],
                    'response' => $this->readFixture("GET_searchresult_others.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/log/process/',
                    'parameters' => [
                        'searchQuery' => 'Test%2520BO',
                        'page' => 1,
                        'perPage' => 100,
                        'service' => null,
                        'provider' => null,
                        'userAction' => 0,
                        'date' => null
                    ],
                    'response' => $this->readFixture("GET_loglist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('data-processList-count="5"', (string)$response->getBody());
        $this->assertStringContainsString('data-processListOther-count="0"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
