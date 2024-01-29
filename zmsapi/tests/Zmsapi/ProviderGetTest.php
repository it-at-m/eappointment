<?php

namespace BO\Zmsapi\Tests;

class ProviderGetTest extends Base
{
    protected $classname = "ProviderGet";

    public function testRendering()
    {
        $response = $this->render(['source' => 'dldb', 'id' => 122217, 'displayName' => 'B\u00fcrgeramt Heerstra\u00dfe'], [], []); //Heerstraße
        $this->assertStringContainsString('provider.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\ErrorException');
        $this->render([], [], []);
    }

    public function testProviderNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Provider\ProviderNotFound');
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
