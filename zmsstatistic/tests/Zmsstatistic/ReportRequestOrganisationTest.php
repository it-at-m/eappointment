<?php

namespace BO\Zmsstatistic\Tests;

class ReportRequestOrganisationTest extends Base
{
    protected $classname = "ReportRequestOrganisation";

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
                  'url' => '/warehouse/requestorganisation/71/',
                  'response' => $this->readFixture("GET_requestorganisation_71.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('Dienstleistungsstatistik Bezirk', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/report/request/organisation/2016-04/">April</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertStringContainsString(
            '<label for="scope-select">Standortauswahl</label>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<optgroup label="Charlottenburg-Wilmersdorf -&gt; Bürgeramt">',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<label>Datumsauswahl</label>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
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
                    'url' => '/warehouse/requestorganisation/71/',
                    'response' => $this->readFixture("GET_requestorganisation_71.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestorganisation/71/2016-04/',
                    'response' => $this->readFixture("GET_requestorganisation_71_042016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-04'], [], []);
        $this->assertStringContainsString(
            '<th class="statistik">Summe</th>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Charlottenburg-Wilmersdorf im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Reisepass beantragen', (string) $response->getBody());
        $this->assertStringContainsString('Dienstleistung wurde nicht erfasst', (string) $response->getBody());
        $this->assertStringContainsString(
            'Dienstleistung konnte nicht erbracht werden',
            (string) $response->getBody()
        );
        $this->assertMatchesRegularExpression(
            '/Ø Bearbeitungsdauer \(unabhängig von DL\) \/ Summe[\s\S]*?>\s*98\s*</',
            (string) $response->getBody()
        );
    }

    public function testWithDownloadXLSX()
    {
        // Start output buffering to capture any output from the test
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
                    'url' => '/warehouse/requestorganisation/71/',
                    'response' => $this->readFixture("GET_requestorganisation_71.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestorganisation/71/2016-04/',
                    'response' => $this->readFixture("GET_requestorganisation_71_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [
                'period' => '2016-04'
            ],
            [
                'type' => 'xlsx'
            ],
            [ ]
        );
        $this->assertStringContainsString('xlsx', $response->getHeaderLine('Content-Disposition'));
        $tempfile = tempnam(sys_get_temp_dir(), 'request-report-organisation-');
        file_put_contents($tempfile, (string) $response->getBody());
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempfile);
            $sheet = $spreadsheet->getActiveSheet();
            $foundSumRow = false;
            foreach ($sheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
                if ($sheet->getCell('A' . $rowIndex)->getValue() === 'Ø Bearbeitungsdauer (unabhängig von DL) / Summe') {
                    $foundSumRow = true;
                    $this->assertSame(98, (int) $sheet->getCell('C' . $rowIndex)->getValue());
                    $this->assertSame(98, (int) $sheet->getCell('D' . $rowIndex)->getValue());
                    break;
                }
            }
            $this->assertTrue($foundSumRow, 'The XLSX export must contain a Summe row');
        } finally {
            unlink($tempfile);
            ob_end_clean();
        }
    }

    public function testWithoutAccess()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountAccessRightsFailed');
        $this->setApiCalls(
            [
              [
                  'function' => 'readGetResult',
                  'url' => '/workstation/',
                  'parameters' => ['resolveReferences' => 2],
                  'response' => $this->readFixture("GET_Workstation_BasicRights.json")
              ]
            ]
        );
        $this->render([ ], ['__uri' => '/report/request/organisation/'], [ ]);
    }
}
