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
                  'url' => '/warehouse/clientscope/141/',
                  'response' => $this->readFixture("GET_clientscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/scope/'], [ ]);
        $this->assertContains('Kundenstatistik Standort', (string) $response->getBody());
        $this->assertContains(
            '<a class="active" href="/report/client/scope/">Bürgeramt Heerstraße </a>',
            (string) $response->getBody()
        );
        $this->assertContains('<a href="/report/client/scope/2016-04/">April</a>', (string) $response->getBody());
        $this->assertContains('Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertContains('Bitte wählen Sie eine Zeit aus.', (string) $response->getBody());
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/notificationscope/141/2016-04/',
                    'parameters' => ['groupby' => 'month'],
                    'response' => $this->readFixture("GET_notificationscope_141_042016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-04'], [ ], [ ]);
        $this->assertContains(
            '<td class="report-board--summary" colspan="2">April 2016</td>',
            (string) $response->getBody()
        );
        $this->assertContains(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertContains('135', (string) $response->getBody());
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016/',
                    'response' => $this->readFixture("GET_clientscope_141_2016.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/notificationscope/141/2016/',
                    'parameters' => ['groupby' => 'month'],
                    'response' => $this->readFixture("GET_notificationscope_141_2016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016'], [ ], [ ]);
        $this->assertContains(
            '<td class="report-board--summary" colspan="2">Kalenderjahr 2016</td>',
            (string) $response->getBody()
        );
        $this->assertContains(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum Kalenderjahr 2016',
            (string) $response->getBody()
        );
        $this->assertContains(
            '<td class="colWochenTag statistik">April</td>',
            (string) $response->getBody()
        );
        $this->assertContains('135', (string) $response->getBody());
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
                        'url' => '/warehouse/clientscope/141/',
                        'response' => $this->readFixture("GET_clientscope_141.json")
                    ],
                    [
                        'function' => 'readGetResult',
                        'url' => '/warehouse/clientscope/141/2016-04/',
                        'response' => $this->readFixture("GET_clientscope_141_042016.json")
                    ],
                    [
                        'function' => 'readGetResult',
                        'url' => '/warehouse/notificationscope/141/2016-04/',
                        'parameters' => ['groupby' => 'month'],
                        'response' => $this->readFixture("GET_notificationscope_141_042016.json")
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
            $this->assertContains('xlsx', $response->getHeaderLine('Content-Disposition'));
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
                    'url' => '/warehouse/clientscope/141/',
                    'response' => $this->readFixture("GET_clientscope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientscope/141/2016-04/',
                    'response' => $this->readFixture("GET_clientscope_141_042016.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/notificationscope/141/2016-04/',
                    'parameters' => ['groupby' => 'month'],
                    'response' => $this->readFixture("GET_notificationscope_141_042016.json")
                ]
            ]
        );
        ob_start();
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
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertContains(
            '"April";"2016";"Charlottenburg-Wilmersdorf";"Bürgeramt";"Bürgeramt Heerstraße ";"135";"";"";""',
            $output
        );
    }

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
    }
}
