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
                    'response' => $this->readFixture("GET_Workstation_Superuser_Resolved1.json")
                ],
                [
                    'function' => 'readGetResult',
                    'url' => '/status/',
                    'response' => $this->readFixture("GET_status.json")
                ]
            ]
        );
        $response = parent::testRendering();
        $this->assertStringContainsString('<th>Version</th>', (string)$response->getBody());
        //check processes.confirmed:
        $this->assertStringContainsString('86861', (string)$response->getBody());
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

    public function testWithoutSuperuserPermission()
    {
        $this->expectException('\BO\Zmsentities\Exception\UserAccountMissingRights');
        $this->setApiCalls(
            [
                [
                    'function' => 'readGetResult',
                    'url' => '/workstation/',
                    'response' => $this->readFixture("GET_workstation_basic.json")
                ]
            ]
        );
        parent::testRendering();
    }
}
