<?php
namespace BO\Zmsentities;

class Calendar extends Schema\Entity
{
    const PRIMARY = 'days';

    public static $schema = "calendar.json";

    public function getDefaults()
    {
        return [
            'calendar' => [],
            'days' => [],
            'clusters' => [],
            'providers' => [],
            'scopes' => [],
            'requests' => []
        ];
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
     * Returns calendar with first and last day
     *
     * @return $this
     */
    public function addFirstAndLastDay($firstDay, $lastDay)
    {
        $firstDay = $this->getDayByDateTime(Helper\DateTime::create($firstDay));
        $lastDay = $this->getDayByDateTime(Helper\DateTime::create($lastDay));
        $this->firstDay = array('year' => $firstDay->year, 'month' => $firstDay->month, 'day' => $firstDay->day);
        $this->lastDay = array('year' => $lastDay->year, 'month' => $lastDay->month, 'day' => $lastDay->day);
        return $this;
    }

    /**
     * Returns a list of associated scope ids
     *
     * @return array
     */
    public function getScopeList()
    {
        $list = array();
        foreach ($this->scopes as $scope) {
            $list[] = $scope['id'];
        }
        return $list;
    }

    /**
     * Returns a list of associated provider ids
     *
     * @return array
     */
    public function getProviderList()
    {
        $list = array();
        foreach ($this->providers as $provider) {
            $list[] = $provider['id'];
        }
        return $list;
    }

    /**
     * Returns a list of contained month given by firstDay and lastDay
     * The return value is a DateTime object for the first day of the month
     *
     * @return [\DateTime]
     */
    public function getMonthList()
    {
        $startDate = new \DateTime();
        $firstDay = $this->getFirstDay();
        $lastDay = $this->getLastDay();
        $startDate->setDate($firstDay->format('Y'), $firstDay->format('m'), $firstDay->format('d'));
        $endDate = new \DateTime();
        $endDate->setDate($lastDay->format('Y'), $lastDay->format('m'), $lastDay->format('d'));
        $currentDate = $startDate;
        if ($startDate->getTimestamp() > $endDate->getTimestamp()) {
            // swith first and last day if necessary
            $currentDate = $endDate;
            $endDate = $startDate;
        }
        $endDate = Helper\DateTime::create($endDate->format('Y-m-t'));
        $endDate->modify('23:59:59');
        $monthList = [];
        do {
            $monthList[] = Helper\DateTime::create($currentDate->format('Y-m-1'));
            $currentDate->modify('+1 month');
        } while ($currentDate->getTimestamp() <= $endDate->getTimestamp());
        return $monthList;
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
        $day = new Day([
            'year' => $year,
            'month' => $month,
            'day' => $dayNumber
        ]);
        $this['days'][] = $day;
        return $day;
    }

    public function getDayByDateTime(\DateTimeInterface $datetime)
    {
        return $this->getDay($datetime->format('Y'), $datetime->format('m'), $datetime->format('d'));
    }

    public function getDateTimeFromDate($date)
    {
        $date = Helper\DateTime::createFromFormat('Y-m-d', $date['year']. '-'. $date['month'] .'-'. $date['day']);
        return Helper\DateTime::create($date);
    }

    public function getFirstDay()
    {
        if (isset($this['firstDay'])) {
            $dateTime = $this->getDateTimeFromDate(array(
                'year' => $this['firstDay']['year'],
                'month' => $this['firstDay']['month'],
                'day' => $this['firstDay']['day']
            ));
        } else {
            $dateTime = Helper\DateTime::create();
        }
        return $dateTime->modify('00:00:00');
    }

    public function getLastDay()
    {
        if (isset($this['lastDay'])) {
            $dateTime = $this->getDateTimeFromDate(array(
                'year' => $this['lastDay']['year'],
                'month' => $this['lastDay']['month'],
                'day' => $this['lastDay']['day']
            ));
        } else {
            $dateTime = Helper\DateTime::create();
        }
        return $dateTime->modify('00:00:00');
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

    public function addFreeProcess(Process $process)
    {
        $exists = false;
        foreach ($process->appointments as $appointment) {
            foreach ($this->freeProcesses as $key => $freeProcess) {
                if ($appointment && false !== $freeProcess->hasAppointment($appointment)) {
                    $this->freeProcesses[$key]->addAppointment($appointment);
                    $exists = true;
                }
            }
        }
        if (false === $exists) {
            $this->freeProcesses[] = $process;
        }
        return $this;
    }
}
