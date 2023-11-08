<?php

namespace BO\Zmsdb\Helper;

class CalculateDayOff
{
    protected $dayOffList = [
        'Y-01-01' => 'Neujahr',
        'Y-03-08' => 'Internationaler Frauentag',
        'E-2'    => 'Karfreitag',
        'E+0'    => 'Ostersonntag',
        'E+1'    => 'Ostermontag',
        'Y-05-01' => 'Maifeiertag',
        'E+39'   => 'Christi Himmelfahrt',
        'E+50'   => 'Pfingstmontag',
        'Y-10-03' => 'Tag der Deutschen Einheit',
        #'Y-12-24' => 'Heiligabend',
        'Y-12-25' => '1. Weihnachtstag',
        'Y-12-26' => '2. Weihnachtstag',
        #'Y-12-31' => 'Silvester'
    ];

    protected $dateEaster;

    protected $dateFormat = 'Y-m-d';

    protected $verbose = false;

    protected $targetYear;

    public function __construct($targetYear, $verbose = false)
    {
        $this->dateTime = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $this->targetYear = $targetYear;
        $this->verbose = $verbose;
    }

    protected function calculateDayOffByYear($year)
    {
        $collection = new \BO\Zmsentities\Collection\DayoffList();
        $dateEaster = $this->calculateEaster($year);
        foreach ($this->dayOffList as $dateExpr => $description) {
            if (strpos($dateExpr, 'E') === 0) {
                $dateExpr = ltrim($dateExpr, 'E');
                $dtCurr = clone $dateEaster;
                $date = $dtCurr->modify($dateExpr.' day')->format($this->dateFormat);
                $entity = new \BO\Zmsentities\Dayoff([
                    'name' => $description,
                    'date' => (new \DateTimeImmutable($date))->getTimestamp()
                ]);
            } else {
                $date = $dateEaster->format($dateExpr);
                $entity = new \BO\Zmsentities\Dayoff([
                    'name' => $description,
                    'date' => (new \DateTimeImmutable($date))->getTimestamp()
                ]);
            }
            $collection->addEntity($entity);
        }
        return $collection;
    }

    protected function calculateEaster($year)
    {
        $date = clone $this->dateTime;
        if ($year) {
            $date = $date->setDate($year, $date->format('m'), $date->format('d'));
        }
        $easterDate = \easter_date($date->format('Y'));
        
        return $date->setTimestamp($easterDate);
    }

    public function writeDayOffListUntilYear($commit = false, $fromnow = false)
    {
        $query = new \BO\Zmsdb\DayOff();
        $list = $this->readDayoffList($query, $this->targetYear);

        if ($fromnow) {
            for ($loopYear = $this->dateTime->format('Y'); $loopYear < $this->targetYear; $loopYear++) {
                $collection = $this->readDayoffList($query, $loopYear);
                $list->addList($collection);
            }
        }
        if ($commit) {
            $query->writeCommonDayoffsByYear($list, null, false);
        }
        if ($this->verbose) {
            return $list->sortByCustomKey('date');
        }
    }
    
    protected function readDayoffList($query, $year)
    {
        $collection = $query->readCommonByYear($year);
        $newDayOffList = $this->calculateDayOffByYear($year);
        $collection = $collection->withNew($newDayOffList);
        return $collection;
    }
}
