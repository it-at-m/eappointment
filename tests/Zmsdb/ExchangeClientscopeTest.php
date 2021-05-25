<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeClientscope as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeClientscopeTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity(141, new DateTime('2016-04-01'), new DateTime('2016-04-30'), 'day');
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(20, count($entity->data));
        $this->assertEquals(84, $entity->data[0][4]); // clients COUNT
        $this->assertEquals(16, $entity->data[0][5]); // clients missed COUNT
        $this->assertEquals(84, $entity->data[0][6]); // clients with appointment COUNT
        $this->assertEquals(61, $entity->data[0][8]); // requests COUNT
    }

    public function testSubjectList()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(2, count($entity->data));
        $this->assertEquals(140, $entity->data[0][0]); // scope id
        $this->assertStringContainsString('Hohenzollerndamm', $entity->data[0][3]); //scope name
    }

    public function testPeriodList()
    {
        $query = new Query();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(3, count($entity->data));
    }
}
