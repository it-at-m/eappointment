<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsentities\Source;

class SourceGetTest extends Base
{
    protected $classname = "SourceGet";

    const SOURCE = 'dldb';

    public function testRendering()
    {
        $response = $this->render(['source' => self::SOURCE], [], []);
        $this->assertContains('source.json', (string)$response->getBody());
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
        $this->render(['source' => 'unittest'], [], []);
    }
}
