<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessListByScopeAndStatusTest extends Base
{
    protected $classname = "ProcessListByScopeAndStatus";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 141, 'status' => 'confirmed'], [], []);
        $this->assertStringContainsString('metaresult.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testRenderingPickup()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 106, 'status' => 'missed'], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertStringContainsString('"status":"missed"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'status' => 'pending'], [], []);
    }
}
