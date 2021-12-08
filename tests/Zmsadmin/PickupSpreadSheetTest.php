<?php

namespace BO\Zmsadmin\Tests;

class PickupSpreadSheetTest extends Base
{
    protected $arguments = [];

    protected $parameters = [
        'selectedprocess' => 82252
    ];

    protected $classname = "PickupSpreadSheet";

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
                    'url' => '/workstation/process/pickup/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'selectedScope' => 141,
                        'limit' => 10000
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_20160527.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            'download; filename="abholer_Buergeramt_Heerstrasse.xlsx',
            $response->getHeader('Content-Disposition')[0]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmpty()
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
                    'url' => '/workstation/process/pickup/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'selectedScope' => 141,
                        'limit' => 10000
                    ],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getScope()
                    ],
                    'response' => $this->readFixture("GET_scope_141.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            'download; filename="abholer_Buergeramt_Heerstrasse.xlsx',
            $response->getHeader('Content-Disposition')[0]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
