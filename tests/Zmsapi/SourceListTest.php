<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsentities\Source;

class SourceListTest extends Base
{
    protected $classname = "SourceList";

    const SOURCE = 'dldb';

    public function testRendering()
    {
        $response = $this->render([], [], []);
        $this->assertContains('source.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
