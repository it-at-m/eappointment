<?php

namespace BO\Zmsadmin\Tests;

class ProcessSaveTest extends Base
{
    protected $arguments = [
        'id' => 82252,
    ];

    protected $parameters = [
        'slotCount' => 1,
        'familyName' => 'Test BO',
        'telephone' => '1234567890',
        'scope' => 141,
        'requests' => [120703]
    ];

    protected $classname = "ProcessSave";

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
                    'url' => '/process/82252/',
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/82252/12a2/',
                    'parameters' => ['initiator' => null],
                    'response' => $this->readFixture("GET_process_82252_12a2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect($response, '/appointmentForm/?selectedprocess=82252&success=process_updated');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testWithQueuedProcess()
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
                    'url' => '/process/100011/',
                    'response' => $this->readFixture("GET_process_queued.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/100011/8d11/',
                    'parameters' => ['initiator' => null],
                    'response' => $this->readFixture("GET_process_queued.json")
                ]
            ]
        );
        $response = $this->render(['id' => 100011], $this->parameters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/appointmentForm/?selectedprocess=100011&success=process_withoutappointment_updated'
        );
        $this->assertEquals(302, $response->getStatusCode());
    }
}
