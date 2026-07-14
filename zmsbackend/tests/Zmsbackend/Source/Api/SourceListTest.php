<?php

namespace BO\Zmsbackend\Tests\Source\Api;

use BO\Zmsentities\Source;

class SourceListTest extends \BO\Zmsbackend\Tests\Api\Base
{
    protected $classname = "SourceList";

    const SOURCE = 'dldb';

    public function testRendering()
    {
        $response = $this->render([], [], []);
        $this->assertStringContainsString('source.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
