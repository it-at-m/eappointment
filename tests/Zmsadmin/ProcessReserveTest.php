<?php

namespace BO\Zmsadmin\Tests;

class ProcessReserveTest extends Base
{
    protected $parameters = [
        'slotCount' => 1,
        'familyName' => 'Test BO',
        'telephone' => '1234567890',
        'email' => 'zmsbo@berlinonline.de',
        'scope' => 141,
        'requests' => [120703],
        'selecteddate' => '2016-04-01',
        'selectedtime' => '11-55'
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
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_100005_95a3_confirmed.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/appointmentForm/?selectedprocess=100005&selectedscope=141&success=process_reserved'
        );
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testWithConfirmations()
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
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
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
                    'function' => 'readPostResult',
                    'url' => '/process/status/confirmed/',
                    'response' => $this->readFixture("GET_process_194104_2b88_notification.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/194104/2b88/confirmation/mail/',
                    'response' => $this->readFixture("POST_mail.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/194104/2b88/confirmation/notification/',
                    'response' => $this->readFixture("POST_notification.json")
                ]
            ]
        );
        $paremeters = array_merge($this->parameters, array('sendConfirmation' => 1, 'sendMailConfirmation' => 1));
        $response = $this->render($this->arguments, $paremeters, [], 'POST');
        $this->assertRedirect(
            $response,
            '/appointmentForm/?selectedprocess=194104&selectedscope=141&success=process_reserved'
        );
        $this->assertEquals(302, $response->getStatusCode());
    }
}
