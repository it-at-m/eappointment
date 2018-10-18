<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeNotificationorganisation as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeNotificationorganisationTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(71, new DateTime('2016-04-01'), new DateTime('2016-04-30'));
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data)); // data grouped by department name
        $this->assertEquals(71, $entity->data[0][0]); // organisation id
        $this->assertEquals("2016-04-28", $entity->data[0][1]); // date
        $this->assertEquals("Charlottenburg-Wilmersdorf", $entity->data[0][2]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[0][3]); // department name
        $this->assertEquals("Bürgeramt Hohenzollerndamm ", $entity->data[0][4]); // scope name
        $this->assertEquals(143, $entity->data[0][5]); // notifications COUNT (grouped by department)
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));
        $this->assertEquals(71, $entity->data[0][0]); // department id
        $this->assertEquals('Charlottenburg-Wilmersdorf', $entity->data[0][3]); //department name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(71);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(3, count($entity->data));
    }
}
