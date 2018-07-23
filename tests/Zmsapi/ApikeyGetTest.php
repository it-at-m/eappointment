<?php

namespace BO\Zmsapi\Tests;

class ApikeyGetTest extends Base
{
    protected $classname = "ApikeyGet";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['key' => '79cc69c11550f5558a0c0da3f6a055cd53c'], [], []);
        $this->assertContains('apikey.json', (string)$response->getBody());
        $this->assertContains('"route":"\/calendar\/"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
