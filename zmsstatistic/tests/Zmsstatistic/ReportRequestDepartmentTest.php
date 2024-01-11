<?php

namespace BO\Zmsstatistic\Tests;

class ReportRequestDepartmentTest extends Base
{
    protected $classname = "ReportRequestDepartment";

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
                  'url' => '/warehouse/requestdepartment/74/',
                  'response' => $this->readFixture("GET_requestdepartment_74.json")
              ]
            ]
        );
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('Dienstleistungsstatistik Behörde', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a class="active" href="/report/request/department/">Bürgeramt</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<a href="/report/request/department/2016-04/">April</a>',
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
                    'url' => '/warehouse/requestdepartment/74/',
                    'response' => $this->readFixture("GET_requestdepartment_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestdepartment/74/2016-04/',
                    'response' => $this->readFixture("GET_requestdepartment_74_042016.json")
                ]
            ]
        );
        $response = $this->render(['period' => '2016-04'], [], []);
        $this->assertStringContainsString(
            '<th class="statistik">Apr</th>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Reisepass beantragen', (string) $response->getBody());
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
                        'url' => '/warehouse/requestdepartment/74/',
                        'response' => $this->readFixture("GET_requestdepartment_74.json")
                    ],
                    [
                        'function' => 'readGetResult',
                        'url' => '/warehouse/requestdepartment/74/2016-04/',
                        'response' => $this->readFixture("GET_requestdepartment_74_042016.json")
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
                    'url' => '/warehouse/requestdepartment/74/',
                    'response' => $this->readFixture("GET_requestdepartment_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/requestdepartment/74/2016-04/',
                    'response' => $this->readFixture("GET_requestdepartment_74_042016.json")
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
            '"Personalausweis beantragen";"14";"14";',
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
        $this->render([ ], ['__uri' => '/report/request/department/'], [ ]);
    }
}
