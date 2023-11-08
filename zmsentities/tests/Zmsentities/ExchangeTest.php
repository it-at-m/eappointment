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

    public function testHashed()
    {
        $entity = (new $this->entityclass())->getExample()->toHashed();
        $this->assertEquals('test', $entity['data'][0]['name']);
        $this->assertEquals('test2', $entity['data'][1]['name']);
        $this->assertEquals('day', $entity['period']);
    }

    public function testWithCalculatedTotals()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity->addDictionaryEntry('id', 'number');
        $entity->addDictionaryEntry('date', 'date');
        $entity->addDictionaryEntry('name', 'string', 'Naming');
        $entity->addDictionaryEntry('count', 'number', 'value');
        $entity->addDictionaryEntry('count2', 'number', 'value');
        $entity->addDataSet([1, '2016-04-01', 'Test', 1, 2]);
        $entity->addDataSet([2, '2016-04-02', 'Test', 2, 3]);
        $entity->addDataSet([3, '2016-04-03', 'Test', 4, 5]);
        $this->assertEquals('totals', $entity->withCalculatedTotals(['count','count2'])->getCalculatedTotals()[2]);
        $this->assertEquals('7', $entity->withCalculatedTotals(['count','count2'])->getCalculatedTotals()[3]);
        $this->assertEquals('10', $entity->withCalculatedTotals(['count','count2'])->getCalculatedTotals()[4]);
        $entity = $entity->withCalculatedTotals(['count','count2'])->toHashed();
        $this->assertEquals(
            7,
            $entity['data'][3]['count']
        );
        $this->assertEquals(
            10,
            $entity['data'][3]['count2']
        );
    }

    public function testWithoutCalculatedTotals()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        /** @var \BO\Zmsentities\Exchange $entity */
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity->addDictionaryEntry('id', 'number');
        $entity->addDictionaryEntry('date', 'date');
        $entity->addDictionaryEntry('wrongname', 'string', 'Naming');
        $entity->addDictionaryEntry('count', 'number', 'value');
        $entity->addDataSet([1, '2016-04-01', 'Test', 1]);
        $entity->addDataSet([2, '2016-04-02', 'Test', 2]);
        $this->assertTrue(null === $entity->withCalculatedTotals(['count'])->getCalculatedTotals());
    }

    public function testGroupedWithMaxByHours()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now->modify('+1day'), 'day');
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope');
        $entity->addDictionaryEntry('date', 'string', 'date of report entry');
        $entity->addDictionaryEntry('hour', 'string', 'hour of report entry');
        $entity->addDictionaryEntry('waitingcount', 'number', 'amount of waiting clients');
        $entity->addDictionaryEntry('waitingtime', 'number', 'real waitingtime');
        $entity->addDictionaryEntry('waitingcalculated', 'number', 'calculated waitingtime');

        $entity->addDataSet([141, '2016-04-01', '8', 1, 2, 3]);
        $entity->addDataSet([141, '2016-04-01', '9', 2, 3, 4]);
        $entity->addDataSet([141, '2016-04-02', '8', 3, 7, 10]);
        $entity->addDataSet([141, '2016-04-02', '9', 5, 8, 11]);
        $entity = $entity
            ->toGrouped(['date','hour'], ['waitingcount','waitingtime','waitingcalculated'])
            ->withMaxByHour(['waitingcount','waitingtime','waitingcalculated'])
            ->withMaxAndAverageFromWaitingTime();
        $this->assertArrayHasKey('2016-04-01', $entity->data);
        $this->assertArrayHasKey('max', $entity->data);
        $this->assertEquals(7, $entity->data['max'][8]['waitingtime']);
        $this->assertEquals(5, $entity->data['max'][9]['waitingcount']);
    }

    public function testGroupedWithRequestsSum()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now->modify('+1day'), 'day');
        $entity->addDictionaryEntry('subjectid', 'string', 'ID of a scope');
        $entity->addDictionaryEntry('date', 'string', 'Date of entry');
        $entity->addDictionaryEntry('name', 'string', 'Name of request');
        $entity->addDictionaryEntry('requestscount', 'number', 'Amount of requests');

        $entity->addDataSet([141, '2016-04-01', 'Dienstleistung wurde nicht erfasst', 25]);
        $entity->addDataSet([141, '2016-04-01', 'Dienstleistung konnte nicht erbracht werden', 2]);
        $entity->addDataSet([141, '2016-04-01', 'Personalausweis beantragen', 14]);
        $entity->addDataSet([141, '2016-04-02', 'Personalausweis beantragen', 17]);

        $entity = $entity
            ->toGrouped(['name','date'], ['requestscount'])
            ->withRequestsSum();
        $this->assertArrayHasKey('sum', $entity->data);
        $this->assertEquals(31, $entity->data['sum']['Personalausweis beantragen']);
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
        $this->expectException('\Exception');
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
        $this->expectException('\Exception');
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
        $this->assertStringNotContainsString('firstDay', $entity->withLessData());
    }

    public function testSchemaIntegerAsString()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity['dictionary'][0]['position'] = 'test';
        $entity->testValid();
    }

    public function testSchemaIntegerAsBool()
    {
        $now = new \DateTimeImmutable('2016-04-01 11:55:00');
        $entity = (new $this->entityclass());
        $entity->setPeriod($now, $now);
        $entity['dictionary'][0]['position'] = false;
        $entity->testValid();
        $this->assertEquals(0, $entity['dictionary'][0]['position']);
    }

    public function testSchemaStringAsNull()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $entity = (new $this->entityclass());
        $entity->period = null;
        $entity->testValid();
    }

    public function testSchemaStringAsBool()
    {
        $this->expectException('\BO\Zmsentities\Exception\SchemaValidation');
        $entity = (new $this->entityclass());
        $entity->period = true;
        $entity->testValid();
    }
}
