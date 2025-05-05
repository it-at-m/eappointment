<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Exception\OverallCalendar\Conflict;
use BO\Zmsdb\Query\OverallCalendar as Calender;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;

class OverallCalendar extends Base
{
    public function insertSlot(
        int $scopeId,
        DateTimeInterface $time,
        int $seat,
        string $status = 'free'
    ): void {
        $this->perform(Calender::INSERT, [
            'scope_id' => $scopeId,
            'time' => $time->format('Y-m-d H:i:s'),
            'seat' => $seat,
            'status' => $status,
        ]);
    }


    public function deleteFreeRange($scopeId, $from, $to): void
    {
        $this->perform(Calender::DELETE_FREE_RANGE, [
            'scope_id' => $scopeId,
            'begin' => $from,
            'finish' => $to,
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
            throw new Conflict();
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
