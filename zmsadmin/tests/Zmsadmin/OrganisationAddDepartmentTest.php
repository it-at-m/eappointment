<?php

namespace BO\Zmsadmin\Tests;

class OrganisationAddDepartmentTest extends Base
{
    protected $arguments = [
        'id' => 71
    ];

    protected $parameters = [];

    protected $classname = "OrganisationAddDepartment";

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
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Neue Behörde hinzufügen', (string)$response->getBody());
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
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/organisation/71/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'name' => 'Testbehörde',
            'contact' => array(
                'street' => 'Musterstraße 12',
                'name' => 'Ansprechpartnername',
            ),
            'hint' => array(
                'Das ist ein Test',
                'Testhinweis',
            ),
            'email' => 'unittest@berlinonline.de',
            'links' => array(
                [
                    'name' => 'Testlink',
                    'url' => 'unittest.de'
                ]
            ),
            'dayoff' => array(
                array(
                  'name' => '',
                  'date' => '01.04.2016',
                ),
            ),
            'save' => 'save',
            '__file' => array(
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'image/png',
                    13535
                )
            )
        ], [], 'POST');
        $this->assertRedirect($response, '/department/74/?success=department_created');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
