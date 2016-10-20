<?php

namespace BO\Zmsapi\Tests;

class ProviderListTest extends Base
{
    protected $classname = "ProviderList";

    public function testRendering()
    {
        $response = $this->render(['dldb'], [], ['isAssigned' => true]);
        $this->assertContains('provider.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $response = $this->render([], [], []);
    }

    public function testSourceFailed()
    {
        $this->expectException('BO\\Zmsdb\\Exception\\UnknownDataSource');
        $this->expectExceptionCode(404);
        $response = $this->render(['test'], [], []);
    }

    public function testListByRequestCsv()
    {
        $response = $this->render(['dldb', '120335'], [], []);
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testListByRequestCsvFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Provider\ProviderNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render(['dldb', '11111111'], [], []);
    }
}
