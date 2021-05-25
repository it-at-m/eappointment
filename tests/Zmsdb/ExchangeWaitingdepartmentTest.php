<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeWaitingdepartment as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeWaitingdepartmentTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(74, new DateTime('2016-03-01'), new DateTime('2016-03-31'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(432, count($entity->data));
    }

    public function testMultipleIds()
    {
        $query = new Query();
        $entity = $query->readEntity('74,75', new DateTime('2016-03-01'), new DateTime('2016-03-31'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(432, count($entity->data));
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(74, $entity->data[0][0]);
        $this->assertStringContainsString('Charlottenburg-Wilmersdorf', $entity->data[0][3]); //department name
        $this->assertStringContainsString('Bürgeramt', $entity->data[0][4]); //department name
    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(74);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(17, count($entity->data));
    }
}
