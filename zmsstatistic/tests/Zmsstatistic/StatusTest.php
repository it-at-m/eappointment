<?php

namespace BO\Zmsstatistic\Tests;

class StatusTest extends Base
{
    protected $arguments = [];
    protected $parameters = [];

    protected function getApiCalls()
    {
        return [
            [
                'function' => 'readGetResult',
                'url' => '/workstation/',
                'response' => $this->readFixture("GET_Workstation_Resolved2.json")
            ],
            [
                'function' => 'readGetResult',
                'url' => '/status/',
                'response' => $this->readFixture("GET_status.json")
            ]
        ];
    }

    public function testRendering()
    {
        $response = parent::testRendering();
        $this->assertStringContainsString('status--table', (string)$response->getBody());
        $this->assertStringContainsString('<th>Version</th>', (string)$response->getBody());
        $this->assertStringContainsString('Betriebsstatus des Systems', (string)$response->getBody());
        $this->assertStringContainsString('Anzahl der Abholer für Dokumente', (string)$response->getBody());
        $this->assertStringContainsString('Alter noch nicht versendeter Mails', (string)$response->getBody());
        $this->assertStringContainsString('86861', (string)$response->getBody());
    }

    public function testWithoutSuperuserPermission()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_Workstation_BasicRights.json")
                ]
            ]
        );
        parent::testRendering();
    }

    public function testWithoutWorkstation()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingLogin');
        $exception = new \BO\Zmsclient\Exception();
        $exception->template = 'BO\Zmsentities\Exception\UserAccountMissingLogin';
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'exception' => $exception
                ]
            ]
        );
        parent::testRendering();
    }
}
