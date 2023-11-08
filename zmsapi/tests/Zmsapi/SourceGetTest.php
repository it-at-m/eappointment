<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsentities\Source;

class SourceGetTest extends Base
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
        $this->expectException('\BO\Zmsapi\Exception\Source\SourceNotFound');
        $this->render([], [], []);
    }

    public function testSourceNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Source\SourceNotFound');
        $this->expectExceptionCode(404);
        $this->render(['source' => 'xxx'], [], []);
    }
}
