<?php

namespace BO\Zmsbackend\Tests\Source\Api;

use BO\Zmsentities\Source;

class SourceGetTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "SourceGet";

    const SOURCE = 'unittest';

    public function testRendering()
    {
        $response = $this->render(['source' => self::SOURCE], [], []);
        $this->assertStringContainsString('source.json', (string)$response->getBody());
        $this->assertStringNotContainsString('providers', (string)$response->getBody());
        $this->assertStringNotContainsString('requests', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithResolvedReferences()
    {
        $response = $this->render(['source' => self::SOURCE], ['resolveReferences' => 1], []);
        $this->assertStringContainsString('source.json', (string)$response->getBody());
        $this->assertStringContainsString('providers', (string)$response->getBody());
        $this->assertStringContainsString('requests', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Zmsbackend\Source\Exception\SourceNotFound');
        $this->render([], [], []);
    }

    public function testSourceNotFound()
    {
        $this->expectException('\BO\Zmsbackend\Source\Exception\SourceNotFound');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'xxx'], [], []);
    }
}
