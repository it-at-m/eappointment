<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangePeriodListTest extends Base
{
    public function testScopeDay()
    {
        $query = new \BO\Zmsdb\ExchangeWaitingscope();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(306, count($entity->data));
    }

    public function testDepartmentMonth()
    {
        $query = new \BO\Zmsdb\ExchangeWaitingdepartment();
        $entity = $query->readPeriodList(74, 'month');
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(15, count($entity->data));
    }

    public function testOrganisationYear()
    {
        $query = new \BO\Zmsdb\ExchangeWaitingorganisation();
        $entity = $query->readPeriodList(71, 'year');
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(2, count($entity->data));
    }
}
