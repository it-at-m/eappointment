<?php

namespace BO\Zmsapi\Tests;

use \BO\Slim\Render;

class ConflictListByScopeTest extends Base
{
    protected $classname = "ConflictListByScope";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 141], [
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-06'
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testScopeNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->render(['id' => 123], [], []);
    }
}
