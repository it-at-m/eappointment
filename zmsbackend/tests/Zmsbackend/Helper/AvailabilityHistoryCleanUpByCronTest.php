<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Availability\Service\AvailabilityHistory;
use BO\Zmsbackend\Helper\AvailabilityHistoryCleanUpByCron;

class AvailabilityHistoryCleanUpByCronTest extends \BO\Zmsbackend\Tests\Service\Base
{
    public const SCOPE_ID = 141;

    public function testConstructor()
    {
        $helper = new AvailabilityHistoryCleanUpByCron();
        $this->assertInstanceOf(AvailabilityHistoryCleanUpByCron::class, $helper);
    }

    public function testStartProcessingDeletesOldRows()
    {
        $service = new AvailabilityHistory();
        $this->seedRow($service, '2010-01-01 12:00:00', 'old-row');
        $this->seedRow($service, '2016-03-15 12:00:00', 'recent-row');

        $helper = new AvailabilityHistoryCleanUpByCron();
        $deletedDryRun = $helper->startProcessing(false);
        $this->assertSame(0, $deletedDryRun);
        $this->assertSame(2, $this->countSeededRows($service));

        $deleted = $helper->startProcessing(true);
        $this->assertGreaterThanOrEqual(1, $deleted);
        $this->assertSame(1, $this->countSeededRows($service));
        $this->assertSame(1, $this->countSeededRows($service, 'recent-row'));
        $this->assertSame(0, $this->countSeededRows($service, 'old-row'));
    }

    protected function seedRow(AvailabilityHistory $service, string $changedAt, string $changedBy): void
    {
        $service->perform(
            'INSERT INTO availability_history
                (scope_id, availability_id, action, weekday, series, valid_from, valid_to,
                 time_range, type, slot_time, workstations, bookable, description,
                 changed_at, changed_by)
             VALUES
                (:scopeId, :availabilityId, :action, :weekday, :series, :validFrom, :validTo,
                 :timeRange, :type, :slotTime, :workstations, :bookable, :description,
                 :changedAt, :changedBy)',
            [
                'scopeId' => self::SCOPE_ID,
                'availabilityId' => 68985,
                'action' => 'updated',
                'weekday' => 2,
                'series' => 'jede Woche',
                'validFrom' => '01.01.2016',
                'validTo' => '31.12.2016',
                'timeRange' => '07:00 - 17:00',
                'type' => 'Terminkunden',
                'slotTime' => '15min',
                'workstations' => '2/0',
                'bookable' => '0-60',
                'description' => 'Test',
                'changedAt' => $changedAt,
                'changedBy' => $changedBy,
            ]
        );
    }

    protected function countSeededRows(AvailabilityHistory $service, ?string $changedBy = null): int
    {
        if ($changedBy === null) {
            $value = $service->fetchValue(
                'SELECT COUNT(*) FROM availability_history
                 WHERE scope_id = :scopeId AND changed_by IN ("old-row", "recent-row")',
                ['scopeId' => self::SCOPE_ID]
            );
        } else {
            $value = $service->fetchValue(
                'SELECT COUNT(*) FROM availability_history
                 WHERE scope_id = :scopeId AND changed_by = :changedBy',
                [
                    'scopeId' => self::SCOPE_ID,
                    'changedBy' => $changedBy,
                ]
            );
        }

        return (int) $value;
    }
}
