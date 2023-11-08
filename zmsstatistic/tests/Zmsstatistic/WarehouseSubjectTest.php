<?php

namespace BO\Zmsstatistic\Tests;

class WarehouseSubjectTest extends Base
{
    protected $classname = "WarehouseSubject";

    protected $arguments = [ ];

    protected $parameters = [ ];

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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/owner/',
                    'response' => $this->readFixture("GET_owner_23.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/',
                    'response' => $this->readFixture("GET_warehouse_waitingscope.json")
                ]
            ]
        );
        $response = $this->render(['subject' => 'waitingscope'], [ ], [ ]);
        $this->assertStringContainsString('Übersicht verfügbarer IDs', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/warehouse/waitingscope/141/">141</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('02.01.2015', (string) $response->getBody());
        $this->assertStringContainsString('24.03.2016', (string) $response->getBody());
        $this->assertStringContainsString('Bürgeramt Heerstraße Bürgeramt', (string) $response->getBody());
    }

    public function testDownload()
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
                    'url' => '/scope/141/department/',
                    'response' => $this->readFixture("GET_department_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/department/74/organisation/',
                    'response' => $this->readFixture("GET_organisation_71_resolved3.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/organisation/71/owner/',
                    'response' => $this->readFixture("GET_owner_23.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/',
                    'response' => $this->readFixture("GET_warehouse_waitingscope.json")
                ]
            ]
        );

        $response = $this->render(['subject' => 'waitingscope'], ['type' => 'xlsx'], [ ]);

        $this->assertStringContainsString(
            'raw_statistic_waitingscope.xlsx',
            $response->getHeaderLine('Content-Disposition')
        );
    }
}
