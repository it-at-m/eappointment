<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\ExchangeCapacityscope as Query;

class ExchangeCapacityscopeTest extends Base
{
    public function testSubjectListIncludesScope141()
    {
        $query = new Query();
        $entity = $query->readSubjectList();
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertGreaterThanOrEqual(44, count($entity->data));

        $byScope = [];
        foreach ($entity->data as $row) {
            $byScope[(int) $row[0]] = $row;
        }

        $this->assertArrayHasKey(141, $byScope);
        $this->assertEquals('2016-03-15', $byScope[141][1]);
        $this->assertEquals('2016-04-02', $byScope[141][2]);
    }

    public function testReadEntityWithDateFilter()
    {
        $query = new Query();
        $entity = $query->readEntity(
            '141',
            new \DateTimeImmutable('2016-04-01'),
            new \DateTimeImmutable('2016-04-30'),
            'day'
        );
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertGreaterThan(0, count($entity->data));

        foreach ($entity->data as $row) {
            $day = substr((string) $row[1], 0, 10);
            $this->assertGreaterThanOrEqual('2016-04-01', $day);
            $this->assertLessThanOrEqual('2016-04-30', $day);
        }
    }

    public function testPeriod()
    {
        $query = new Query();
        $entity = $query->readPeriodList(141);
        $this->assertEntity("\\BO\\Zmsentities\\Exchange", $entity);
        $this->assertEquals('Terminkapazität ', $entity->title);
        $this->assertEquals('day', $entity->period);
    }
}
