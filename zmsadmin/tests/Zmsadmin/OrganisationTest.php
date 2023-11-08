<?php

namespace BO\Zmsadmin\Tests;

class OrganisationTest extends Base
{
    protected $arguments = [
        'id' => 71
    ];

    protected $parameters = [];

    protected $classname = "Organisation";

    public function testRendering()
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
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Bezirk: Einrichtung', (string)$response->getBody());
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/organisation/71/',
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
        $this->assertRedirect($response, '/organisation/71/?success=organisation_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
