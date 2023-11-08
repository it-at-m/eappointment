<?php

namespace BO\Zmsapi\Tests;

class IndexTest extends Base
{
    protected $classname = "Index";

    public function testRendering()
    {
        $response = $this->render([ ], [ ], [ ]);
        $this->assertStringContainsString('swagger.json', (string) $response->getBody());
    }

    public function testNow()
    {
        $now = \App::getNow();
        $this->assertEquals('2016-04-01', $now->format('Y-m-d'));

        \App::$now = null;
        $dateTime = \App::getNow();
        $this->assertTrue('2016-04-01' !== $dateTime->format('Y-m-d'));

        \App::$now = $now;
    }
}
