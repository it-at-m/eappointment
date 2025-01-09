<?php

namespace BO\Zmsadmin\Tests;

class QueueTableTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selecteddate' => '2016-04-01',
        'withCalled' => 1
    ];

    protected $classname = "QueueTable";

    public function testRendering()
    {
        $this->setApiCalls(
            [

                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/queue/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'status' => 'called',
                    ],
                    'response' => $this->readFixture("GET_queuelist_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('queue-table', (string)$response->getBody());
        $this->assertStringContainsString('<small>(1)</small>', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithClusterEnabled()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/process/2016-04-01/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_processlist_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/queue/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'status' => 'called',
                    ],
                    'response' => $this->readFixture("GET_queuelist_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('queue-table', (string)$response->getBody());
        $this->assertStringContainsString('Kürzel', (string)$response->getBody());
        $this->assertStringNotContainsString('Alle Clusterstandorte anzeigen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithMultipleClusterScopes()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_cluster_scopelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/169/department/',
                    'response' => $this->readFixture("GET_department_81.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/169/cluster/',
                    'response' => $this->readFixture("GET_cluster_117.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/169/process/2016-04-01/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_processlist_scope_169.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/useraccount/queue/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'status' => 'called',
                    ],
                    'response' => $this->readFixture("GET_queuelist_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Alle Clusterstandorte anzeigen', (string)$response->getBody());
    }

    public function testWithResetedProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/process/2016-04-01/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_processlist_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render([], [
            'selectedprocess' => 100044,
            'selecteddate' => '2016-04-01',
            'success' => 'process_reset_queued']);
        $this->assertStringContainsString(
            'Der Vorgang mit der Nummer 100044 (Name: BO) wurde erfolgreich zum Aufruf zurückgesetzt.',
            (string)$response->getBody()
        );
    }
}
