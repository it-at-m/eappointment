<?php

namespace BO\Zmsadmin\Tests;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ScopeAppointmentsByDayXlsExportTest extends Base
{
    protected $arguments = [
        'id' => 141,
        'date' => '2016-04-01'
    ];

    protected $parameters = [];

    protected $classname = "ScopeAppointmentsByDayXlsExport";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString(
            'attachment; filename="Tagesuebersicht',
            $response->getHeader('Content-Disposition')[0]
        );
        $this->assertStringContainsString('.xlsx', $response->getHeader('Content-Disposition')[0]);
        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->getHeader('Content-Type')[0]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEscapingFormula()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 1,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'parameters' => [
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getProcess()
                    ],
                    'response' => $this->readFixture("GET_processList_with_csvInjection.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertEquals(200, $response->getStatusCode());

        if (method_exists($response->getBody(), 'rewind')) {
            $response->getBody()->rewind();
        }

        $tmp = tempnam(sys_get_temp_dir(), 'scope_day_xlsx_');
        file_put_contents($tmp, (string) $response->getBody());
        $sheet = IOFactory::load($tmp)->getActiveSheet();
        @unlink($tmp);

        $this->assertStringStartsWith("'=", (string) $sheet->getCell('F2')->getValue());
        $this->assertStringStartsWith("'=", (string) $sheet->getCell('H2')->getValue());
    }
}
