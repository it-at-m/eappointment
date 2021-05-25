<?php

namespace BO\Zmsadmin\Tests;

class WorkstationSelectTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationSelect";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Standort und Arbeitsplatz auswählen', (string)$response->getBody());
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSelect()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'scope' => 141,
            'workstation' => 12,
            'hint' => 'Test Aufrufzusatz',
            'workstation_select_form_validate' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSelectWithCustomRedirect()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'scope' => 141,
            'workstation' => 12,
            'hint' => 'Test Aufrufzusatz',
            'workstation_select_form_validate' => 1,
            'redirect' => 'useraccount'
        ], [], 'POST');
        $this->assertRedirect($response, '/useraccount/');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingSelectFailedValidation()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'scope' => '',
            'workstation' => 12,
            'hint' => 'Test Aufrufzusatz',
            'workstation_select_form_validate' => 1
        ], [], 'POST');
        $this->assertStringContainsString('has-error', (string)$response->getBody());
        $this->assertStringContainsString('Bitte wählen Sie einen Standort aus', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingSelectAppointmentsOnlyWithCluster()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation_with_process_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'appointmentsOnly' => 1,
            'scope' => 'cluster'
        ], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
