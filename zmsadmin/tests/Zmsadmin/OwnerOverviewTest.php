<?php

namespace BO\Zmsadmin\Tests;

class OwnerOverviewTest extends Base
{
    protected $arguments = [
        'id' => 23
    ];

    protected $parameters = [];

    protected $classname = "OwnerOverview";

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
        $this->assertStringContainsString('Behörden und Standorte', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingMissingRights()
    {
        $this->expectException('\BO\Zmsclient\Exception');

        $exception = new \BO\Zmsclient\Exception();
        $exception->template = '\BO\Zmsentities\Exception\UserAccountMissingRights';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/owner/',
                    'parameters' => ['resolveReferences' => 4],
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }
}
