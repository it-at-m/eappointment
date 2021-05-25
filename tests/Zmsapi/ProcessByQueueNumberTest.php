<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class ProcessByQueueNumberTest extends Base
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
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999, 'number' => 1], [], []);
    }

    public function testProcessNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessByQueueNumberNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 141, 'number' => 999], [], []);
    }
}
