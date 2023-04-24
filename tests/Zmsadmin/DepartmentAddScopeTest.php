<?php

namespace BO\Zmsadmin\Tests;

class DepartmentAddScopeTest extends Base
{
    protected $arguments = [
        'id' => 74
    ];

    protected $parameters = [];

    protected $classname = "DepartmentAddScope";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => true],
                    'response' => $this->readFixture("GET_providerlist_assigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => false],
                    'response' => $this->readFixture("GET_providerlist_notassigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Neuen Standort hinzufügen', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => true],
                    'response' => $this->readFixture("GET_providerlist_assigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => false],
                    'response' => $this->readFixture("GET_providerlist_notassigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/department/74/scope/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'provider' => array(
                'source' => 'dldb',
                'id' => '327316',
            ),
            'contact' => array(
                'name' => 'Bürgeramt Heerstr / An-, Ab- und Ummeldung',
                'street' => 'Heerstraße 12',
                'email' => '',
            ),
            'hint' => array(
                'Das ist ein Test',
                'Testhinweis',
            ),
            'shortName' => 'BH-Test',
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
        $this->assertRedirect($response, '/scope/141/?success=scope_created');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testUpdateFailed()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\SchemaValidation';
        $exception->data['emailFrom']['messages'] = [
            'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein'
        ];

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => true],
                    'response' => $this->readFixture("GET_providerlist_assigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/provider/dldb/',
                    'parameters' => ['isAssigned' => false],
                    'response' => $this->readFixture("GET_providerlist_notassigned.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/',
                    'parameters' => ['resolveReferences' => 0],
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/department/74/scope/',
                    'exception' => $exception
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'provider' => [
                'source' => 'dldb',
                'id' => '122217',
            ],
            'contact' => [
                'name' => 'Bürgeramt Heerstraße',
                'street' => 'Heerstr. 12',
                'email' => '',
            ],
            'hint' => [
                'Nr. wird zum Termin aufgerufen ',
                ' Nr. wird zum Termin aufgerufen'
            ],
            'save' => 'save'

        ], [], 'POST');
        $this->assertStringContainsString(
            'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
            (string)$response->getBody()
        );
    }
}
