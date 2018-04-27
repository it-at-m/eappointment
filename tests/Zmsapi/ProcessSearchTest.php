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

    public function testWithLessData()
    {
        $this->setWorkstation();
        $response = $this->render([], ['query' => 'dayoff', 'lessResolvedData' => 1], []);
        $this->assertContains('process.json', (string)$response->getBody());
        $this->assertNotContains('availability', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
