<?php

namespace BO\Zmsbackend\Tests\Exchange\Service;

use BO\Zmsbackend\Exchange\Service\ExchangeCapacityscope as Query;

class ExchangeCapacityscopeTest extends \BO\Zmsbackend\Tests\Service\Base
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
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) $byScope[141][1]);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', (string) $byScope[141][2]);
        $this->assertGreaterThanOrEqual((string) $byScope[141][1], (string) $byScope[141][2]);
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
