<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessLogTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessLog";

    const SEARCH_PARAM = 10029;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        (new \BO\Zmsbackend\Tests\Process\Api\ProcessUpdateTest('dummyTest'))->testRendering();
        $this->setWorkstation()->getUseraccount()->setPermissions('superuser');

        $response = $this->render([], ['searchQuery' => self::SEARCH_PARAM], []);
        $this->assertStringContainsString('log.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setPermissions('logs');
        $response = $this->render([], ['searchQuery' => 123456], []);
        $this->assertStringContainsString('"data":[]', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
