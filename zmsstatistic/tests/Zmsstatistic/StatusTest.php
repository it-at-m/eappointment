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
        $this->assertStringContainsString('API Version', (string)$response->getBody());
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
}
