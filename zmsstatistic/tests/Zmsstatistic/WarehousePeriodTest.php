<?php

namespace BO\Zmsstatistic\Tests;

class WarehousePeriodTest extends Base
{
    protected $classname = "WarehousePeriod";

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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_warehouse_waitingscope_141.json")
                ]
            ]
        );
        $response = $this->render(
            [
            'subject' => 'waitingscope',
            'subjectid' => 141
            ],
            [ ],
            [ ]
        );

        $this->assertStringContainsString('Übersicht verfügbarer Zeit-Perioden', (string) $response->getBody());
        $this->assertStringContainsString('<a href="/warehouse/waitingscope/141/2016/">', (string) $response->getBody());
        $this->assertStringContainsString('<li>Wartestatistik</li>', (string) $response->getBody());
        $this->assertStringContainsString('<a href="/warehouse/waitingscope/141/2016/?type=xlsx"', (string) $response->getBody());
    }
}
