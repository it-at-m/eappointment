<?php

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;

class WarehousePeriodListGetTest extends Base
{
    protected $classname = "WarehousePeriodListGet";

    public function testRendering()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope');
        $response = $this->render(['subject' => 'waitingscope', 'subjectId' => 141], [], []);
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"period":"day"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWaitingDepartmentByMonth()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope', 'department');
        $response = $this->render(['subject' => 'waitingdepartment', 'subjectId' => 74], ['period' => 'month'], []);
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"period":"month"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWaitingOrganisationByYear()
    {
        $workstation = $this->setWorkstation();
        $workstation->getUseraccount()->setRights('scope', 'department', 'organisation');
        $response = $this->render(['subject' => 'waitingorganisation', 'subjectId' => 72], ['period' => 'year'], []);
        $this->assertContains('exchange.json', (string)$response->getBody());
        $this->assertContains('"period":"year"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
