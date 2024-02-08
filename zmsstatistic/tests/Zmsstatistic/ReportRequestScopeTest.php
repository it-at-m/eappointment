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
                  'url' => '/warehouse/requestscope/141/',
                  'response' => $this->readFixture("GET_requestscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('Dienstleistungsstatistik Standort', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a class="active" href="/report/request/scope/">Bürgeramt Heerstraße </a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<a href="/report/request/scope/2016-04/">April</a>',
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
            '<th class="statistik">Apr</th>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Reisepass beantragen', (string) $response->getBody());
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
                'type' => 'csv'
            ],
            [ ]
        );

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString(
            '"Personalausweis beantragen";"";"14";"14";',
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

        $response = $this->render(
            [
                'period' => '2016'
            ],
            [
                'type' => 'csv'
            ],
            [ ]
        );

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString(
            '"Zeitraum:";"01.01.2016";"bis";"31.12.2016"',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '"Personalausweis beantragen";"";"14";"0";"0";"0";"14";"0";"0";"0";"0";"0";"0";"0";"0"',
            (string) $response->getBody()
        );
    }
}
