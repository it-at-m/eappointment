<?php

namespace BO\Zmsstatistic\Tests;

class WarehouseReportTest extends Base
{
    protected $classname = "WarehouseReport";

    protected $arguments = [
        'subject' => 'waitingscope',
        'subjectid' => 141,
        'period' => '2016-03'
    ];

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
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_warehouse_waitingscope_141_report.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, [ ]);
        $this->assertStringContainsString('Report Rohdaten', (string) $response->getBody());
        $this->assertStringContainsString('Wartestatistik', (string) $response->getBody());
        $this->assertStringContainsString('ID of a scope', (string) $response->getBody());
        $this->assertStringContainsString('2016-03-01', (string) $response->getBody());
        $this->assertStringContainsString('2016-03-24', (string) $response->getBody());
        $this->assertStringContainsString('hour', (string) $response->getBody());
        $this->assertStringContainsString('waitingcount', (string) $response->getBody());
        $this->assertStringContainsString('waitingtime', (string) $response->getBody());
        $this->assertStringContainsString('waitingcalculated', (string) $response->getBody());
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
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_warehouse_waitingscope_141_report.json")
                ]
            ]
        );

        $response = $this->render($this->arguments, ['type' => 'xlsx'], [ ]);

        $this->assertStringContainsString(
            'raw_statistic_waitingscope_141_2016-03.xlsx',
            $response->getHeaderLine('Content-Disposition')
        );
    }
}
