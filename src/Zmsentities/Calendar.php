<?php

namespace BO\Zmsentities;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(TooManyPublicMethods)
 */
class Calendar extends Schema\Entity
{
    const PRIMARY = 'days';

    public static $schema = "calendar.json";

    public function getDefaults()
    {
        return [
            'days' => [ ],
            'clusters' => [ ],
            'providers' => [ ],
            'scopes' => [ ],
            'requests' => [ ]
        ];
    }

    public function addDates($date, \DateTimeInterface $now, $timeZone)
    {
        $validDate = \BO\Mellon\Validator::value($date)->isDate();
        $date = (! $validDate->hasFailed()) ? $validDate->getValue() : $now->format('U');
        $this->addFirstAndLastDay($date, $timeZone);
        return $this;
    }

    /**
     * Returns calendar with first and last day
     *
     * @return $this
     */
    public function addFirstAndLastDay($date, $timeZone)
    {
        $timeZone = new \DateTimeZone($timeZone);
        $dateTime = $this->getDateTimeFromTs($date, $timeZone);
        $firstDay = $dateTime->setTime(0, 0, 0);
        $lastDay = $dateTime->modify("+1 Month")->modify('last day of this month')->setTime(23, 59, 59);
        $this->firstDay = array (
            'year' => $firstDay->format('Y'),
            'month' => $firstDay->format('m'),
            'day' => $firstDay->format('d')
        );
        $this->lastDay = array (
            'year' => $lastDay->format('Y'),
            'month' => $lastDay->format('m'),
            'day' => $lastDay->format('d')
        );
        return $this;
    }

    /**
     * Returns calendar with added Providers
     *
     * @return $this
     */
    public function addProvider($source, $idList)
    {
        foreach (explode(',', $idList) as $id) {
            $provider = new Provider();
            $provider->source = $source;
            $provider->id = $id;
            $this->providers[] = $provider;
        }
        return $this;
    }

    /**
     * Returns calendar with added Clusters
     *
     * @return $this
     */
    public function addCluster($idList)
    {
        foreach (explode(',', $idList) as $id) {
            $cluster = new Cluster();
            $cluster->id = $id;
            $this->clusters[] = $cluster;
        }
        return $this;
    }

    /**
     * Returns calendar with added requests
     *
     * @return $this
     */
    public function addRequest($source, $requestList)
    {
        foreach (explode(',', $requestList) as $id) {
            $request = new Request();
            $request->source = $source;
            $request->id = $id;
            $this->requests[] = $request;
        }
        return $this;
    }

    /**
     * Returns a list of associated scope ids
     *
     * @return array
     */
    public function getScopeList()
    {
        $scopeList = new \BO\Zmsentities\Collection\ScopeList();
        foreach ($this->scopes as $scope) {
            $scope = new Scope($scope);
            $scopeList->addEntity($scope);
        }
        return $scopeList;
    }

    /**
     * Returns a list of associated provider ids
     *
     * @return array
     */
    public function getProviderList()
    {
        $list = array ();
        foreach ($this->providers as $provider) {
            $list[] = $provider['id'];
        }
        return $list;
    }

    /**
     * Returns a day by given year, month and daynumber
     *
     * @return \ArrayObject
     */
    public function getDay($year, $month, $dayNumber)
    {
        foreach ($this['days'] as $key => $day) {
            if ($day['year'] == $year && $day['month'] == $month && $day['day'] == $dayNumber) {
                if (! ($day instanceof Day)) {
                    $day = new Day($day);
                    $this['days'][$key] = $day;
                }
                return $day;
            }
        }
        $day = new Day(
            [
                'year' => $year,
                'month' => $month,
                'day' => $dayNumber
            ]
        );
        $this['days'][] = $day;
        return $day;
    }

    public function getDayByDateTime(\DateTimeInterface $datetime)
    {
        return $this->getDay($datetime->format('Y'), $datetime->format('m'), $datetime->format('d'));
    }

    public function getDateTimeFromDate($date)
    {
        $day = (isset($date['day'])) ? $date['day'] : 1;
        $date = Helper\DateTime::createFromFormat('Y-m-d', $date['year'] . '-' . $date['month'] . '-' . $day);
        return Helper\DateTime::create($date);
    }

    public function getFirstDay()
    {
        if (isset($this['firstDay'])) {
            $dateTime = $this->getDateTimeFromDate(
                array (
                    'year' => $this['firstDay']['year'],
                    'month' => $this['firstDay']['month'],
                    'day' => $this['firstDay']['day']
                )
            );
        } else {
            $dateTime = Helper\DateTime::create();
        }
        return $dateTime->modify('00:00:00');
    }

    public function getLastDay()
    {
        if (isset($this['lastDay'])) {
            $dateTime = $this->getDateTimeFromDate(
                array (
                    'year' => $this['lastDay']['year'],
                    'month' => $this['lastDay']['month'],
                    'day' => $this['lastDay']['day']
                )
            );
        } else {
            $dateTime = Helper\DateTime::create();
        }
        return $dateTime->modify('23:59:59');
    }

    public function getDateTimeFromTs($timestamp, $timezone = null)
    {
        $dateTime = new Helper\DateTime('@' . $timestamp);
        $dateTime = $dateTime->setTimezone($timezone);
        $dateTime = Helper\DateTime::create($dateTime);
        return $dateTime;
    }

    /**
     * Check if given day exists in calendar
     *
     * @return bool
     */
    public function hasDay($year, $month, $dayNumber)
    {
        foreach ($this['days'] as $day) {
            if ($day['year'] == $year && $day['month'] == $month && $day['day'] == $dayNumber) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a list of contained month given by firstDay and lastDay
     * The return value is a month entity object for the first day of the month
     *
     * @return [\DateTime]
     */
    public function getMonthList()
    {
        $firstDay = $this->getFirstDay()->modify('first day of this month')->modify('00:00:00');
        $lastDay = $this->getLastDay()->modify('last day of this month')->modify('23:59:59');
        $currentDate = $firstDay;
        if ($firstDay->getTimestamp() > $lastDay->getTimestamp()) {
            // swith first and last day if necessary
            $currentDate = $lastDay;
            $lastDay = $firstDay;
        }
        $this->getDay($firstDay->format('Y'), $firstDay->format('m'), $firstDay->format('d'));
        $this->getDay($lastDay->format('Y'), $lastDay->format('m'), $lastDay->format('d'));
        $monthList = new Collection\MonthList();
        $dayList = new Collection\DayList($this->days);
        do {
            $startDow = date('w', mktime(0, 0, 0, $currentDate->format('m'), 1, $currentDate->format('Y')));
            $month = (new Month(
                array(
                    'year' => $currentDate->format('Y'),
                    'month' => $currentDate->format('m'),
                    'calHeadline' => strftime('%B %Y', $currentDate->getTimestamp()),
                    'startDow' => ($startDow == 0) ? 6 : $startDow - 1, // change for week start with monday on 0,
                    'days' => $dayList->withAssociatedDays($currentDate->format('m'))
                )
            ));
            $monthList->addEntity($month);
            $currentDate = $currentDate->modify('+1 month');
        } while ($currentDate->getTimestamp() <= $lastDay->getTimestamp());
        return $monthList;
    }

    public function getMonthListWithStatedDays(\DateTimeInterface $now)
    {
        $monthList = new Collection\MonthList();
        if ($this->toProperty()->days->get()) {
            foreach ($this->getMonthList() as $month) {
                $monthList->addEntity($month->getWithStatedDayList($now));
            }
        }
        return $monthList;
    }
}
