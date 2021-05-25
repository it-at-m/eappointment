<?php

namespace BO\Zmsapi\Tests;

class OwnerGetTest extends Base
{
    protected $classname = "OwnerGet";

    public function testRendering()
    {
        $this->setWorkstation()->getUseraccount()->setRights('superuser');
        $response = $this->render(['id' => 99], ['resolveReferences' => 1], []);
        $this->assertStringContainsString('owner.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testReducedData()
    {
        $response = $this->render(['id' => 99], ['resolveReferences' => 1], []);
        $this->assertStringContainsString('reducedData', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testOwnerNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Owner\OwnerNotFound');
        $this->expectExceptionCode(404);
        $this->render(['id' => 999], [], []);
    }
}
