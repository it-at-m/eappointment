<?php
namespace BO\Zmsentities\Collection;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(PublicMethod)
 *
 */
class QueueList extends Base implements \BO\Zmsentities\Helper\NoSanitize
{
    const ENTITY_CLASS = '\BO\Zmsentities\Queue';

    const FAKE_WAITINGNUMBER = -1;

    protected $processTimeAverage;

    protected $workstationCount;

    protected $transferedProcessList = false;

    public function setWaitingTimePreferences($processTimeAverage, $workstationCount)
    {
        if ($processTimeAverage <= 0) {
            throw new \Exception("QueueList::withEstimatedWaitingTime() requires processTimeAverage");
        }
        $this->processTimeAverage = $processTimeAverage;
        $this->workstationCount = $workstationCount;
        return $this;
    }

    public function setTransferedProcessList($bool = true)
    {
        $this->transferedProcessList = $bool;
        return $this;
    }

    public function getProcessTimeAverage()
    {
        return $this->processTimeAverage;
    }

    public function getWorkstationCount()
    {
        return $this->workstationCount ? $this->workstationCount : 1;
    }

    public function withEstimatedWaitingTime(
        $processTimeAverage,
        $workstationCount,
        \DateTimeInterface $dateTime,
        $createFake = true
    ) {
        $this->setWaitingTimePreferences($processTimeAverage, $workstationCount);
        $queueFull = $this->withWaitingTime($dateTime);
        $queueWithWaitingTime = $queueFull->withStatus(['called']);
        $queueFull = $queueFull->withoutStatus(['called']);
        if ($createFake) {
            $queueFull = $queueFull->withFakeWaitingnumber($dateTime);
        }
        $listWithAppointment = $queueFull->withAppointment()->withSortedArrival()->getArrayCopy();
        $listNoAppointment = $queueFull->withOutAppointment()->withSortedArrival()->getArrayCopy();
        $nextWithAppointment = array_shift($listWithAppointment);
        $nextNoAppointment = array_shift($listNoAppointment);
        $currentTime = $dateTime->getTimestamp() + 120;
        $optimisticTime = $dateTime->getTimestamp();

        $waitingTime = 0;
        $waitingTimeOpt = 0;
        $timeSlot = ($workstationCount) ? $processTimeAverage * 60 / $workstationCount : $processTimeAverage * 60;
        $timeSlotOptimistic = $timeSlot * 0.8;
        while ($nextWithAppointment || $nextNoAppointment) {
            if ($nextWithAppointment && $currentTime >= $nextWithAppointment->arrivalTime) {
                $nextWithAppointment->waitingTimeEstimate = $waitingTime + 1;
                $nextWithAppointment->waitingTimeOptimistic =
                    floor(($nextWithAppointment->arrivalTime - $dateTime->getTimestamp()) / 60);
                if ($optimisticTime >= $nextWithAppointment->arrivalTime) {
                    $nextWithAppointment->waitingTimeOptimistic = $waitingTimeOpt;
                    $nextWithAppointment->waitingTimeEstimate = $waitingTime;
                }
                $optimisticTime += $timeSlotOptimistic;
                $queueWithWaitingTime->addEntity($nextWithAppointment);
                $nextWithAppointment = array_shift($listWithAppointment);
            } elseif ($nextNoAppointment) {
                $nextNoAppointment->waitingTimeEstimate = $waitingTime;
                $nextNoAppointment->waitingTimeOptimistic = $waitingTimeOpt;
                $queueWithWaitingTime->addEntity($nextNoAppointment);
                $nextNoAppointment = array_shift($listNoAppointment);
                $optimisticTime += $timeSlotOptimistic;
            }
            $currentTime += $timeSlot;
            $waitingTime = (int)ceil(($currentTime - $dateTime->getTimestamp()) / 60);
            $waitingTimeOpt = (int)floor(($optimisticTime - $dateTime->getTimestamp()) / 60);
        }
        return $queueWithWaitingTime;
    }

    public function withWaitingTime(\DateTimeInterface $dateTime)
    {
        $queueList = clone $this;
        $timestamp = $dateTime->getTimestamp();
        foreach ($queueList as $entity) {
            if ($timestamp > $entity->arrivalTime) {
                $entity->waitingTime = floor(($timestamp - $entity->arrivalTime) / 60);
            }
        }
        return $queueList;
    }

    public function withSortedArrival()
    {
        $queueList = clone $this;
        $queueList->uasort(function ($first, $second) {
            $firstSort = sprintf("%011d%011d", $first['arrivalTime'], $first['number']);
            //error_log($firstSort);
            $secondSort = sprintf("%011d%011d", $second['arrivalTime'], $second['number']);
            return strcmp($firstSort, $secondSort);
        });
        return $queueList;
    }

    public function withSortedWaitingTime()
    {
        $queueList = clone $this;
        return $queueList->sortByCustomKey('waitingTimeEstimate');
    }

    public function withAppointment()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->withAppointment) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    public function withOutAppointment()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if (! $entity->withAppointment) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    public function getEstimatedWaitingTime($processTimeAverage, $workstationCount, \DateTimeInterface $dateTime)
    {
        $queueList = $this->withFakeWaitingnumber($dateTime);
        $queueList = $queueList
          ->withEstimatedWaitingTime($processTimeAverage, $workstationCount, $dateTime);
        $newEntity = $queueList->getFakeOrLastWaitingnumber();
        $dataOfFackedEntity = array(
            'amountBefore' => $queueList->getQueuePositionByNumber($newEntity->number),
            'waitingTimeEstimate' => $newEntity->waitingTimeEstimate
        );
        return $dataOfFackedEntity;
    }

    public function withFakeWaitingnumber(\DateTimeInterface $dateTime)
    {
        $queueList = clone $this;
        $entity = new \BO\Zmsentities\Queue();
        $entity->number = self::FAKE_WAITINGNUMBER;
        $entity->status = 'fake';
        $entity->withAppointment = false;
        $entity->destination = (string)$this->getProcessTimeAverage();
        $entity->destinationHint = (string)$this->getWorkstationCount();
        $entity->arrivalTime = $dateTime->getTimestamp();
        $queueList->addEntity($entity);
        return $queueList;
    }

    public function getFakeOrLastWaitingnumber()
    {
        $entity = $this->getQueueByNumber(self::FAKE_WAITINGNUMBER);
        if (!$entity) {
            $entity = end($this);
        }
        return $entity;
    }

    public function getQueueByNumber($number)
    {
        foreach ($this as $entity) {
            if ($entity->number == $number) {
                return $entity;
            }
        }
        return null;
    }

    public function getNextProcess(\DateTimeInterface $dateTime, $exclude = null)
    {
        $excludeNumbers = explode(',', $exclude);
        $queueList = clone $this;
        // sort by waiting time to get realistic next process
        $queueList = $queueList
            ->withStatus(['confirmed', 'queued'])
            ->withEstimatedWaitingTime(10, 1, $dateTime, false)
            ->getArrayCopy()
            ;
        $next = array_shift($queueList);
        $currentTime = $dateTime->getTimestamp();
        while ($next) {
            if (! in_array($next->number, $excludeNumbers) &&
                (0 == $next->lastCallTime || ($next->lastCallTime + (5 * 60)) <= $currentTime)
            ) {
                return $next->getProcess();
            }
            $next = array_shift($queueList);
        }
        return null;
    }

    public function getQueuePositionByNumber($number)
    {
        $list = array_values($this->getArrayCopy());
        foreach ($list as $key => $entity) {
            if ($entity->number == $number) {
                return $key;
            }
        }
        return null;
    }

    public function getCountWithWaitingTime()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->waitingTime || ! $entity->withAppointment) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    /**
     * @param array $statusList of possible strings in process.status
     *
     */
    public function withStatus(array $statusList)
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->toProperty()->status->isAvailable() && in_array($entity->status, $statusList)) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    /**
     * @param array $statusList of excepted strings in process.status
     *
     */
    public function withoutStatus(array $statusList)
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->toProperty()->status->isAvailable() && ! in_array($entity->status, $statusList)) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    public function withShortNameDestinationHint(\BO\Zmsentities\Cluster $cluster, \BO\Zmsentities\Scope $scope)
    {
        $queueList = clone $this;
        $list = new self();
        foreach ($queueList as $entity) {
            if ($cluster->shortNameEnabled && $scope->shortName) {
                $entity->destinationHint = $scope->shortName;
            }
            $list->addEntity($entity);
        }
        $listWithPickups = $list->withPickupDestination($scope);
        return $listWithPickups;
    }

    public function withPickupDestination(\BO\Zmsentities\Scope $scope)
    {
        $queueList = clone $this;
        $list = new self();
        foreach ($queueList as $entity) {
            if (! $entity->toProperty()->destination->get()) {
                $entity->destination = $scope->toProperty()->preferences->pickup->alternateName->get();
            }
            $list->addEntity($entity);
        }
        return $list;
    }

    public function toProcessList()
    {
        $processList = new ProcessList();
        foreach ($this as $queue) {
            $process = $queue->getProcess();
            if ($process) {
                $processList->addEntity($process);
            }
        }
        return $processList;
    }

    public function withoutDublicates()
    {
        $list = new self();
        $exists = [];
        foreach ($this as $entity) {
            $key = "$entity->number-$entity->arrivalTime-$entity->withAppointment";
            if (!isset($exists[$key])) {
                $list[] = $entity;
                $exists[$key] = true;
            }
        }
        return $list->withSortedArrival(); // Cloning with this function
    }

    public function getWaitingNumberList()
    {
        $list = [];
        foreach ($this as $entity) {
            $list[] = $entity->number;
        }
        return $list;
    }

    public function getWaitingNumberListCsv()
    {
        return implode(',', $this->getWaitingNumberList());
    }
}
