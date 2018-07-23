<?php

namespace BO\Zmsapi\Tests;

class ApikeyDeleteTest extends Base
{
    protected $classname = "ApikeyDelete";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['key' => '79cc69c11550f5558a0c0da3f6a055cd53c'], [], []);
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
