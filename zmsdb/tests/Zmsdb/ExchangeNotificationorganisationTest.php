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
        $this->assertEquals(2, count($entity->data)); // all departments in the org, including those with 0 SMS sent
        $this->assertEquals(71, $entity->data[0][0]); // organisation id
        $this->assertEquals("2016-04-01", $entity->data[1][1]); // date
        $this->assertEquals("Charlottenburg-Wilmersdorf", $entity->data[0][2]); // organisation name
        $this->assertEquals("Bürgeramt", $entity->data[1][3]); // department name
        $this->assertEquals("Bürgeramt Hohenzollerndamm ", $entity->data[1][4]); // scope name
        $this->assertEquals(143, $entity->data[1][5]); // notifications COUNT (grouped by department)
        // Also departments without any sms sent appear in list
        $this->assertEquals("Ordnungsamt", $entity->data[0][3]); // department name
        $this->assertEquals("Ordnungsamt Charlottenburg-Wilmersdorf (Zentrale Anlauf- und Beratungsstelle) ", $entity->data[0][4]); // scope name
        $this->assertEquals(0, $entity->data[0][5]); // notifications COUNT (grouped by department)
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
