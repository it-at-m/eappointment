<?php

namespace BO\Zmsentities;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(TooManyPublicMethods)
 * @SuppressWarnings(Complexity)
 */
class Calendar extends Schema\Entity
{
    const PRIMARY = 'days';

    public static $schema = "calendar.json";

    public function getDefaults()
    {
        return [
            'firstDay' => new Day(),
            'lastDay' => null,
            'days' => new Collection\DayList(),
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
        if (! $this->toProperty()->firstDay->day->get()) {
            $this->addFirstAndLastDay($date, $timeZone);
        }
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
        $dateTime = Helper\DateTime::create()->setTimezone($timeZone)->setTimestamp($date);
        $firstDay = $dateTime->setTime(0, 0, 0);
        $lastDay = $dateTime->modify('last day of next month')->setTime(23, 59, 59);
        $this->firstDay = array(
            'year' => (int) $firstDay->format('Y'),
            'month' => (int) $firstDay->format('m'),
            'day' => (int) $firstDay->format('d')
        );
        $this->lastDay = array(
            'year' => (int) $lastDay->format('Y'),
            'month' => (int) $lastDay->format('m'),
            'day' => (int) $lastDay->format('d')
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
            if ($id) {
                $provider = new Provider();
                $provider->source = $source;
                $provider->id = $id;
                $this->providers[] = $provider;
            }
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
            if ($id) {
                $request = new Request();
                $request->source = $source;
                $request->id = $id;
                $this->requests[] = $request;
            }
        }
        return $this;
    }

    /**
     * Returns calendar with added scopes
     *
     * @return $this
     */
    public function addScope($scopeList)
    {
        foreach (explode(',', $scopeList) as $id) {
            if ($id) {
                $scope = new Scope();
                $scope->id = $id;
                $this->scopes[] = $scope;
            }
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
        if (isset($this->scopes)) {
            foreach ($this->scopes as $scope) {
                $scope = new Scope($scope);
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList;
    }

    /**
     * Returns a list of associated request entities
     *
     * @return array
     */
    public function getRequestList()
    {
        $requestList = new \BO\Zmsentities\Collection\RequestList();
        foreach ($this->requests as $request) {
            $request = new Request($request);
            $requestList->addEntity($request);
        }
        return $requestList;
    }

    /**
     * Returns a list of associated provider ids
     *
     * @return array
     */
    public function getProviderList()
    {
        $providerList = new \BO\Zmsentities\Collection\ProviderList();
        foreach ($this->providers as $provider) {
            $entity = new Provider($provider);
            $providerList->addEntity($entity);
        }
        return $providerList;
    }

    public function getDayList()
    {
        if (!$this->days instanceof Collection\DayList) {
            $this->days = new Collection\DayList($this->days);
        }
        return $this->days->setSortByDate();
    }

    /**
     * Check if given day exists in calendar
     *
     * @return bool
     */
    public function hasDay($year, $month, $dayNumber)
    {
        return $this->getDayList()->hasDay($year, $month, $dayNumber);
    }

    /**
     * Returns a day by given year, month and daynumber
     *
     * @return \ArrayObject
     */
    public function getDay($year, $month, $dayNumber)
    {
        return $this->getDayList()->getDay($year, $month, $dayNumber);
    }

    public function getDayByDateTime(\DateTimeInterface $datetime)
    {
        return $this->getDayList()->getDayByDateTime($datetime);
    }

    public function getDateTimeFromDate($date)
    {
        $day = (isset($date['day'])) ? $date['day'] : 1;
        $date = Helper\DateTime::createFromFormat('Y-m-d', $date['year'] . '-' . $date['month'] . '-' . $day);
        return Helper\DateTime::create($date);
    }

    /**
     * Simple quick check, if first and last day are defined
     *
     */
    public function hasFirstAndLastDay()
    {
        if (!$this->toProperty()->firstDay->day->get()) {
            return false;
        }
        if (!$this->toProperty()->lastDay->day->get()) {
            return false;
        }
        return true;
    }

    public function getFirstDay()
    {
        if (isset($this['firstDay'])) {
            $dateTime = $this->getDateTimeFromDate(
                array(
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

    public function getLastDay($createIfNotProvided = true)
    {
        if (! $createIfNotProvided && ! isset($this['lastDay'])) {
            return null;
        }

        if (isset($this['lastDay'])) {
            $dateTime = $this->getDateTimeFromDate(
                array(
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

    public function setLastDayTime($date)
    {
        $day = new Day();
        $day->setDateTime($date);
        $this['lastDay'] = $day;
        return $this;
    }

    public function setFirstDayTime($date)
    {
        $day = new Day();
        $day->setDateTime($date);
        $this['firstDay'] = $day;
        return $this;
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
            // switch first and last day if necessary
            $currentDate = $lastDay;
            $lastDay = $firstDay;
        }
        $monthList = new Collection\MonthList();
        do {
            $monthList->addEntity(Month::createForDateFromDayList($currentDate, $this->days));
            $currentDate = $currentDate->modify('+1 month');
        } while ($currentDate->getTimestamp() < $lastDay->getTimestamp());
        return $monthList;
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        $entity = clone $this;

        foreach ($entity['scopes'] as $scope) {
            if ($scope->toProperty()->provider->data->isAvailable()) {
                $payment = $scope->toProperty()->provider->data->payment->get();
                unset($scope['provider']['data']);
                $scope['provider']['data'] = ['payment' => $payment];
                unset($scope['dayoff']);
                unset($scope['status']);
                unset($scope['preferences']);
            }
        }
        foreach ($entity['days'] as $day) {
            if (isset($day['allAppointments'])) {
                unset($day['allAppointments']);
            }
        }
        unset($entity['providers']);
        unset($entity['clusters']);
        unset($entity['freeProcesses']);
        return $entity;
    }

    public function withFilledEmptyDays()
    {
        $entity = clone $this;

        $firstDay = $this->getFirstDay()->modify('first day of this month')->modify('00:00:00');
        $lastDay = $this->getLastDay()->modify('last day of this month')->modify('23:59:59');
        $currentDate = $firstDay;
        $dayList = new Collection\DayList($entity->days);

        do {
            $day = new Day([
                'year' => $currentDate->format('Y'),
                'month' => $currentDate->format('m'),
                'day' => $currentDate->format('d')
            ]);
            $dayTimestamp = $day->toDateTime()->getTimestamp();
            $dayFound = false;

            foreach ($dayList as $checkingDay) {
                $checkingTimestamp = $checkingDay->toDateTime()->getTimestamp();
                if ($checkingTimestamp === $dayTimestamp) {
                    $dayFound = true;
                }
            }

            if (!$dayFound) {
                $dayList->addEntity($day);
            }

            $currentDate = $currentDate->modify('+1 day');
        } while ($currentDate->getTimestamp() < $lastDay->getTimestamp());

        $entity->days = $dayList;

        return $entity;
    }

    public function __toString()
    {
        $string = '';
        foreach ($this->days as $day) {
            $day = ($day instanceof Day) ? $day : new Day($day);
            $string .= "$day\n";
        }
        foreach ($this->scopes as $scope) {
            $scope = ($scope instanceof Scope) ? $scope : new Scope($scope);
            $string .= "$scope\n";
        }
        return $string;
    }
}
