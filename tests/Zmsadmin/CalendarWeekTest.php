<?php

namespace BO\Zmsadmin\Tests;

class CalendarWeekTest extends Base
{
    protected $arguments = ['year' => 2016, 'weeknr' => 13];

    protected $parameters = [
        'slottype' => 'intern',
        'slotsrequired' => 0
    ];

    protected $classname = "CalendarWeek";

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
                    'url' => '/scope/141/cluster/',
                    'response' => $this->readFixture("GET_cluster_109.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/process/status/free/',
                    'parameters' => ['slotType' => 'intern', 'slotsRequired' => 0, 'keepLessData' => ['availability']],
                    'response' => $this->readFixture("GET_freeprocesslist_empty.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-03-28/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],[
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-03-29/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],[
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-03-30/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],[
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-03-31/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-01/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-02/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/scope/141/process/2016-04-03/',
                    'response' => $this->readFixture("GET_processList_141_20160401.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertContains(
            'Standort: Bürgeramt Heerstraße, 13. Kalenderwoche',
            (string)$response->getBody()
        );
        $this->assertContains('<span class="pid">184432</span>', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
