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
                    'url' => '/warehouse/waitingscope/141/',
                    'response' => $this->readFixture("GET_waitingscope_141.json")
                ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/scope/'], [ ]);
        $this->assertContains('Wartestatistik Standort', (string) $response->getBody());
        $this->assertContains(
            '<a class="active" href="/report/waiting/scope/">Bürgeramt Heerstraße </a>',
            (string) $response->getBody()
        );
        $this->assertContains('<a href="/report/waiting/scope/2016-03/">März</a>', (string) $response->getBody());
        $this->assertContains('Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertContains('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
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
        $this->assertContains('<th class="statistik">Mär</th>', (string) $response->getBody());
        $this->assertContains(
            'Auswertung für Bürgeramt Heerstraße im Zeitraum März 2016',
            (string) $response->getBody()
        );
        $this->assertContains('532', (string) $response->getBody());
        $this->assertContains('294', (string) $response->getBody());
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
        $this->assertContains('<th class="statistik">Jan</th>', (string) $response->getBody());
        $this->assertContains(
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
        ob_start();
        $response = $this->render(['period' => '2016-03'], ['type' => 'csv'], []);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertContains(
            '"Tagesmaximum";"532";"414";"280";"160";"256";"437";"455";"202";"532";"359";"384";"417";"148";"375";"343";',
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
        ob_start();
        $response = $this->render(['period' => '2016'], ['type' => 'csv'], []);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertContains('"2016";"Januar";"Februar";"März"', $output);
        $this->assertContains('"Tagesmaximum";"532";"384";"506";"532"', $output);
    }
}
