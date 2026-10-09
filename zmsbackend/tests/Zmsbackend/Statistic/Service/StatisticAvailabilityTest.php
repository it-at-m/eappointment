<?php

namespace BO\Zmsbackend\Tests\Statistic\Service;

use BO\Zmsbackend\Statistic\Service\StatisticAvailability;

class StatisticAvailabilityTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public function testCheckReturnsStructuredResult(): void
    {
        $date = new \DateTimeImmutable('2016-04-01');
        
        $result = (new StatisticAvailability())->check($date);

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('checkedDate', $result);
        $this->assertArrayHasKey('checkedAt', $result);
        $this->assertArrayHasKey('statisticsChecked', $result);
        $this->assertArrayHasKey('statisticsMissing', $result);
        $this->assertArrayHasKey('missing', $result);

        $this->assertSame('2016-04-01', $result['checkedDate']);
        $this->assertIsString($result['status']);
        $this->assertIsInt($result['statisticsChecked']);
        $this->assertIsInt($result['statisticsMissing']);
        $this->assertIsArray($result['missing']);
    }

    public function testAvailableWaitingStatisticIsHealthy(): void
    {
        $date = new \DateTimeImmutable('2016-03-01');
        $result = (new StatisticAvailability())->check($date);

        $this->assertSame('ok', $result['status']);
        $this->assertSame(1, $result['statisticsChecked']);
        $this->assertSame(0, $result['statisticsMissing']);
        $this->assertSame([], $result['missing']);
    }

    public function testMissingWaitingStatisticIsReported(): void
    {
        $date = new \DateTimeImmutable('2016-03-01');

        \BO\Zmsbackend\Cli\Db::executeSql(
            "DELETE FROM wartenrstatistik
            WHERE standortid = 141
            AND datum = '2016-03-01'"
        );

        $result = (new StatisticAvailability())->check($date);

        $this->assertSame('error', $result['status']);
        $this->assertSame(1, $result['statisticsMissing']);

        $this->assertContains(
            [
                'statistic' => 'waitingscope',
                'scopeId' => '141',
                'scopeName'=> $result['missing'][0]['scopeName'],
            ],
            $result['missing']
        );
        
    }
}