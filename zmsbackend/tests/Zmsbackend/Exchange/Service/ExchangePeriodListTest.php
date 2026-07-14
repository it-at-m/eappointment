<?php

namespace BO\Zmsbackend\Tests\Exchange\Service;

use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangePeriodListTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testScope()
    {
        $query = new \BO\Zmsbackend\Exchange\Service\ExchangeWaitingscope();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }

    public function testDepartment()
    {
        $query = new \BO\Zmsbackend\Exchange\Service\ExchangeWaitingdepartment();
        $entity = $query->readPeriodList(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }

    public function testOrganisation()
    {
        $query = new \BO\Zmsbackend\Exchange\Service\ExchangeWaitingorganisation();
        $entity = $query->readPeriodList(71);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }
}
