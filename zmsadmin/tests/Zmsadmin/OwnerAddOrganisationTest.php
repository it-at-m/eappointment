<?php

namespace BO\Zmsadmin\Tests;

class OwnerAddOrganisationTest extends Base
{
    protected $arguments = [
        'id' => 23
    ];

    protected $parameters = [];

    protected $classname = "OwnerAddOrganisation";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Referat: Einrichtung und Administration', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/owner/23/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'name' => 'Charlottenburg-Wilmersdorf',
            'contact' => array(
                'street' => 'Otto-Suhr-Allee 100, 10585 Berlin'
            ),
            'preferences' => array(
                'ticketPrinterProtectionEnabled' => 1
            ),
            'save' => 'save'
        ], [], 'POST');
        $this->assertRedirect($response, '/organisation/71/?success=organisation_created');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
