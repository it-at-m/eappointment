<?php

namespace BO\Zmsstatistic\Tests;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            '<a href="/report/waiting/organisation/2016-03/">März</a>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', (string) $response->getBody());
        $this->assertStringContainsString(
            '<label for="scope-select">Standortauswahl</label>',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<optgroup label="Charlottenburg-Wilmersdorf -&gt; Bürgeramt">',
            (string) $response->getBody()
        );
        $this->assertStringContainsString(
            '<label>Datumsauswahl</label>',
            (string) $response->getBody()
        );
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
        $body = (string) $response->getBody();

        $this->assertStringContainsString('Gesamt', $body);
        $this->assertStringContainsString('Terminkunden', $body);
        $this->assertStringContainsString('Spontankunden', $body);

        // Reihenfolge: Gesamt vor Termin vor Spontan
        $this->assertTrue(strpos($body, 'Gesamt') < strpos($body, 'Terminkunden'));
        $this->assertTrue(strpos($body, 'Terminkunden') < strpos($body, 'Spontankunden'));
        $this->assertStringContainsString('<th class="statistik">Zeilenmaximum</th>', (string) $response->getBody());
        $this->assertStringContainsString(
            'Auswertung für Charlottenburg-Wilmersdorf im Zeitraum März 2016',
            (string) $response->getBody()
        );
        $this->assertStringContainsString('532', (string) $response->getBody());
        $this->assertStringContainsString('294', (string) $response->getBody());
    }

    public function testWithDownloadXLSX()
    {
        // Start output buffering to capture any output from the test
        ob_start();
        
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
        if (method_exists($response->getBody(), 'rewind')) {
            $response->getBody()->rewind();
        }

        $tmp = tempnam(sys_get_temp_dir(), 'waiting_xlsx_');
        file_put_contents($tmp, (string) $response->getBody());

        $spreadsheet = IOFactory::load($tmp);

        $this->assertSame(['Gesamt', 'Terminkunden', 'Spontankunden'], $spreadsheet->getSheetNames());
        $this->assertSame('Gesamt', $spreadsheet->getActiveSheet()->getTitle());

        @unlink($tmp);
        // Clean up output buffer (discard any captured output)
        ob_end_clean();
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
        $this->render([ ], ['__uri' => '/report/waiting/organisation/'], [ ]);
    }
}
