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
                  'url' => '/warehouse/requestscope/141/',
                  'response' => $this->readFixture("GET_requestscope_141.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('Dienstleistungsstatistik Standort', (string) $response->getBody());
        $this->assertContains(
            '<a class="active" href="/report/request/scope/">Bürgeramt Heerstraße </a>',
            (string) $response->getBody()
        );
        $this->assertContains('<a href="/report/request/scope/2016-04/">April</a>', (string) $response->getBody());
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
        $this->assertContains(
            '<th class="statistik">Apr</th>',
            (string) $response->getBody()
        );
        $this->assertContains(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertContains('Reisepass beantragen', (string) $response->getBody());
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
        ob_start();
        $response = $this->render(
            [
                'period' => '2016-04'
            ],
            [
                'type' => 'csv'
            ],
            [ ]
        );
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertContains(
            '"Personalausweis beantragen";"14";"14";',
            $output
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
        ob_start();
        $response = $this->render(
            [
                'period' => '2016'
            ],
            [
                'type' => 'csv'
            ],
            [ ]
        );
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertContains('"Zeitraum:";"01.01.2016";"bis";"31.12.2016"', $output);
        $this->assertContains(
            '"Personalausweis beantragen";"14";"0";"0";"0";"14";"0";"0";"0";"0";"0";"0";"0";"0"',
            $output
        );
    }
}
