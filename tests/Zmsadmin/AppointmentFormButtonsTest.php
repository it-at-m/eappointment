<?php

namespace BO\Zmsadmin\Tests;

class AppointmentFormButtonsTest extends Base
{
    protected $arguments = [];

    protected $parameters = [];

    protected $classname = "AppointmentFormButtons";

    public function testRendering()
    {
        \App::$now = new \DateTimeImmutable('2016-04-01 11:55:00', new \DateTimeZone('Europe/Berlin'));
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-04-01'], []);
        $this->assertContains('Spontankunden hinzufügen', (string)$response->getBody());
    }

    public function testWithSelectedDateWithTime()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => ['resolveReferences' => 2],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27', 'selectedtime' => '08-00'], []);
        $this->assertContains('Termin buchen', (string)$response->getBody());
    }

    public function testWithSelectedProcess()
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
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render([], [
            'selectedprocess' => '100044',
            'selectedtime' => '17-00',
            'selecteddate' => '2016-05-27'
        ], []);
        $this->assertContains('Speichern', (string)$response->getBody());
        $this->assertContains('Löschen', (string)$response->getBody());
        $this->assertContains('Wartenr. drucken', (string)$response->getBody());
        $this->assertContains('Als neu hinzufügen', (string)$response->getBody());
        $this->assertContains('Abbrechen', (string)$response->getBody());
    }

    public function testWithSelectedProcessAndNewDate()
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
                    'url' => '/process/100044/',
                    'response' => $this->readFixture("GET_process_100044_57c2.json")
                ]
            ]
        );
        $response = $this->render([], [
            'selectedprocess' => '100044',
            'selectedtime' => '17-00',
            'selecteddate' =>
            '2016-05-30'
        ], []);
        $this->assertNotContains('Speichern', (string)$response->getBody());
        $this->assertNotContains('Löschen', (string)$response->getBody());
        $this->assertNotContains('Wartenr. drucken', (string)$response->getBody());
        $this->assertContains('Termin ändern', (string)$response->getBody());
        $this->assertContains('Abbrechen', (string)$response->getBody());
    }
}
