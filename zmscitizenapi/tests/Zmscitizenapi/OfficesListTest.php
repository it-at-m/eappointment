<?php

namespace BO\Zmscitizenapi\Tests;

class OfficesListTest extends Base
{
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
        $this->assertEqualsCanonicalizing([
            "offices" => [
                [
                    "id" => "9999998",
                    "name" => "Unittest Source Dienstleister",
                ],
                [
                    "id" => "9999999",
                    "name" => "Unittest Source Dienstleister 2",
                ]
            ]
        ], $responseData);
    }
}
