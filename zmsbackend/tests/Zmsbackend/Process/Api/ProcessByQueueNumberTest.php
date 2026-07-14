<?php

namespace BO\Zmsbackend\Tests\Process\Api;

use BO\Zmsbackend\Helper\User;

class ProcessByQueueNumberTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "ProcessByQueueNumber";

    public function testRendering()
    {
        $response = $this->render(['id' => 141, 'number' => 92940], [], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Scope\Exception\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'number' => 1], [], []);
    }

    public function testProcessNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Process\Exception\ProcessByQueueNumberNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 141, 'number' => 999], [], []);
    }
}
