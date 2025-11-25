<?php

namespace BO\Zmsstatistic\Tests;

class ReportRequestScopeTest extends Base
{
    protected $classname = "ReportRequestIndex";

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
                  'url' => '/warehouse/requestscope/141/',
                  'response' => $this->readFixture("GET_requestscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('Dienstleistungsstatistik', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/report/request/scope/2016-04/">April</a>',
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016-04/',
                    'response' => $this->readFixture("GET_requestscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-04'], [], []);
        $this->assertStringContainsString(
            '<th class="statistik">Summe</th>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Reisepass beantragen', (string) $response->getBody());
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016/',
                    'response' => $this->readFixture("GET_requestscope_141_2016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016'], [ ], [ ]);
        $this->assertStringContainsString(
            '<th class="statistik">2016</th>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum Kalenderjahr 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<th class="statistik">&nbsp;Apr</th>',
            (string) $response->getBody()
        );
    }

    public function testWithDateRange()
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016/',
                    'parameters' => [
                        'groupby' => 'day',
                        'fromDate' => '2016-04-01',
                        'toDate' => '2016-04-30'
                    ],
                    'response' => $this->readFixture("GET_requestscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/request/scope/',
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2015/',
                    'parameters' => [
                        'groupby' => 'day',
                        'fromDate' => '2015-12-31',
                        'toDate' => '2016-04-01'
                    ],
                    'response' => $this->readFixture("GET_requestscope_141_2015.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016/',
                    'parameters' => [
                        'groupby' => 'day',
                        'fromDate' => '2015-12-31',
                        'toDate' => '2016-04-01'
                    ],
                    'response' => $this->readFixture("GET_requestscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/request/scope/',
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141,142/2016-04/',
                    'response' => $this->readFixture("GET_requestscope_141,142_2016-04.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/request/scope/2016-04/',
                'scopes' => ['141', '142']
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/request/scope/',
                'from' => 'invalid-date',
                'to' => '2016-04-30'
            ],
            []
        );
        $this->assertStringContainsString('Dienstleistungsstatistik', (string) $response->getBody());
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016-04/',
                    'response' => $this->readFixture("GET_requestscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/request/scope/2016-04/',
                'scopes' => ['invalid', '0', '-1']
            ],
            []
        );
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016-04/',
                    'response' => $this->readFixture("GET_requestscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-04'],
            [
                '__uri' => '/report/request/scope/2016-04/',
                'scopes' => ['141', 'invalid', '0']
            ],
            []
        );
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
                    'url' => '/warehouse/requestscope/141/',
                    'response' => $this->readFixture("GET_requestscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestscope/141/2016-04/',
                    'response' => $this->readFixture("GET_requestscope_141_042016.json")
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
        
        // Clean up output buffer (discard any captured output)
        ob_end_clean();
    }
}
