<?php

namespace BO\Zmsstatistic\Tests;

class ReportWaitingDepartmentTest extends Base
{
    protected $classname = "ReportWaitingDepartment";

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
                  'url' => '/warehouse/waitingdepartment/74/',
                  'response' => $this->readFixture("GET_waitingdepartment_74.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertContains('Wartestatistik Behörde', (string) $response->getBody());
        $this->assertContains(
            '<a class="active" href="/report/waiting/department/">Bürgeramt</a>',
            (string) $response->getBody()
        );
        $this->assertContains('<a href="/report/waiting/department/2016-03/">März</a>', (string) $response->getBody());
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
                    'url' => '/warehouse/waitingdepartment/74/',
                    'response' => $this->readFixture("GET_waitingdepartment_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingdepartment/74/2016-03/',
                    'response' => $this->readFixture("GET_waitingdepartment_74_032016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-03'], [], []);
        $this->assertContains('<th class="statistik">Mär</th>', (string) $response->getBody());
        $this->assertContains(
            'Auswertung für Bürgeramt im Zeitraum März 2016',
            (string) $response->getBody()
        );
        $this->assertContains('532', (string) $response->getBody());
        $this->assertContains('294', (string) $response->getBody());
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
                        'url' => '/warehouse/waitingdepartment/74/',
                        'response' => $this->readFixture("GET_waitingdepartment_74.json")
                    ],
                    [
                        'function' => 'readGetResult',
                        'url' => '/warehouse/waitingdepartment/74/2016-03/',
                        'response' => $this->readFixture("GET_waitingdepartment_74_032016.json")
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
                    'url' => '/warehouse/waitingdepartment/74/',
                    'response' => $this->readFixture("GET_waitingdepartment_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/waitingdepartment/74/2016-03/',
                    'response' => $this->readFixture("GET_waitingdepartment_74_032016.json")
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
        $this->render([ ], ['__uri' => '/report/waiting/department/'], [ ]);
    }
}
