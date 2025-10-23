<?php

namespace BO\Zmsstatistic\Tests;

class ReportWaitingScopeTest extends Base
{
    protected $classname = "ReportWaitingIndex";

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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/scope/'], [ ]);
        $this->assertStringContainsString('Wartestatistik', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a href="/report/waiting/scope/2016-03/">März</a>',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-03'], [], []);
        $this->assertStringContainsString('<th class="statistik">Max.</th>', (string) $response->getBody());
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum März 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('532', (string) $response->getBody());
        $this->assertStringContainsString('294', (string) $response->getBody());
    }

    public function testYearChange()
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016-01/',
                    'response' => $this->readFixture("GET_waitingscope_141_012016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-01'], [], []);
        $this->assertStringContainsString('<th class="statistik">Max.</th>', (string) $response->getBody());
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum Januar 2016',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016/',
                    'response' => $this->readFixture("GET_waitingscope_141_2016.json")
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/waiting/scope/',
                'from' => '2016-03-01',
                'to' => '2016-03-31'
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für die ausgewählten Standorte im Zeitraum 01.03.2016 bis 31.03.2016',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2015/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_waitingscope_141_2015.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016/',
                    'parameters' => ['groupby' => 'day'],
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/waiting/scope/',
                'from' => '2015-12-31',
                'to' => '2016-03-01'
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für die ausgewählten Standorte im Zeitraum 31.12.2015 bis 01.03.2016',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141,142/2016-03/',
                    'response' => $this->readFixture("GET_waitingscope_141,142_032016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-03'],
            [
                '__uri' => '/report/waiting/scope/2016-03/',
                'scopes' => ['141', '142']
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum März 2016',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ]
            ]
        );
        $response = $this->render(
            [],
            [
                '__uri' => '/report/waiting/scope/',
                'from' => 'invalid-date',
                'to' => '2016-03-30'
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-03'],
            [
                '__uri' => '/report/waiting/scope/2016-03/',
                'scopes' => ['invalid', '0', '-1']
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum März 2016',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );
        $response = $this->render(
            ['period' => '2016-03'],
            [
                '__uri' => '/report/waiting/scope/2016-03/',
                'scopes' => ['141', 'invalid', '0']
            ],
            []
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum März 2016',
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-03'], ['type' => 'xlsx'], []);
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016-03/',
                    'response' => $this->readFixture("GET_waitingscope_141_032016.json")
                ]
            ]
        );

        $response = $this->render(['period' => '2016-03'], ['type' => 'csv'], []);

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString(
            'Stunden-Max (Spaltenmaximum) der Wartezeit in Min.";"532:00";"414:00";"280:00";"160:00";"256:00";"437:00";"455:00";"202:00";"532:00";"359:00";"384:00";"417:00";"148:00";"375:00";"343:00";',
            (string) $response->getBody()
        );
    }

    public function testWithDownloadByMonthCSV()
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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingscope/141/2016/',
                    'response' => $this->readFixture("GET_waitingscope_141_2016.json")
                ]
            ]
        );

        $response = $this->render(['period' => '2016'], ['type' => 'csv'], []);

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString('"Max.";"01.01.2016";"01.02.2016";"01.03.2016"', (string) $response->getBody());
        $this->assertStringContainsString('Stunden-Max (Spaltenmaximum) der Wartezeit in Min.";"532:00";"384:00";"506:00";"532:00"', (string) $response->getBody());
    }
}
