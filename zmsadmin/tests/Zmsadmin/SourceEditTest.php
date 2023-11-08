<?php

namespace BO\Zmsadmin\Tests;

class SourceEditTest extends Base
{
    protected $arguments = ['name' => 'unittest'];

    protected $parameters = [];

    protected $classname = 'SourceEdit';

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved2.json')
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/source/unittest/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture('GET_source_unittest.json')
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('https:\/\/schema.berlin.de\/queuemanagement\/source.json', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNoSuperuser()
    {
        $this->expectException('\BO\Zmsadmin\Exception\NotAllowed');
        $this->expectExceptionCode(403);
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_workstation_basic.json')
                ]
            ]
        );
        $this->render($this->arguments, $this->parameters, []);
    }

    public function testRenderingAddNew()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved2.json')
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/source/',
                    'response' => $this->readFixture('GET_source_unittest.json')
                ]
            ]
        );
        $response = $this->render(['name' => 'add'], [
            'source' => 'unittest',
            'contact' => [
                'name' => 'BerlinOnline Stadtportal GmbH',
                'email' => 'zms@berlinonline.de'
            ],
            'label' => 'Unittest Source',
            'save' => 'save'
        ], [], 'POST');
        $this->assertRedirect($response, '/source/unittest/?success=source_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testAddNewFailed()
    {
        $this->expectException('\BO\Zmsclient\Exception\ApiFailed');
        $exception = new \BO\Zmsclient\Exception\ApiFailed();
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved2.json')
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/source/',
                    'exception' => $exception
                ]
            ]
        );
        $this->render(['name' => 'add'], [
            'source' => 'unittest',
            'contact' => [
                'name' => 'BerlinOnline Stadtportal GmbH',
                'email' => 'zms@berlinonline.de'
            ],
            'label' => 'Unittest Source',
            'save' => 'save'
        ], [], 'POST');
    }

    public function testAddNewValidationFailed()
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
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture('GET_Workstation_Resolved2.json')
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/source/',
                    'exception' => $exception
                ]
            ]
        );
        $response = $this->render(['name' => 'add'], [
            'source' => 'unittest',
            'contact' => [
                'name' => 'BerlinOnline Stadtportal GmbH',
                'email' => 'zms@berlinonline.de'
            ],
            'label' => 'Unittest Source',
            'save' => 'save'
        ], [], 'POST');
        $this->assertStringContainsString(
            'Die E-Mail Adresse muss eine valide E-Mail im Format max@mustermann.de sein',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
