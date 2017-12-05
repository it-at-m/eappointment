<?php

namespace BO\Zmsentities\Tests;

class ExchangeTest extends EntityCommonTests
{
    const FIRST_DAY = '2015-11-19';

    const LAST_DAY = '2015-12-31';

    public $entityclass = '\BO\Zmsentities\Exchange';

    public function testBasic()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertTrue($entity->isValid());
    }

    public function testPeriod()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now, 'MONTH');
        $this->assertEquals(2016, $entity->firstDay->year);
        $this->assertEquals(4, $entity->lastDay->month);
        $this->assertEquals('MONTH', $entity->period);
    }

    public function testDictionary()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity->addDictionaryEntry('id', 'number');
        $this->assertEquals(1, count($entity['dictionary']));
        $this->assertEquals(0, $entity['dictionary'][0]['position']);
        $this->assertEquals('id', $entity['dictionary'][0]['variable']);
        $entity->addDictionaryEntry('date', 'date');
        $this->assertEquals(2, count($entity['dictionary']));
        $this->assertEquals(1, $entity['dictionary'][1]['position']);
        $this->assertEquals('date', $entity['dictionary'][1]['type']);
        $entity->addDictionaryEntry('name', 'string', 'Naming');
        $this->assertEquals(3, count($entity['dictionary']));
        $this->assertEquals(2, $entity['dictionary'][2]['position']);
        $this->assertEquals('Naming', $entity['dictionary'][2]['description']);
    }

    public function testData()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity->addDictionaryEntry('id', 'number');
        $entity->addDictionaryEntry('date', 'date');
        $entity->addDictionaryEntry('name', 'string', 'Naming');
        $entity->addDataSet([1, '2016-04-01', 'Test']);
        $this->assertEquals(1, count($entity->data));
    }

    public function testDataFormat()
    {
        $this->setExpectedException('\Exception');
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity->addDictionaryEntry('id', 'number');
        $entity->addDictionaryEntry('date', 'date');
        $entity->addDictionaryEntry('name', 'string', 'Naming');
        $entity->addDataSet('2016-04-01');
    }

    public function testDataLength()
    {
        $this->setExpectedException('\Exception');
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity->addDictionaryEntry('id', 'number');
        $entity->addDictionaryEntry('date', 'date');
        $entity->addDictionaryEntry('name', 'string', 'Naming');
        $entity->addDataSet([1, '2016-04-01']);
    }

    public function testWithLessData()
    {
        $entity = (new $this->entityclass())->getExample();
        $this->assertNotContains('firstDay', $entity->withLessData());
    }
}
