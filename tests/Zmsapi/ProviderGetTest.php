<?php

namespace BO\Zmsapi\Tests;

class ProviderGetTest extends Base
{
    protected $classname = "ProviderGet";

    public function testRendering()
    {
        $response = $this->render(['dldb', 122217], [], []); //Heerstraße
        $this->assertContains('provider.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setExpectedException('\ErrorException');
        $response = $this->render([], [], []);
    }

    public function testProviderNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Provider\ProviderNotFound');
        $this->expectExceptionCode(404);
        $response = $this->render(['dldb', 123456], [], []);
    }

    public function testSourceFailed()
    {
        $this->expectException('BO\\Zmsdb\\Exception\\UnknownDataSource');
        $this->expectExceptionCode(404);
        $response = $this->render(['test', 123456], [], []);
    }
}
