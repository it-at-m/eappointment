<?php

namespace BO\Zmsapi\Tests;

class ProviderListTest extends Base
{
    protected $classname = "ProviderList";

    public function testRendering()
    {
        $response = $this->render(['source' => 'unittest'], ['isAssigned' => true], []);
        $this->assertStringContainsString('provider.json', (string)$response->getBody());
        $this->assertStringContainsString('9999999', (string)$response->getBody());
        $this->assertStringNotContainsString('9999998', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testIsAssignedFalse()
    {
        $response = $this->render(['source' => 'unittest'], ['isAssigned' => false], []);
        $this->assertStringContainsString('provider.json', (string)$response->getBody());
        $this->assertStringContainsString('9999998', (string)$response->getBody());
        $this->assertStringNotContainsString('9999999', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithRequestList()
    {
        $response = $this->render(
            ['source' => 'unittest'],
            ['requestList' => '9999998,9999999'],
            []
        );
        $this->assertStringContainsString('provider.json', (string)$response->getBody());
        $this->assertStringContainsString('9999999', (string)$response->getBody());
        $this->assertStringContainsString('9999998', (string)$response->getBody());
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
        $this->render(['source' => 'test'], [], []);
    }
}
