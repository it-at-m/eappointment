<?php

namespace BO\Zmsstatistic\Tests;

class WarehouseIndexTest extends Base
{
    protected $classname = "WarehouseIndex";

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
                    'url' => '/warehouse/',
                    'response' => $this->readFixture("GET_warehouse.json")
                ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('Übersicht verfügbarer Kategorien', (string) $response->getBody());
        $this->assertContains('Wartestatistik Standort', (string) $response->getBody());
        $this->assertContains('Kundenstatistik Behörde', (string) $response->getBody());
        $this->assertContains('SMS-Statistik Organisation', (string) $response->getBody());
        $this->assertContains('Dienstleistungsstatistik Organisation', (string) $response->getBody());
    }
}
