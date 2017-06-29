<?php

namespace BO\Zmsadmin\Tests;

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
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/',
                    'response' => $this->readFixture("GET_scope_141.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains(
            'download; filename="tagesuebersicht_01.04.2016.xlsx',
            $response->getHeader('Content-Disposition')[0]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }
}
