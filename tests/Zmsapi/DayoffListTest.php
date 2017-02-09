<?php

namespace BO\Zmsapi\Tests;

class DayoffListTest extends Base
{
    protected $classname = "DayoffList";

    public function testRendering()
    {
        $response = $this->render([2016], [], []);
        $this->assertContains('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
