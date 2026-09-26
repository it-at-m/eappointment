<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessRedirectTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "WorkstationProcessRedirect";

    #[\Override]
    public function testRendering()
    {
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
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Termin Weiterleiten', $body);
        $this->assertStringContainsString('Termin buchen', $body);
        $this->assertStringContainsString('Abbrechen der Weiterleitung', $body);
        $this->assertStringContainsString('data-page="redirect"', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostWithoutAssignedProcessReturnsToWorkstation()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_workstation_without_process.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['location' => 141], [], 'POST');
        $this->assertRedirect($response, '/workstation/');
    }
}
