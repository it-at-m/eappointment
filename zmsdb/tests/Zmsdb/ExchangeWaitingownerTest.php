<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeWaitingowner as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeWaitingownerTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(23, new DateTime('2016-03-01'), new DateTime('2016-03-31'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(432, count($entity->data));
    }

    public function testSubject()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(23, $entity->data[0][0]);
    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(23);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }
}
