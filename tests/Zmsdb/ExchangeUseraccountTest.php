<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeUseraccount as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeUseraccountTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(11, count($entity->data));
        $this->assertEquals('berlinonline', $entity->data[0][2]);
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals('_', $entity->data[0][0]);
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals('_', $entity->data[0][0]);
    }
}
