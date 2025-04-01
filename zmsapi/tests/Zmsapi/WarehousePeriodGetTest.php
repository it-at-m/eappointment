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
        $this->assertStringContainsString('exchange.json', (string)$response->getBody());
        $this->assertStringContainsString('"firstDay":{"year":"2016","month":"03","day":"01"', (string)$response->getBody());
        $this->assertStringContainsString('"lastDay":{"year":"2016","month":"03","day":"01"', (string)$response->getBody());

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
        $entity = (new \BO\Zmsclient\Result($response))->getEntity();
        $this->assertStringContainsString('exchange.json', (string)$response->getBody());
        $this->assertEquals($entity->period, 'day');
        $this->assertStringContainsString('"firstDay":{"year":"2016","month":"03","day":"01"', (string)$response->getBody());
        $this->assertStringContainsString('"lastDay":{"year":"2016","month":"03","day":"31"', (string)$response->getBody());

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
        $this->assertStringContainsString('exchange.json', (string)$response->getBody());
        $this->assertStringContainsString('"firstDay":{"year":"2016","month":"01","day":"01"', (string)$response->getBody());
        $this->assertStringContainsString('"lastDay":{"year":"2016","month":"12","day":"31"', (string)$response->getBody());

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
        $this->assertStringContainsString('exchange.json', (string)$response->getBody());
        $this->assertStringContainsString('"period":"hour"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Warehouse\UnknownReportType');
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
            ['subject' => 'waitingscope', 'subjectId' => 141, 'period' => '2017-04'],
            ['groupby' => 'day'],
            []
        );
    }
}
