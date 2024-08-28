<?php

namespace BO\Zmscitizenapi\Tests;

use BO\Zmscitizenapi\AppointmentGet;

class AppointmentGetTest extends Base
{
    protected $classname = "AppointmentGet";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101002/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],                    
                    'response' => $this->readFixture("GET_process.json")
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertArrayHasKey('processId', $responseBody);
        $this->assertEquals('101002', $responseBody['processId']);

        $this->assertArrayHasKey('timestamp', $responseBody);
        $this->assertEquals(1724907600, $responseBody['timestamp']);

        $this->assertArrayHasKey('authKey', $responseBody);
        $this->assertEquals('fb43', $responseBody['authKey']);

        $this->assertArrayHasKey('familyName', $responseBody);
        $this->assertEquals('Doe', $responseBody['familyName']);

        $this->assertArrayHasKey('email', $responseBody);
        $this->assertEquals('johndoe@example.com', $responseBody['email']);

        $this->assertArrayHasKey('telephone', $responseBody);
        $this->assertEquals('0123456789', $responseBody['telephone']);

        $this->assertArrayHasKey('officeName', $responseBody);
        $this->assertEquals('Bürgerbüro Orleansplatz DEV (KVR-II/231 DEV)', $responseBody['officeName']);

        $this->assertArrayHasKey('officeId', $responseBody);
        $this->assertEquals('102522', $responseBody['officeId']);

        $this->assertArrayHasKey('scope', $responseBody);
        $this->assertArrayHasKey('id', $responseBody['scope']);
        $this->assertEquals('64', $responseBody['scope']['id']);

        $this->assertArrayHasKey('serviceId', $responseBody);
        $this->assertEquals('1063424', $responseBody['serviceId']);

        $this->assertArrayHasKey('serviceCount', $responseBody);
        $this->assertEquals(1, $responseBody['serviceCount']);

        $this->assertArrayHasKey('subRequestCounts', $responseBody);
        $this->assertEmpty($responseBody['subRequestCounts']);
    }

    public function testMissingProcessId()
    {
        $parameters = [
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertEquals('processId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
    }

    public function testMissingAuthKey()
    {
        $parameters = [
            'processId' => '101002',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertEquals('authKey should be a string', $responseBody['errors'][0]['msg']);
    }

    public function testInvalidProcessId()
    {
        $parameters = [
            'processId' => 'invalid',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertEquals('processId should be a 32-bit integer', $responseBody['errors'][0]['msg']);
    }

    public function testInvalidAuthKey()
    {
        $parameters = [
            'processId' => '101002',
            'authKey' => 12345,
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $this->assertEquals('authKey should be a string', $responseBody['errors'][0]['msg']);
    }

    public function testBothParametersMissing()
    {
        $parameters = [];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $responseBody);
        $errors = $responseBody['errors'];

        $this->assertCount(2, $errors);

        $this->assertContains(
            [
                'type' => 'field',
                'msg' => 'processId should be a 32-bit integer',
                'path' => 'processId',
                'location' => 'query'
            ],
            $errors
        );

        $this->assertContains(
            [
                'type' => 'field',
                'msg' => 'authKey should be a string',
                'path' => 'authKey',
                'location' => 'query'
            ],
            $errors
        );
    }

    public function testAppointmentNotFound()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/101002/fb43/',
                    'parameters' => [
                        'resolveReferences' => 2,
                    ],                    
                    'exception' => new \Exception('API-Error: Zu den angegebenen Daten konnte kein Termin gefunden werden.')
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
        ];

        $response = $this->render([], $parameters, []);
        $responseBody = json_decode((string)$response->getBody(), true);

        $this->assertEquals(404, $response->getStatusCode());

        $this->assertArrayHasKey('errorMessage', $responseBody);
        $this->assertEquals('Termin wurde nicht gefunden', $responseBody['errorMessage']);
    }
}
