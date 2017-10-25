<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeWaitingnumber as Query;
use \BO\Zmsentities\Exchange;
use \BO\Zmsentities\Scope;
use \DateTimeImmutable as DateTime;

class ExchangeWaitingnumberTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(new Scope(['id' => 141]), new DateTime('2016-03-01'), new DateTime('2016-04-01'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(432, count($entity->data));
    }

    public function testSubject()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(141, $entity->data[0][0]);
    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(new Scope(['id' => 141]));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(306, count($entity->data));
    }
}
