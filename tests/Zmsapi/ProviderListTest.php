<?php

namespace BO\Zmsapi\Tests;

class ProviderListTest extends Base
{
    protected $classname = "ProviderList";

    public function testRendering()
    {
        $response = $this->render(['source' => 'dldb'], [], ['isAssigned' => true]);
        $this->assertContains('provider.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testSourceFailed()
    {
        $this->expectException('\BO\Zmsdb\Exception\UnknownDataSource');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'test'], [], []);
    }
}
