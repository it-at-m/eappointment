<?php

namespace BO\Zmsapi\Tests;

class AvailabilityListByScopeTest extends Base
{
    protected $classname = "AvailabilityListByScope";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['id' => 141], [], []);
        $this->assertContains('availability.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Scope\ScopeNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
