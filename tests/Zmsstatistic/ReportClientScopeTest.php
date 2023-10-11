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
                  'url' => '/warehouse/clientscope/141/',
                  'response' => $this->readFixture("GET_clientscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/scope/'], [ ]);
        $this->assertStringContainsString('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a class="active" href="/report/client/scope/">Bürgeramt Heerstraße </a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<a href="/report/client/scope/2016-04/">April</a>',
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
