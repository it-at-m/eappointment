<?php

namespace BO\Zmsadmin\Tests;

class DayoffByYearTest extends Base
{
    protected $arguments = ['year' => 2016];

    protected $parameters = [];

    protected $classname = "DayoffByYear";

    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/dayoff/2016/',
                    'response' => $this->readFixture("GET_dayofflist_2016.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, $this->parameters, []);
        $this->assertStringContainsString('Allgemein gültige Feiertage 2016', (string)$response->getBody());
        $this->assertStringContainsString('Tag der Deutschen Einheit', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSave()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 1],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/dayoff/2016/',
                    'response' => $this->readFixture("GET_dayofflist_2016.json")
                ],
                [
                    'function' => 'readPostResult',
                    'url' => '/dayoff/2016/',
                    'response' => $this->readFixture("GET_dayofflist_2016.json")
                ]
            ]
        );
        $response = $this->render($this->arguments, [
            'dayoff' => array(
                  array(
                    'name' => 'Karfreitag',
                    'date' => '25.03.2016',
                  ),
                  array(
                    'name' => 'Ostermontag',
                    'date' => '28.03.2016',
                  ),
                  array(
                    'name' => 'Maifeiertag',
                    'date' => '01.05.2016',
                  ),
                  array(
                    'name' => 'Christi Himmelfahrt',
                    'date' => '05.05.2016',
                  ),
                  array(
                    'name' => 'Pfingstmontag',
                    'date' => '16.05.2016',
                  ),
                  array(
                    'name' => 'Tag der Deutschen Einheit',
                    'date' => '03.10.2016',
                  ),
                  array(
                    'name' => '1. Weihnachtstag',
                    'date' => '25.12.2016',
                  ),
                  array(
                    'name' => '2. Weihnachtstag',
                    'date' => '26.12.2016',
                  )
            ),
            'save' => 'save'
        ], [], 'POST');
        $this->assertRedirect($response, '/dayoff/2016/?success=dayoff_saved');
        $this->assertEquals(302, $response->getStatusCode());
    }
}
