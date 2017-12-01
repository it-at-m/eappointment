<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangePeriodListTest extends Base
{
    public function testScope()
    {
        $query = new \BO\Zmsdb\ExchangeWaitingscope();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }

    public function testDepartment()
    {
        $query = new \BO\Zmsdb\ExchangeWaitingdepartment();
        $entity = $query->readPeriodList(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }

    public function testOrganisation()
    {
        $query = new \BO\Zmsdb\ExchangeWaitingorganisation();
        $entity = $query->readPeriodList(71);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }
}
