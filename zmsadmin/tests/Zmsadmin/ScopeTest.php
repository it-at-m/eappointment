<?php

namespace BO\Zmsadmin\Tests;

class ScopeTest extends Base
{
    protected $arguments = [
        'id' => 141
    ];

    protected $parameters = [];

    protected $classname = "Scope";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Bürgeramt Heerstraße', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSaveWithUploadImage()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/',
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
            'provider' => [
                'source' => 'dldb',
                'id' => '122217',
            ],
            'id' => 141,
            'contact' => [
                'name' => 'Bürgeramt Heerstraße',
                'street' => 'Heerstr. 12',
                'email' => '',
            ],
            'hint' => [
                'Nr. wird zum Termin aufgerufen ',
                ' Nr. wird zum Termin aufgerufen'
            ],
            'save' => 'save',
            '__file' => [
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'image/png',
                    13535
                )
            ]
        ], [], 'POST');
        $this->assertRedirect($response, '/scope/141/?success=scope_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSaveWithUploadImageFailed()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Wrong Mediatype given, use gif, jpg, svg or png');
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $this->render($this->arguments, [
            'provider' => [
                'source' => 'dldb',
                'id' => '122217',
            ],
            'contact' => [
                'name' => 'Bürgeramt Heerstraße',
                'street' => 'Heerstr. 12',
                'email' => '',
            ],
            'id' => 141,
            'hint' => [
                'Nr. wird zum Termin aufgerufen ',
                ' Nr. wird zum Termin aufgerufen'
            ],
            'save' => 'save',
            '__file' => array(
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'application/json',
                    13535
                )
            )
        ], [], 'POST');
    }

    public function testSaveWithDeleteImage()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readDeleteResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
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
            'id' => 141,
            'hint' => [
                'Nr. wird zum Termin aufgerufen ',
                ' Nr. wird zum Termin aufgerufen'
            ],
            'save' => 'save',
            'removeImage' => 1
        ], [], 'POST');
        $this->assertRedirect($response, '/scope/141/?success=scope_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSaveSuccessMessage()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, ['success' => 'scope_saved'], []);
        $this->assertStringContainsString(
            'Der Standort wurde am 01.04.2016 um 11:55 Uhr erfolgreich aktualisiert.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
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
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/',
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
            'id' => 141,
            'hint' => [
                'Nr. wird zum Termin aufgerufen ',
                ' Nr. wird zum Termin aufgerufen'
            ],
            'save' => 'save',
            '__file' => [
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'image/png',
                    13535
                )
            ]

        ], [], 'POST');
        $this->assertStringContainsString(
            'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
            (string)$response->getBody()
        );
    }

    public function testUnknownException()
    {
        $this->expectException('BO\Zmsclient\Exception');
        $exception = new \BO\Zmsclient\Exception("TestUnknownException");
        $exception->template = '';

        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/',
                    'response' => $this->readFixture("GET_sourcelist.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/dldb/',
                    'response' => $this->readFixture("GET_source.json")
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
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'accessRights' => 'scope',
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/imagedata/calldisplay/',
                    'response' => $this->readFixture("GET_imagedata_empty.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/scope/141/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render($this->arguments, [
            'id' => 141,
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
            'save' => 'save',
            '__file' => [
                'uploadCallDisplayImage' => new \Slim\Psr7\UploadedFile(
                    dirname(__FILE__) . '/fixtures/baer.png',
                    'baer.png',
                    'image/png',
                    13535
                )
            ]

        ], [], 'POST');
    }
}
