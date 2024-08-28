<?php

namespace BO\Zmsadmin\Tests;

class DialogHandlerDeleteTest extends Base
{
    protected $arguments = [];

    protected $classname = "\BO\Zmsadmin\Helper\DialogHandler";

    public function testRendering()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['clients'][0]['telephone'] = '01234567890';
        $process['data']['scope']['preferences']['appointment']['notificationConfirmationEnabled'] = '1';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render([], [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100044,
                'name' => 'unittest'
                ]
            ], []);
        $this->assertStringContainsString('100044 (unittest)', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString('E-Mail und/oder SMS', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNoNotifcationEnabled()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['clients'][0]['telephone'] = '01234567890';
        $process['data']['scope']['preferences']['appointment']['notificationConfirmationEnabled'] = '0';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render([], [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100044,
                'name' => 'unittest'
                ]
            ], []);
        $this->assertStringContainsString('100044 (unittest)', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString('E-Mail', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithAppointmentNoTelephone()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['scope']['preferences']['appointment']['notificationConfirmationEnabled'] = '1';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render([], [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100044,
                'name' => 'unittest'
                ]
            ], []);
        $this->assertStringContainsString('100044 (unittest)', (string)$response->getBody());
        $this->assertStringContainsString(
            'Der Kunde wird darüber per E-Mail informiert.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithAppointmentNoMail()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['clients'][0]['email'] = '';
        $process['data']['clients'][0]['telephone'] = '01234567890';
        $process['data']['scope']['preferences']['appointment']['notificationConfirmationEnabled'] = '1';
        
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render(
            [],
            [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100044,
                'name' => 'unittest'
                ]
            ],
            []
        );
        $this->assertStringContainsString('100044 (unittest)', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString(
        #    'Der Kunde wird darüber per SMS informiert.',
        #    (string)$response->getBody()
        #);
        $this->assertStringContainsString(
            'Beachten Sie, dass der Kunde darüber nicht per eMail informiert werden kann.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithAppointmentNoMailNoNotification()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['clients'][0]['email'] = '';
        $process['data']['clients'][0]['telephone'] = '01234567890';
        $process['data']['scope']['preferences']['appointment']['notificationConfirmationEnabled'] = '0';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render(
            [],
            [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100044,
                'name' => 'unittest'
                ]
            ],
            []
        );
        $this->assertStringContainsString('100044 (unittest)', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString(
        #    'Beachten Sie, dass der Kunde darüber weder per eMail noch per SMS informiert werden kann.',
        #    (string)$response->getBody()
        #);
        $this->assertStringContainsString(
            'Beachten Sie, dass der Kunde darüber nicht per eMail informiert werden kann.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithAppointmentNoMailNoTelephone()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['clients'][0]['email'] = '';
        $process['data']['clients'][0]['telephone'] = '';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100044/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render(
            [],
            [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100044,
                'name' => 'unittest'
                ]
            ],
            []
        );
        $this->assertStringContainsString('100044 (unittest)', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString(
        #    'Beachten Sie, dass der Kunde darüber weder per eMail noch per SMS informiert werden kann.',
        #    (string)$response->getBody()
        #);
        $this->assertStringContainsString(
            'Beachten Sie, dass der Kunde darüber nicht per eMail informiert werden kann.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithSpontaneousClient()
    {
        $process = json_decode($this->readFixture("GET_process_spontankunde.json"), 1);
        $process['data']['clients'][0]['telephone'] = '01234567890';
        $process['data']['scope']['preferences']['appointment']['notificationConfirmationEnabled'] = '1';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100632/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render(
            [],
            [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100632,
                'name' => 'unittest'
                ]
            ],
            []
        );
        $this->assertStringContainsString('Nummer 6', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString('Der Kunde wird darüber per SMS informiert.', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithSpontaneousClientNoTelephone()
    {
        $process = json_decode($this->readFixture("GET_process_spontankunde.json"), 1);
        $process['data']['clients'][0]['telephone'] = '';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/process/100632/',
                    'response' => json_encode($process)
                ]
            ]
        );
        $response = $this->render(
            [],
            [
            'template' => 'confirm_delete',
            'parameter' => [
                'id' => 100632,
                'name' => 'unittest'
                ]
            ],
            []
        );
        $this->assertStringContainsString('Nummer 6', (string)$response->getBody());
        #SMS Modul nicht aktiviert, daher wird nur geprüft, ob eine Benachrichtigung per Mail möglich ist
        #$this->assertStringContainsString(
        #    'Beachten Sie, dass der Kunde darüber nicht per SMS informiert werden kann.',
        #    (string)$response->getBody()
        #);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
