<?php

namespace BO\Zmscitizenapi\Tests;

class ScopesListTest extends Base
{
    public function testRendering() {
        // Mock the API call to return a predefined fixture
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/source/unittest/',
                'parameters' => [
                    'resolveReferences' => 2,
                ],
                'response' => $this->readFixture("GET_SourceGet_dldb.json"), // Use the same fixture as before
            ]
        ]);

        // Render the response JSON
        $responseData = $this->renderJson();

        // Define the expected output based on the fixture
        $expectedResponse = [
            "scopes" => [
                [
                    "id" => "1",
                    "provider" => [
                        '$schema' => "https://schema.berlin.de/queuemanagement/provider.json",
                        "id" => "9999998",
                        "source" => "unittest"
                    ],
                    "shortName" => "Scope 1",
                    "telephoneActivated" => "1",
                    "telephoneRequired" => "0",
                    "customTextfieldActivated" => "1",
                    "customTextfieldRequired" => "0",
                    "customTextfieldLabel" => "Custom Label",
                    "captchaActivatedRequired" => "1"
                ],
                [
                    "id" => "2",
                    "provider" => [
                        '$schema' => "https://schema.berlin.de/queuemanagement/provider.json",
                        "id" => "9999999",
                        "source" => "unittest"
                    ],
                    "shortName" => "Scope 2",
                    "telephoneActivated" => "0",
                    "telephoneRequired" => "1",
                    "customTextfieldActivated" => "0",
                    "customTextfieldRequired" => "1",
                    "customTextfieldLabel" => "",
                    "captchaActivatedRequired" => "0"
                ]
            ]
        ];

        // Assert that the actual response matches the expected response
        $this->assertEqualsCanonicalizing($expectedResponse, $responseData);
    }
}
