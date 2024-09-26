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
        $responseBody = json_decode((string) $response->getBody(), true);
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

    public function testAppointmentNotAvailable()
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
                ]
            ]
        );

        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616300",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(404, $response->getStatusCode(), 'Expected a 404 Not Found response.');
        $this->assertArrayHasKey('errorCode', $responseBody, 'Expected errorCode in response.');
        $this->assertEquals('appointmentNotAvailable', $responseBody['errorCode'], 'Expected errorCode to be appointmentNotAvailable.');
        $this->assertArrayHasKey('errorMessage', $responseBody, 'Expected errorMessage in response.');
    }

    public function testMissingOfficeId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Missing officeId.', $responseBody['errors'][0]['msg'], 'Expected error message for missing officeId.');
    }

    public function testMissingServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => '10546',
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Missing serviceId.', $responseBody['errors'][0]['msg'], 'Expected error message for missing serviceId.');
    }

    public function testMissingTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Missing timestamp.', $responseBody['errors'][0]['msg'], 'Expected error message for missing timestamp.');
    }

    public function testMissingOfficeIdAndServiceId()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(2, $responseBody['errors'], 'Expected two errors in response.');
        $this->assertEquals('Missing officeId.', $responseBody['errors'][0]['msg'], 'Expected error message for missing officeId.');
        $this->assertEquals('Missing serviceId.', $responseBody['errors'][1]['msg'], 'Expected error message for missing serviceId.');
    }

    public function testMissingOfficeIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(2, $responseBody['errors'], 'Expected two errors in response.');
        $this->assertEquals('Missing officeId.', $responseBody['errors'][0]['msg'], 'Expected error message for missing officeId.');
        $this->assertEquals('Missing timestamp.', $responseBody['errors'][1]['msg'], 'Expected error message for missing timestamp.');
    }

    public function testMissingServiceIdAndTimestamp()
    {
        $this->setApiCalls([]);

        $parameters = [
            'officeId' => '10546',
            'serviceCount' => [0],
            'captchaSolution' => null
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(2, $responseBody['errors'], 'Expected two errors in response.');
        $this->assertEquals('Missing serviceId.', $responseBody['errors'][0]['msg'], 'Expected error message for missing serviceId.');
        $this->assertEquals('Missing timestamp.', $responseBody['errors'][1]['msg'], 'Expected error message for missing timestamp.');
    }

    public function testMissingAllFields()
    {
        $this->setApiCalls([]);

        $parameters = [];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(3, $responseBody['errors'], 'Expected three errors in response.');
        $this->assertEquals('Missing officeId.', $responseBody['errors'][0]['msg'], 'Expected error message for missing officeId.');
        $this->assertEquals('Missing serviceId.', $responseBody['errors'][1]['msg'], 'Expected error message for missing serviceId.');
        $this->assertEquals('Missing timestamp.', $responseBody['errors'][2]['msg'], 'Expected error message for missing timestamp.');
    }

    public function testInvalidOfficeIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => 'invalid_id',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Invalid officeId format. It should be a numeric value.', $responseBody['errors'][0]['msg'], 'Expected error message for invalid officeId format.');
    }
    
    public function testInvalidServiceIdFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['invalid_service_id'],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Invalid serviceId format. It should be an array of numeric values.', $responseBody['errors'][0]['msg'], 'Expected error message for invalid serviceId format.');
    }
    
    public function testInvalidTimestampFormat()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => [0],
            'timestamp' => 'invalid_timestamp',
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Invalid timestamp format. It should be a positive numeric value.', $responseBody['errors'][0]['msg'], 'Expected error message for invalid timestamp format.');
    }
    public function testEmptyServiceIdArray()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => [],
            'serviceCount' => [0],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Missing serviceId.', $responseBody['errors'][0]['msg'], 'Expected error message for empty serviceId array.');
    }
    
    public function testInvalidServiceCount()
    {
        $this->setApiCalls([]);
    
        $parameters = [
            'officeId' => '10546',
            'serviceId' => ['1063423'],
            'serviceCount' => ['invalid'],
            'timestamp' => "32526616522",
            'captchaSolution' => null
        ];
    
        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
    
        $this->assertEquals(400, $response->getStatusCode(), 'Expected a 400 Bad Request response.');
        $this->assertArrayHasKey('errors', $responseBody, 'Expected errors in response.');
        $this->assertCount(1, $responseBody['errors'], 'Expected one error in response.');
        $this->assertEquals('Invalid serviceCount format. It should be an array of non-negative numeric values.', $responseBody['errors'][0]['msg'], 'Expected error message for invalid serviceCount.');
    }

}
