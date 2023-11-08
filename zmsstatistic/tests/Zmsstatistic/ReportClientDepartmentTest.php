<?php

namespace BO\Zmsstatistic\Tests;

class ReportClientDepartmentTest extends Base
{
    protected $classname = "ReportClientDepartment";

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
                  'url' => '/warehouse/clientdepartment/74/',
                  'response' => $this->readFixture("GET_clientdepartment_74.json")
              ]
            ]
        );
        $response = $this->render([ ], ['__uri' => '/report/client/department/'], [ ]);
        $this->assertStringContainsString('Kundenstatistik Behörde', (string) $response->getBody());
        $this->assertStringContainsString(
            '<a class="active" href="/report/client/department/">Bürgeramt</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<a href="/report/client/department/2016-04/">April</a>',
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
                  'url' => '/warehouse/clientdepartment/74/',
                  'response' => $this->readFixture("GET_clientdepartment_74.json")
              ],
              [
                  'function' => 'readGetResult',
                  'url' => '/warehouse/clientdepartment/74/2016-04/',
                  'response' => $this->readFixture("GET_clientdepartment_74_042016.json")
              ],
              [
                  'function' => 'readGetResult',
                  'url' => '/warehouse/notificationdepartment/74/2016-04/',
                  'parameters' => ['groupby' => 'month'],
                  'response' => $this->readFixture("GET_notificationdepartment_74_042016.json")
              ]
            ]
        );
        $response = $this->render(
            [
            'period' => '2016-04'
            ],
            [
            '__uri' => '/report/client/department/2016-04/'
            ],
            [ ]
        );
        $this->assertStringContainsString(
            '<td class="report-board--summary" colspan="2">April 2016</td>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            'Auswertung für Bürgeramt im Zeitraum April 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('135', (string) $response->getBody());
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
                    'url' => '/warehouse/clientdepartment/74/',
                    'response' => $this->readFixture("GET_clientdepartment_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientdepartment/74/2016-04/',
                    'response' => $this->readFixture("GET_clientdepartment_74_042016.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/notificationdepartment/74/2016-04/',
                    'parameters' => ['groupby' => 'month'],
                    'response' => $this->readFixture("GET_notificationdepartment_74_042016.json")
                ]
                ]
            );
            $response = $this->render(
                [
                'period' => '2016-04'
                ],
                [
                '__uri' => '/report/client/department/2016-04/',
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
                    'url' => '/warehouse/clientdepartment/74/',
                    'response' => $this->readFixture("GET_clientdepartment_74.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/clientdepartment/74/2016-04/',
                    'response' => $this->readFixture("GET_clientdepartment_74_042016.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/warehouse/notificationdepartment/74/2016-04/',
                    'parameters' => ['groupby' => 'month'],
                    'response' => $this->readFixture("GET_notificationdepartment_74_042016.json")
                ]
            ]
        );

        $response = $this->render(
            [
                'period' => '2016-04'
            ],
            [
                '__uri' => '/report/client/department/2016-04/',
                'type' => 'csv'
            ],
            [ ]
        );

        $this->assertStringContainsString('csv', $response->getHeaderLine('Content-Disposition'));
        $this->assertStringContainsString(
            '"April";"2016";"Bürgeramt Heerstraße ";"N/A";"N/A";"135";"";"";""',
            $response->getBody()
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
        $this->render([ ], ['__uri' => '/report/client/department/'], [ ]);
    }
}
