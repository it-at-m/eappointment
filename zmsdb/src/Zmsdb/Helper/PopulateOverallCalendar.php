<?php
namespace BO\Zmsdb\Helper;

use BO\Zmsdb\OverallCalendar;
use BO\Zmsdb\Query\OverallCalendar as Q;
use BO\Zmsentities\Availability;

class PopulateOverallCalendar extends CalculateSlots
{
    private const MAX_DAYS = 180;
    private $cal;

    public function __construct($verbose = false)
    {
        parent::__construct($verbose);
        $this->cal = new OverallCalendar();
    }


    public function writeCalendar(\DateTimeInterface $now): void
    {
        $pdo = \BO\Zmsdb\Connection\Select::getWriteConnection();
        $this->log('PopulateOverallCalendar gestartet ' . $now->format('c'));

        foreach ((new \BO\Zmsdb\Scope())->readList() as $scope) {
            $this->writeClosedRaster($scope->id, $now);
            $this->updateFreeByAvailabilities($scope->id, $now);
        }
        \BO\Zmsdb\Connection\Select::writeCommit();
        $this->log('PopulateOverallCalendar beendet');
    }


    private function writeClosedRaster(int $scopeId, \DateTimeInterface $now): void
    {
        if ($this->cal->existsToday($scopeId)) {
            return;
        }
        $start = (new \DateTimeImmutable('today', $now->getTimezone()))->setTime(0,0);
        for ($i = 0; $i < 288; $i++) {
            $slotTime = $start->add(new \DateInterval('PT' . ($i*5) . 'M'));
            $this->cal->insertClosed($scopeId, $slotTime);
        }
        $this->log("Raster 'closed' für Scope $scopeId erzeugt");
    }


    private function updateFreeByAvailabilities(int $scopeId, \DateTimeInterface $now): void
    {
        $from = $now->setTime(0,0)->format('Y-m-d H:i:s');
        $to   = (clone $now)->modify('+'.self::MAX_DAYS.' days 23:59:59')
            ->format('Y-m-d H:i:s');

        $this->cal->resetRange($scopeId, $from, $to);

        $availList = (new \BO\Zmsdb\Availability())
            ->readAvailabilityListByScope(
                (new \BO\Zmsentities\Scope(['id'=>$scopeId]))
            );

        foreach ($availList as $a) {
            $cursor = $a->getBookableStart($now);
            $endAll = $a->getBookableEnd($now);

            while ($cursor <= $endAll) {
                if ($a->hasDate($cursor, $now)) {
                    $dayStart  = $cursor->setTime(...explode(':', $a->startTime));
                    $dayFinish = $cursor->setTime(...explode(':', $a->endTime));
                    $this->cal->openRange(
                        $scopeId,
                        $dayStart ->format('Y-m-d H:i:s'),
                        $dayFinish->format('Y-m-d H:i:s')
                    );
                }
                $cursor = $cursor->modify('+1 day');
            }
        }
        $this->log("Status 'free' für Scope $scopeId gesetzt");
    }
}
