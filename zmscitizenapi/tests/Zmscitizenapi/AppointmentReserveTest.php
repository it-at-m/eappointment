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
                    'response' => $this->readFixture("GET_reserve_SourceGet_dldb.json"),
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
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertEquals(200, $response->getStatusCode(), 'Expected a 200 OK response.');
        $this->assertArrayHasKey('processId', $responseBody, 'Expected processId in response.');
        $this->assertArrayHasKey('email', $responseBody, 'Expected email in response.');
        $this->assertEquals('test@muenchen.de', $responseBody['email'], 'Expected test@muenchen.de email.');
        $this->assertArrayHasKey('authKey', $responseBody, 'Expected authKey in response.');
        $this->assertEquals('b93e', $responseBody['authKey'], 'Expected correct authKey.');
        $this->assertArrayHasKey('timestamp', $responseBody, 'Expected timestamp in response.');
        $this->assertEquals('32526616522', $responseBody['timestamp'], 'Expected correct timestamp.');
        $this->assertArrayHasKey('scope', $responseBody, 'Expected scope in response.');
        $this->assertEquals('58', $responseBody['scope']['id'], 'Expected correct scope id.');
        $this->assertEquals('dldb', $responseBody['scope']['provider']['source'], 'Expected correct provider source.');
        $this->assertArrayHasKey('serviceCount', $responseBody, 'Expected serviceCount in response.');
        $this->assertEquals(0, $responseBody['serviceCount'], 'Expected serviceCount to be 0.');
    }
}
