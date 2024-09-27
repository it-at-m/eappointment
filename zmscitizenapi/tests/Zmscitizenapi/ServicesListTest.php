<?php

namespace BO\Zmscitizenapi\Tests;

class ServicesListTest extends Base
{

    protected $classname = "ServicesList";

    public function testRendering() {
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json")
            ]
        ]);

        $response = $this->render();
        $responseBody = json_decode((string)$response->getBody(), true);
        $expectedResponse = [
            "services" => [
                [
                    "id" => "1",
                    "name" => "Unittest Source Dienstleistung",
                    "maxQuantity" => 1
                ],
                [
                    "id" => "2",
                    "name" => "Unittest Source Dienstleistung 2",
                    "maxQuantity" => 1
                ]
            ],
            "status" => 200,
        ];

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEqualsCanonicalizing($expectedResponse, $responseBody);
    }
}
