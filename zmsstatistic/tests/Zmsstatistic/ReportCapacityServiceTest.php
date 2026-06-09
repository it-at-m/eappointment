<?php

namespace BO\Zmsstatistic\Tests;

use BO\Zmsentities\Exchange;
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
            ['141', '2026-06-01', 10, 20, 100, 200, 6, 12, 60, 120],
            ['142', '2026-06-01', 4, 8, 80, 160, 2, 4, 40, 80],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, false);

        $this->assertCount(1, $aggregated);
        $this->assertSame(14, $aggregated[0][2]);
        $this->assertSame(28, $aggregated[0][3]);
        $this->assertSame(180, $aggregated[0][4]);
        $this->assertSame(360, $aggregated[0][5]);
        $this->assertSame(8, $aggregated[0][6]);
        $this->assertSame(16, $aggregated[0][7]);
        $this->assertSame(100, $aggregated[0][8]);
        $this->assertSame(200, $aggregated[0][9]);
    }

    public function testAggregateRowsByDateUsesEachScopeSlotTimeBeforeSummingMinutes(): void
    {
        $rows = [
            ['141', '2026-06-01 08:00', 2, 4, 30, 60, 1, 2, 15, 30],
            ['142', '2026-06-01 08:00', 2, 4, 50, 100, 1, 2, 25, 50],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, true);

        $this->assertCount(1, $aggregated);
        $this->assertSame('2026-06-01 08:00', $aggregated[0][1]);
        $this->assertSame(4, $aggregated[0][2]);
        $this->assertSame(8, $aggregated[0][3]);
        $this->assertSame(80, $aggregated[0][4]);
        $this->assertSame(160, $aggregated[0][5]);
        $this->assertSame(2, $aggregated[0][6]);
        $this->assertSame(4, $aggregated[0][7]);
        $this->assertNotSame($aggregated[0][2] * 15, $aggregated[0][4]);
        $this->assertNotSame($aggregated[0][3] * 15, $aggregated[0][5]);
    }

    public function testAggregateRowsByDateSumsDailyMinutesAcrossScopesOnSameDate(): void
    {
        $rows = [
            ['141', '2016-04-01', 10, 20, 100, 200, 6, 12, 60, 120],
            ['142', '2016-04-01', 5, 10, 50, 100, 2, 4, 20, 40],
            ['141', '2016-04-02', 15, 25, 150, 250, 9, 15, 90, 150],
            ['142', '2016-04-02', 5, 10, 50, 100, 2, 4, 20, 40],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, false);

        $this->assertCount(2, $aggregated);
        $this->assertSame(15, $aggregated[0][2]);
        $this->assertSame(30, $aggregated[0][3]);
        $this->assertSame(150, $aggregated[0][4]);
        $this->assertSame(300, $aggregated[0][5]);
        $this->assertSame(8, $aggregated[0][6]);
        $this->assertSame(16, $aggregated[0][7]);
        $this->assertSame(20, $aggregated[1][2]);
        $this->assertSame(35, $aggregated[1][3]);
        $this->assertSame(200, $aggregated[1][4]);
        $this->assertSame(350, $aggregated[1][5]);
        $this->assertSame(11, $aggregated[1][6]);
        $this->assertSame(19, $aggregated[1][7]);
    }

    public function testAggregateHourlyRowsToDailySumsAllHours(): void
    {
        $rows = [
            ['141', '2026-06-09 08:00', 1, 12, 10, 120, 0, 6, 0, 60],
            ['141', '2026-06-09 09:00', 2, 12, 20, 120, 0, 6, 0, 60],
            ['141', '2026-06-09 18:00', 0, 6, 0, 60, 0, 6, 0, 60],
        ];

        $aggregated = $this->service->aggregateRowsByDate($rows, false);

        $this->assertCount(1, $aggregated);
        $this->assertSame('2026-06-09', $aggregated[0][1]);
        $this->assertSame(3, $aggregated[0][2]);
        $this->assertSame(30, $aggregated[0][3]);
        $this->assertSame(30, $aggregated[0][4]);
        $this->assertSame(300, $aggregated[0][5]);
    }

    public function testLongRangeChartModesUseDailyTimelineWithMatchingSums(): void
    {
        $range = ['from' => '2026-06-08', 'to' => '2026-06-23'];
        $rows = [];

        for ($day = 9; $day <= 10; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $planned = ($hour >= 8 && $hour < 16) ? 12 : 6;
                $rows[] = [
                    '141',
                    sprintf('2026-06-%02d %02d:00', $day, $hour),
                    0,
                    $planned,
                    0,
                    $planned * 10,
                    0,
                    $planned,
                    0,
                    $planned * 10,
                ];
            }
        }

        $exchange = new Exchange();
        $exchange->data = $this->service->aggregateRowsByDate($rows, false);
        $exchange->period = 'day';
        $exchange->dictionary = [
            ['position' => 2, 'variable' => 'bookedcount'],
            ['position' => 3, 'variable' => 'plannedcount'],
        ];

        $sparse = $this->service->buildSparseChartExchange($exchange, $range, null);
        $full = $this->service->buildChartExchange($exchange, $range, null);

        $this->assertFalse($this->service->shouldFetchHourlyFromApi($range, null));
        $this->assertSame('2026-06-09', $sparse->data[0][1]);
        $this->assertSame('2026-06-08', $full->data[0][1]);
        $this->assertCount(16, $full->data);
        $this->assertSame(192, $sparse->data[0][3]);
        $this->assertSame(192, $full->data[1][3]);
        $this->assertSame(0, $full->data[0][3]);
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
