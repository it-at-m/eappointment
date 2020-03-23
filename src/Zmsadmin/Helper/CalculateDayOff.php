<?php

namespace BO\Zmsadmin\Helper;

class CalculateDayOff 
{
    protected $dayOffList = [
        'Y-01-01' => 'Neujahr',
        'E-2'    => 'Karfreitag',
        'E+0'    => 'Ostersonntag',
        'E+1'    => 'Ostermontag',
        'Y-05-01' => 'Maifeiertag',
        'E+39'   => 'Christi Himmelfahrt',
        'E+50'   => 'Pfingstmontag',
        'Y-10-03' => 'Tag der Deutschen Einheit',
        'Y-12-24' => 'Heiligabend',
        'Y-12-25' => '1. Weihnachtstag',
        'Y-12-26' => '2. Weihnachtstag',
        'Y-12-31' => 'Silvester'
    ];

    protected $dateEaster;

    protected $dateFormat = 'Y-m-d';

    public function __construct()
    {
        $this->dateTime = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
    }

    public function calculateDayOffByYear($year = null)
    {
        $collection = new \BO\Zmsentities\Collection\DayoffList();
        $dateEaster = $this->calculateEaster($year);
        foreach ($this->dayOffList as $dateExpr => $description) {
            if ( strpos($dateExpr, 'E') === 0 ) {
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

    public function writeDayOffListUntilYear($year)
    {
        for ($loopYear = $this->dateTime->format('Y'); $loopYear <= $year; $loopYear++) {
            $collection = $this->calculateDayOffByYear($year);
            \App::$http->readPostResult('/dayoff/'. $loopYear .'/', $collection);
        }
    }
    
}