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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertContains('Terminvereinbarung Neu', (string)$response->getBody());
        $this->assertContains('title="Spontankunde"', (string)$response->getBody());
    }

    public function testNotSuperUser()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed');
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
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render([], []);
        $this->assertContains('Bürgeramt Heerstraße', (string)$response->getBody());
    }

    public function testClusterEnabledWithSelectedScope()
    {
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
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
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
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedscope' => 141]);
        $this->assertContains('Bürgeramt Heerstraße', (string)$response->getBody());
    }

    public function testSelectedProcess()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedprocess' => 100044]);
        $this->assertContains('Terminvereinbarung Aktualisieren', (string)$response->getBody());
    }

    public function testSelectedDate()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27']);
        $this->assertContains('Terminvereinbarung Neu', (string)$response->getBody());
        $this->assertContains('27.05.2016', (string)$response->getBody());
        $this->assertNotContains('slotCount', (string)$response->getBody());
    }

    public function testWithSlotsRequired()
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
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 3],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], [
            'selecteddate' => '2016-05-27',
            'selectedscope' => 141,
            'slotsRequired' => 3
        ], []);
        $this->assertContains('slotCount', (string)$response->getBody());
    }

    public function testWithUpdateSuccess()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedprocess' => 100044, 'success' => 'process_updated']);
        $this->assertContains(
            'Der Termin mit der Nummer 100044 wurde erfolgreich aktualisiert.',
            (string)$response->getBody()
        );
    }

    public function testWithDeleteSuccess()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedprocess' => 100044, 'success' => 'process_deleted']);
        $this->assertContains(
            'Der Termin wurde erfolgreich entfernt.',
            (string)$response->getBody()
        );
    }

    public function testWithQueuedSuccess()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedprocess' => 100044, 'success' => 'process_queued']);
        $this->assertContains('Die Wartenummer für "BO" lautet: 11', (string)$response->getBody());
        $this->assertContains('Wartenummer drucken', (string)$response->getBody());
    }

    public function testWithReservedSuccess()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render([], ['selectedprocess' => 100044, 'success' => 'process_reserved']);
        $this->assertContains('Die Wartenummer für "BO" lautet: 11', (string)$response->getBody());
        $this->assertContains('Wartenummer drucken', (string)$response->getBody());
        $this->assertContains('message-keep', (string)$response->getBody());
    }

    public function testPostFailed()
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
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'familyName' => 'Unittest'
        ], [], 'POST');
        $this->assertContains('data-formData="null"', (string)$response->getBody());
    }

    public function testWithReserveSubmit()
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
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'familyName' => 'Unittest',
            'reserve' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/process/reserve/', 307);
        $this->assertEquals(307, $response->getStatusCode());
    }
    
    public function testWithReserveSubmitRequiredMail()
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
                    'response' => $this->readFixture("GET_scope_141_required_mail.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'familyName' => 'Unittest',
            'reserve' => 1
        ], [], 'POST');
        $this->assertContains(
            'Für den Standort muss eine gültige E-Mail Adresse eingetragen werden',
            (string)$response->getBody()
        );
    }

    public function testWithCopySubmit()
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
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'selectedprocess' => 100044,
            'familyName' => 'Unittest',
            'reserve' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/process/reserve/', 307);
        $this->assertEquals(307, $response->getStatusCode());
    }

    public function testWithQueueSubmit()
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
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'familyName' => 'Unittest',
            'queue' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/process/queue/', 307);
        $this->assertEquals(307, $response->getStatusCode());
    }

    public function testWithDeleteSubmit()
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
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'processId' => 100044,
            'scope' => 141,
            'delete' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/process/100044/delete/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSubmitValidationFailed()
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
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'scope' => 141,
            'requests' => [120703],
            'selecteddate' => '2016-04-01',
            'selectedtime' => '11-55',
            'reserve' => 1
        ], [], 'POST');
        $this->assertContains('has-error', (string)$response->getBody());
        $this->assertContains('Es muss ein aussagekräftiger Name eingegeben werden', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27', 'selectedprocess' => 100044], []);
        $this->assertContains('17:00 (noch 0 frei)', (string)$response->getBody());
        $this->assertNotContains('title="Spontankunde"', (string)$response->getBody());
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/request/',
                    'response' => $this->readFixture("GET_scope_141_requestlist.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27', 'selectedprocess' => 100044], []);
        $this->assertContains('17:00 (noch 0 frei)', (string)$response->getBody());
        $this->assertNotContains('title="Spontankunde"', (string)$response->getBody());
    }
}
