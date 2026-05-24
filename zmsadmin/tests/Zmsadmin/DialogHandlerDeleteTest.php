<?php

namespace BO\Zmsadmin\Tests;

class DialogHandlerDeleteTest extends Base
{
    protected $arguments = [];

    protected $classname = "\BO\Zmsadmin\Helper\DialogHandler";

    public function testRendering()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
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
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithAppointmentNoMail()
    {
        $process = json_decode($this->readFixture("GET_process_100044_57c2.json"), 1);
        $process['data']['clients'][0]['email'] = '';
        
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
        $this->assertStringContainsString(
            'Beachten Sie, dass der Kunde darüber nicht per eMail informiert werden kann.',
            (string)$response->getBody()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithSpontaneousClient()
    {
        $process = json_decode($this->readFixture("GET_process_spontankunde.json"), 1);
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
        $this->assertEquals(200, $response->getStatusCode());
    }
}
