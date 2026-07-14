<?php

namespace BO\Zmsbackend\Tests\Exchange\Service;

use \BO\Zmsbackend\Exchange\Service\ExchangeRequestowner as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeRequestownerTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(23, new DateTime('2016-04-01'), new DateTime('2016-04-31'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(20, count($entity->data));
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
        $this->assertEquals(3, count($entity->data));
    }
}
