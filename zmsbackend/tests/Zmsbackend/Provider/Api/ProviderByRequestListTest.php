<?php

namespace BO\Zmsbackend\Tests\Provider\Api;

class ProviderByRequestListTest extends \BO\Zmsbackend\Tests\Api\Base
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
        $this->expectException('\BO\Zmsbackend\Source\Exception\UnknownDataSource');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'test', 'csv' => '120335'], [], []);
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Provider\Exception\ProviderNotFound');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'dldb', 'csv' => '11111111'], [], []);
    }

    public function testUnvalidCsv()
    {
        $this->expectException('\BO\Zmsbackend\Provider\Exception\RequestsMissed');
        $this->expectExceptionCode(400);
        $this->render(['source' => 'dldb', 'csv' => ''], [], []);
    }
}
