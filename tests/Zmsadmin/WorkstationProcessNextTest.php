<?php

namespace BO\Zmsadmin\Tests;

class WorkstationProcessNextTest extends Base
{
    protected $arguments = [
        'id' => 82252
    ];

    protected $parameters = [];

    protected $classname = "WorkstationProcessNext";

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
                    'url' => '/scope/141/queue/next/',
                    'parameters' => ['exclude' => ''],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/82252/called/?exclude=');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingClusterEnabledWithExcludeIds()
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
                    'url' => '/cluster/109/queue/next/',
                    'parameters' => ['exclude' => '999999'],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['exclude' => '999999'], []);
        $this->assertRedirect($response, '/workstation/process/82252/called/?exclude=999999');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRenderingPreCallRedirect()
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
                    'url' => '/scope/141/queue/next/',
                    'parameters' => ['exclude' => ''],
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ]
            ]
        );
        $response = $this->render(['id' => 194104], $this->parameters, []);
        $this->assertRedirect($response, '/workstation/process/194104/precall/?exclude=');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
