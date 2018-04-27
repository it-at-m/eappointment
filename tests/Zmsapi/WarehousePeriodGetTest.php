<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WarehousePeriodGetTest extends Base
{
    protected $classname = "WarehousePeriodGet";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(
            ['subject' => 'waitingscope', 'subjectId' => 141, 'period' => '2016-03-01'],
            ['groupby' => 'day'],
            []
        );
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"firstDay":{"year":"2016","month":"03","day":"01"', (string)$response->getBody());
        $this->assertContains('"lastDay":{"year":"2016","month":"03","day":"01"', (string)$response->getBody());

        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testMonth()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(
            ['subject' => 'waitingscope', 'subjectId' => 141, 'period' => '2016-03'],
            ['groupby' => 'day'],
            []
        );
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"period":"day"', (string)$response->getBody());
        $this->assertContains('"firstDay":{"year":"2016","month":"03","day":"01"', (string)$response->getBody());
        $this->assertContains('"lastDay":{"year":"2016","month":"03","day":"31"', (string)$response->getBody());

        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testYear()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(
            ['subject' => 'waitingscope', 'subjectId' => 141, 'period' => '2016'],
            [],
            []
        );
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"firstDay":{"year":"2016","month":"01","day":"01"', (string)$response->getBody());
        $this->assertContains('"lastDay":{"year":"2016","month":"12","day":"31"', (string)$response->getBody());

        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testToday()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(
            ['subject' => 'clientscope', 'subjectId' => 141, 'period' => '_'],
            [],
            []
        );
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"period":"hour"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Warehouse\ReportNotFound');
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(
            ['subject' => 'unittest', 'subjectId' => 141, 'period' => '2016-03'],
            ['groupby' => 'day'],
            []
        );
    }

    public function testWithoutPeriod()
    {
        $this->expectException('\BO\Zmsapi\Exception\Warehouse\ReportNotFound');
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(
            ['subject' => 'waitingscope', 'subjectId' => 141, 'period' => '2016-04'],
            ['groupby' => 'day'],
            []
        );
    }
}
