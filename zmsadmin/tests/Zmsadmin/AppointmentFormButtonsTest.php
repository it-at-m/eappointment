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
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-04-01'], []);
        $this->assertStringContainsString('Spontankunden hinzufügen', (string)$response->getBody());
    }

    public function testWithSelectedDateWithTime()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
                    'response' => $this->readFixture("GET_Workstation_Resolved2.json")
                ]
            ]
        );
        $response = $this->render([], ['selecteddate' => '2016-05-27', 'selectedtime' => '08-00'], []);
        $this->assertStringContainsString('Termin buchen', (string)$response->getBody());
    }

    public function testWithSelectedProcess()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
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
        $this->assertStringContainsString('Speichern', (string)$response->getBody());
        $this->assertStringContainsString('Löschen', (string)$response->getBody());
        $this->assertStringContainsString('Termin drucken', (string)$response->getBody());
        $this->assertStringContainsString('Als neu hinzufügen', (string)$response->getBody());
        $this->assertStringContainsString('Abbrechen', (string)$response->getBody());
    }

    public function testWithSelectedProcessAndNewDate()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'parameters' => [
                        'resolveReferences' => 2,
                        'gql' => \BO\Zmsadmin\Helper\GraphDefaults::getWorkstation()
                    ],
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
        $this->assertStringNotContainsString('Speichern', (string)$response->getBody());
        $this->assertStringNotContainsString('Löschen', (string)$response->getBody());
        $this->assertStringNotContainsString('Wartenr. drucken', (string)$response->getBody());
        $this->assertStringContainsString('Termin ändern', (string)$response->getBody());
        $this->assertStringContainsString('Abbrechen', (string)$response->getBody());
    }
}
