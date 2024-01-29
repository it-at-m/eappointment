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
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/scope/'], [ ]);
        $this->assertStringContainsString('Wartestatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a class="active" href="/report/waiting/scope/">Bürgeramt Heerstraße </a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<a href="/report/waiting/scope/2016-03/">März</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string) $response->getBody());
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
        $this->assertStringContainsString('<th class="statistik">Mär</th>', (string) $response->getBody());
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
        $this->assertStringContainsString('<th class="statistik">Jan</th>', (string) $response->getBody());
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum Januar 2016',
            (string) $response->getBody()
        );
    }

    public function testWithDownloadXLSX()
    {
        $this->setOutputCallback(function () {
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
        });
    }

    public function testWithDownloadCSV()
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
            '"Tagesmaximum Spontankunden";"532";"414";"280";"160";"256";"437";"455";"202";"532";"359";"384";"417";"148";"375";"343";',
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
        $this->assertStringContainsString('"2016";"Januar";"Februar";"März"', (string) $response->getBody());
        $this->assertStringContainsString('"Tagesmaximum Spontankunden";"532";"384";"506";"532"', (string) $response->getBody());
    }
}
