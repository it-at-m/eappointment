<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessLogTest extends Base
{
    protected $classname = "ProcessLog";

    const SEARCH_PARAM = 10029;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        (new ProcessUpdateTest)->testRendering();
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['search' => self::SEARCH_PARAM], [], []);
        $this->assertStringContainsString('log.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['search' => 123], [], []);
        $this->assertStringContainsString('"data":[]', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
