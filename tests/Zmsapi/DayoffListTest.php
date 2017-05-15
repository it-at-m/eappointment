<?php

namespace BO\Zmsapi\Tests;

class DayoffListTest extends Base
{
    protected $classname = "DayoffList";

    public function testRendering()
    {
        $this->setWorkstation();
        $response = $this->render(['year' => 2016], [], []);
        $this->assertContains('dayoff.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testEmpty()
    {
        $this->setWorkstation();
        $this->setExpectedException('\ErrorException');
        $this->render([], [], []);
    }

    public function testNotFound()
    {
        $testYear = (new \DateTimeImmutable)->modify('+ 11years')->format('Y');
        $this->setWorkstation();
        $this->expectException('\BO\Zmsapi\Exception\Dayoff\YearOutOfRange');
        $this->expectExceptionCode(404);
        $this->render(['year' => $testYear], [], []);
    }
}
