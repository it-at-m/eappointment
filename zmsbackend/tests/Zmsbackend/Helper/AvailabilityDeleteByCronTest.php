<?php

namespace BO\Zmsbackend\Tests\Helper;

use \BO\Zmsbackend\Helper\AvailabilityDeleteByCron;
use \BO\Zmsbackend\Availability\Service\Availability;

class AvailabilityDeleteByCronTest extends \BO\Zmsbackend\Tests\Service\Base
{

    public function testConstructor()
    {
        $availabilityObject = new AvailabilityDeleteByCron();
        $this->assertInstanceOf(AvailabilityDeleteByCron::class, $availabilityObject);
    }

    public function testStartProcessingNoCommit()
    {
        $availabilityDelete = new AvailabilityDeleteByCron($verbose = false); // verbose
        $entity = new Availability();
        $now = new \DateTimeImmutable('2016-05-01 11:55');
        $datetime = $now->modify('- 4 weeks');
        $availabilityUnits = count($entity->readAvailabilityListBefore($datetime));
        $availabilityDelete->startProcessing($datetime, $commit = false);
        $this->assertEquals(33, $availabilityUnits);
     
        $availabilityDelete->startProcessing($datetime, $commit = true);
        $availabilityUnits = count($entity->readAvailabilityListBefore($datetime));
        $this->assertEquals(0, $availabilityUnits);
    }

    public function testDeletesExpiredOpeningHours()
    {
        $availability = new Availability();
        $expiredId = 990301;
        $currentId = 990302;
        $this->insertOpeningHours($availability, $expiredId, '2016-01-01', '2016-03-01');
        $this->insertOpeningHours($availability, $currentId, '2016-04-02', '2016-06-30');

        $cutoff = (new \DateTimeImmutable('2016-05-01 11:55'))->modify('-30 days');
        $job = new AvailabilityDeleteByCron(false);
        $job->startProcessing($cutoff, false);
        $this->assertSame(1, $this->countAvailability($availability, $expiredId));
        $this->assertSame(1, $this->countAvailability($availability, $currentId));

        $job->startProcessing($cutoff, true);
        $this->assertSame(0, $this->countAvailability($availability, $expiredId));
        $this->assertSame(1, $this->countAvailability($availability, $currentId));
    }

    private function insertOpeningHours(
        Availability $availability,
        int $id,
        string $startDate,
        string $endDate
    ): void {
        $availability->perform(
            'INSERT INTO oeffnungszeit (
                OeffnungszeitID, scope_id, start_date, end_date, every_x_weeks, every_other_week, weekday,
                start_time, appointment_start_time, end_time, appointment_end_time, time_slot,
                workstation_count, appointment_workstation_count, comment, internet_reduction,
                multiple_slots_allowed, open_from_days, open_until_days, version
            ) VALUES (
                :id, :scopeId, :startDate, :endDate, 1, 0, 62,
                :startTime, :appointmentStartTime, :endTime, :appointmentEndTime, :timeSlot,
                0, 0, :comment, 0, 0, 0, 0, 1
            )',
            [
                'id' => $id,
                'scopeId' => 141,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'startTime' => '08:00:00',
                'appointmentStartTime' => '00:00:00',
                'endTime' => '12:00:00',
                'appointmentEndTime' => '00:00:00',
                'timeSlot' => '00:10:00',
                'comment' => 'cron-opening-hours-test',
            ]
        );
    }

    private function countAvailability(Availability $availability, int $id): int
    {
        return (int) $availability->fetchValue(
            'SELECT COUNT(*) FROM oeffnungszeit WHERE OeffnungszeitID = :id',
            ['id' => $id]
        );
    }
}
