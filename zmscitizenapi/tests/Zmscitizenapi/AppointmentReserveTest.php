<?php

namespace BO\Zmscitizenapi\Tests;

use Psr\Http\Message\ResponseInterface;

class AppointmentReserveTest extends Base
{
    protected $classname = "\BO\Zmscitizenapi\Controllers\AppointmentReserve";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],
                    'response' => $this->readFixture("POST_SourceGet_dldb.json"),
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'response' => $this->readFixture("GET_appointments_free.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/reserved/', 
                    'response' => $this->readFixture("POST_reserve_timeslot.json")
                ]
            ]
        );

        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [1],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode(), 'Expected a 200 OK response.');
        $this->assertArrayHasKey('reservedProcess', $responseBody, 'Expected reservedProcess in response.');
        $this->assertArrayHasKey('officeId', $responseBody, 'Expected officeId in response.');
        $this->assertEquals('10546', $responseBody['officeId'], 'Expected correct officeId.');
        $this->assertEquals('test@muenchen.de', $responseBody['reservedProcess']['email'], 'Expected test@muenchen.de email.');
    }
}
