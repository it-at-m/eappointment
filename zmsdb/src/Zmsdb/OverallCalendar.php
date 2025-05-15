<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Query\OverallCalendar as Calender;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class OverallCalendar extends Base
{
    public function insertSlot(
        int $scopeId,
        int $availabilityId,
        DateTimeInterface $time,
        int $seat,
        string $status = 'free'
    ): void {
        $this->perform(Calender::INSERT, [
            'scope_id' => $scopeId,
            'availability_id' => $availabilityId,
            'time' => $time->format('Y-m-d H:i:s'),
            'seat' => $seat,
            'status' => $status,
        ]);
    }

    public function deleteFreeRange(
        int $scopeId,
        int $availabilityId,
        DateTimeInterface $begin,
        DateTimeInterface $finish
    ): void {
        $this->perform(Calender::DELETE_FREE_RANGE, [
            'scope_id' => $scopeId,
            'availability_id' => $availabilityId,
            'begin' => $begin->format('Y-m-d H:i:s'),
            'finish' => $finish->format('Y-m-d H:i:s'),
        ]);
    }

    public function deleteOlderThan(DateTimeInterface $date): bool
    {
        return (bool) $this->perform(Calender::DELETE_ALL_BEFORE, [
            'threshold' => $date->format('Y-m-d 00:00:00'),
        ]);
    }

    public function book(
        int $scopeId,
        string $startTime,
        int $processId,
        int $slotUnits
    ): void {
        $start = new DateTimeImmutable($startTime);
        $end   = $start->add(new DateInterval('PT' . ($slotUnits * 5) . 'M'));

        $seat = $this->fetchValue(Calender::FIND_FREE_SEAT, [
            'scope' => $scopeId,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
            'units' => $slotUnits,
        ]);

        if (!$seat) {
            error_log("Failed to book a seat for scope ID {$scopeId} from {$start->format('Y-m-d H:i:s')} to {$end->format('Y-m-d H:i:s')}. No free seats available.");
            return;
        }

        $this->perform(Calender::BLOCK_SEAT_RANGE, [
            'pid'   => $processId,
            'units' => $slotUnits,
            'scope' => $scopeId,
            'seat'  => $seat,
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end  ->format('Y-m-d H:i:s'),
        ]);
    }

    public function unbook(int $scopeId, int $processId): void
    {
        $this->perform(Calender::UNBOOK_PROCESS, [
            'scope_id' => $scopeId,
            'process_id' => $processId,
        ]);
    }
}
