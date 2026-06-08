<?php

namespace BO\Zmsstatistic\Tests;

class ReportCapacityScopeTest extends Base
{
    protected $classname = "ReportCapacityIndex";

    protected $arguments = [];

    protected $parameters = [];

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
                    'url' => '/scope/',
                    'response' => $this->readFixture("GET_scope_list.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/',
                    'response' => $this->readFixture("GET_slotscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/_/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/',
                    'response' => $this->readFixture("GET_warehouse_slotscope.json")
                ],
            ]
        );
        $response = $this->render([], ['__uri' => '/report/capacity/scope/'], []);
        $this->assertStringContainsString('Terminkapazität Standort', (string) $response->getBody());
        $this->assertStringContainsString('data-scope-date-bounds', (string) $response->getBody());
        $this->assertStringContainsString('data-picker-scope-ids', (string) $response->getBody());
        $this->assertStringContainsString('2016-03-15', (string) $response->getBody());
        $this->assertStringContainsString('2016-04-02', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/report/capacity/scope/2016-04/">April</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<label for="scope-select">Standortauswahl</label>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
        $this->assertStringNotContainsString('report-board--chart-minutes', (string) $response->getBody());
    }

    public function testWithPeriod()
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
                    'url' => '/scope/',
                    'response' => $this->readFixture("GET_scope_list.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/',
                    'response' => $this->readFixture("GET_slotscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/_/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/',
                    'response' => $this->readFixture("GET_warehouse_slotscope.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/2016-04/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
            ]
        );
        $response = $this->render(['period' => '2016-04'], [], []);
        $this->assertStringContainsString('01.04.2016', (string) $response->getBody());
        $this->assertStringContainsString('02.04.2016', (string) $response->getBody());
        $this->assertStringNotContainsString('15.03.2016', (string) $response->getBody());
        $this->assertStringContainsString('Kapazität laut Öffnungszeiten – Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016', (string) $response->getBody());
        $this->assertStringContainsString('Zeitschlitzdauer laut Öffnungszeit: 12 Min.', (string) $response->getBody());
        $this->assertStringContainsString('Summe ·', (string) $response->getBody());
        $this->assertStringContainsString('report-board--capacity-summary', (string) $response->getBody());
        $this->assertStringContainsString('50 %', (string) $response->getBody());
        $this->assertStringContainsString('data-chartist-sparse', (string) $response->getBody());
        $this->assertStringContainsString('data-chartist-full', (string) $response->getBody());
        $this->assertStringContainsString('report-board--chart-minutes', (string) $response->getBody());
        $this->assertStringContainsString('report-board--chart-download', (string) $response->getBody());
        $this->assertStringContainsString('report-board--auto-refresh-interval', (string) $response->getBody());
        $this->assertStringContainsString('report-board--chart-sparse', (string) $response->getBody());
        $this->assertStringContainsString('allowSparseTimeline', (string) $response->getBody());
        $this->assertStringContainsString('report-board--table-download', (string) $response->getBody());
        $this->assertStringContainsString('type=xlsx', (string) $response->getBody());
        $this->assertStringContainsString('ylabelMinutes', (string) $response->getBody());
    }

    public function testWithMultipleScopes()
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
                    'url' => '/scope/',
                    'response' => $this->readFixture("GET_scope_list.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/',
                    'response' => $this->readFixture("GET_slotscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/_/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/',
                    'response' => $this->readFixture("GET_warehouse_slotscope.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141,142/2016-04/',
                    'response' => $this->readFixture("GET_slotscope_141_142_report.json")
                ],
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/capacity/scope/2016-04/',
                'scopes' => ['141', '142'],
            ],
            []
        );
        $body = (string) $response->getBody();
        $this->assertStringContainsString('01.04.2016', $body);
        $this->assertStringContainsString('02.04.2016', $body);
        $this->assertStringContainsString('>15<', $body);
        $this->assertStringContainsString('>30<', $body);
        $this->assertStringContainsString('>20<', $body);
        $this->assertStringContainsString('>35<', $body);
        $this->assertStringContainsString('data-table-sparse', $body);
        $this->assertStringContainsString(',150,300', $body);
        $this->assertStringContainsString(',200,350', $body);
        $this->assertStringContainsString(
            'Zeitschlitzdauer laut Öffnungszeit: 12 Min. (alle ausgewählten Standorte)',
            $body
        );
        $this->assertStringContainsString('50 %', $body);
        $this->assertStringContainsString('57.1 %', $body);
    }

    public function testWithDownloadXlsx()
    {
        ob_start();

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
                    'url' => '/scope/',
                    'response' => $this->readFixture("GET_scope_list.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/',
                    'response' => $this->readFixture("GET_slotscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/_/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/',
                    'response' => $this->readFixture("GET_warehouse_slotscope.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/2016-04/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/capacity/scope/2016-04/',
                'type' => 'xlsx',
            ],
            []
        );
        $this->assertStringContainsString('xlsx', $response->getHeaderLine('Content-Disposition'));

        if (method_exists($response->getBody(), 'rewind')) {
            $response->getBody()->rewind();
        }

        $tmp = tempnam(sys_get_temp_dir(), 'capacity_xlsx_');
        file_put_contents($tmp, (string) $response->getBody());
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
        $sheet = $spreadsheet->getActiveSheet();
        $this->assertSame(
            [
                'Standort-ID',
                'Datum',
                'Gebuchte Kapazität (Zeitschlitze)',
                'Geplante Kapazität (Zeitschlitze)',
                'Gebuchte Kapazität (Minuten)',
                'Geplante Kapazität (Minuten)',
            ],
            [
                $sheet->getCell('A1')->getValue(),
                $sheet->getCell('B1')->getValue(),
                $sheet->getCell('C1')->getValue(),
                $sheet->getCell('D1')->getValue(),
                $sheet->getCell('E1')->getValue(),
                $sheet->getCell('F1')->getValue(),
            ]
        );
        @unlink($tmp);
        ob_end_clean();
    }

    public function testHourlyDateRange()
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
                    'url' => '/scope/',
                    'response' => $this->readFixture("GET_scope_list.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/',
                    'response' => $this->readFixture("GET_slotscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/_/',
                    'response' => $this->readFixture("GET_slotscope_141_report.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/',
                    'response' => $this->readFixture("GET_warehouse_slotscope.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/capacityscope/141/2016-04-01/',
                    'parameters' => [
                        'fromDate' => '2016-04-01',
                        'toDate' => '2016-04-01',
                        'groupby' => 'hour',
                    ],
                    'response' => $this->readFixture("GET_slotscope_141_hourly_report.json")
                ],
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/capacity/scope/',
                'from' => '2016-04-01',
                'to' => '2016-04-01',
            ],
            []
        );
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Zeitpunkt', $body);
        $this->assertStringContainsString('2016-04-01 08:00', $body);
        $this->assertStringContainsString('2016-04-01 09:00', $body);
        $this->assertStringContainsString('data-chartist-sparse', $body);
        $this->assertStringContainsString('data-chartist-full', $body);
        $this->assertStringContainsString('50 %', $body);
    }
}
