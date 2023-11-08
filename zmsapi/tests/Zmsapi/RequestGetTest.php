<?php

namespace BO\Zmsapi\Tests;

class RequestGetTest extends Base
{
    protected $classname = "RequestGet";

    public function testRendering()
    {
        $response = $this->render(['source' => 'dldb', 'id' => 120335], [], []);
        $this->assertStringContainsString('request.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsdb\Exception\Request\RequestNotFound');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'dldb', 'id' => 999], [], []);
    }

    public function testSourceFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Source\UnknownDataSource');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'test', 'id' => 123456], [], []);
    }
}
