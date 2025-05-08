<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Availability;
use BO\Zmsdb\Connection\Select;
use BO\Zmsdb\OverallCalendar;
use BO\Zmsdb\Scope;
use DateTimeInterface;

class PopulateOverallCalendar extends CalculateSlots
{
    private const MAX_DAYS = 180;
    private $cal;

    public function __construct($verbose = false)
    {
        parent::__construct($verbose);
        $this->cal = new OverallCalendar();
    }


    public function writeCalendar(DateTimeInterface $now): void
    {
        $this->log('PopulateOverallCalendar gestartet ' . $now->format('c'));

        foreach ((new Scope())->readList() as $scope) {
            $this->updateFreeByAvailabilities($scope->id, $now);
        }
        Select::writeCommit();
        $this->log('PopulateOverallCalendar beendet');
    }

    private function updateFreeByAvailabilities(
        int $scopeId,
        DateTimeInterface $now
    ): void {

        $from = $now->setTime(0, 0)->format('Y-m-d H:i:s');
        $to   = (clone $now)->modify('+' . self::MAX_DAYS . ' days 23:59:59')
            ->format('Y-m-d H:i:s');

        $this->cal->deleteFreeRange($scopeId, $from, $to);

        $availList = (new Availability())
            ->readAvailabilityListByScope(new \BO\Zmsentities\Scope(['id' => $scopeId]));

        foreach ($availList as $availability) {
            $cursor = $availability->getBookableStart($now);
            $endAll = $availability->getBookableEnd($now);

            while ($cursor <= $endAll) {
                if ($availability->hasDate($cursor, $now)) {
                    $slot     = $cursor->setTime(...explode(':', $availability->startTime));
                    $slotEnd  = $cursor->setTime(...explode(':', $availability->endTime));
                    $maxSeat  = (int)$availability->workstationCount['intern'];

                    while ($slot < $slotEnd) {
                        for ($seat = 1; $seat <= $maxSeat; $seat++) {
                            $this->cal->insertSlot($scopeId, $slot, $seat);
                        }
                        $slot = $slot->modify('+5 minutes');
                    }
                }
                $cursor = $cursor->modify('+1 day');
            }
        }

        $this->log("Freie Slots (inkl. Seats) für Scope $scopeId neu aufgebaut");
    }
}
