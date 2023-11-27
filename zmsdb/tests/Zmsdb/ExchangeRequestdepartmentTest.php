<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeRequestdepartment as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeRequestdepartmentTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(74, new DateTime('2016-04-01'), new DateTime('2016-04-31'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(20, count($entity->data));
    }

    public function testMultipleIds()
    {
        $query = new Query();
        $entity = $query->readEntity('74,75', new DateTime('2016-04-01'), new DateTime('2016-04-31'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(20, count($entity->data));
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
        $this->assertEquals(3, count($entity->data));
    }
}
