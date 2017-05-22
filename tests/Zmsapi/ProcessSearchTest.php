<?php

namespace BO\Zmsapi\Tests;

class ProcessSearchTest extends Base
{
    protected $classname = "ProcessSearch";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render([], ['query' => 'dayoff'], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
