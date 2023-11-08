<?php

namespace BO\Zmsadmin\Tests;

class ProcessQueueResetTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selectedprocess' => 82252,
        'selecteddate' => '2016-04-01'
    ];

    protected $classname = "ProcessQueueReset";

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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/queued/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertRedirect(
            $response,
            '/queueTable/?selecteddate=2016-04-01&selectedprocess=82252&success=process_reset_queued'
        );
        $this->assertEquals(302, $response->getStatusCode());
    }
}
