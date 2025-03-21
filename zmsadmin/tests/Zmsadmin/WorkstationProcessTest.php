<?php

namespace BO\Zmsadmin\Tests;

use DateTime;

class WorkstationProcessTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'ghostworkstationcount' => 2
    ];

    protected $classname = "WorkstationProcess";

    public function testRendering()
    {
        $date = (new DateTime())->format('Y-m-d');

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
                    'url' => '/scope/141/workstationcount/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_Workstation_cluster_scopelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141_workstationlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/' . $date . '/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_scope_141_freeProcessList.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/' . $date . '/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => ''
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Aufruf nächster Kunde', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithCalledProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process_called.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [$this->parameters], []);
        $this->assertRedirect($response, '/workstation/process/82252/called/');
    }

    public function testRenderingWithProcessingProcess()
    {
        $date = (new DateTime())->format('Y-m-d');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_with_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/workstationcount/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_Workstation_cluster_scopelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141_workstationlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/' . $date . '/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_scope_141_freeProcessList.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/' . $date . '/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => ''
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [$this->parameters], []);
        $this->assertStringContainsString('<span class="color-blue"><i class="fas fa-info-circle" aria-hidden="true"></i></span> 
 Kundeninformationen', (string)$response->getBody());
        $this->assertStringContainsString('Personalausweis beantragen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithClusterEnabled()
    {
        $date = (new DateTime())->format('Y-m-d');

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/workstationcount/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/process/' . $date . '/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_scope_141_freeProcessList.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/process/' . $date . '/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => ''
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/cluster/109/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_cluster_109_workstationlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Aufruf nächster Kunde', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
