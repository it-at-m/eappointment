<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Query\OverallCalendar as Q;

class OverallCalendar extends Base
{
    public function insertClosed($scopeId, \DateTimeInterface $time): void
    {
        $this->perform(Q::INSERT, [
            'scope_id' => $scopeId,
            'time'     => $time->format('Y-m-d H:i:s'),
            'status'   => 'closed',
        ]);
    }

    public function existsToday($scopeId): bool
    {
        return (bool) $this->fetchValue(Q::EXISTS_TODAY, ['scope_id' => $scopeId]);
    }

    public function resetRange($scopeId, $from, $to): void
    {
        $this->perform(Q::RESET_RANGE, [
            'scope_id' => $scopeId,
            'begin'    => $from,
            'finish'   => $to,
        ]);
    }

    public function openRange($scopeId, $from, $to): void
    {
        $this->perform(Q::UPDATE_STATUS, [
            'scope_id' => $scopeId,
            'begin'    => $from,
            'finish'   => $to,
            'status'   => 'free',
        ]);
    }

    public function book(int $scopeId, string $startTime, int $processId, int $slotUnits): void
    {
        $start = new \DateTimeImmutable($startTime);

        $this->perform(Query\OverallCalendar::UPDATE_TO_BOOKED, [
            'pid'   => $processId,
            'slots' => $slotUnits,
            'scope' => $scopeId,
            'time'  => $start->format('Y-m-d H:i:s'),
        ]);

        if ($slotUnits > 1) {
            $startFollow = $start->modify('+5 minutes');
            $end         = $start->add(new \DateInterval('PT' . ($slotUnits * 5) . 'M'));

            $this->perform(Query\OverallCalendar::UPDATE_FOLLOWING_SLOTS, [
                'pid'   => $processId,
                'scope' => $scopeId,
                'start' => $startFollow->format('Y-m-d H:i:s'),
                'end'   => $end->format('Y-m-d H:i:s'),
            ]);
        }

        (new \BO\Zmsdb\Helper\CalculateSlots())->log("Booked $slotUnits slots for process $processId from $startTime");
    }

    public function unbook(int $scopeId, int $processId): void
    {
        $this->perform(Query\OverallCalendar::UNBOOK_PROCESS, [
            'scope_id'   => $scopeId,
            'process_id' => $processId,
        ]);
    }
}
