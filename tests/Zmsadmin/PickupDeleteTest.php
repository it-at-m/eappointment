<?php

namespace BO\Zmsadmin\Tests;

class PickupDeleteTest extends Base
{
    protected $arguments = [
        'ids' => '82252,194104'
    ];

    protected $parameters = [];

    protected $classname = "PickupDelete";

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
                    'function' => 'readGetResult',
                    'url' => '/process/194104/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/workstation/process/',
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/finished/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertContains(
            'Alle Termine wurden erfolgreich archiviert und aus der Liste entfernt.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
