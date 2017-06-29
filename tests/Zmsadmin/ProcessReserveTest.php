<?php

namespace BO\Zmsadmin\Tests;

class ProcessReserveTest extends Base
{
    protected $arguments = [
        'date' => '2016-04-01',
        'time' => '11-55'
    ];

    protected $parameters = [
        'slotCount' => 1,
        'familyName' => 'Test BO',
        'telephone' => '1234567890',
        'email' => 'zmsbo@berlinonline.de',
        'requests' => [120703]
    ];

    protected $classname = "ProcessReserve";

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
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/',
                    'parameters' => ['slotType' => 'intern'],
                    'response' => $this->readFixture("GET_process_100005_95a3_reserved.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_100005_95a3_reserved.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertContains('Reservierung erfolgreich', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRenderingValidationFailed()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'slotCount' => 1,
            'telephone' => '1234567890',
            'email' => 'zmsbo@berlinonline.de',
            'requests' => [120703]
        ], [], 'POST');
        $this->assertContains('"failed":true', (string)$response->getBody());
        $this->assertEquals(428, $response->getStatusCode());
    }
}
