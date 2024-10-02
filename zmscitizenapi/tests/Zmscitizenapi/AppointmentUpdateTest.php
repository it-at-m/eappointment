<?php

namespace BO\Zmscitizenapi\Tests;

class AppointmentUpdateTest extends Base
{

    protected $classname = "AppointmentUpdate";

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
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/101002/fb43/',
                    'response' => $this->readFixture("POST_update_appointment.json")
                ]
            ]
        );

        $parameters = [
            'processId' => '101002',
            'authKey' => 'fb43',
            'familyName' => 'Smith',
            'email' => "test@muenchen.de",
            'telephone' => '123456789',
            'customTextfield'=> "Some custom text",
        ];

        $response = $this->render([], $parameters, [], 'POST');
        $responseBody = json_decode((string) $response->getBody(), true);
        $expectedResponse = [
            "processId" => "101002",
            "timestamp" => 1727865900,
            "authKey" => "fb43",
            "familyName" => "Smith",
            "customTextfield" => "Some custom text",
            "email" => "test@muenchen.de",
            "telephone" => "123456789",
            "officeName" => null,
            "officeId" => null,
            "scope" => [
                '$schema' => "https://schema.berlin.de/queuemanagement/scope.json",
                "id" => 0,
                "source" => "dldb"
            ],
            "subRequestCounts" => [],
            "serviceId" => "10242339",
            "serviceCount" => 1
        ];
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody, true);

    }
}
