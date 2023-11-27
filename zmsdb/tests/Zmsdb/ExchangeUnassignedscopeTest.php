<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\ExchangeUnassignedscope as Query;
use \BO\Zmsentities\Exchange;
use \DateTimeImmutable as DateTime;

class ExchangeUnassignedscopeTest extends Base
{
    public function testBasic()
    {
        $query = new Query();
        $entity = $query->readEntity();
        $this->assertEquals('Nicht der DLDB zugeordnete Standorte mit Terminen', $entity->title);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(0, count($entity->data));
        $this->assertEquals('TerminAnzahl', $entity->dictionary[2]['variable']);
        $this->assertEquals('TerminDaten', $entity->dictionary[3]['variable']);
    }

    public function testSubject()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEquals('Nicht der DLDB zugeordnete Standorte mit Terminen', $entity->title);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals(1, count($entity->data));

    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals("Nicht der DLDB zugeordnete Standorte mit Terminen", $entity->title);
        $this->assertEquals("day", $entity->period);
    }
}
