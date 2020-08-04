<?php

namespace BO\Zmsadmin\Tests;

class StatusTest extends Base
{
    protected $arguments = [];
    protected $parameters = [];


    public function testRendering()
    {
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertContains('API Version', (string)$response->getBody());
        //check processes.confirmed:
        $this->assertContains('86861', (string)$response->getBody());
    }

    public function testWithoutWorkstation()
    {
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsaoi\Exception\Workstation\WorkstationNotFound';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'exception' => $exception
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertNotContains('(Nur für Superuser sichtbar)', (string)$response->getBody());
    }
}
