<?php

namespace BO\Zmsstatistic\Tests;

class ReportClientScopeTest extends Base
{
    protected $classname = "ReportClientIndex";

    protected $arguments = [ ];

    protected $parameters = [ ];

    public function testRendering()
    {
        $this->setApiCalls(
            [
              [
                  'function' => 'readGetResult',
                  'url' => '/workstation/',
                  'parameters' => ['resolveReferences' => 3],
                  'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                  'url' => '/warehouse/clientscope/141/',
                  'response' => $this->readFixture("GET_clientscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/scope/'], [ ]);
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/report/client/scope/2016-04/">April</a>',
            (string) $response->getBody()
        );
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
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-04'], [ ], [ ]);
        $this->assertStringContainsString(
            '<td class="report-board--summary" colspan="2">April 2016</td>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
    }

    public function testWithPeriodYear()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016/',
                    'response' => $this->readFixture("GET_clientscope_141_2016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016'], [ ], [ ]);
        $this->assertStringContainsString(
            '<td class="report-board--summary" colspan="2">Kalenderjahr 2016</td>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum Kalenderjahr 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<td class="colWochenTag statistik">April</td>',
            (string) $response->getBody()
        );
    }

    public function testWithDateRangeFabian()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => '2016-04-01',
                'to' => '2016-04-30'
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für die ausgewählten Standorte im Zeitraum 01.04.2016 bis 30.04.2016',
            (string) $response->getBody()
        );
    }

    public function testWithDateRangeAcrossYears()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2015/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141_2015.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => '2015-12-31',
                'to' => '2016-04-01'
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für die ausgewählten Standorte im Zeitraum 31.12.2015 bis 01.04.2016',
            (string) $response->getBody()
        );
    }

    public function testWithMultipleScopes()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141,142/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141,142_2016-04.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/client/scope/2016-04/',
                'scopes' => ['141', '142']
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
    }

    public function testWithMultipleScopesAndDateRange()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141,142/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141,142_2016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => '2016-04-01',
                'to' => '2016-04-30',
                'scopes' => ['141', '142']
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für die ausgewählten Standorte im Zeitraum 01.04.2016 bis 30.04.2016',
            (string) $response->getBody()
        );
    }

    public function testWithInvalidDateRange()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => 'invalid-date',
                'to' => '2016-04-30'
            ],
            []
        );
        // Should render without data since date range is invalid
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
    }

    public function testWithInvalidScopeIds()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/client/scope/2016-04/',
                'scopes' => ['invalid', '0', '-1']
            ],
            []
        );
        // Should fall back to workstation scope since all provided scopes are invalid
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
    }

    public function testWithMixedValidAndInvalidScopeIds()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/client/scope/2016-04/',
                'scopes' => ['141', 'invalid', '0']
            ],
            []
        );
        // Should use only valid scope IDs
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
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
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [
            'period' => '2016-04'
            ],
            [
            '__uri' => '/report/client/scope/2016-04/',
            'type' => 'xlsx'
            ],
            [ ]
        );
        $this->assertStringContainsString('xlsx', $response->getHeaderLine('Content-Disposition'));
        
        // Clean up output buffer (discard any captured output)
        ob_end_clean();
    }

    public function testWithDownloadCSV()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );

        $response = $this->render(
            [
                'period' => '2016-04'
            ],
            [
                '__uri' => '/report/client/scope/2016-04/',
                'type' => 'csv'
            ],
            [ ]
        );

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString(
            '"April";"2016";"84";"16";"84";"16";"0";"0";"61"',
            (string) $response->getBody()
        );
    }

    public function testWithDownloadXLSXAndDateRange()
    {
        // Start output buffering to capture any output from the test
        ob_start();
        
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'type' => 'xlsx',
                'from' => '2016-04-01',
                'to' => '2016-04-30'
            ],
            []
        );
        $this->assertStringContainsString('xlsx', $response->getHeaderLine('Content-Disposition'));
        
        // Clean up output buffer (discard any captured output)
        ob_end_clean();
    }

    public function testWithDownloadCSVAndMultipleScopes()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141,142/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141,142_2016-04.json")
                ]
            ]
        );

        $response = $this->render(
            [
                'period' => '2016-04'
            ],
            [
                '__uri' => '/report/client/scope/2016-04/',
                'type' => 'csv',
                'scopes' => ['141', '142']
            ],
            []
        );

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
    }

    public function testWithEmptyDateRange()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => '',
                'to' => ''
            ],
            []
        );
        // Should render without data since date range is empty
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
    }

    public function testWithPartialDateRange()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => '2016-04-01',
                'to' => ''
            ],
            []
        );
        // Should render without data since date range is incomplete
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
    }

    public function testWithReversedDateRange()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'from' => '2016-04-30',
                'to' => '2016-04-01'
            ],
            []
        );
        // Should still work with reversed dates (filtering will handle it)
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
    }

    public function testWithSpecificURLStructure()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 3],
                    'response' => $this->readFixture("GET_Workstation_Resolved3.json")
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2025/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/client/scope/',
                'scopes' => ['141'],
                'from' => '2025-03-03',
                'to' => '2025-04-04'
            ],
            []
        );
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString(
            'Auswertung für die ausgewählten Standorte im Zeitraum 03.03.2025 bis 04.04.2025',
            (string) $response->getBody()
        );
    }

    /*
    public function testWithoutSelectedScope()
    {
        $this->expectException('\BO\Zmsentities\Exception\WorkstationMissingScope');
        $this->setApiCalls(
            [
              [
                  'function' => 'readGetResult',
                  'url' => '/workstation/',
                  'parameters' => ['resolveReferences' => 2],
                  'response' => $this->readFixture("GET_Workstation_withoutSelectedScope.json")
              ]
            ]
        );
        $this->render([ ], [ ], [ ]);
    }*/
}
