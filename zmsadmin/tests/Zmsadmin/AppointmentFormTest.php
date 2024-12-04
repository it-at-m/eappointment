<?php

namespace BO\Zmsadmin\Tests;

class AppointmentFormTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "AppointmentForm";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 0,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertStringContainsString('Terminvereinbarung Neu', (string)$response->getBody());
        $this->assertStringContainsString('title="Spontankunde"', (string)$response->getBody());
        $this->assertStringContainsString('Liste leeren', (string)$response->getBody());
    }

    public function testNotSuperUser()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_process_not_matching_id.json")
                ]
            ]
        );
        $this->render([], ['selectedprocess' => 100044]);
    }

    public function testClusterEnabled()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render([], []);
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
    }

    public function testClusterEnabledWithSelectedScope()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_clusterEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 0,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedscope' => 141]);
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
    }

    public function testSelectedProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedprocess' => 100044]);
        $this->assertStringContainsString('Termin aktualisieren', (string)$response->getBody());
    }

    public function testSelectedDate()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 0,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27']);
        $this->assertStringContainsString('Termin erstellen', (string)$response->getBody());
        $this->assertStringContainsString('2016-05-27', (string)$response->getBody());
        $this->assertStringNotContainsString('slotCount', (string)$response->getBody());
    }

    public function testWithSlotsRequired()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141_multipleSlotsEnabled.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 3,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], [
            'selecteddate' => '2016-05-27',
            'selectedscope' => 141,
            'slotsRequired' => 3
        ], []);
        $this->assertStringContainsString('slotCount', (string)$response->getBody());
    }

    /**
    *
    * Test appointment time of selected process in filled free process list on editing process
    *
    */

    public function testSelectedProcessWithDate()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
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
        $response = $this->render([], ['selecteddate' => '2016-05-27', 'selectedprocess' => 100044], []);
        $this->assertStringContainsString('17:00', (string)$response->getBody());
        $this->assertStringNotContainsString('title="Spontankunde"', (string)$response->getBody());
    }

    /**
    *
    * Test appointment time of selected process in empty free process list on editing process
    *
    */

    public function testWithSelectedProcessWithFreeProcessListEmpty()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
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
        $response = $this->render([], ['selecteddate' => '2016-05-27', 'selectedprocess' => 100044], []);
        $this->assertStringContainsString('17:00', (string)$response->getBody());
        $this->assertStringNotContainsString('title="Spontankunde"', (string)$response->getBody());
    }

    /**
    *
    * Test appointment form with empty request list
    *
    */

    public function testWithWithRequestListEmpty()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstationWithProvider()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getDepartment()
                    ],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getRequest()
                    ],
                    'response' => $this->readFixture("GET_scope_requestlist_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => [
                        'slotType' => 'intern',
                        'slotsRequired' => 0,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getFreeProcessList()
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertStringContainsString(
            'Dem ausgewählten Standort sind keine Dienstleistungen zugeordnet',
            (string)$response->getBody()
        );
    }
}
