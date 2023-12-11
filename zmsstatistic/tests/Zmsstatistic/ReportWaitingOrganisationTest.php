<?php

namespace BO\Zmsstatistic\Tests;

class ReportWaitingOrganisationTest extends Base
{
    protected $classname = "ReportWaitingOrganisation";

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
                  'url' => '/warehouse/waitingorganisation/71/',
                  'response' => $this->readFixture("GET_waitingorganisation_71.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('Wartestatistik Bezirk', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a class="active" href="/report/waiting/organisation/">Charlottenburg-Wilmersdorf</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<a href="/report/waiting/organisation/2016-03/">März</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertStringContainsString('Bitte wählen Sie einen Zeitraum aus.', (string) $response->getBody());
    }

    /*public function testWithPeriod()
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
                    'url' => '/warehouse/waitingorganisation/71/',
                    'response' => $this->readFixture("GET_waitingorganisation_71.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingorganisation/71/2016-03/',
                    'response' => $this->readFixture("GET_waitingorganisation_71_032016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-03'], [], []);
        $this->assertStringContainsString('<th class="statistik">Mär</th>', (string) $response->getBody());
        $this->assertStringContainsString(
            'Auswertung für Charlottenburg-Wilmersdorf im Zeitraum März 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('532', (string) $response->getBody());
        $this->assertStringContainsString('294', (string) $response->getBody());
    }*/

    /*public function testWithDownloadXLSX()
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
                        'url' => '/warehouse/waitingorganisation/71/',
                        'response' => $this->readFixture("GET_waitingorganisation_71.json")
                    ],
                    [
                        'function' => 'readGetResult',
                        'url' => '/warehouse/waitingorganisation/71/2016-03/',
                        'response' => $this->readFixture("GET_waitingorganisation_71_032016.json")
                    ]
                ]
            );
            $response = $this->render(['period' => '2016-03'], ['type' => 'xlsx'], []);
            $this->assertStringContainsString('xlsx', $response->getHeaderLine('Content-Disposition'));
        });
    }*/

    /*public function testWithDownloadCSV()
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
                    'url' => '/warehouse/waitingorganisation/71/',
                    'response' => $this->readFixture("GET_waitingorganisation_71.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingorganisation/71/2016-03/',
                    'response' => $this->readFixture("GET_waitingorganisation_71_032016.json")
                ]
            ]
        );

        $response = $this->render(['period' => '2016-03'], ['type' => 'csv'], []);

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString(
            '"Tagesmaximum";"532";"414";"280";"160";"256";"437";"455";"202";"532";"359";"384";"417";"148";"375";"343";',
            (string) $response->getBody()
        );
    }*/

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
        $this->render([ ], ['__uri' => '/report/waiting/organisation/'], [ ]);
    }
}
