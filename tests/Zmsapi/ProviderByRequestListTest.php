<?php

namespace BO\Zmsapi\Tests;

class ProviderByRequestListTest extends Base
{
    protected $classname = "ProviderByRequestList";

    public function testRendering()
    {
        $response = $this->render(['source' => 'dldb', 'csv' => '120335'], [], []);
        $this->assertStringContainsString('provider.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testSourceFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\Source\UnknownDataSource');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'test', 'csv' => '120335'], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Provider\ProviderNotFound');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'dldb', 'csv' => '11111111'], [], []);
    }

    public function testUnvalidCsv()
    {
        $this->expectException('\BO\Zmsapi\Exception\Provider\RequestsMissed');
        $this->expectExceptionCode(400);
        $this->render(['source' => 'dldb', 'csv' => ''], [], []);
    }
}
