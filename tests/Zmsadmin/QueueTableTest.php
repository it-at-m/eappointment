<?php

namespace BO\Zmsadmin\Tests;

class QueueTableTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selecteddate' => '2016-04-01'
    ];

    protected $classname = "QueueTable";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
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
                    'url' => '/scope/141/process/2016-04-01/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('queue-table', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithClusterEnabled()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
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
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_processlist_cluster_109.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains('queue-table', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithResetedProcess()
    {
        $this->setApiCalls(
            [
              [
                  'function' => 'readGetResult',
                  'url' => '/workstation/',
                  'parameters' => ['resolveReferences' => 1],
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
                  'parameters' => ['resolveReferences' => 0],
                  'response' => $this->readFixture("GET_processlist_cluster_109.json")
              ],
              [
                  'function' => 'readGetResult',
                  'url' => '/process/100044/',
                  'response' => $this->readFixture("GET_process_100044_57c2.json")
              ]
            ]
        );
        $response = $this->render([], [
            'selectedprocess' => 100044,
            'selecteddate' => '2016-04-01',
            'success' => 'process_reset_queued']);
        $this->assertContains(
            'Der Termin mit der Nummer 100044 (Name: BO) wurde erfolgreich zum Aufruf zurückgesetzt.',
            (string)$response->getBody()
        );
    }
}
