<?php

namespace BO\Zmsadmin\Tests;

class OwnerOverviewTest extends Base
{
    protected $arguments = [
        'id' => 23
    ];

    protected $parameters = [];

    protected $classname = "OwnerOverview";

    #[\Override]
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
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 4],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $body = (string)$response->getBody();
        $this->assertStringContainsString('Behörden und Standorte', $body);
        $this->assertStringContainsString('(Id: 23) Berlin', $body);
        $this->assertStringContainsString('(Id: 54) Pankow', $body);
        $this->assertStringContainsString('(Id: 55) Bürgeramt', $body);
        $this->assertStringContainsString('(Id: 130) Cluster - BüA Prenzlauer Berg', $body);
        $this->assertStringContainsString('(Id: 102)', $body);
        $this->assertStringContainsString('(Id: 381)', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingHidesIdsWithoutSuperuser()
    {
        $workstation = json_decode($this->readFixture("GET_workstation_appointment_admin.json"), true);
        $workstation['data']['useraccount']['permissions']['superuser'] = false;

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => json_encode($workstation)
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 4],
                    'response' => $this->readFixture("GET_ownerlist.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $body = (string)$response->getBody();
        $this->assertStringContainsString('Behörden und Standorte', $body);
        $this->assertStringNotContainsString('(Id:', $body);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingWithoutRequiredPermissions()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/workstation/',
                'parameters' => ['resolveReferences' => 1],
                'response' => $this->readFixture("GET_workstation_basic.json")
            ]
        ]);

        $this->render($this->arguments, $this->parameters, []);
    }

    public function testRenderingOwnerMissingPermissionsFromApi()
    {
        $this->expectException('\BO\Zmsclient\Exception');

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '\BO\Zmsentities\Exception\UserAccountMissingRights';

        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/workstation/',
                'parameters' => ['resolveReferences' => 1],
                'response' => $this->readFixture("GET_Workstation_Resolved2.json")
            ],
            [
                'function' => 'readGetResult',
                'url' => '/owner/',
                'parameters' => ['resolveReferences' => 4],
                'exception' => $exception
            ]
        ]);

        $this->render($this->arguments, $this->parameters, []);
    }
}
