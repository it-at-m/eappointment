<?php

namespace BO\Zmscitizenapi\Tests;

class ServicesListTest extends Base
{

    protected $classname = "\BO\Zmscitizenapi\Controllers\ServicesList";

    public function testRendering() {
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json"),
            ]
        ]);
        $responseData = $this->renderJson();
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
            ]
        ];
        $this->assertEqualsCanonicalizing($expectedResponse, $responseData);
    }
}
