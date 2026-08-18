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
                (scope_id, availability_id, action, start_date, end_date, every_x_weeks, every_other_week,
                 weekday, start_time, appointment_start_time, end_time, appointment_end_time, time_slot,
                 workstation_count, appointment_workstation_count, comment, internet_reduction,
                 multiple_slots_allowed, open_from_days, open_until_days, version, changed_at, changed_by)
             VALUES
                (:scopeId, :availabilityId, :action, :startDate, :endDate, :everyXWeeks, :everyOtherWeek,
                 :weekday, :startTime, :appointmentStartTime, :endTime, :appointmentEndTime, :timeSlot,
                 :workstationCount, :appointmentWorkstationCount, :comment, :internetReduction,
                 :multipleSlotsAllowed, :openFromDays, :openUntilDays, :version, :changedAt, :changedBy)',
            [
                'scopeId' => self::SCOPE_ID,
                'availabilityId' => 68985,
                'action' => 'updated',
                'startDate' => '2016-01-01',
                'endDate' => '2016-12-31',
                'everyXWeeks' => 1,
                'everyOtherWeek' => 0,
                'weekday' => 2,
                'startTime' => '00:00:00',
                'appointmentStartTime' => '07:00:00',
                'endTime' => '00:00:00',
                'appointmentEndTime' => '17:00:00',
                'timeSlot' => '00:15:00',
                'workstationCount' => 0,
                'appointmentWorkstationCount' => 2,
                'comment' => 'Test',
                'internetReduction' => 2,
                'multipleSlotsAllowed' => 0,
                'openFromDays' => 0,
                'openUntilDays' => 60,
                'version' => 1,
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
