<?php

namespace BO\Zmsadmin\Tests;

class AppointmentFormMultipleSlotsTest extends Base
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
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_scope_141_multipleSlotsEnabled.json")
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
        $this->assertStringContainsString('Termindauer in Minuten', (string)$response->getBody());
    }
}
