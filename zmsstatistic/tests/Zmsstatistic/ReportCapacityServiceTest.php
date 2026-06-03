<?php

namespace BO\Zmsstatistic\Tests;

use BO\Zmsstatistic\Service\ReportCapacityService;

class ReportCapacityServiceTest extends \PHPUnit\Framework\TestCase
{
    private ReportCapacityService $service;

    protected function setUp(): void
    {
        $this->service = new ReportCapacityService();
    }

    public function testResolveChartLabelIntervalHoursByRangeLength(): void
    {
        $oneDay = ['from' => '2026-06-01', 'to' => '2026-06-01'];
        $this->assertSame(1, $this->service->resolveChartLabelIntervalHours($oneDay, null));

        $twoDays = ['from' => '2026-06-01', 'to' => '2026-06-02'];
        $this->assertSame(2, $this->service->resolveChartLabelIntervalHours($twoDays, null));

        $fiveDays = ['from' => '2026-06-01', 'to' => '2026-06-05'];
        $this->assertSame(6, $this->service->resolveChartLabelIntervalHours($fiveDays, null));

        $tenDays = ['from' => '2026-06-01', 'to' => '2026-06-10'];
        $this->assertSame(12, $this->service->resolveChartLabelIntervalHours($tenDays, null));

        $twentyDays = ['from' => '2026-06-01', 'to' => '2026-06-20'];
        $this->assertNull($this->service->resolveChartLabelIntervalHours($twentyDays, null));
    }

    public function testShouldFetchHourlyUpToFourteenDays(): void
    {
        $range = ['from' => '2026-06-01', 'to' => '2026-06-14'];
        $this->assertTrue($this->service->shouldFetchHourlyFromApi($range, null));

        $range = ['from' => '2026-06-01', 'to' => '2026-06-15'];
        $this->assertFalse($this->service->shouldFetchHourlyFromApi($range, null));
    }
}
