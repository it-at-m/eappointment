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

    public function testAggregateRowsByDateSumsPerScopeMinutesWithDifferentSlotLengths(): void
    {
        $rows = [
            ['141', '2026-06-01', 10, 20, 100, 200],
            ['142', '2026-06-01', 4, 8, 80, 160],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, false);

        $this->assertCount(1, $aggregated);
        $this->assertSame(14, $aggregated[0][2]);
        $this->assertSame(28, $aggregated[0][3]);
        $this->assertSame(180, $aggregated[0][4]);
        $this->assertSame(360, $aggregated[0][5]);
    }

    public function testAggregateRowsByDateUsesEachScopeSlotTimeBeforeSummingMinutes(): void
    {
        $rows = [
            ['141', '2026-06-01 08:00', 2, 4, 30, 60],
            ['142', '2026-06-01 08:00', 2, 4, 50, 100],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, true);

        $this->assertCount(1, $aggregated);
        $this->assertSame('2026-06-01 08:00', $aggregated[0][1]);
        $this->assertSame(4, $aggregated[0][2]);
        $this->assertSame(8, $aggregated[0][3]);
        $this->assertSame(80, $aggregated[0][4]);
        $this->assertSame(160, $aggregated[0][5]);
        $this->assertNotSame($aggregated[0][2] * 15, $aggregated[0][4]);
        $this->assertNotSame($aggregated[0][3] * 15, $aggregated[0][5]);
    }

    public function testAggregateRowsByDateSumsDailyMinutesAcrossScopesOnSameDate(): void
    {
        $rows = [
            ['141', '2016-04-01', 10, 20, 100, 200],
            ['142', '2016-04-01', 5, 10, 50, 100],
            ['141', '2016-04-02', 15, 25, 150, 250],
            ['142', '2016-04-02', 5, 10, 50, 100],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, false);

        $this->assertCount(2, $aggregated);
        $this->assertSame(15, $aggregated[0][2]);
        $this->assertSame(30, $aggregated[0][3]);
        $this->assertSame(150, $aggregated[0][4]);
        $this->assertSame(300, $aggregated[0][5]);
        $this->assertSame(20, $aggregated[1][2]);
        $this->assertSame(35, $aggregated[1][3]);
        $this->assertSame(200, $aggregated[1][4]);
        $this->assertSame(350, $aggregated[1][5]);
    }

    public function testFormatScopeSlotTimeHintForSingleScope(): void
    {
        $hint = $this->service->formatScopeSlotTimeHint([
            ['id' => '141', 'name' => 'Bürgeramt Heerstraße', 'slotTimeInMinutes' => 10],
        ]);

        $this->assertSame('Zeitschlitzdauer laut Öffnungszeit: 10 Min.', $hint);
    }

    public function testFormatScopeSlotTimeHintForMultipleScopesWithSameSlotTime(): void
    {
        $hint = $this->service->formatScopeSlotTimeHint([
            ['id' => '141', 'name' => 'Bürgeramt Heerstraße', 'slotTimeInMinutes' => 10],
            ['id' => '142', 'name' => 'Bürgeramt Wilmersdorfer Straße', 'slotTimeInMinutes' => 10],
        ]);

        $this->assertSame(
            'Zeitschlitzdauer laut Öffnungszeit: 10 Min. (alle ausgewählten Standorte)',
            $hint
        );
    }

    public function testFormatScopeSlotTimeHintForMultipleScopesWithDifferentSlotTimes(): void
    {
        $hint = $this->service->formatScopeSlotTimeHint([
            ['id' => '141', 'name' => 'Bürgeramt Heerstraße', 'slotTimeInMinutes' => 10],
            ['id' => '142', 'name' => 'Bürgeramt Wilmersdorfer Straße', 'slotTimeInMinutes' => 15],
        ]);

        $this->assertSame(
            'Zeitschlitzdauer laut Öffnungszeit: Bürgeramt Heerstraße: 10 Min.; Bürgeramt Wilmersdorfer Straße: 15 Min.',
            $hint
        );
    }

    public function testFormatScopeSlotTimeHintGroupsManyScopesByDuration(): void
    {
        $scopeSlotTimes = [
            ['id' => '1', 'name' => 'Standort A', 'slotTimeInMinutes' => 5],
            ['id' => '2', 'name' => 'Standort B', 'slotTimeInMinutes' => 5],
            ['id' => '3', 'name' => 'Standort C', 'slotTimeInMinutes' => 10],
            ['id' => '4', 'name' => 'Standort D', 'slotTimeInMinutes' => 15],
            ['id' => '5', 'name' => 'Standort E', 'slotTimeInMinutes' => 90],
        ];

        $hint = $this->service->formatScopeSlotTimeHint($scopeSlotTimes);

        $this->assertSame(
            'Zeitschlitzdauer laut Öffnungszeit: 5 Min. (2 Standorte), 10 Min. (1 Standort), 15 Min. (1 Standort), 90 Min. (1 Standort)',
            $hint
        );
    }
}
